<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

use App\Services\ARHS_Data_Extractor\Formatters;

class Detectors {
    static function findDateIndex($date, $document, $maxRepeat)
    {
        $dateParams = [
            'day' => $date->format('j'),
            'week day' => strtolower($date->format('l')),
            'month' => strtolower($date->format('F')),
            'year' => $date->format('Y')
        ];
        for ($i = 0; $i < $maxRepeat; $i++){
            $str = strtolower($document[$i]);
            foreach ($dateParams as $dateElement){
                if (!str_contains($str, $dateElement)){
                    continue 2;
                }
            }
            return $i;
        }
        return null;
    }
    static function findStartIndex($document, $target, $index, $maxRepeat)
    {
        $target = match ($target){
            't' => 'the following teachers is/are absent today',
            's' => 'the following staff is/are absent today',
            'r' => 'please note the following room changes',
            'end' => '_______________________________________________________________'
        };
        for ($i = 0; $i <= $maxRepeat; $i++){
            $index += 1;
            $str = strtolower($document[$index]);
            if (str_contains($str, $target)){
                return $index;
            }
            else if (str_contains($str, '_______________________________________________________________')){
                return null;
            }
        }
        return null;
    }
    static function find_diference($array_new, $array_old)
    {
        $new_data = [
          0 => [],
          1 => []
        ];
        foreach ($array_new as $period_index => $period) {
            foreach ($period as $object) {
                if (isset($array_old[$period_index][$object['name']])) {
                    if ($array_old[$period_index][$object['name']] !== $object) {
                        $new_data[1][$period_index][] = $object;
                    }
                } else {
                    $new_data[1][$period_index][] = $object;
                }
            }
        }
        foreach ($array_old as $period_index => $period) {
            foreach ($period as $object) {
                if (!isset($array_new[$period_index][$object['name']])) {
                    $new_data[0][$period_index][] = $object;
                }
            }
        }
        return $new_data;
    }
}