<?php

namespace App\Services;

class Utils
{
    static function symbols($str){
        return preg_replace('/[^\p{L}\p{N}\s]/u', '', $str);
    }

    static function heavy($str){
        return preg_replace('/\s+/', ' ', trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str)));
    }
    static function noSpaces($str){
        return preg_replace('/\s+/', '', $str);
    }
    static function sanitizeAndSaveImage($avatarURL, $shortName)  {
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/school_staff_pictures/' . self::noSpaces($shortName) . '.png';
        if (!file_exists($savePath)){
            $imageContents = file_get_contents($avatarURL);
            if ($imageContents !== false) {
                $tempImagePath = tempnam(sys_get_temp_dir(), 'img');
                file_put_contents($tempImagePath, $imageContents);
                $imageInfo = getimagesize($tempImagePath);
                if ($imageInfo !== false) {
                    if (in_array($imageInfo['mime'], ['image/jpeg', 'image/png'])) {
                        $imageResource = null;
                        if ($imageInfo['mime'] == 'image/jpeg') {
                            $imageResource = imagecreatefromjpeg($tempImagePath);
                        } elseif ($imageInfo['mime'] == 'image/png') {
                            $imageResource = imagecreatefrompng($tempImagePath);
                        }
                        if ($imageResource !== null) {

                            if ($imageInfo['mime'] == 'image/jpeg') {
                                imagejpeg($imageResource, $savePath, 90); // Adjust quality if needed
                            } elseif ($imageInfo['mime'] == 'image/png') {
                                imagepng($imageResource, $savePath);
                            }
                            imagedestroy($imageResource);
                            unlink($tempImagePath);
                        }
                    }
                }
                if (file_exists($tempImagePath)) {
                    unlink($tempImagePath);
                }
            }
        }
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
}