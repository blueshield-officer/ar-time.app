<?php

use App\Services\DBConnection;

if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}

// errors
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors', 'On');


// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
require_once __DIR__ . "/../../libs/simplehtmldom/simple_html_dom.php";
require __DIR__ . '/../../app/app.php';
$pdo = DBConnection::getPDO();
//  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

class Calendar {
    private $calendarArray;
    private $date;
    private $availableDays;

    function __construct()
    {
        $this->calendarArray = [];
        $this->date = new DateTime('now');
        $this->availableDays = 0;
    }
    function loadCalendarHTML($monthAmount, $type){
        for ($i = 0; $i < $monthAmount; $i++) {
            $this->countAvailableDays($i);
            $month = $this->date->format('m');
            $year = $this->date->format('Y');
            $this->calendarArray[$year . '-' . $month] = $this->calendarHTML($month, $year, $type);
            $this->nextMonth();
        }
    }
    private function nextMonth()
    {
        $this->date = $this->date->modify('first day of +1 month');
    }
    private function calendarHTML($month, $year, $type)
    {
        if ($type === 'schedule') {
            $path = 'schedule_calendar';
            $id = 0;
        }
        else {
            $path = 'sport_calendar';
            $id = 1;
        }
        $url = 'https://www.archbishopryan.com/apps/events/' . $year . '/' . $month . '/print/?id=' . $id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        }
        curl_close($ch);

        $f = fopen(__DIR__ . '/../data-txt/' . $path . '/' . $month . '_' . $year . '.txt', "w");
        fwrite($f, $data);
        fclose($f);
        return file_get_html(__DIR__ . '/../data-txt/' . $path . '/' . $month . '_' . $year . '.txt');
    }
    private function countAvailableDays($i)
    {
        $days = $this->date->format('t');
        if ($i === 0) {
            $todayDay = new DateTime('now');
            $days = $days - (int)$todayDay->format('j') + 1;
        }
        $this->availableDays += $days;
    }
    function getMonthHTML($year, $month)
    {
        return $this->calendarArray[$year . '-' . $month];
    }
    function deductAvailableDays($days)
    {
        $this->availableDays -= $days;
    }
    function getAvailableDays()
    {
        return $this->availableDays;
    }
}

class DateListHTML
{
    private $dateList;

    function __construct()
    {
        $this->dateList = [];
    }

    function add($year, $month, $day, $htmlElement)
    {
        $this->dateList[$year . '-' . $month . '-' . $day] = $htmlElement;
    }
    function getList()
    {
        return $this->dateList;
    }
}


class DayInfo {
    private $date;
    private $schedule = null;
    private $description = null;
    private $testDay = null;
    private $school = 0;

    function __construct($date){
        $this->date = $date;
    }

    private function scheduleToNumber($schedule){
        if (strlen($schedule) === 1){
            $number = match (strtolower($schedule)) {
                'a' => 1,
                'b' => 2,
                'c' => 3,
                'd' => 4,
                'e' => 5,
                'f' => 6,
                'g' => 7,
                'h' => 8,
                'p' => 9,
                'r' => 10,
                't' => 11,
                'v' => 12
            };
            $this->setDescription($number);
            return (string)$number;
        }
        return $schedule;
    }
    private function setDescription($schedule)
    {
        $html = file_get_html(__DIR__ . '/../data-txt/bell_schedules.txt');
        $this->description = $html->find('.bell-schedule .bell-schedule-description', $schedule - 1)->plaintext;
    }

    function setSchedule($schedule)
    {
        $this->schedule = self::scheduleToNumber($schedule);
    }
    function setTestDay($testDay)
    {
        $this->testDay = $testDay;
    }
    function setSchool()
    {
        $this->school = 1;
    }

    function getInfo()
    {
        return [
            'date' => $this->date,
            'schedule' => $this->schedule,
            'description' => $this->description,
            'testDay' => $this->testDay,
            'school' => $this->school
        ];
    }
    function noData()
    {
       $this->schedule = null;
       $this->description = null;
       $this->testDay = null;
       $this->school = 0;
    }
    function getDate()
    {
        return $this->date;
    }
}

class DaySpecial
{
    private $date;
    private $title;
    private $description;
    private $code;
    private $important = 0;

    function __construct($date, $title, $description, $code = null){
        $this->date =  $date;
        $this->title = $title;
        $this->description = $description;
        $this->code = $code;
        if ($code !== null){
            $this->important = 1;
        }
    }
    function getDate()
    {
        return $this->date;
    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'title' => $this->title,
            'description' => $this->description,
            'code' => $this->code,
            'important' => $this->important
        ];
    }
}
class DayNoGrade {
    private $date;
    private $grade;

    function __construct($date, $grade){
        $this->date = $date;
        $this->grade = $grade;
    }
    function getDate(){
        return $this->date;
    }
    function getInfo(){
        return [
            'date' => $this->date,
            'grade' => join(', ', $this->grade)
        ];
    }
}
class SportsDateInfo {
    private $date;
    private $sport;
    private $gender;
    private $side;
    private $varsity;
    private $opponent;
    private $description;
    private $time;
    private $location;
    private $key;

    function __construct($date, $sport, $gender, $side, $varsity, $opponent, $description, $time, $location, $md5key){
        $this->date = $date;
        $this->sport = self::sportUncode($sport);
        $this->gender = $gender;
        $this->side = $side;
        $this->varsity = $varsity;
        $this->opponent = $opponent;
        $this->description = $description;
        $this->time = $time;
        $this->location = $location;
        $this->md5key = $md5key;
    }
    private function sportUncode($sport)
    {
        return match (strtolower($sport)){
            'bkb' => 'Basketball',
            'lax' => 'Lacrosse',
            'vb' => 'Volleyball',
            'bb' => 'Baseball',
            'wrs' => 'Wrestling',
            'sw' => 'Swimming',
            'ih' => 'Ice Hockey',
            'sb' => 'Soccer',
            default => ucwords($sport)
        };
    }
    function getDate()
    {
        return $this->date;
    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'sport' => $this->sport,
            'gender' => $this->gender,
            'side' => $this->side,
            'varsity' => $this->varsity,
            'opponent' => $this->opponent,
            'description' => $this->description,
            'time' => $this->time,
            'location' => $this->location,
            'md5_key' => $this->md5key
        ];
    }
}
class FormattingType {
    static function symbols($str){
        return preg_replace('/[^\p{L}\p{N}\s]/u', '', $str);
    }
    static function noSpaces($str)
    {
        return preg_replace('/\s+/', '', $str);
    }
    static function analyzable($str){
        return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
    }
    static function heavy($str){
        return preg_replace('/\s+/', ' ', trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str)));
    }
}
class Detectors
{
    static function eventsToArray($html){
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
    static function sportEventsToArray($html): array
    {
        $tempArray = [];
        $md5key = [];
        $eventArray = $html->find('.day-event-info');
        foreach ($eventArray as $element){
            // creating key
            $md5key[] = $element;
            // removing TBA time
            $event = trim($element->find('.event-title-wrap .event-link', 0)->plaintext);
            $patterns = ['/Time: TBA -/', '/Archbishop Ryan High School/'];
            $event = trim(preg_replace($patterns, '', $event));

            // extraction data from string
            $patternsForData = '/(\w{2,3})\s+([BG])\s+(\w{1,2})\s+(Home|Away)\s+([\w\s-]+)(?:\((.+?)\))?/';
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
                'location' => $location
            ];
        }
        $md5key = md5(join('', $md5key));
        foreach ($tempArray as $key => $element){
            $tempArray[$key]['md5_key'] = $md5key;
        }
        return $tempArray;
    }

    static function exist($html)
    {
        if ($html->hasClass('empty')){
            return false;
        }
        return true;
    }


    static function isSchoolClosed($str)
    {
        if (str_contains($str, 'school closed')){
            return true;
        }
        if (str_contains($str, 'no classes') && !str_contains($str, 'grade') && !preg_match('/\d/', $str)) {
            return true;
        }
        return false;
    }
    static function checkIfHasSchedule($str)
    {
        if (str_contains($str, 'schedule')){
            return true;
        }
        return false;

    }
    static function findSpecial($str){
        $special = ['special', 'pep rally'];
        foreach ($special as $element){
            if (str_contains($str, $element)){
                if ($element === 'pep rally'){
                    return 'p';
                }
                return $element;
            }
        }
        return false;
    }
    static function findSchedule($str)
    {
        $str = FormattingType::heavy($str);
        $array = explode('schedule', $str);
        $schedule = trim($array[0]);
        return $schedule;
    }
    static function findTestDay($str)
    {
        $str = FormattingType::heavy($str);
        if (str_contains($str, 'day')){
            $array = explode('day', $str);
            $day = (int)$array[count($array) - 1];
            if ($day !== '' && $day !== 0){
                return $day;
            }
            else {
                return null;
            }
        }
        else {
            return null;
        }
    }

    static function isRetreat($str)
    {
        if (str_contains($str, 'no classes') && str_contains($str, 'grades')){
            $str = FormattingType::heavy(str_replace('-', 6, $str));
            $gradesArray = explode('grades', $str);
            $grades = (int)$gradesArray[count($gradesArray) - 1];
            $gradesArray = array_map('intval', explode(6, $grades));
            if (count($gradesArray) === 3){
                return $gradesArray;
            }
            else {
                return false;
            }
        }
        if (str_contains($str, 'retreat')){
            $classes = ['freshman', 'sophomore', 'junior', 'senior'];
            foreach ($classes as $element){
                if (str_contains($str, $element)){
                    return array_diff([9, 10, 11, 12], [match ($element){
                        'freshman' => 9,
                        'sophomore' => 10,
                        'junior' => 11,
                        'senior' => 12,
                        'default' => 'error'
                    }]);
                }
            }
        }
        return false;
    }
    static function isGradeOff($str)
    {
        $regularGradeOff = str_contains($str, 'no classes') && str_contains($str, 'grade');
        $reportGradeOff = str_contains($str, 'report') && str_contains($str, 'grade');
        if ($regularGradeOff || $reportGradeOff){
            $tempArray = explode('grade', $str);
            $str = str_replace('-', 6, $tempArray[count($tempArray) - 1]);
            $str = str_replace('and', 6, $str);
            $numericStr = (preg_replace('/\D/', '', $str));
            $gradeArray = explode(6, $numericStr);

            if ($regularGradeOff){
                return array_intersect([9, 10, 11, 12], $gradeArray);
            }
            else {
                return array_diff([9, 10, 11, 12], $gradeArray);
            }
        }
        return false;
    }
    static function isDressDown($str)
    {
        if (str_contains($str, 'dress down')){
            return true;
        }
        return false;
    }
}
class DataBaseFormatters {
    static function containsArray(array $array): bool {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false; // No arrays found
    }
    static function newData ($newArray, $oldArray)
    {
        $array = [];
        foreach ($newArray as $index => $element){
            if (isset($oldArray[$index])){
                if ($oldArray[$index] !== $element){
                    $array[] = $element;
                }
            }
            else {
                $array[] = $element;
            }
        }
        return $array;
    }
    static function getDays ($array, $difficult)
    {
        return array_map(function ($element) use ($difficult) {
            return $difficult ? reset($element)['date'] : $element['date'];
        }, $array);
    }
}
class DataBaseRequests {
    static function getOldData($pdo, $database, $dateStart, $difficult){
        $select = match ($database){
            'schedule_calendar_test' => '`date`, `schedule`, `description`, `testDay`, `school`',
            'event_calendar_test' => '`date`, `title`, `description`, `code`, `important`, `md5_key`',
            'grade_off_test' => '`date`, `grade`',
            'sport_calendar_test' => '`date`, `sport`, `gender`, `side`, `varsity`, `opponent`, `description`, `time`, `location`, `md5_key`'
        };
        $query = 'SELECT ' . $select . ' FROM ' . $database . ' WHERE date >= ?';
        $statement = $pdo->prepare($query);
        $statement->execute([$dateStart]);
        $oldSchedule = $statement->fetchAll();
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
        return $oldData;
    }
    static function insertNewData($pdo, $database, $data){
        $queries = [
            'schedule_calendar_test' => [
                'sql' => 'INSERT INTO schedule_calendar_test (`date`, `schedule`, `description`, `testDay`, `school`) VALUES (?, ?, ?, ?, ?)',
                'fields' => ['date', 'schedule', 'description', 'testDay', 'school']
            ],
            'grade_off_test' => [
                'sql' => 'INSERT INTO grade_off_test (`date`, `grade`) VALUES (?, ?)',
                'fields' => ['date', 'grade']
            ],
            'event_calendar_test' => [
                'sql' => 'INSERT INTO event_calendar_test (`date`, `title`, `description`, `code`, `important`, `md5_key`) VALUES (?, ?, ?, ?, ?, ?)',
                'fields' => ['date', 'title', 'description', 'code', 'important', 'md5_key']
            ],
            'sport_calendar_test' => [
                'sql' => 'INSERT INTO sport_calendar_test (`date`, `sport`, `gender`, `side`, `varsity`, `opponent`, `description`, `time`, `location`, `md5_key`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'fields' => ['date', 'sport', 'gender', 'side', 'varsity', 'opponent', 'description', 'time', 'location', 'md5_key']
            ]
        ];
        $query = $queries[$database]['sql'];
        $fields = $queries[$database]['fields'];

        $statement = $pdo->prepare($query);
        foreach ($data as $element){
            if (DataBaseFormatters::containsArray($element)){
                foreach ($element as $item){
                    $values = array_map(fn($field) => $item[$field] ?? null, $fields);
                    $statement->execute(array_values($values));
                }
            }
            else {
                $values = array_map(fn($field) => $element[$field] ?? null, $fields);
                $statement->execute(array_values($values));
            }
        }
    }
    static function deleteOldData($pdo, $database, $dateArray){
        $placeholders = join(', ', array_fill(0, count($dateArray), '?'));
        $query = 'DELETE FROM ' . $database . ' WHERE `date` IN ('.$placeholders.')';
        $statement = $pdo->prepare($query);
        $statement->execute($dateArray);
    }
}


// POLYGON ----------------

// POLYGON ----------------

// getting HTML
$calendar = new Calendar();
$calendar->loadCalendarHTML(5, 'schedule');


// creating date + month and year control
$date = new DateTime('now');
$month = null;
$year = null;

// removing today's day and tomorrows too if time > 15:00
if ($date->format('H:i') > '15:00') {
    $calendar->deductAvailableDays(2);
    $date->modify('+2 day');
}
else {
    $calendar->deductAvailableDays(1);
    $date->modify('+1 day');
}
$dateStart = $date->format('Y-m-d');


// finding HTML object and adding them to array
$dateListHTML = new DateListHTML();
for ($i = 0; $i < $calendar->getAvailableDays(); $i++) {
    $newMonth = $date->format('m');
    $newYear = $date->format('Y');
    if ($month !== $newMonth || $year !== $newYear) {
        $monthHTML = $calendar->getMonthHTML($newYear, $newMonth);
        $month = $newMonth;
        $year = $newYear;
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
function dayInfoRecord(&$dayInfoArray, $dayInfo, &$dataRecorded){
    $dayInfoArray[$dayInfo->getDate()] = $dayInfo->getInfo();
    $dataRecorded = true;
}
function daySpecialRecord(&$daySpecialArray, $daySpecial, &$md5key, $event, &$dataRecorded)
{
    $daySpecialArray[$daySpecial->getDate()][] = $daySpecial->getInfo();
    $md5key[] = join('', $event);
    $dataRecorded = true;
}

// MOST IMPORTANT ARRAY
foreach ($dateListHTML->getList() as $date => $htmlElement) {
    $dayInfo = new DayInfo($date);
    $md5key = [];
    // DOES OBJECT HAVE ELEMENTS CHECK
    if (Detectors::exist($htmlElement)) foreach (Detectors::eventsToArray($htmlElement) as $event){
        $dataRecorded = false;
        $strOriginal = trim($event['event']);
        $str = FormattingType::analyzable($event['event']);
        $description = $event['description'];

        // Special Check
        $special = Detectors::findSpecial($str);
        if ($special !== false && !$dataRecorded){
            $dayInfo->setSchedule($special);
            $dayInfo->setTestDay(Detectors::findTestDay($str));
            $dayInfo->setSchool();

            if ($special === 'p'){
                $daySpecial = new DaySpecial($date, $strOriginal, 'Please notice that Pep Rally often is a bit different from an actual schedule.', 'yellow');
            }
            else if ($special === 'special'){
                $daySpecial = new DaySpecial($date, 'Special Schedule', 'We are unable to provide data for Special Schedule', 'red');
            }
            else {
                $daySpecial = new DaySpecial($date, 'Error', 'Error', 'gamma');
            }
            daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
            dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
        }
        // SCHEDULE CHECK
        if (Detectors::checkIfHasSchedule($str) && !$dataRecorded){
            $dayInfo->setSchedule(Detectors::findSchedule($str));
            $dayInfo->setTestDay(Detectors::findTestDay($str));
            $dayInfo->setSchool();

            dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
        }
        if (Detectors::isSchoolClosed($str) && !$dataRecorded){
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
        if ($gradeOff !== false && !$dataRecorded){
            $dayNoGrade = new DayNoGrade($date, $gradeOff);
            $gradeOffArray[$dayNoGrade->getDate()] = $dayNoGrade->getInfo();
            dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
        }
        // DRESS DOWN DAY CHECK
        if (Detectors::isDressDown($str) && !$dataRecorded){
            $daySpecial = new DaySpecial($date, $strOriginal, $description, 'green');
            daySpecialRecord($daySpecialArray, $daySpecial, $md5key, $event, $dataRecorded);
            dayInfoRecord($dayInfoArray, $dayInfo, $dataRecorded);
        }
        // RECORD TO SPECIAL IF THERE ARE NO POSITIVE CHECK
        if (!$dataRecorded){
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
    if (isset($daySpecialArray[$date])){
        foreach ($daySpecialArray[$date] as $key => $element){
            $daySpecialArray[$date][$key]['md5_key'] = $md5key;
        }
    }
}



// SPORT CALENDAR
// getting HTML
$calendar = new Calendar();
$calendar->loadCalendarHTML(12, 'sport');

$date = new DateTime('now');
$month = null;
$year = null;

// finding HTML object and adding them to array
$dateListHTML = new DateListHTML();
for ($i = 0; $i < $calendar->getAvailableDays(); $i++) {
    $newMonth = $date->format('m');
    $newYear = $date->format('Y');
    if ($month !== $newMonth || $year !== $newYear) {
        $monthHTML = $calendar->getMonthHTML($newYear, $newMonth);
        $month = $newMonth;
        $year = $newYear;
        $isThereData = $monthHTML->find('#events_list_partial .no-events', 0);
        if (!is_null($isThereData)) {
            break;
        }
    }
    $day = $date->format('d');
    $dateListHTML->add($year, $month, $day, $monthHTML->find('#events_list_partial #day-' . (int)$day, 0));
    $date->modify('+1 day');
};


foreach ($dateListHTML->getList() as $index => $html) {
    if (Detectors::exist($html)){
        $dataObject = Detectors::sportEventsToArray($html);
        foreach ($dataObject as $element){
            $sportObject = new SportsDateInfo(
                $index,
                $element['sport'],
                $element['gender'],
                $element['side'],
                $element['varsity'],
                $element['opponent'],
                $element['description'],
                $element['time'],
                $element['location'],
                $element['md5_key']);
            $daySportArray[$sportObject->getDate()][] = $sportObject->getInfo();
        }
    }
}



// MAKE IT A FUNCTIONS
// DATABASE schedule_calendar_test
function databaseUpdate($pdo, $database, $data, $date, $difficult): void
{
    $oldData = DataBaseRequests::getOldData($pdo, $database, $date, $difficult);
    $newData = DataBaseFormatters::newData($data, $oldData);
    echo '<pre>';
    print_r($newData);
    echo '</pre>';
    if (count($newData) > 0) {
        $datesToDelete = DatabaseFormatters::getDays($newData, $difficult);
        DataBaseRequests::deleteOldData($pdo, $database, $datesToDelete);
        DatabaseRequests::insertNewData($pdo, $database, $newData);
    }
}
$info = [
    [
        'database' => 'schedule_calendar_test',
        'data' => $dayInfoArray,
        'date' => $dateStart,
        'difficult' => false
    ],
    [
        'database' => 'event_calendar_test',
        'data' => $daySpecialArray,
        'date' => $dateStart,
        'difficult' => true
    ],
    [
        'database' => 'grade_off_test',
        'data' => $gradeOffArray,
        'date' => $dateStart,
        'difficult' => false
    ],
    [
        'database' => 'sport_calendar_test',
        'data' => $daySportArray,
        'date' => (new DateTime('now'))->format('Y-m-d'),
        'difficult' => true
    ]
];
foreach ($info as $element){
    databaseUpdate($pdo, $element['database'], $element['data'], $element['date'], $element['difficult']);
}


//
//
//$oldSchedule = DataBaseRequests::getOldData($pdo, 'schedule_calendar_test', $dateStart);
//$newScheduleData = DataBaseFormatters::newData($dayInfoArray, $oldSchedule);
//if (count($newScheduleData) > 0){
//    $datesToDelete = DatabaseFormatters::getDays($newScheduleData);
//    DataBaseRequests::deleteOldData($pdo, 'schedule_calendar_test', $datesToDelete);
//    DatabaseRequests::insertNewData($pdo, 'schedule_calendar_test', $newScheduleData);
//}
//
//// DATABASE grade_off_test
//$oldGradeOff = DataBaseRequests::getOldData($pdo, 'grade_off_test', $dateStart);
//$newGradeOffData = DataBaseFormatters::newData($gradeOffArray, $oldGradeOff);
//if (count($newGradeOffData) > 0){
//    $datesToDelete = DatabaseFormatters::getDays($newGradeOffData);
//    DataBaseRequests::deleteOldData($pdo, 'grade_off_test', $datesToDelete);
//    DatabaseRequests::insertNewData($pdo, 'grade_off_test', $newGradeOffData);
//}
//
//// DATABASE
//$oldEvents = DataBaseRequests::getOldData($pdo, 'event_calendar_test', $dateStart, true);
//$newEventData = DataBaseFormatters::newData($daySpecialArray, $oldEvents);
//var_dump($daySpecialArray);
//if (count($newEventData) > 0){
//    $datesToDelete = DatabaseFormatters::getDays($newEventData, true);
//    DataBaseRequests::deleteOldData($pdo, 'event_calendar_test', $datesToDelete);
//    DatabaseRequests::insertNewData($pdo, 'event_calendar_test', $newEventData, true);
//}




//// DATABASE event_calendar_test
//// getting old data
//$indexedOldEvents = DataBaseRequests::getOldData($pdo, 'event_calendar_test', $dateStart, true);
//// finding new data
//$newEventData = DatabaseFormatters::newData($indexedOldEvents);
//var_dump($newEventData);


//  NOTES
//  If there are 2 School = 0, while it is work day and there are no mention it about it, futher check will just
//  shut down OR will go without schedules