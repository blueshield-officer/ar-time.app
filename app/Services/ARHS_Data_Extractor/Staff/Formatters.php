<?php

namespace App\Services\ARHS_Data_Extractor\Staff;

class Formatters
{
    static function removeTrash($str){
        return str_replace('IHM', '', $str);
    }
    static function noSpaces($str){
        return preg_replace('/\s+/', '', $str);
    }
    static function leaveOnlyLetter($str)
    {
        return preg_replace('/[^a-zA-Z ]/', '', $str);
    }
    static function clear_array($array, $key)
    {
        $new_array = [];
        foreach ($array as $value) {
            $new_array[] = self::noSpaces($value[$key]);
        }
        return $new_array;
    }
    static function sanitizeAndSaveImage($avatarURL, $shortName, $location): void  {
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . '/' . Formatters::noSpaces($shortName) . '.png';
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
}