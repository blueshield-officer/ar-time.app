<?php

namespace App\Services\ARHS_Data_Extractor\Staff;


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
            Formatters::sanitizeAndSaveImage($avatarURL, $shortName, 'assets/img/school_staff_pictures');
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