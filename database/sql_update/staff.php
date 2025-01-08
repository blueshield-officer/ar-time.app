<?php

use App\Services\DBConnection;

if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}

// errors
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors', 'On');


// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
require __DIR__ . "/../../libs/simplehtmldom/simple_html_dom.php";
require __DIR__ . '/../../app/app.php';
$pdo = DBConnection::getPDO();
//  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

class Formatters
{
    static function removeTrash($str){
        return str_replace('IHM', '', $str);
    }
    static function noSpaces($str){
        return preg_replace('/\s+/', '', $str);
    }
    static function leaveOnlyLetter($str)
    {
        return preg_replace('/[^a-zA-Z ]/', '', $str);
    }
    static function sanitizeAndSaveImage($avatarURL, $shortName): void  {
        $imageContents = file_get_contents($avatarURL);
        if ($imageContents !== false) {
            $tempImagePath = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($tempImagePath, $imageContents);
            $imageInfo = getimagesize($tempImagePath);
            if ($imageInfo !== false) {
                if (in_array($imageInfo['mime'], ['image/jpeg', 'image/png'])) {
                    $imageResource = null;
                    if ($imageInfo['mime'] == 'image/jpeg') {
                        $imageResource = imagecreatefromjpeg($tempImagePath);
                    } elseif ($imageInfo['mime'] == 'image/png') {
                        $imageResource = imagecreatefrompng($tempImagePath);
                    }
                    if ($imageResource !== null) {
                        $savePath = __DIR__ . '/../../img/school_staff_pictures/' . Formatters::noSpaces($shortName) . '.png';
                        if ($imageInfo['mime'] == 'image/jpeg') {
                            imagejpeg($imageResource, $savePath, 90); // Adjust quality if needed
                        } elseif ($imageInfo['mime'] == 'image/png') {
                            imagepng($imageResource, $savePath);
                        }
                        imagedestroy($imageResource);
                        unlink($tempImagePath);
                    }
                }
            }
            if (file_exists($tempImagePath)) {
                unlink($tempImagePath);
            }
        }
    }
}

class Detectors
{
    static function getStaffList($html)
    {
        return $html->find('#staff_group_main .list li');
    }
    static function extractInfo($html){
        $people = [];
        foreach ($html as $element){
            $holder = $element->find('.user-info .name-position', 0);
            $name =  $holder->find('.name', 0)->plaintext;
            $position = $holder->find('.user-position', 0)->plaintext;
            $avatarURL = $element->find('.user-profile-pic img', 0)->src;
            $fullName = explode(' ', trim(Formatters::leaveOnlyLetter(Formatters::removeTrash(trim($name)))));
            $honorific = $fullName[0];
            if (count($fullName) > 3){
                $firstName = $fullName[1] . ' ' . $fullName[2];
                $lastName = $fullName[3];
            }
            else {
                $firstName = $fullName[1];
                $lastName = $fullName[2];
            }
            $people[] = [
                'honorific' => $honorific,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'short_name' => $shortName = substr($firstName, 0, 1) . '. ' . $lastName,
                'position' => self::determinePositionId(trim($position), $firstName . ' ' . $lastName),
                'position_full' => trim($position),
            ];
            // saving image
//            $imageContents = file_get_contents($avatarURL);
//            if ($imageContents !== false) {
//                $savePath = __DIR__ . '/../../img/school_staff_pictures/' . Formatters::shortNameToFileName($shortName);
//                file_put_contents($savePath, $imageContents);
//            }
            Formatters::sanitizeAndSaveImage($avatarURL, $shortName);
        }
        return $people;
    }
    static function determinePositionId($position, $fullName)
    {
        $position = strtolower($position);
        if (str_contains($position, 'teacher')){
            return 't';
        }
        else {
            return 's';
        }
    }
}

class DatabaseRequests
{
    static function insertPeople($pdo, $people)
    {
        $query = 'INSERT INTO `school_staff_list` (`honorific`, `first_name`, `last_name`, `short_name`, `position`, `position_full`) VALUES (?, ?, ?, ?, ?, ?)';
        $statement = $pdo->prepare($query);
        foreach ($people as $element){
            $statement->execute([$element['honorific'], $element['first_name'], $element['last_name'], $element['short_name'], $element['position'], $element['position_full']]);
        }
    }
    static function getOldPeople($pdo)
    {
        $query = 'SELECT `honorific`, `first_name`, `last_name`, `short_name`, `position`, `position_full` FROM `school_staff_list`';
        $statement = $pdo->prepare($query);
        $statement->execute();
        $people = $statement->fetchAll();
        return $people;
    }
    static function deleteOldData($pdo, $data)
    {
        $placeholders = join(', ', array_fill(0, count($data), '?'));
        $query = 'DELETE FROM `school_staff_list` WHERE `short_name` NOT IN (' . $placeholders . ')';
        $statement = $pdo->prepare($query);
        $statement->execute($data);
    }
}
class DatabaseFormatters
{
    static function getUpdate($oldData, $newData){
        $indexToKeep = [];
        $arrayToAdd = [];
        foreach ($newData as $index => $element){
            if (isset($oldData[$index])){
                if ($oldData[$index] === $element){
                    $indexToKeep[] = $index;
                }
                else {
                    $arrayToAdd[] = $element;
                }
            }
            else {
                $arrayToAdd[] = $element;
            }
        }
        return [
            'indexToKeep' => $indexToKeep,
            'arrayToAdd' => $arrayToAdd
        ];
    }
    static function addIndex($array)
    {
        $newArray = [];
        foreach ($array as $element){
            $newArray[$element['short_name']] = $element;
        }
        return $newArray;
    }
}
function loadStaffPage()
{
    $url = 'https://www.archbishopryan.com/apps/staff/';
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

    $f = fopen(__DIR__ . '/../data-txt/staff.txt', "w");
    fwrite($f, $data);
    fclose($f);
    return file_get_html(__DIR__ . '/../data-txt/staff.txt');
}

$newPeople = DatabaseFormatters::addIndex(Detectors::extractInfo(Detectors::getStaffList(loadStaffPage())));
$oldPeople = DatabaseFormatters::addIndex(DatabaseRequests::getOldPeople($pdo));

$updatedData = DatabaseFormatters::getUpdate($oldPeople, $newPeople);
echo '<pre>';
var_dump($updatedData);
echo '</pre>';

if (count($updatedData['indexToKeep']) > 0){
    DataBaseRequests::deleteOldData($pdo, $updatedData['indexToKeep']);
}
if (count($updatedData['arrayToAdd']) > 0){
    DatabaseRequests::insertPeople($pdo, $updatedData['arrayToAdd']);
}

