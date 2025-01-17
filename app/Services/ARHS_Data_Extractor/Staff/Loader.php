<?php

namespace App\Services\ARHS_Data_Extractor\Staff;

use App\Services\DatabaseRequests;
use App\Services\PageDownloader;
use App\Services\DBC;

class Loader
{
    static private function sanitizeAndSaveImage($avatarURL, $shortName, $location): void  {
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . '/' . preg_replace('/\s+/', '', $shortName) . '.png';
        if (!file_exists($savePath)){
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
    static private function extractInfo($html){
        $people = [];
        foreach ($html as $element){
            $holder = $element->find('.user-info .name-position', 0);
            $name =  $holder->find('.name', 0)->plaintext;
            $position = $holder->find('.user-position', 0)->plaintext;
            $avatarURL = $element->find('.user-profile-pic img', 0)->src;
            $email = $element->find('.user-info .email-phone .user-email .email', 0);
            if (!is_null($email)){
                $email = strtolower(html_entity_decode($email->plaintext));
            }
            $fullName = explode(' ', trim(
                preg_replace('/[^a-zA-Z ]/', '', (
                    str_replace('IHM', '', (
                        trim($name)))))));
            $honorific = $fullName[0];
            if ($honorific == 'Mrs'){
                $honorific = 'Ms';
            }
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
                'email' => $email,
                'room' => null,
                'position' => self::determinePositionId(trim($position), $firstName . ' ' . $lastName),
                'position_full' => trim($position),
            ];
            // saving image
//            $imageContents = file_get_contents($avatarURL);
//            if ($imageContents !== false) {
//                $savePath = __DIR__ . '/../../img/school_staff_pictures/' . Formatters::shortNameToFileName($shortName);
//                file_put_contents($savePath, $imageContents);
//            }
            self::sanitizeAndSaveImage($avatarURL, $shortName, 'assets/img/school_staff_pictures');
        }
        return $people;
    }
    static private function determinePositionId($position, $fullName)
    {
        $position = strtolower($position);
        if (str_contains($position, 'teacher')){
            return 't';
        }
        else {
            return 's';
        }
    }
    static private function addIndex($array)
    {
        $newArray = [];
        foreach ($array as $element){
            $newArray[$element['short_name']] = $element;
        }
        return $newArray;
    }
    static private function getNewData($oldData, $newData){
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
//            'indexToKeep' => $indexToKeep,
            'arrayToAdd' => $arrayToAdd
        ];
    }
    static public function load()
    {
        $DBC = DBC::getInstance();
        $DatabaseRequests = new DatabaseRequests($DBC);

        $pageDownloader = new PageDownloader();
        $pageHTML = $pageDownloader->downloadPage('https://www.archbishopryan.com/apps/staff/', 'data_txt/', 'staff.txt');

        $newPeople = self::addIndex(self::extractInfo($pageHTML->find('#staff_group_main .list li')));
        $oldPeople = self::addIndex($DBC->fetchAll($DBC->query('SELECT `honorific`, `first_name`, `last_name`, `short_name`, `email`, `room`, `position`, `position_full` FROM `school_staff_list`')));


        $updatedData = self::getNewData($oldPeople, $newPeople);
        echo '<pre>';
        var_dump($updatedData);
        echo '</pre>';

        // turn on if you want to delete non-existing people at website
//        if (count($updatedData['indexToKeep']) > 0){
//            Database_Requests::deleteOldData($pdo, $updatedData['indexToKeep']);
//        }

        if (count($updatedData['arrayToAdd']) > 0){
            foreach ($updatedData['arrayToAdd'] as $person) {
                $DatabaseRequests->schoolStaffInsertPerson($person);
            }
        }

        // add default image if there are no for some teacher
        $short_name_array = $DBC->fetchAll($DBC->query('SELECT `short_name` FROM `school_staff_list`'));
        $short_name_files_array = [];
        foreach ($short_name_array as $value) {
            $short_name_files_array[] = preg_replace('/\s+/', '', $value['short_name']);
        }
        foreach ($short_name_files_array as $short_name){
            if (!file_exists('assets/img/school_staff_pictures/' . $short_name . '.png')){
                self::sanitizeAndSaveImage('assets/img/school_staff_pictures/Unknown.png', $short_name, 'assets/img/school_staff_pictures');
            }
        }
    }
}