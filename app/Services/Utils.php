<?php

namespace App\Services;

use DateTime;

class Utils
{
    static function assimilate($str){
        return trim(strtolower(preg_replace('/\s+/', '', $str)));
    }
    static function symbols($str){
        return preg_replace('/[^\p{L}\p{N}\s]/u', '', $str);
    }

    static function heavy($str){
        return preg_replace('/\s+/', ' ', trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str)));
    }
    static function noSpaces($str){
        return preg_replace('/\s+/', '', $str);
    }

    static function usDateToYmd($date){
        $dateObject = explode('-', $date);
        foreach ($dateObject as $key => $value) {
            if ($key !== 2){
                if (strlen($value) < 2){
                    $dateObject[$key] = 0 . $value;
                }
            }
        }
        return $dateObject[2] . '-' . $dateObject[0] . '-' . $dateObject[1];
    }
    static public function isJson($data): bool {
        if (gettype($data) === 'string') {
            json_decode($data);
            return json_last_error() === JSON_ERROR_NONE;
        }
        return false;
    }
    static private function formatTimeParams($value, $type)
    {
        if ($value === 0) {
            return null;
        }
        $string = $value . ' ';
        if ($type === 's') {
            $string .= $value === 1 ? 'second' : 'seconds';
        }
        elseif ($type === 'm') {
            $string .= $value === 1 ? 'minute' : 'minutes';
        }
        elseif ($type === 'h') {
            $string .= $value === 1 ? 'hour' : 'hours';
        }
        else {
            return null;
        }
        return $string;
    }
    static public function secondsToTime($seconds, $type = 'string', $accurate = true)
    {
        // max is hours
        if ($seconds === 0) {
            return null;
        }
        $hours = (int)floor($seconds / 3600);
        $minutes = (int)floor($seconds / 60) % 60;
        $seconds = $seconds % 60;
        if ($type === 'string') {
            return (($accurate === false) ? '≈ ' : '' )
                . (self::formatTimeParams($hours, 'h') ?? '')
                . (($hours !== 0) ? ', ' : '' )
                . (self::formatTimeParams($minutes, 'm') ?? '')
                . (($minutes !== 0 && $seconds !== 0) ? ', ' : '' )
                . (self::formatTimeParams($seconds, 's') ?? '');
        }
        else {
            return [
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds,
            ];
        }
    }
    static public function isTime($str)
    {
        preg_match('/(\d{2}):(\d{2})/', $str, $matches);
        if (isset($matches[1]) && isset($matches[2])){
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];

            if (!(0 <= $hours && $hours < 24)){
                return false;
            }
            if (!(0 <= $minutes && $minutes < 60)){
                return false;
            }
            return true;
        }
        return false;
    }
    static function sqlFieldsToStr($array)
    {
        return join(', ', array_map(fn($element) => '`' . $element . '`', $array));
    }

    static function getClientIP(){
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = array_map('trim', explode(',', $_SERVER[$header]));

                // Return the first IP in the list that is valid
                foreach ($ips as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP,
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    static function generateRandomSymbols($length): string
    {
        return bin2hex(
            random_bytes(round($length))
        );
    }

    static function validateEmail ($email, $arEmail = true): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($arEmail === true) {
                if (str_ends_with($email, '@arhs.org') || str_ends_with($email, '@ryanhs.org')) {
                    return true;
                }
                return false;
            }
            else {
                return true;
            }
        }
        return false;
    }

    static function getSecondsFromMidnight(): int
    {
        $date = new DateTime('now');
        $midnightSeconds = (clone $date)->setTime(0, 0, 0)->getTimestamp();;
        return $date->getTimestamp() - $midnightSeconds;
    }

    static function isNestedArray($array): bool {
       if (is_array($array)) {
           foreach ($array as $value) {
               if (is_array($value)) {
                   return true;
               }
           }
       }
       return false;
    }

    static function strToAnalyzable($str) : string
    {
        return strtolower(preg_replace("/[^a-zA-Z0-9]/", '', $str));
    }

    static function isYmdTypeDate($str)
    {
        $d = DateTime::createFromFormat('Y-m-d', $str);
        return $d && $d->format('Y-m-d') === $str;
    }
}