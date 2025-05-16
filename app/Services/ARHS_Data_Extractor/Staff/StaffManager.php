<?php

namespace App\Services\ARHS_Data_Extractor\Staff;

use App\Services\ARHS_Data_Extractor\SchoolStaffUpdater;
use App\Services\DBC;
use App\Services\PageDownloader;

class StaffManager
{
    private $DBC;
    private $pageHTML;

    public function __construct($DBC)
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/simplehtmldom/simple_html_dom.php';

        $this->DBC = $DBC;
    }

    public function loadHTMLPage()
    {
        $this->pageHTML = file_get_html(
            PageDownloader::downloadPage('https://www.archbishopryan.com/apps/staff/', 'data_txt/', 'staff', 'txt')
        );
    }

    public function loadPeople()
    {
        $this->extractInfo(
            $this->pageHTML->find('#staff_group_main .list li')
        );
    }

    private function extractInfo($html){
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
            $shortName = substr($firstName, 0, 1) . '. ' . $lastName;

            $schoolStaffUpdater = new SchoolStaffUpdater(
                $this->DBC,
                'website',
                $shortName,
                $this->determinePositionId($position),
                $honorific,
                $email,
                $firstName,
                $lastName,
                trim($position),
                $avatarURL
            );
            $schoolStaffUpdater->getPersonID();
        }
    }
    private function determinePositionId($position)
    {
        $position = trim(strtolower($position));
        if (str_contains($position, 'teacher')){
            return 't';
        }
        else {
            return 's';
        }
    }
}