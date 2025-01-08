<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

use App\Services\ARHS_Data_Extractor\Formatters;

class DocString_Formatters
{
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
            }
            $directionArray = explode('-', $str);
            foreach ($directionArray as $element){
                $tempArray = explode('To', ucwords($element));
                $teachers = Formatters::fix_name($tempArray[0]);
                $location = trim($tempArray[1]);
                foreach ($teachers as $teacher){
                    $roomChangesArray[$period][$teacher]['name'] = $teacher;
                    $roomChangesArray[$period][$teacher]['location'] = $location;
                }
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
        $absent_people_array = [];
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
                        $periods = explode('-', str_replace(' ', '-', trim($tempArray[1], ')')));
                    }
                    else {
                        $periods = ['SB', 'HR', '1',
                                    '2', '3', '4',
                                    '5', '6', '7',
                                    '8'];
                    }
                    // array for better work with periods
                    foreach ($periods as $period){
                        $absent_people_array[$period][$name] = [
                            'name' => $name,
                            'type' => $type
                        ];
                    }
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
        if (count($absent_people_array) === 0){
            return null;
        }
        return $absent_people_array;
    }
}