<?php

namespace App\Services\ARHS_Data_Extractor\Staff;

use App\Services\DB_Connection;
use http\Env\Request;

class Loader
{
    static public function load()
    {
        $pdo = DB_Connection::getPDO();

        require_once __DIR__ . "/../../../../libs/simplehtmldom/simple_html_dom.php";

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

            $f = fopen($_SERVER['DOCUMENT_ROOT'] . '/data_txt/staff.txt', "w");
            fwrite($f, $data);
            fclose($f);
            return file_get_html($_SERVER['DOCUMENT_ROOT'] . '/data_txt/staff.txt');
        }

        $newPeople = Database_Formatters::addIndex(Detectors::extractInfo(Detectors::getStaffList(loadStaffPage())));
        $oldPeople = Database_Formatters::addIndex(Database_Requests::getOldPeople($pdo));

        $updatedData = Database_Formatters::getUpdate($oldPeople, $newPeople);
        echo '<pre>';
        var_dump($updatedData);
        echo '</pre>';

        // turn on if you want to delete non-existing people at website
//        if (count($updatedData['indexToKeep']) > 0){
//            Database_Requests::deleteOldData($pdo, $updatedData['indexToKeep']);
//        }
        if (count($updatedData['arrayToAdd']) > 0){
            Database_Requests::insertPeople($pdo, $updatedData['arrayToAdd']);
        }

        // add default image if there are no for some teacher
        $short_name_array = Formatters::clear_array(Database_Requests::get_short_names($pdo), 'short_name');
        foreach ($short_name_array as $short_name){
            if (!file_exists('assets/img/school_staff_pictures/' . $short_name . '.png')){
                Formatters::sanitizeAndSaveImage('assets/img/school_staff_pictures/Unknown.png', $short_name, 'assets/img/school_staff_pictures');
            }
        }
    }
}