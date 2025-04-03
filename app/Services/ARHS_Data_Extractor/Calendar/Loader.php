<?php
namespace App\Services\ARHS_Data_Extractor\Calendar;

use App\Services\CacheOperator;
use App\Services\DBC;
use App\Services\PageDownloader;
use App\Services\RedisOperator;
use App\Services\Utils;


//                          IMPORTANT
//
//     REDIS LOGIC
//     Redis is written as data:TABLE_NAME:date = json
//     IF it is difficult array, it would be just FAT json, with few object in it,
//     in common it is just one, for non-difficult one
//




class Loader
{
    private static $tableData = [
        'calendar_schedules' => ['date', 'schedule', 'testDay'],
        'calendar_events' => ['date', 'title', 'description', 'time_start', 'time_end', 'location', 'code', 'type', 'important', 'md5_key'],
        'calendar_day_off' => ['date', 'percentage', 'type', '9', '10', '11', '12'],
        'calendar_sports' => ['date', 'sport', 'gender', 'side', 'varsity', 'opponent', 'description', 'time_start', 'time_end', 'location', 'special', 'md5_key'],
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
    static private function findScheduleDefaultDescription($DBC, $schedule)
    {
        if (is_null($schedule)) {
            return null;
        }
        $result = $DBC->fetch($DBC->query('SELECT `description` FROM `schedule_default_descriptions` WHERE `schedule` = ?', [$schedule]));
        if ($result === false) {
            return null;
        }
        return reset($result);
    }
    static private function nestedArray(array $array): bool {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false; // No arrays found
    }
    private static function keep_update($DBC, $date, $table)
    {
        $result = $DBC->fetch($DBC->query('SELECT `keep_update` FROM `' . $table . '` WHERE `date` = ?', [$date]));
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
    static private function tableUpdate($DBC, $table, $data, $date, $difficult)
    {
        // TODO make something up with keep_update
        $date = $date->format('Y-m-d');
        // old data
        $fields = self::$tableData[$table];
        // update check
        if (in_array($table, ['calendar_schedules', 'calendar_day_off'])){
            $fields[] = 'keep_update';
        }
        $fields = join(', ', array_map(fn($element) => '`' . $element . '`', $fields));

        $queryAllTable = 'SELECT ' . $fields . ' FROM ' . $table;
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
                // no update check
                if (isset($item['keep_update'])){
                    if ($item['keep_update'] === 1){
                        unset($item['keep_update']);
                        $oldData[$item['date']] = $item;
                    }
                    else {
                        $oldData[$item['date']] = 'no update';
                    }
                }
                else {
                    $oldData[$item['date']] = $item;
                }
            }
        }

        // finding new data
        $newData = [];
        foreach ($data as $index => $element){
            if (isset($oldData[$index])){
                if ($oldData[$index] !== $element){
                    if ($oldData[$index] === 'no update'){
                        continue;
                    }
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
            return true;
        }
    }
    static private function dayInfoRecord(&$dayInfoArray, $dayInfo, &$dataRecorded)
    {
        $dayInfoArray[$dayInfo->getDate()] = $dayInfo->getInfo();
        $dataRecorded = true;
    }
    static private function daySpecialRecord(&$daySpecialArray, $daySpecial, &$md5key, $event, &$dataRecorded)
    {
        $daySpecialArray[$daySpecial->getDate()][] = $daySpecial->getInfo();
        $md5key[] = join('', $event);
        $dataRecorded = true;
    }
    static private function dayOffRecord(&$dayOffArray, $dayOff, &$dataRecorded)
    {
        $dayOffArray[$dayOff->getDate()] = $dayOff->getInfo();
        $dataRecorded = true;
    }
    static private function removeOddInfo($array)
    {
        unset($array['date']);
        unset($array['keep_update']);
        return $array;
    }
    static private function hoursToPercentage($hours)
    {
        $maxGoodHours = 168;
        $percentagePerHour = 10 / $maxGoodHours;
        $badPercentagePerHourMultiplyer = 0.01;

        // if > 90%
        if ($hours <= $maxGoodHours) {
            $result = 100 - ($percentagePerHour * $hours);
        }
        else {
            $result = 100 - ($percentagePerHour * $maxGoodHours);
            $hours -= $maxGoodHours;
            for ($i = 1; $i <= $hours; $i++) {
                $percentagePerHour += $badPercentagePerHourMultiplyer;
                $result -=  $percentagePerHour;
                if ($result <= 0) {
                    $result = 0.01;
                }
            }
        }
        return round($result, 2);
    }
    static public function load(){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/simplehtmldom/simple_html_dom.php';

        $DBC = DBC::getInstance();

        $dateToday = new \DateTime('2024-09-01');
//        $dateToday = new \DateTime('now');
        $date = clone $dateToday;

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
        $dayOffArray = [];
        $daySportArray = [];
        // DayInfo record function

        // MOST IMPORTANT ARRAY
        foreach ($dateListHTML->getList() as $date => $htmlElement) {
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
                    // make it day off on FID day, maybe more in future
                    if ($special === 'fid') {
                        $dayOff = new DayOff($date, 'all', 100, 'p');
                    }
                    $daySpecial = match ($special) {
                        'p' => new DaySpecial($date, $strOriginal, 'Please notice that Pep Rally often is a bit different from an actual schedule.', 'yellow'),
                        'fid' => new DaySpecial($date, 'Flexible Instruction Day', 'No school for today, but dont forget about some schoology assignments.', 'green'),
                        'special' => new DaySpecial($date, 'Special Schedule', 'We are unable to provide data for Special Schedule', 'red'),
                        default => new DaySpecial($date, 'Error', 'Error', 'gamma'),
                    };
                    $dayInfo = new DayInfo($date, $special, Detectors::findTestDay($str));
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    self::dayOffRecord($dayOffArray, $dayOff, $dataRecorded);

                }
                // Check SCHEDULE
                if (Detectors::checkIfHasSchedule($str) && !$dataRecorded) {
                    $schedule = Detectors::findSchedule($str);
                    $dayInfo = new DayInfo($date, $schedule, Detectors::findTestDay($str));
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                }
                // Check is it SCHOOL DAY
                if (Detectors::isSchoolClosed($str) && !$dataRecorded) {
                    $dataRecorded = false;
                    $dayOff = new DayOff($date, 'all', 100, 'f');
                    self::dayOffRecord($dayOffArray, $dayOff, $dataRecorded);
                }
                // Check is it DANCE
                if (Detectors::isDance($str)){
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green', 'dance');
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                }
                // Check is it RETREAT
                $retreat = Detectors::isRetreat($str);
                if ($retreat !== false && !$dataRecorded) {
                    $dayInfo = new DayInfo($date, 'special', null);
                    $dayOff = new DayOff($date, $retreat, 100, 'f');
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'red', 'retreat');
                    self::dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                    self::dayOffRecord($dayOffArray, $dayOff, $dataRecorded);
                }
                // Check is it QUARTER
                // TODO переместить с спесиалов ваших в норм табличку "calendar_academic"
                $quarter = Detectors::isQuarter($str);
                if ($quarter !== false && !$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'yellow', 'quarter');
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                }
                // Check is it NO SCHOOL FOR GRADE (REPORT INCLUDED)
                $gradeOff = Detectors::isGradeOff($str);
                if ($gradeOff !== false && !$dataRecorded) {
                    $dayOff = new DayOff($date, $gradeOff, 100, 'f');
                    $dayOffArray[$dayOff->getDate()] = $dayOff->getInfo();
                    self::dayOffRecord($dayOffArray, $dayOff, $dataRecorded);
                }
                // Check is it DRESS DOWN DAY
                if (Detectors::isDressDown($str) && !$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green', 'dressdown');
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                }
                // RECORD TO SPECIAL IF THERE ARE NO POSITIVE CHECK
                if (!$dataRecorded) {
                    $daySpecial = new DaySpecial($date, $strOriginal, $description);
                    self::daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
                }
            }
            // ADDING UNCONFIRMED DAY OFF ( 1% - 99% )
            if (!isset($dayInfo)){
                $dateFuture = new \DateTime($date);
                if($dateToday->getTimestamp() < $dateFuture->getTimestamp()) {
                    $weekDay = (int)date('w', strtotime($date));
                    if ($weekDay !== 0 && $weekDay !== 6) {
                        if (!isset($dayOffArray[$date])){
                            $timeInterval = $dateToday->diff($dateFuture);
                            $hours = $timeInterval->days * 24 + $timeInterval->h;
                            $percentage = self::hoursToPercentage($hours);
                            if ($percentage > 0.01) {
                                $dayOff = new DayOff($date, 'all', $percentage, 'f');
                                self::dayOffRecord($dayOffArray, $dayOff, $dataRecorded);
                            }
                        }
                    }
                }
            }
            $md5key = md5(join('', $md5key));
            if (isset($daySpecialArray[$date])) {
                foreach ($daySpecialArray[$date] as $key => $element) {
                    $daySpecialArray[$date][$key]['md5_key'] = $md5key;
                }
            }
        }


        // SPORT CALENDAR
        $date = clone $dateToday;
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


        $info = [
            [
                'database' => 'calendar_schedules',
                'data' => $dayInfoArray,
                'date' => $dateToday,
                'difficult' => false
            ],
            [
                'database' => 'calendar_day_off',
                'data' => $dayOffArray,
                'date' => $dateToday,
                'difficult' => false
            ],
            [
                'database' => 'calendar_sports',
                'data' => $daySportArray,
                'date' => $dateToday,
                'difficult' => true
            ],
            [
                'database' => 'calendar_events',
                'data' => $daySpecialArray,
                'date' => $dateToday,
                'difficult' => true
            ],
        ];
        // SQL update
        foreach ($info as $element) {
            // writing down new Data to SQL
            self::tableUpdate($DBC, $element['database'], $element['data'], $element['date'], $element['difficult']);
        }
    }
}