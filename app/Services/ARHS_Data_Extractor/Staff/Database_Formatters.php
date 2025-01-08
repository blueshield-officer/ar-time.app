<?php

namespace App\Services\ARHS_Data_Extractor\Staff;

class Database_Formatters
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
//            'indexToKeep' => $indexToKeep,
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