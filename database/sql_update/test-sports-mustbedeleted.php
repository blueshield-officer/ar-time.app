<?php

use App\Services\DBConnection;

if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}

// errors
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors', 'On');


// TEMP !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
require __DIR__ . "/../../libs/simplehtmldom/simple_html_dom.php";
require __DIR__ . '/../../app/app.php';
$pdo = DBConnection::getPDO();
// TEMP !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

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
    function loadCalendarHTML($monthAmount){
        for ($i = 0; $i < $monthAmount; $i++) {
            $this->countAvailableDays($i);
            $month = $this->date->format('m');
            $year = $this->date->format('Y');
            $this->calendarArray[$year . '-' . $month] = $this->calendarHTML($month, $year);
            $this->nextMonth();
        }
    }
    private function nextMonth()
    {
        $this->date = $this->date->modify('first day of +1 month');
    }
    private function calendarHTML($month, $year)
    {
        $url = 'https://www.archbishopryan.com/apps/events/' . $year . '/' . $month . '/print/?id=1';
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

        $f = fopen(__DIR__ . '/../data-txt/sports_calendar/' . $month . '_' . $year . '.txt', "w");
        fwrite($f, $data);
        fclose($f);
        return file_get_html(__DIR__ . '/../data-txt/sports_calendar/' . $month . '_' . $year . '.txt');
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

class SportsDateInfo {
    private $date;
    private $sport;
    private $gender;
    private $side;
    private $versatility;
    private $opponent;
    private $description;
    private $time;
    private $location;
    private $key;

    function __construct($date, $sport, $gender, $side, $versatility, $opponent, $description, $time, $location, $key){
        $this->date = $date;
        $this->sport = self::sportUncode($sport);
        $this->gender = $gender;
        $this->side = $side;
        $this->versatility = $versatility;
        $this->opponent = $opponent;
        $this->description = $description;
        $this->time = $time;
        $this->location = $location;
        $this->key = $key;
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
    function getInfo()
    {
        return [
            'date' => $this->date,
            'sport' => $this->sport,
            'gender' => $this->gender,
            'side' => $this->side,
            'versatility' => $this->versatility,
            'opponent' => $this->opponent,
            'description' => $this->description,
            'time' => $this->time,
            'location' => $this->location,
            'key' => $this->key
        ];
    }
}

class Detectors
{
    static function exist($html)
    {
        if ($html->hasClass('empty')){
            return false;
        }
        return true;
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
                $versatility = $matches[3];
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
                'versatility' => $versatility,
                'opponent' => $opponent,
                'description' => $description,
                'time' => $time,
                'location' => $location
            ];
        }
        $md5key = md5(join('', $md5key));
        foreach ($tempArray as &$element){
            $element['key'] = $md5key;
        }
        return $tempArray;
    }
}


// getting HTML
$calendar = new Calendar();
$calendar->loadCalendarHTML(12);


// creating date + month and year control
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


// Array operator
$dayInfoArray = [];
foreach ($dateListHTML->getList() as $index => $html) {
    if (Detectors::exist($html)){
        $dataObject = Detectors::sportEventsToArray($html);

        foreach ($dataObject as $element){
            $sportObject = new SportsDateInfo(
                $index, $element['sport'],
                $element['gender'],
                $element['side'],
                $element['versatility'],
                $element['opponent'],
                $element['description'],
                $element['time'],
                $element['location'],
                $element['key']);
            $dayInfoArray[] = $sportObject->getInfo();
        }
    }
}


// writing down data to DB
$query = 'SELECT `md5_key` FROM `sport_calendar_test` WHERE `date` >= ?';
$statement = $pdo->prepare($query);
$statement->execute([(new DateTime('now'))->format('Y-m-d')]);
$result = $statement->fetchAll();


// cleaning old data
$resultKeys = array_unique(array_column($result, 'md5_key'));
$dayInfoKeys = array_unique(array_column($dayInfoArray, 'key'));
$keysToDelete = array_diff($resultKeys, $dayInfoKeys);


if (!empty($keysToDelete)) {
    $placeholders = implode(', ', array_fill(0, count($keysToDelete), '?'));
    $query = "DELETE FROM `sport_calendar_test` WHERE `md5_key` IN ($placeholders)";
    $statement = $pdo->prepare($query);
    $statement->execute(array_values($keysToDelete));
}

// writing down new one
$newEvents = [];
foreach ($dayInfoArray as $element) {
    if (!in_array($element['key'], $resultKeys)) {
        $newEvents[] = $element;
    }
}
if (count($newEvents) > 0){
    foreach ($newEvents as $element){
        $query = 'INSERT INTO `sport_calendar_test` (`date`, `sport`, `gender`, `side`, `versality`, `opponent`, `description`, `time`, `location`, `md5_key`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $pdo->prepare($query);
        $statement->execute([
            $element['date'],
            $element['sport'],
            $element['gender'],
            $element['side'],
            $element['versatility'],
            $element['opponent'],
            $element['description'],
            $element['time'],
            $element['location'],
            $element['key']
        ]);
    }
}

echo 'New <br>';
echo '<pre>';
var_dump($newEvents);
echo '</pre>';
echo '<pre>';
echo 'Deleted <br>';
var_dump($keysToDelete);
echo '</pre>';
