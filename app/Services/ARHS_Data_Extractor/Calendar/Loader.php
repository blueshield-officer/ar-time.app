<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

use App\Services\DBC;
use App\Services\PageDownloader;

class Loader
{
    private static $tableData = [
        'calendar_schedules' => ['date', 'schedule', 'description', 'testDay', 'school'],
        'calendar_events' => ['date', 'title', 'description', 'code', 'type', 'important', 'md5_key'],
        'calendar_grade_off' => ['date', '9', '10', '11', '12'],
        'calendar_sports' => ['date', 'sport', 'gender', 'side', 'varsity', 'opponent', 'description', 'time', 'location', 'special', 'md5_key'],
        'calendar_education' => ['date', 'quarter', 'status'],
    ];
    private static function html_isMonthEmpty($html){
        if (is_null($html->find('#events_list_partial .no-events', 0))){
            return false;
        }
        return true;
    }
    private static function html_exist($html)
    {
        if ($html->hasClass('empty')){
            return false;
        }
        return true;
    }
    private static function html_eventsToArray($html){
        $tempArray = [];
        $eventArray = $html->find('.day-event-info');
        foreach ($eventArray as $element){
            $event = FormattingType::analyzable($element->find('.event-title-wrap .event-link', 0)->plaintext);
            $description = trim(str_replace('events', '', FormattingType::analyzable($element->find('.dateloc', 0)->plaintext)));
            if ($description === ''){
                $description = null;
            }
            $tempArray[] = [
                'event' => ucwords($event),
                'description' => $description
            ];
        }
        return $tempArray;
    }
    private static function html_sportEventsToArray($html): array
    {
        // TODO КЭФТЕМЕ, чёт слишком сложно или что, надо подумать крч
        $tempArray = [];
        $md5key = [];
        $eventArray = $html->find('.day-event-info');
        foreach ($eventArray as $element){
            // creating key
            $md5key[] = $element;

            // getting string and removing TBA
            $event = trim($element->find('.event-title-wrap .event-link', 0)->plaintext);
            $event = str_replace('Time: TBA -', '', $event);

            // extracting SPECIAL if it exists
            $patternForSpecial = '/(^\w+(?=:))/';
            preg_match($patternForSpecial, $event, $matches);
            if (!empty($matches)){
                $special = $matches[0];
            }
            else {
                $special = null;
            }
            // formatting page to normal format without trash
            $event = explode('Archbishop Ryan High School', $event)[1];

            // extraction data from string
            $patternsForData = '/(\w{2,3})\s+([BG])\s+(\w{1,2})\s+(Home|Away)\s+([\w.\s-]+)(?:\((.+?)\))?/';
            if (preg_match($patternsForData, $event, $matches)){
                $sport = $matches[1];
                $gender = $matches[2];
                $varsity = $matches[3];
                $side = $matches[4];
                $opponent = trim($matches[5]);
                $description = $matches[6] ?? null;;
            }
            else {
                $tempArray[] = 'Error in parsing data from string';
                continue;
            }
            $tempDescription = explode('(', trim(preg_replace('/\s+/', ' ', str_replace('Athletic Calendar', '', $element->find('.dateloc', 0)->plaintext))));
            $time = $tempDescription[0];
            if (isset($tempDescription[1])){
                $location = str_replace(')', '', $tempDescription[1]);
            }
            else {
                $location = null;
            }
            $tempArray[] = [
                'sport' => $sport,
                'gender' => $gender,
                'side' => $side,
                'varsity' => $varsity,
                'opponent' => $opponent,
                'description' => $description,
                'time' => $time,
                'location' => $location,
                'special' => $special
            ];
        }
        $md5key = md5(join('', $md5key));
        foreach ($tempArray as $key => $element){
            $tempArray[$key]['md5_key'] = $md5key;
        }
        return $tempArray;
    }
    static private function nestedArray(array $array): bool {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false; // No arrays found
    }
    private static function keep_update($DBC, $date)
    {
        $result = $DBC->fetch($DBC->query('SELECT `keep_update` FROM `calendar_schedules` WHERE `date` = ?', [$date]));
        if ($result !== false){
            $result = $result['keep_update'];
            if ($result === 1){
                return true;
            }
            return false;
        }
        else {
            return true;
        }
    }
    static private function tableUpdate($DBC, $table, $data, $date, $difficult): void
    {
        $date = $date->format('Y-m-d');
        // old data
        $fields = join(', ', array_map(fn($element) => '`' . $element . '`', self::$tableData[$table]));
        $query = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE date >= ?';
        $oldSchedule = $DBC->fetchAll($DBC->query($query, [$date]));
        $oldData = [];
        if ($difficult === true){
            foreach ($oldSchedule as $item) {
                $oldData[$item['date']][] = $item;
            }
        }
        else {
            foreach ($oldSchedule as $item) {
                $oldData[$item['date']] = $item;
            }
        }
        // finding new data
        $newData = [];
        foreach ($data as $index => $element){
            if (isset($oldData[$index])){
                if ($oldData[$index] !== $element){
                    $newData[] = $element;
                }
            }
            else {
                $newData[] = $element;
            }
        }
        echo '<pre>';
        print_r($newData);
        echo '</pre>';
        if (count($newData) > 0) {
            // finding dates to delete
            $datesToDelete = array_map(function ($element) use ($difficult) {
                return $difficult ? reset($element)['date'] : $element['date'];
            }, $newData);;
            // deleting data
            $placeholders = join(', ', array_fill(0, count($datesToDelete), '?'));
            $query = 'DELETE FROM ' . $table . ' WHERE `date` IN ('.$placeholders.')';
            $DBC->query($query, array_values($datesToDelete));
            // writing down new data
            $params = self::$tableData[$table];
            $fields = array_map(fn($element) => '`' . $element . '`', $params);
            $query = 'INSERT INTO ' . $table . ' (' . join(', ', $fields) . ') VALUES ('. join(', ', array_fill(0, count($fields), '?')) .')';
            foreach ($newData as $element){
                if (self::nestedArray($element)){
                    foreach ($element as $item){
                        $values = array_map(fn($field) => $item[$field] ?? null, $params);
                        $DBC->query($query, array_values($values));
                    }
                }
                else {
                    $values = array_map(fn($field) => $element[$field] ?? null, $params);
                    $DBC->query($query, array_values($values));
                }
            }
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
            if (self::html_isMonthEmpty($monthHTML)){
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

        // MOST IMPORTANT ARRAY
        // TODO FID day
        foreach ($dateListHTML->getList() as $date => $htmlElement) {
            $dayInfo = new DayInfo($date);
            // check "keep_update"
            if (self::keep_update($DBC, $date) === false) {
                $dayInfo->setNoUpdate();
            }
            $md5key = [];
            // DOES OBJECT HAVE ELEMENTS CHECK
            if (self::html_exist($htmlElement)) foreach (self::html_eventsToArray($htmlElement) as $event) {
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
                // CHECK IS IT DANCE
                if (Detectors::isDance($str)){
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green', 'dance');
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // RETREATS CHECK
                $retreat = Detectors::isRetreat($str);
                if ($retreat !== false && !$dataRecorded) {
                    $dayNoGrade = new DayNoGrade($date, $retreat);
                    $gradeOffArray[$dayNoGrade->getDate()] = $dayNoGrade->getInfo();
                    if (str_contains($str, 'retreat')) {
                        // starts with small letter for some reason
                        $daySpecial = new DaySpecial($date, $strOriginal, $description, 'red', 'retreat');
                        self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                        $dayInfo->setSchedule('special');
                        $dayInfo->setSchool();
                    }
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // QUARTER CHECK
                $quarter = Detectors::isQuarter($str);
                if ($quarter !== false && !$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'yellow', 'quarter');
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
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
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green', 'dressdown');
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
                if (self::html_isMonthEmpty($monthHTML)){
                    $calendar->skipMonth($date);
                    continue;
                }
            }
            $day = $date->format('d');
            $dateListHTML->add($year, $month, $day, $monthHTML->find('#events_list_partial #day-' . (int)$day, 0));
            $date->modify('+1 day');
        };


        foreach ($dateListHTML->getList() as $index => $html) {
            if (self::html_exist($html)) {
                $dataObject = self::html_sportEventsToArray($html);
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