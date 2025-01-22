<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class Detectors
{
    static function isSchoolClosed($str)
    {
        if (str_contains($str, 'school closed')){
            return true;
        }
        if (str_contains($str, 'no classes') && !str_contains($str, 'grade') && !preg_match('/\d/', $str)) {
            return true;
        }
        return false;
    }
    static function checkIfHasSchedule($str)
    {
        if (str_contains($str, 'schedule')){
            return true;
        }
        return false;

    }
    static function findSpecial($str){
        $special = ['special', 'pep rally', 'fid'];
        foreach ($special as $element){
            if (str_contains($str, $element)){
                if ($element === 'pep rally'){
                    return 'p';
                }
                else if ($element === 'fid'){
                    return 'fid';
                }
                return $element;
            }
        }
        return false;
    }
    static function findSchedule($str)
    {
        $str = FormattingType::heavy($str);
        $array = explode('schedule', $str);
        $schedule = trim($array[0]);
        return $schedule;
    }
    static function findTestDay($str)
    {
        $str = FormattingType::heavy($str);
        if (str_contains($str, 'day')){
            $array = explode('day', $str);
            $day = (int)$array[count($array) - 1];
            if ($day !== '' && $day !== 0){
                return $day;
            }
            else {
                return null;
            }
        }
        else {
            return null;
        }
    }

    static function isRetreat($str)
    {
        if (str_contains($str, 'no classes') && str_contains($str, 'grades')){
            $str = FormattingType::heavy(str_replace('-', 6, $str));
            $gradesArray = explode('grades', $str);
            $grades = (int)$gradesArray[count($gradesArray) - 1];
            $gradesArray = array_map('intval', explode(6, $grades));
            if (count($gradesArray) === 3){
                return $gradesArray;
            }
            else {
                return false;
            }
        }
        if (str_contains($str, 'retreat')){
            $classes = ['freshman', 'sophomore', 'junior', 'senior'];
            foreach ($classes as $element){
                if (str_contains($str, $element)){
                    return array_diff([9, 10, 11, 12], [match ($element){
                        'freshman' => 9,
                        'sophomore' => 10,
                        'junior' => 11,
                        'senior' => 12,
                        'default' => 'error'
                    }]);
                }
            }
        }
        return false;
    }
    static function isGradeOff($str)
    {
        $regularGradeOff = str_contains($str, 'no classes') && str_contains($str, 'grade');
        $reportGradeOff = str_contains($str, 'report') && str_contains($str, 'grade');
        if ($regularGradeOff || $reportGradeOff){
            $tempArray = explode('grade', $str);
            $str = str_replace('-', 6, $tempArray[count($tempArray) - 1]);
            $str = str_replace('and', 6, $str);
            $numericStr = (preg_replace('/\D/', '', $str));
            $gradeArray = explode(6, $numericStr);

            if ($regularGradeOff){
                return array_intersect([9, 10, 11, 12], $gradeArray);
            }
            else {
                return array_diff([9, 10, 11, 12], $gradeArray);
            }
        }
        return false;
    }
    static function isQuarter($str)
    {
        if (str_contains($str, 'quarter')){
            // getting status
            if (str_contains($str, 'begins')){
                $status = 1;
            }
            else if (str_contains($str, 'ends')){
                $status = 0;
            }
            else {
                return false;
            }
            // getting number
            $quarter = (int)preg_replace('/[^0-9]/', '', $str);
            if ($quarter === 0){
                $quarter = match (true) {
                    str_contains($str, 'first') => 1,
                    str_contains($str, 'second') => 2,
                    str_contains($str, 'third') => 3,
                    str_contains($str, 'fourth') => 4,
                    default => null,
                };
                if (is_null($quarter)){
                    return false;
                }
            }
            return [
                'quarter' => $quarter,
                'status' => $status
            ];
        }
        return false;
    }
    static function isDressDown($str)
    {
        if (str_contains($str, 'dress down')){
            return true;
        }
        return false;
    }
    static function isDance($str)
    {
        if (str_contains($str, 'dance') && !str_contains($str, 'permission forms')){
            return true;
        }
        return false;
    }
}