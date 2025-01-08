<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

use App\Services\DB_Connection;

class Loader
{
    static public function load(){
        require_once __DIR__ . "/../../../../libs/simplehtmldom/simple_html_dom.php";

        $pdo = DB_Connection::getPDO();
        $date_today = new \DateTime('2024-09-01');
        $date = clone $date_today;
//        $date_today = new \DateTime('now');

        // creating date + month and year control
        $month = null;
        $year = null;

        $calendar = new GetCalendar_HTML($date, 'schedule');
        $calendar->loadCalendarHTML(12);


        // finding HTML object and adding them to array
        $dateListHTML = new DateList_HTML();
        for ($i = 0; $i < $calendar->getAvailableDays(); $i++) {
            $newMonth = $date->format('m');
            $newYear = $date->format('Y');
            if ($month !== $newMonth || $year !== $newYear) {
                $monthHTML = $calendar->getMonthHTML($newYear, $newMonth);
                $month = $newMonth;
                $year = $newYear;
            }
            if (Detectors::isMonthEmpty($monthHTML)){
                $calendar->skipMonth($date);
                continue;
            }
            $day = $date->format('d');
            $dateListHTML->add($year, $month, $day, $monthHTML->find('#events_list_partial #day-' . (int)$day, 0));
            $date->modify('+1 day');
        }

        echo 'Days loaded: ' . $calendar->getAvailableDays();
        echo ' ';
        echo '<br>';

        // MOST IMPORTANT ARRAY
        $dayInfoArray = [];
        $daySpecialArray = [];
        $gradeOffArray = [];
        $daySportArray = [];
        // DayInfo record function
        function dayInfoRecord(&$dayInfoArray, $dayInfo, &$dataRecorded)
        {
            if ($dayInfo->keep_update() === true){
                $dayInfoArray[$dayInfo->getDate()] = $dayInfo->getInfo();
            }
            $dataRecorded = true;
        }

        function daySpecialRecord(&$daySpecialArray, $daySpecial, &$md5key, $event, &$dataRecorded)
        {
            $daySpecialArray[$daySpecial->getDate()][] = $daySpecial->getInfo();
            $md5key[] = join('', $event);
            $dataRecorded = true;
        }

        // MOST IMPORTANT ARRAY
        // TODO FID day
        foreach ($dateListHTML->getList() as $date => $htmlElement) {
            $dayInfo = new DayInfo($date);
            // check "keep_update"
            if (Database_Requests::keep_update($pdo, $date) === false) {
                $dayInfo->setNoUpdate();
                echo 'no';
            }
            $md5key = [];
            // DOES OBJECT HAVE ELEMENTS CHECK
            if (Detectors::exist($htmlElement)) foreach (Detectors::eventsToArray($htmlElement) as $event) {
                $dataRecorded = false;
                $strOriginal = trim($event['event']);
                $str = FormattingType::analyzable($event['event']);
                $description = $event['description'];


                // Special Check
                $special = Detectors::findSpecial($str);
                if ($special !== false && !$dataRecorded) {
                    $dayInfo->setSchedule($special);
                    $dayInfo->setTestDay(Detectors::findTestDay($str));
                    $dayInfo->setSchool();

                    if ($special === 'p') {
                        $daySpecial = new DaySpecial($date, $strOriginal, 'Please notice that Pep Rally often is a bit different from an actual schedule.', 'yellow');
                    } else if ($special === 'special') {
                        $daySpecial = new DaySpecial($date, 'Special Schedule', 'We are unable to provide data for Special Schedule', 'red');
                    } else {
                        $daySpecial = new DaySpecial($date, 'Error', 'Error', 'gamma');
                    }
                    daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // SCHEDULE CHECK
                if (Detectors::checkIfHasSchedule($str) && !$dataRecorded) {
                    $dayInfo->setSchedule(Detectors::findSchedule($str));
                    $dayInfo->setTestDay(Detectors::findTestDay($str));
                    $dayInfo->setSchool();

                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // CHECK IS IT SCHOOL DAY
                if (Detectors::isSchoolClosed($str) && !$dataRecorded) {
                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }

                // RETREATS CHECK
                $retreat = Detectors::isRetreat($str);
                if ($retreat !== false && !$dataRecorded) {
                    $dayNoGrade = new DayNoGrade($date, $retreat);
                    $gradeOffArray[$dayNoGrade->getDate()] = $dayNoGrade->getInfo();
                    if (str_contains($str, 'retreat')) {
                        // starts with small letter for some reason
                        $daySpecial = new DaySpecial($date, $strOriginal, $description, 'red');
                        daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                        $dayInfo->setSchedule('special');
                        $dayInfo->setSchool();
                    }
                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // NO SCHOOL FOR GRADE (REPORT INCLUDED) CHECK
                $gradeOff = Detectors::isGradeOff($str);
                if ($gradeOff !== false && !$dataRecorded) {
                    $dayNoGrade = new DayNoGrade($date, $gradeOff);
                    $gradeOffArray[$dayNoGrade->getDate()] = $dayNoGrade->getInfo();
                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // DRESS DOWN DAY CHECK
                if (Detectors::isDressDown($str) && !$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green');
                    daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // RECORD TO SPECIAL IF THERE ARE NO POSITIVE CHECK
                if (!$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description);
                    daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
            }
            else {
                $dayInfo->noData();
                dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
            }
            $md5key = md5(join('', $md5key));
            if (isset($daySpecialArray[$date])) {
                foreach ($daySpecialArray[$date] as $key => $element) {
                    $daySpecialArray[$date][$key]['md5_key'] = $md5key;
                }
            }
        }


// SPORT CALENDAR
        $date = clone $date_today;
        $month = null;
        $year = null;

        // getting HTML
        $calendar = new GetCalendar_HTML($date, 'sport');
        $calendar->loadCalendarHTML(12);

// finding HTML object and adding them to array
        $dateListHTML = new DateList_HTML();
        for ($i = 0; $i < $calendar->getAvailableDays(); $i++) {
            $newMonth = $date->format('m');
            $newYear = $date->format('Y');
            if ($month !== $newMonth || $year !== $newYear) {
                $monthHTML = $calendar->getMonthHTML($newYear, $newMonth);
                $month = $newMonth;
                $year = $newYear;
                if (Detectors::isMonthEmpty($monthHTML)){
                    $calendar->skipMonth($date);
                    continue;
                }
            }
            $day = $date->format('d');
            $dateListHTML->add($year, $month, $day, $monthHTML->find('#events_list_partial #day-' . (int)$day, 0));
            $date->modify('+1 day');
        };


        foreach ($dateListHTML->getList() as $index => $html) {
            if (Detectors::exist($html)) {
                $dataObject = Detectors::sportEventsToArray($html);
                foreach ($dataObject as $element) {
                    $sportObject = new DaySports(
                        $index,
                        $element['sport'],
                        $element['gender'],
                        $element['side'],
                        $element['varsity'],
                        $element['opponent'],
                        $element['description'],
                        $element['time'],
                        $element['location'],
                        $element['special'],
                        $element['md5_key']);
                    $daySportArray[$sportObject->getDate()][] = $sportObject->getInfo();
                }
            }
        }


// MAKE IT A FUNCTIONS
// DATABASE schedule_calendar_test
        function databaseUpdate($pdo, $database, $data, $date, $difficult): void
        {
            $date = $date->format('Y-m-d');
            $oldData = Database_Requests::getOldData($pdo, $database, $date, $difficult);
            $newData = Database_Formatters::newData($data, $oldData);
            echo '<pre>';
            print_r($newData);
            echo '</pre>';
            if (count($newData) > 0) {
                $datesToDelete = Database_Formatters::getDays($newData, $difficult);
                Database_Requests::deleteOldData($pdo, $database, $datesToDelete);
                Database_Requests::insertNewData($pdo, $database, $newData);
            }
        }

        $info = [
            [
                'database' => 'schedule_calendar_test',
                'data' => $dayInfoArray,
                'date' => $date_today,
                'difficult' => false
            ],
            [
                'database' => 'event_calendar_test',
                'data' => $daySpecialArray,
                'date' => $date_today,
                'difficult' => true
            ],
            [
                'database' => 'grade_off_test',
                'data' => $gradeOffArray,
                'date' => $date_today,
                'difficult' => false
            ],
            [
                'database' => 'sport_calendar_test',
                'data' => $daySportArray,
                'date' => $date_today,
                'difficult' => true
            ]
        ];
        foreach ($info as $element) {
            databaseUpdate($pdo, $element['database'], $element['data'], $element['date'], $element['difficult']);
        }
    }
}