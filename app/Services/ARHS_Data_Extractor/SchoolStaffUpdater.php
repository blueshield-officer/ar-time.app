<?php

namespace App\Services\ARHS_Data_Extractor;

use App\Services\Utils;

class SchoolStaffUpdater
{
    private $DBC;
    private $short_name;
    private $honorific;
    private $first_name;
    private $last_name;
    private $email;
    private $position;
    private $position_full;
    private $verified = 0;


    public function __construct($DBC, $methodAdd, $shortName, $position, $honorific = null, $email = null, $firstName = null, $lastName = null, $positionFull = null, $avatarURL = null)
    {
        $this->DBC = $DBC;

        if ($methodAdd === 'website') {
            $verified = 1;
        }
        else if ($methodAdd === 'googleDoc') {
            $verified = 0;
        }
        else {
            $verified = 0;
        }

        // email creation
        if (is_null($email)) {
            $email = preg_replace(
                '/[^A-Za-z0-9]/', '', strtolower($shortName)
            );
            $email .= '@arhs.org';
        }

        // last name
        if (is_null($lastName)) {
            $nameArray = explode('.', $shortName);
            if (isset($nameArray[1])){
                $lastName = $nameArray[1];
            }
        }

        // avatar save
        if (!is_null($avatarURL) && !is_null($shortName)) {
            $this->sanitizeAndSaveImage($avatarURL, $shortName);
        }

        $this->short_name = $shortName;
        $this->email = $email;
        $this->verified = $verified;
        $this->position = $position;

        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->honorific = $honorific;
        $this->position_full = $positionFull;
    }



    private function sanitizeAndSaveImage($avatarURL, $shortName)  {
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/img/school_staff_pictures/' . strtolower(FormattingType::noSpaces($shortName)) . '.png';
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

    public function getPersonID($autoAdd = true, $log = false)
    {
        // `short_name` = ?
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `teacher_id` FROM `school_faculty_list` WHERE `short_name` = ?', [$this->short_name],
            )
        );
        if ($result === false) {
            if ($log) echo $this->short_name;
            if ($autoAdd === true){
                return $this->addPersonToDB();
            }
            return false;
        }
        return $result['teacher_id'];
    }

    private function addPersonToDB()
    {
        $query = 'INSERT INTO `school_faculty_list` (`honorific`, `first_name`, `last_name`, `short_name`, `email`, `position`, `position_full`, `verified`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $params = [
            $this->honorific, $this->first_name,
            $this->last_name, $this->short_name, $this->email,
            $this->position, $this->position_full, $this->verified
        ];
        $this->DBC->query($query, $params);
        return $this->DBC->getLastInsertID();
    }
}