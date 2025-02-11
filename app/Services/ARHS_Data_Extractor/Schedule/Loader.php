<?php

namespace App\Services\ARHS_Data_Extractor\Schedule;

use App\Services\DBC;
use App\Services\PageDownloader;

class Loader
{
    static function timeToUnix($str)
    {
        if (is_null($str)){
            return null;
        }
        $timestamp = strtotime($str);
        return $timestamp - strtotime('today');
    }
    static function load()
    {
        $DBC = DBC::getInstance();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/simplehtmldom/simple_html_dom.php';

        $PageDownloader = new PageDownloader();
        $file = $PageDownloader->downloadPage('https://www.archbishopryan.com/apps/bell_schedules/printerfriendly.jsp', 'data_txt/', 'schedule_default', 'txt');
        $html = file_get_html($file);

        $html_dives = $html->find('.bell-schedule');
        $data = [];
        foreach ($html_dives as $div) {
            $schedule_id = strtolower(explode(' ', trim($div->find('.dark a', 0)->plaintext))[1]);
            $description = trim($div->find('thead tr td', 0)->plaintext);
            $table_rows = $div->find('tbody tr');

            $table = [];
            $position = 0;

            foreach ($table_rows as $row) {
                $position++;
                $name = trim($row->find('td', 0)->plaintext);
                $start_time = trim($row->find('td', 1)->plaintext);
                $end_time = trim($row->find('td', 2)->plaintext);
                $length = trim($row->find('td', 3)->plaintext);

                $length = $length == '&#8212;' ? null : $length;

                $start_time = $start_time == '&#8212;' ? null : $start_time;
                $end_time = $end_time == '&#8212;' ? null : $end_time;

                $start_unix = self::timeToUnix($start_time);
                $end_unix = self::timeToUnix($end_time);

                $table[$position] = [
                    'name' => $name,
                    'position' => $position,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'length' => $length,
                    'start_unix' => $start_unix,
                    'end_unix' => $end_unix,
                ];
            }


            $data[$schedule_id] = [
                'schedule_id' => $schedule_id,
                'description' => $description,
                'table' => $table
            ];
        }

        // SQL
        $DBC->query('TRUNCATE `schedule_default`');

        foreach ($data as $schedule_id => $schedule) {
            foreach ($schedule['table'] as $period) {
                $DBC->query('INSERT INTO `schedule_default` (`position`, `name`, `start_time`, `end_time`, `length`, `start_unix`, `end_unix`, `schedule_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                    $period['position'],
                    $period['name'],
                    $period['start_time'],
                    $period['end_time'],
                    $period['length'],
                    $period['start_unix'],
                    $period['end_unix'],
                    $schedule_id
                ]);
            }
        }

        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}