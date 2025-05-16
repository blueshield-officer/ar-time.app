<?php

namespace App\Services\ARHS_Data_Extractor;

class FormattingType {
    static function symbols($str){
        return preg_replace('/[^\p{L}\p{N}\s]/u', '', $str);
    }
    static function noSpaces($str)
    {
        return preg_replace('/\s+/', '', $str);
    }
    static function oneSpace($str){
        return preg_replace('/\s+/', ' ', $str);
    }
    static function analyzable($str){
        return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
    }
    static function heavy($str){
        return preg_replace('/\s+/', ' ', trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str)));
    }
}