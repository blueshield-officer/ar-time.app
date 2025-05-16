<?php
namespace App\Services\ARHS_Data_Extractor\Calendar;

use App\Services\ARHS_Data_Extractor\FormattingType;
use App\Services\PageDownloader;
use App\Services\Utils;


//                          IMPORTANT
//
//     REDIS LOGIC
//     Redis is written as data:TABLE_NAME:date = json
//     IF it is difficult array, it would be just FAT json, with few object in it,
//     in common it is just one, for non-difficult one
//




class CalendarManager
{
    private $DBC;
    private $tableData;
    private $date;

    private $dateListHTML;
    private $dataRecorded;


    private $schedules = [];
    private $specials = [];
    private $dayOff = [];
    private $sports = [];
    private $academics = [];
    private $retreats = [];


    public function __construct($DBC)
    {
        $this->DBC = $DBC;
        $this->tableData = [];
        $this->dateListHTML = [];
    }

    public function registerTable($tableName)
    {
        $tableData = $this->DBC->getTableParams($tableName);
        $this->tableData[$tableName] = $tableData['columnsArray'];
    }

    public function loadSimpleDomLib()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/simplehtmldom/simple_html_dom.php';

    }

    public function setDate($date = null)
    {
        if (is_null($date)) {
            $this->date = new \DateTime();
        }
        else {
            $this->date = \DateTime::createFromFormat('Y-m-d', $date);
        }
    }

    public function loadCalendarMonthsHTML($monthAmount, $type){
        $date = clone $this->date;
        $month = null;
        $year = null;

        $pageDownloader = new PageDownloader();
        $calendar = new AnalyzerHTML($date, $type, $pageDownloader);
        $calendar->loadCalendarHTML($monthAmount);


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

        if ($type === 'sport'){
            $this->dateListHTML['sport'] = $dateListHTML;
        }
        else if ($type === 'schedule'){
            $this->dateListHTML['schedule'] = $dateListHTML;
        }
        else {
            throw new ('for DateList_HTML Analyzer set proper type.');
        }
        echo $type;
        echo 'Days loaded: ' . $calendar->getAvailableDays();
        echo ' ';
        echo '<br>';
        echo '<br>';
    }


    public function process($type)
    {
        // MOST IMPORTANT ARRAY
        if ($type === 'schedule') {
            foreach ($this->dateListHTML[$type]->getList() as $date => $htmlElement) {
                $md5key = [];
                // DOES OBJECT HAVE ELEMENTS CHECK
                if (self::html_exist($htmlElement)) foreach (self::html_eventsToArray($htmlElement) as $event) {
                    $this->dataRecorded = false;
                    $strOriginal = trim($event['event']);
                    $str = FormattingType::analyzable($event['event']);
                    $description = $event['description'];

                    // Special Check
                    $special = Detectors::findSpecial($str);
                    if ($special !== false && !$this->dataRecorded) {
                        // make it day off on FID day, maybe more in future
                        if ($special === 'fid') {
                            $dayOff = new DayOff($date, 'all', 100, 'p');
                            self::dayOffRecord($dayOff);
                        }
                        $dayInfo = new DayInfo($date, $special, Detectors::findTestDay($str));
                        self::dayInfoRecord($dayInfo);
                    }
                    // Check SCHEDULE
                    if (Detectors::checkIfHasSchedule($str) && !$this->dataRecorded) {
                        $schedule = Detectors::findSchedule($str);
                        $dayInfo = new DayInfo($date, $schedule, Detectors::findTestDay($str));
                        self::dayInfoRecord($dayInfo);
                    }
                    // Check is it SCHOOL DAY
                    if (Detectors::isSchoolClosed($str) && !$this->dataRecorded) {
                        $this->dataRecorded = false;
                        $dayOff = new DayOff($date, 'all', 100, 'f');
                        self::dayOffRecord($dayOff);
                    }
                    // Check is it DANCE
                    if (Detectors::isDance($str)) {
                        $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green', 'dance');
                        self::dayInfoRecord($dayInfo);
                        self::daySpecialRecord($daySpecial, $md5key, $event);
                    }
                    // Check is it RETREAT
                    $retreatInfo = Detectors::isRetreat($str);
                    if ($retreatInfo !== false && !$this->dataRecorded) {
                        $dayInfo = new DayInfo($date, 'special', null);
                        $dayOff = new DayOff($date, $retreatInfo['dayOffGrades'], 100, 'f');
                        $retreat = new Retreat($date, $retreatInfo['retreatGrade']);
                        self::dayInfoRecord($dayInfo);
                        self::retreatRecord($retreat);
                        self::dayOffRecord($dayOff);
                    }
                    // Check is it QUARTER
                    // TODO DO SOMETHING WITH QUARTEST
//                    $quarter = Detectors::isQuarter($str);
//                    if ($quarter !== false && !$this->dataRecorded) {
//                        $quarter = new Quarter($date, $quarter['quarter'], $quarter['status']);
//                        self::quarterRecord($quarter);
//                    }
                    // Check is it NO SCHOOL FOR GRADE (REPORT INCLUDED)
                    $gradeOff = Detectors::isGradeOff($str);
                    if ($gradeOff !== false && !$this->dataRecorded) {
                        $dayOff = new DayOff($date, $gradeOff, 100, 'f');
                        self::dayOffRecord($dayOff);
                    }
                    // Check is it DRESS DOWN DAY
                    if (Detectors::isDressDown($str) && !$this->dataRecorded) {
                        $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green', 'dressdown');
                        self::daySpecialRecord($daySpecial, $md5key, $event);
                    }
                    // RECORD TO SPECIAL IF THERE ARE NO POSITIVE CHECK
                    if (!$this->dataRecorded) {
                        $daySpecial = new DaySpecial($date, $strOriginal, $description);
                        self::daySpecialRecord($daySpecial, $md5key, $event);
                    }
                }
                // ADDING UNCONFIRMED DAY OFF ( 1% - 99% )
                if (!isset($dayInfo)) {
                    $dateFuture = new \DateTime($date);
                    if ($this->date->getTimestamp() < $dateFuture->getTimestamp()) {
                        $weekDay = (int)date('w', strtotime($date));
                        if ($weekDay !== 0 && $weekDay !== 6) {
                            if (!isset($this->dayOff[$date])) {
                                $timeInterval = $this->date->diff($dateFuture);
                                $hours = $timeInterval->days * 24 + $timeInterval->h;
                                $percentage = self::hoursToPercentage($hours);
                                if ($percentage > 0.01) {
                                    $dayOff = new DayOff($date, 'all', $percentage, 'f');
                                    self::dayOffRecord($dayOff);
                                }
                            }
                        }
                    }
                }
                $md5key = md5(join('', $md5key));
                if (isset($this->specials[$date])) {
                    foreach ($this->specials[$date] as $key => $element) {
                        $this->specials[$date][$key]['md5_key'] = $md5key;
                    }
                }
            }
        } else if ($type === 'sport') {
            foreach ($this->dateListHTML[$type]->getList() as $index => $html) {
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
                        $this->sports[$sportObject->getDate()][] = $sportObject->getInfo();
                    }
                }
            }
        }
    }

    public function updateSQL($table, $difficult)
    {
        $dataPath = [
            'calendar_schedules' => $this->schedules,
            'calendar_sports' => $this->sports,
            'calendar_day_off' => $this->dayOff,
            'calendar_events' => $this->specials,
            'calendar_academics' => $this->academics,
            'calendar_retreats' => $this->retreats,
        ];
        $this->tableUpdate($table, $dataPath[$table], $this->date, $difficult);
    }








    private function html_isMonthEmpty($html){
        if (is_null($html->find('#events_list_partial .no-events', 0))){
            return false;
        }
        return true;
    }

    private function html_exist($html)
    {
        if ($html->hasClass('empty')){
            return false;
        }
        return true;
    }

    private function html_eventsToArray($html){
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

    private function html_sportEventsToArray($html): array
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
            $patternsForData = '/(\w{2,3})\s+([BG])\s+(\w{1,2})\s+(Home|Away)\s+([\w.\'\s-]+)(?:\((.+?)\))?/';
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

    private function findScheduleDefaultDescription($DBC, $schedule)
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
    private function nestedArray(array $array): bool {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false; // No arrays found
    }

    private function keep_update($DBC, $date, $table)
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

    private function tableUpdate($table, $data, $date, $difficult)
    {
        // TODO make something up with keep_update
        $date = $date->format('Y-m-d');
        // old data
        $fieldsArray = $this->tableData[$table];
        // update check
        $fields = Utils::sqlFieldsToStr($fieldsArray);
        $query = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE date >= ?';
        $oldSchedule = $this->DBC->fetchAll($this->DBC->query($query, [$date]));


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
        foreach ($data as $index => $element) {
            if (isset($oldData[$index])) {
                if ($oldData[$index] !== $element) {
                    if ($oldData[$index] === 'no update') {
                        continue;
                    }
                    $newData[] = $element;
                }
            } else {
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
            $this->DBC->query($query, array_values($datesToDelete));

            if (($key = array_search('keep_update', $fieldsArray)) !== false) {
                unset($fieldsArray[$key]);
                $fields = Utils::sqlFieldsToStr($fieldsArray);
            }

            $query = 'INSERT INTO ' . $table . ' (' . $fields . ') VALUES ('. join(', ', array_fill(0, count($fieldsArray), '?')) .')';

            foreach ($newData as $element){
                if (self::nestedArray($element)){
                    foreach ($element as $item){
                        $values = array_map(fn($field) => $item[$field] ?? null, $fieldsArray);
                        $this->DBC->query($query, array_values($values));
                    }
                }
                else {
                    $values = array_map(fn($field) => $element[$field] ?? null, $fieldsArray);
                    $this->DBC->query($query, array_values($values));
                }
            }
            return true;
        }
    }
    private function dayInfoRecord($dayInfo)
    {
        $this->schedules[$dayInfo->getDate()] = $dayInfo->getInfo();
        $this->dataRecorded = true;
    }
    private function daySpecialRecord($daySpecial, &$md5key, $event)
    {
        $this->specials[$daySpecial->getDate()][] = $daySpecial->getInfo();
        $md5key[] = join('', $event);
        $this->dataRecorded = true;
    }
    private function dayOffRecord($dayOff)
    {
        $this->dayOff[$dayOff->getDate()] = $dayOff->getInfo();
        $this->dataRecorded = true;
    }
    private function retreatRecord($retreat)
    {
        $this->retreats[$retreat->getDate()] = $retreat->getInfo();
    }
    private function quarterRecord($quarter)
    {
        $this->academics[$quarter->getDate()] = $quarter->getInfo();
    }
    private function removeOddInfo($array)
    {
        unset($array['date']);
        unset($array['keep_update']);
        return $array;
    }
    private function hoursToPercentage($hours)
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
}