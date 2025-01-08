<?php

use App\Services\DBConnection;

if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}

require_once __DIR__ . '/../../libs/google/vendor/autoload.php';
date_default_timezone_set('America/New_York');

include __DIR__ . '/../../app/app.php';
$pdo = DBConnection::getPDO();

class Absences
{
    private $pdo;
    private $date;
    private $version;
    private $people = null;
    private $roomChanges = null;

    public function __construct($pdo, $date)
    {
        $this->pdo = $pdo;
        $this->date = $date;
    }
    function setPeople($array)
    {
        $this->people = $array;
    }
    function setRoomChanges($array)
    {
        $this->roomChanges = $array;
    }
    function getPeople()
    {
        if ($this->people === null){
            return null;
        }
        return $this->people;
    }

    function getRoomChanges()
    {
        if ($this->roomChanges === null){
            return null;
        }
        return $this->roomChanges;
    }
}

class Detectors {
    static function findDateIndex($date, $document, $maxRepeat)
    {
        $dateParams = [
            'day' => $date->format('j'),
            'week day' => strtolower($date->format('l')),
            'month' => strtolower($date->format('F')),
            'year' => $date->format('Y')
        ];
        for ($i = 0; $i < $maxRepeat; $i++){
            $str = strtolower($document[$i]);
            foreach ($dateParams as $dateElement){
                if (!str_contains($str, $dateElement)){
                    continue 2;
                }
            }
            return $i;
        }
        return null;
    }
    static function findStartIndex($document, $target, $index, $maxRepeat)
    {
        $target = match ($target){
            't' => 'the following teachers is/are absent today',
            's' => 'the following staff is/are absent today',
            'r' => 'please note the following room changes',
            'end' => '_______________________________________________________________'
        };
        for ($i = 0; $i <= $maxRepeat; $i++){
            $index += 1;
            $str = strtolower($document[$index]);
            if (str_contains($str, $target)){
                return $index;
            }
            else if (str_contains($str, '_______________________________________________________________')){
                return null;
            }
        }
        return null;
    }
    static function roomChangesToArray($array)
    {
        $roomChangesArray = [];
        $period = 0;
        foreach ($array as $str){
            $str = trim(strtolower($str));
            if (str_contains($str, 'period')){
                $period = substr($str, 7, 1);
                $str = substr($str, 8);
                if ($str === ''){
                    continue;
                }
                $roomChangesArray[$period] = [
                    'teacher' => [],
                    'location' => [],
                ];
            }
            $directionArray = explode('-', $str);
            foreach ($directionArray as $element){
                $tempArray = explode('To', ucwords($element));
                $roomChangesArray[$period]['teacher'][] = ucwords(trim($tempArray[0]));
                $roomChangesArray[$period]['location'][] = ucwords(trim($tempArray[1]));
            }

        }
        if (count($roomChangesArray) === 0){
            return null;
        }
        return $roomChangesArray;
    }
    static function peopleToArray($teachers, $staff){
        $typeArray = [
            't' => $teachers,
            's' => $staff
        ];
        $namesArray = [];
        foreach ($typeArray as $type => $str){
            $lastSpace = null;
            for ($i = 0; substr($str, $i, 1) !== ''; $i++){
                $symbol = substr($str, $i, 1);
                if ($symbol === ' '){
                    $lastSpace = $i;
                }
                else if ($symbol === '.'){
                    if (count(explode('.', $str)) === 2){
                        $lastSpace = 'LAST PERSON';
                        $fullString = trim($str);
                    }
                    else if ($lastSpace === null){
                        continue;
                    }
                    else {
                        $fullString = substr($str, 0, $lastSpace);
                    }
                    $tempArray = explode('(', $fullString);
                    $name = trim($tempArray[0]);
                    if (isset($tempArray[1])){
                        $description = join(', ', explode('-', str_replace(' ', '-', trim($tempArray[1], ')'))));
                    }
                    else {
                        $description = null;
                    }
                    $namesArray[] = [
                        'name' => $name,
                        'description' => $description,
                        'type' => $type
                    ];
                    if ($lastSpace === 'LAST PERSON'){
                        break;
                    }
                    else {
                        $str = substr($str, $lastSpace);
                        $i = 0;
                        $lastSpace = null;
                    }
                }
            }
        }
        if (count($namesArray) === 0){
            return null;
        }
        return $namesArray;
    }
}


function docExtractor()
{
    $client = new Google\Client();
    $client->setAuthConfig(__DIR__ . '/../../../credentials.json');
    $client->setScopes([Google\Service\Docs::DOCUMENTS]);
    $client->setAccessType('offline');
    $service = new Google\Service\Docs($client);
    $id = '1KhmTALivDbAvLusQjZQOOQmPKDm-2Do-gVfvAdf_6WA';
    $doc = $service->documents->get($id);

    $allText = [];
    foreach ($doc->body->content as $structuralElement) {
        if ($structuralElement->paragraph) {
            foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                if ($paragraphElement->textRun && strlen(trim($paragraphElement->textRun->content)) > 0) {
                    $allText[] = trim($paragraphElement->textRun->content);
                }
            }
        }
    }
    return $allText;
}




// getting next available date
$date = new DateTime('now');
function fetchDayData($pdo, $date)
{
    $query = 'SELECT * FROM `calendar` WHERE `date` >= ? AND `school` = 1 ORDER BY `date` ASC LIMIT 1';
    $statement = $pdo->prepare($query);
    $statement->execute([$date->format('Y-m-d')]);
    return $statement->fetch();
}
function getNextSchoolDay($pdo, $date){

}
function isDay($pdo, $date, $schedule)
{
    if ((int)$schedule !== 0) {
        $query = 'SELECT * FROM `classes` WHERE `schedule_id` = ' . $schedule . ' ORDER BY `position` ASC';
        $statement = $pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $end_unix = $result[count($result) - 1]['end_unix'];
        if (is_null($end_unix)) {
            $end_unix = $result[count($result) - 1]['start_unix'];
        }
        $end_unix = $end_unix + strtotime(date_format($date, 'Y-m-d'));
        if ($end_unix < time()) {
            return false;
        } else {
            return true;
        }
    }
    else {
        $end_unix = strtotime(date_format($date, 'Y-m-d')) + 51600;
        if ($end_unix < time()) {
            return false;
        } else {
            return true;
        }
    }
}

$dayData = fetchDayData($pdo, $date);
if (isDay($pdo, $date, $dayData['schedule']) !== true){
    $date->modify('+1 day');
    $date = fetchDayData($pdo, $date)['date'];
}
else {
    $date = $dayData['date'];
}

$date = DateTime::createFromFormat('Y-m-d', $date);
// DEBUG
//$date = DateTime::createFromFormat('Y-m-d', '2024-11-20');






// Actuall work
$absences = new Absences($pdo, $date);
$document = docExtractor();
$documentStartIndex = Detectors::findDateIndex($date, $document, 1000);

if ($documentStartIndex !== null){
    $index = [
        'teachers' => $teachers = Detectors::findStartIndex($document, 't', $documentStartIndex, 3),
        'staff' => $staff = Detectors::findStartIndex($document, 's', $teachers, 30),
        'roomChanges' => $roomChanges = Detectors::findStartIndex($document, 'r', $staff, 30),
        'end' => $end = Detectors::findStartIndex($document, 'end', $roomChanges, 30)
    ];
    $absenceData = [
        'teachers' => null,
        'staff' => null,
        'roomChanges' => null
    ];
    $typesArray = ['teachers', 'staff', 'roomChanges'];

// writing down strings of teachers and staff
    foreach ($typesArray as $type){
        $indexType = match ($type){
            'teachers' => 'staff',
            'staff' => 'roomChanges',
            'roomChanges' => 'end'
        };
        $stringArray = [];
        for ($i = $index[$type] + 1; $i < $index[$indexType]; $i++){
            if (!str_contains($document[$i], '__________')){
                $stringArray[] = $document[$i];
            }
        }
        if ($type === 'roomChanges'){
            $absenceData[$type] = $stringArray;
        }
        else {
            $absenceData[$type] = $output = preg_replace('!\s+!', ' ', trim(join(' ', $stringArray)));
        }
    }

    // adders
    $absences->setPeople(Detectors::peopleToArray($absenceData['teachers'], $absenceData['staff']));
    $absences->setRoomChanges(Detectors::roomChangesToArray($absenceData['roomChanges']));
}


// Date to usable formatt
$date = $date->format('Y-m-d');



// Finding new absent people
$changed = false;
if ($absences->getPeople() !== null) {
    // Work with SQL
    $query = 'SELECT `name`, `description`, `type` FROM `absent_people_test` WHERE `date` = ?';
    $statement = $pdo->prepare($query);
    $statement->execute([$date]);
    $result = $statement->fetchAll();


    // Check in TO REWRITE?
    if ($absences->getPeople() !== $result){
        $changed = true;

        // new people message send check ONLY
        // CAN BE TURNED OFF
        $resultNames = array_map(fn($person) => $person['name'], $result);
        $newPeople = array_filter((array)$absences->getPeople(), fn($person) => !in_array($person['name'], $resultNames));
        echo '<pre>';
        echo 'New absent people:<br>';
        echo 'Here suppose to be future messages:<br>';
        var_dump($newPeople);
        echo '</pre>';


        // Writing down new data to SQL
        $query = 'DELETE FROM `absent_people_test` WHERE `date` = ?';
        $statement = $pdo->prepare($query);
        $statement->execute([$date]);
        foreach ($absences->getPeople() as $person) {
            $query = 'INSERT INTO `absent_people_test` (`date`, `name`, `description`, `type`) VALUES (?, ?, ?, ?)';
            $statement = $pdo->prepare($query);
            $statement->execute([$date, $person['name'], $person['description'], $person['type']]);
        }
    }
}


if ($absences->getRoomChanges() !== null) {
    // Working with SQL
    $query = 'SELECT `period`, `teacher`, `location` FROM `room_changes_test` WHERE `date` = ?';
    $statement = $pdo->prepare($query);
    $statement->execute([$date]);
    $result = $statement->fetchAll();

    // formatting new array
    $formattedRoomChanges = [];
    foreach ($absences->getRoomChanges() as $period => $roomChanges){
        $formattedRoomChanges[] = [
            'period' => $period,
            'teacher' => join('; ', $roomChanges['teacher']),
            'location' => join('; ', $roomChanges['location'])
        ];
    }

    if ($result !== $formattedRoomChanges){
        $changed = true;
        echo 'yes';
        // Writing down new data to SQL
        $query = 'DELETE FROM `room_changes_test` WHERE `date` = ?';
        $statement = $pdo->prepare($query);
        $statement->execute([$date]);

        foreach ($formattedRoomChanges as $period => $roomChanges){
            $query = 'INSERT INTO `room_changes_test` (`date`, `period`, `teacher`, `location`) VALUES (?, ?, ?, ?)';
            $statement = $pdo->prepare($query);
            $statement->execute([$date, $period, $roomChanges['teacher'], $roomChanges['location']]);
        }
    }
}

echo 'Changed? ' . var_dump($changed);
if  ($changed === true) {
    $query = 'INSERT INTO `absence_info_test` (`date`, `time_version`, `date_version`) VALUES (?, ?, ?)';
}