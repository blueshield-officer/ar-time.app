<?php

namespace App\Services;

class PageDownloader
{
    public function __construct()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/simplehtmldom/simple_html_dom.php';
    }

    public function downloadPage($url, $location, $filename){
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

        $directory = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . $filename;

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
        return file_get_html($directory);
    }
}