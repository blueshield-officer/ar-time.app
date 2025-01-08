<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;


class Database_Formatters {
    static function containsArray(array $array): bool {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false; // No arrays found
    }
    static function newData ($newArray, $oldArray)
    {
        $array = [];
        foreach ($newArray as $index => $element){
            if (isset($oldArray[$index])){
                if ($oldArray[$index] !== $element){
                    $array[] = $element;
                }
            }
            else {
                $array[] = $element;
            }
        }
        return $array;
    }
    static function getDays ($array, $difficult)
    {
        return array_map(function ($element) use ($difficult) {
            return $difficult ? reset($element)['date'] : $element['date'];
        }, $array);
    }
}