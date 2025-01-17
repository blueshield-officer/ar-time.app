<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class Detectors
{
    static function eventsToArray($html){
        $tempArray = [];
        $eventArray = $html->find('.day-event-info');
        foreach ($eventArray as $element){
            $event = FormattingType::analyzable($element->find('.event-title-wrap .event-link', 0)->plaintext);
            $description = trim(str_replace('events', '', FormattingType::analyzable($element->find('.dateloc', 0)->plaintext)));
            if ($description === ''){
                $description = null;
            }
            $tempArray[] = [
                'event' => ucwords($event),
                'description' => $description
            ];
        }
        return $tempArray;
    }
    static function sportEventsToArray($html): array
    {
        $tempArray = [];
        $md5key = [];
        $eventArray = $html->find('.day-event-info');
        foreach ($eventArray as $element){
            // creating key
            $md5key[] = $element;

            // getting string and removing TBA
            $event = trim($element->find('.event-title-wrap .event-link', 0)->plaintext);
            $event = str_replace('Time: TBA -', '', $event);

            // extracting SPECIAL if it exists
            $patternForSpecial = '/(^\w+(?=:))/';
            preg_match($patternForSpecial, $event, $matches);
            if (!empty($matches)){
                $special = $matches[0];
            }
            else {
                $special = null;
            }
            // formatting page to normal format without trash
            $event = explode('Archbishop Ryan High School', $event)[1];

            // extraction data from string
            $patternsForData = '/(\w{2,3})\s+([BG])\s+(\w{1,2})\s+(Home|Away)\s+([\w.\s-]+)(?:\((.+?)\))?/';
            if (preg_match($patternsForData, $event, $matches)){
                $sport = $matches[1];
                $gender = $matches[2];
                $varsity = $matches[3];
                $side = $matches[4];
                $opponent = trim($matches[5]);
                $description = $matches[6] ?? null;;
            }
            else {
                $tempArray[] = 'Error in parsing data from string';
                continue;
            }
            $tempDescription = explode('(', trim(preg_replace('/\s+/', ' ', str_replace('Athletic Calendar', '', $element->find('.dateloc', 0)->plaintext))));
            $time = $tempDescription[0];
            if (isset($tempDescription[1])){
                $location = str_replace(')', '', $tempDescription[1]);
            }
            else {
                $location = null;
            }
            $tempArray[] = [
                'sport' => $sport,
                'gender' => $gender,
                'side' => $side,
                'varsity' => $varsity,
                'opponent' => $opponent,
                'description' => $description,
                'time' => $time,
                'location' => $location,
                'special' => $special
            ];
        }
        $md5key = md5(join('', $md5key));
        foreach ($tempArray as $key => $element){
            $tempArray[$key]['md5_key'] = $md5key;
        }
        return $tempArray;
    }

    static function exist($html)
    {
        if ($html->hasClass('empty')){
            return false;
        }
        return true;
    }

    static function isMonthEmpty($html){
        if (is_null($html->find('#events_list_partial .no-events', 0))){
            return false;
        }
        return true;
    }


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
}