<?php

namespace App\Services;

class Formatters
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
}