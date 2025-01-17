<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

use App\Services\DBC;
use App\Services\PageDownloader;

class Loader
{
    static private function tableUpdate($DBC, $table, $data, $date, $difficult): void
    {
        $date = $date->format('Y-m-d');
        $oldData = Database_Requests::getOldData($DBC, $table, $date, $difficult);
        $newData = Database_Formatters::newData($data, $oldData);
        echo '<pre>';
        print_r($newData);
        echo '</pre>';
        if (count($newData) > 0) {
            $datesToDelete = Database_Formatters::getDays($newData, $difficult);
            Database_Requests::deleteOldData($DBC, $table, $datesToDelete);
            Database_Requests::insertNewData($DBC, $table, $newData);
        }
    }
    static private function dayInfoRecord(&$dayInfoArray, $dayInfo, &$dataRecorded)
    {
        if ($dayInfo->keep_update() === true){
            $dayInfoArray[$dayInfo->getDate()] = $dayInfo->getInfo();
        }
        $dataRecorded = true;
    }
    static private function daySpecialRecord(&$daySpecialArray, $daySpecial, &$md5key, $event, &$dataRecorded)
    {
        $daySpecialArray[$daySpecial->getDate()][] = $daySpecial->getInfo();
        $md5key[] = join('', $event);
        $dataRecorded = true;
    }
    static public function load(){
        $DBC = DBC::getInstance();
        $date_today = new \DateTime('2024-09-01');
        $date = clone $date_today;
//        $date_today = new \DateTime('now');

        // creating date + month and year control
        $month = null;
        $year = null;

        $pageDownloader = new PageDownloader();
        $calendar = new AnalyzerHTML($date, 'schedule', $pageDownloader);
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
        $quarterArray = [];
        // DayInfo record function

        // MOST IMPORTANT ARRAY
        // TODO FID day
        foreach ($dateListHTML->getList() as $date => $htmlElement) {
            $dayInfo = new DayInfo($date);
            // check "keep_update"
            if (Database_Requests::keep_update($DBC, $date) === false) {
                $dayInfo->setNoUpdate();
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

                    $daySpecial = match ($special){
                        'p' => new DaySpecial($date, $strOriginal, 'Please notice that Pep Rally often is a bit different from an actual schedule.', 'yellow'),
                        'fid' => new DaySpecial($date, 'Flexible Instruction Day', 'No school for today, but dont forget about some schoology assignments.', 'green'),
                        'special' => new DaySpecial($date, 'Special Schedule', 'We are unable to provide data for Special Schedule', 'red'),
                        default => new DaySpecial($date, 'Error', 'Error', 'gamma')
                    };
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // SCHEDULE CHECK
                if (Detectors::checkIfHasSchedule($str) && !$dataRecorded) {
                    $dayInfo->setSchedule(Detectors::findSchedule($str));
                    $dayInfo->setTestDay(Detectors::findTestDay($str));
                    $dayInfo->setSchool();

                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // CHECK IS IT SCHOOL DAY
                if (Detectors::isSchoolClosed($str) && !$dataRecorded) {
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // RETREATS CHECK
                $retreat = Detectors::isRetreat($str);
                if ($retreat !== false && !$dataRecorded) {
                    $dayNoGrade = new DayNoGrade($date, $retreat);
                    $gradeOffArray[$dayNoGrade->getDate()] = $dayNoGrade->getInfo();
                    if (str_contains($str, 'retreat')) {
                        // starts with small letter for some reason
                        $daySpecial = new DaySpecial($date, $strOriginal, $description, 'red');
                        self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                        $dayInfo->setSchedule('special');
                        $dayInfo->setSchool();
                    }
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // QUARTER WRITER
                $quarter = Detectors::isQuarter($str);
                if ($quarter !== false && !$dataRecorded) {
                    $dayQuarter = new DayEducation($date, $quarter['quarter'], $quarter['status']);
                    $quarterArray[$dayQuarter->getDate()] = $dayQuarter->getInfo();
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // NO SCHOOL FOR GRADE (REPORT INCLUDED) CHECK
                $gradeOff = Detectors::isGradeOff($str);
                if ($gradeOff !== false && !$dataRecorded) {
                    $dayNoGrade = new DayNoGrade($date, $gradeOff);
                    $gradeOffArray[$dayNoGrade->getDate()] = $dayNoGrade->getInfo();
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // DRESS DOWN DAY CHECK
                if (Detectors::isDressDown($str) && !$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green');
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // RECORD TO SPECIAL IF THERE ARE NO POSITIVE CHECK
                if (!$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description);
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
            }
            else {
                $dayInfo->noData();
                self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
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
        $calendar = new AnalyzerHTML($date, 'sport', $pageDownloader);
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


        $info = [
            [
                'database' => 'calendar_schedules',
                'data' => $dayInfoArray,
                'date' => $date_today,
                'difficult' => false
            ],
            [
                'database' => 'calendar_grade_off',
                'data' => $gradeOffArray,
                'date' => $date_today,
                'difficult' => false
            ],
            [
                'database' => 'calendar_education',
                'data' => $quarterArray,
                'date' => $date_today,
                'difficult' => false
            ],
            [
                'database' => 'calendar_sports',
                'data' => $daySportArray,
                'date' => $date_today,
                'difficult' => true
            ],
            [
                'database' => 'calendar_events',
                'data' => $daySpecialArray,
                'date' => $date_today,
                'difficult' => true
            ],
        ];
        foreach ($info as $element) {
            self::tableUpdate($DBC, $element['database'], $element['data'], $element['date'], $element['difficult']);
        }
    }
}