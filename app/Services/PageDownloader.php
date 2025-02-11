<?php

namespace App\Services;

class PageDownloader
{
    static public function downloadPage($url, $location, $filename, $extension){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        }
        curl_close($ch);

        $directory = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . $filename . '.' . $extension;

        try {
            $f = fopen($directory, "w");
            if ($f === false) {
                throw new \Exception('Error opening file');
            }
            fwrite($f, $data);
            fclose($f);
        }
        catch (\Exception $e) {
            var_dump($e->getMessage());
        }
        return $directory;
    }
}