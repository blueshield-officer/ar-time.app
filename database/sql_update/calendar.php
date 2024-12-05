<?php
if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}
function calendarHTML($month, $year)
{
    $url = 'https://www.archbishopryan.com/apps/events/' . $year . '/' . $month . '/print/?id=0';
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

    $f = fopen(__DIR__ . '/../data-txt/calendar_' . $month . '_' . $year . '.txt', "w");
    fwrite($f, $data);
    fclose($f);
    return file_get_html(__DIR__ . '/../data-txt/calendar_' . $month . '_' . $year . '.txt');
}

class DateInfo
{
    private $scheduleBox;
    private $scheduleText;
    private $secondEvent;
    private $thirdEvent;
    private $schedule;
    private $description;
    private $date;
    private $day;
    private $month;
    private $year;
    private $testDay;

    private $special;

    function __construct($dateBox, $day, $month, $year)
    {
        $this->scheduleBox = $dateBox;
        $this->scheduleText = trim(strtolower($dateBox->find('.day-event-info .event-title-wrap .event-link', 0)->plaintext));
        $this->secondEvent = null;
        $this->thirdEvent = null;
        $this->schedule = null;
        $this->description = null;
        $this->date = (DateTime::createFromFormat('Y-m-d', $year.'-'.$month.'-'.$day))->format('Y-m-d');
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
        $this->testDay = null;
        $this->special = null;
        $this->bellsHTML = $this->bell_schedulesTXT();

        $this->additionalEvents();
    }

    public function analysis(): array
    {
        // no school days (hard function end)
        $jd = gregoriantojd($this->month, $this->day, $this->year);
        if (jddayofweek($jd, 0) == 0 || jddayofweek($jd, 0) == 6) {
            return $this->noSchoolClass();
        }
        if (!str_starts_with($this->scheduleText, '*') || str_contains($this->scheduleText, 'school closed') || str_contains($this->scheduleText, 'no classes') && !str_contains($this->scheduleText, 'grade')) {
            return $this->noSchoolClass();
        }
        // 12.11.2024
        $this->scheduleText = trim(substr($this->scheduleText, 1));
        // retreats (special update + hard function end)
        if (str_contains($this->scheduleText, 'retreat')) {
            $this->specialAdder('special schedule');
            $this->specialAdder($this->scheduleText);
            $this->specialAdder($this->secondEvent);
            return $this->retreat();
        }
        // no classes for some grades (special update)
        foreach ([$this->secondEvent, $this->thirdEvent] as $element) {
            if (!is_null($element) && str_contains($element, 'no classes') && str_contains($element, 'grade')) {
                $this->specialAdder($element);
            }
        }
        // test day Checker
        for ($i = 1; $i <= 6; $i++) {
            if (str_contains($this->scheduleText, 'day ' . $i)) {
                $this->testDay = $i;;
                break;
            }
        }
        // finding schedule (schedule update)
        if (str_contains($this->scheduleText, 'special schedule')) {
            $this->schedule = "Special";
            $this->specialAdder('special schedule');
        } else if (substr($this->scheduleText, 1, 1) == 'pep rally schedule') {
            $this->schedule = 9;
            $this->specialAdder('pep rally');
        } else {
            $this->schedule = match (substr($this->scheduleText, 0, 1)) {
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
            if (is_null($this->schedule)) {
                $this->schedule = 'Error';
                $this->specialAdder('fatal error');
            } else if (is_numeric($this->schedule)) {
                $descriptionFinder = $this->schedule - 1;
                $this->description = $this->bellsHTML->find('.bell-schedule .bell-schedule-description', $descriptionFinder)->plaintext;
            }
        }
        return $this->returnData();
    }

    private function returnData(): array
    {
        return [
            'schedule' => $this->schedule,
            'description' => $this->description,
            'date' => $this->date,
            'testDay' => $this->testDay,
            'special' => $this->special,
            'school' => true
        ];
    }

    private function bell_schedulesTXT()
    {
        return file_get_html(__DIR__ . '/../data-txt/bell_schedules.txt');
    }

    private function specialAdder($alert)
    {
        if (is_null($this->special)) {
            $this->special = [];
        }
        switch ($alert) {
            case 'special schedule':
                $code = 'red';
                $text = 'Unfortunately, we cannot access data for special schedules, so we are unable to provide table data or countdowns. Please refer to the schedule sent by the principal via Remind or Gmail for this day.';
                break;
            case 'pep rally':
                $code = 'yellow';
                $text = 'Please note that the Pep Rally Schedule may differ from the actual P Schedule. Some data may not be accurate. Before relying on this information, we recommend checking the latest updates sent by the principal via Remind or Gmail.';
                break;
            case 'fatal error':
                $code = 'gamma';
                $text = 'Our system was unable to detect the schedule, so we cannot provide accurate information. We also cannot confirm whether it is a school day or not. Please refer to the original calendar on the ARHS website. We will update the database as soon as possible.';
                break;
            case str_contains($alert, 'no classes') && str_contains($alert, 'grade'):
                $code = 'red';
                $text = ucwords($alert);
                break;
            case str_contains($alert, 'retreat'):
                $code = 'red';
                $text = ucwords($alert);
                break;
            default:
                $code = 'test';
                $text = 'test';
        }
        $this->special[] = [
            "code" => $code,
            "text" => $text
        ];
    }

    private function additionalEvents()
    {
        $this->secondEvent = $this->scheduleBox->find('.day-event-info .event-title-wrap .event-link', 1);
        if (!is_null($this->secondEvent)) {
            $this->secondEvent = trim(strtolower($this->secondEvent->plaintext));
        }
        $this->thirdEvent = $this->scheduleBox->find('.day-event-info .event-title-wrap .event-link', 2);
        if (!is_null($this->thirdEvent)) {
            $this->thirdEvent = trim(strtolower($this->thirdEvent->plaintext));
        }
    }

    private function retreat(): array
    {
        return [
            'schedule' => 'Special',
            'description' => null,
            'date' => $this->date,
            'testDay' => null,
            'special' => $this->special,
            'school' => true
        ];
    }

    private function noSchoolClass(): array
    {
        return [
            'schedule' => null,
            'description' => null,
            'date' => $this->date,
            'testDay' => null,
            'special' => null,
            'school' => false,
        ];
    }

    static function noSchool($day, $month, $year): array
    {
        return [
            'schedule' => null,
            'description' => null,
            'date' => (DateTime::createFromFormat('Y-m-d', $year.'-'.$month.'-'.$day))->format('Y-m-d'),
            'testDay' => null,
            'special' => null,
            'school' => false,
        ];
    }
}

function starterFunction($calendarHTML, $day, $month, $year)
{
    $dateArray = [];
    for ($i = 1; $i <= 9; $i++) {
        if (checkdate($month, $day, $year)) {
            $dateBox = $calendarHTML->find('#day-' . $day, 0);
            if (!$dateBox->hasClass('empty')) {
                $myData = new DateInfo($dateBox, $day, $month, $year);
                $dateArray[] = $myData->analysis();
            } else {
                $dateArray[] = DateInfo::noSchool($day, $month, $year);
            }
            $day += 1;
        } else {
            $day = 1;
            $month += 1;
            if ($month > 12) {
                $month = 1;
                $year += 1;
            }
            $calendarHTML = calendarHTML($month, $year);
            $i -= 1;
        }
    }
    return $dateArray;
}

function classesSQL($pdo, $dateArray)
{
    foreach ($dateArray as $element) {
        var_dump($element['date']);
        $query = 'SELECT * FROM `calendar` WHERE date = ?';
        $statement = $pdo->prepare($query);
        $statement->execute([$element['date']]);
        if ($statement->fetch() === false) {
            $query = 'INSERT INTO `calendar` (`schedule`, `description`, `date`, `testDay`, `special`, `school`) VALUES (?, ?, ?, ?, ?, ?)';
            $statement = $pdo->prepare($query);
            if (!is_null($element['special'])) {
                $element['special'] = json_encode($element['special']);
            }
            $statement->execute([$element['schedule'], $element['description'], $element['date'], $element['testDay'], $element['special'], $element['school']]);
        }
    }
}


require __DIR__ . "/../../libs/simplehtmldom/simple_html_dom.php";
require __DIR__ . '/../../app/app.php';

$day = (int)date('j');
$month = (int)date('m');
$year = (int)date('Y');

$dateArray = starterFunction(calendarHTML($month, $year), $day, $month, $year);

classesSQL(App::getPDO(), $dateArray);