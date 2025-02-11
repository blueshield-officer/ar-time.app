<?php

namespace App\Controllers\services;

use App\Services\DBC;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ScheduleUploader
{
    public function execute(){
        function unixCounter($timeString)
        {
            $tempArray = str_split($timeString);
            if (empty($tempArray) || !is_numeric($tempArray[0])) {
                return null;
            }
            list($time, $dayPart) = explode(" ", $timeString);
            list($hours, $minutes) = explode(":", $time);
            if ($dayPart === "PM") {
                $hours += 0;
                if ($hours !== 12) {
                    $hours += 12;
                }
            }
            return $hours * 60 * 60 + $minutes * 60;
        }
        function recordPeriod(&$periods, $period, $position, $name, $start_time, $end_time, $length, $start_unix, $end_unix)
        {
            $periods[$period] = [
                'position' => $position,
                'name' => $name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'length' => $length,
                'start_unix' => $start_unix,
                'end_unix' => $end_unix,
            ];
        }

        $DBC = DBC::getInstance();

        // the uploaded image
        $file = $_FILES['file']['tmp_name'];

        // Get file info
        $data = getimagesize($file);
        $mimetype = $data['mime'];

        // Creat the image
        switch($mimetype){
            case("image/png"):
                $image = imagecreatefrompng($file);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            case('image/jpeg'):
            case('image/pjpeg'):
            case('image/x-jps'):
                $image = imagecreatefromjpeg($file);
                break;
            case('image/gif'):
                $image = imagecreatefromgif($file);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            default:
                throw new \Exception("Invalid image type. Only excepts PNG, JPG, and GIF. You entered a {$mimetype} type image.");
        }

        // Output to browser with maximum quality
        imagepng($image, $_SERVER['DOCUMENT_ROOT'].'/assets/img/schedules/' . $_FILES['file']['name'], 9);


        // loading image analyser
        $imagePath = $_SERVER['DOCUMENT_ROOT'].'/assets/img/schedules/' . $_FILES['file']['name'];
        $tesseractPath = 'C:/Program Files/Tesseract-OCR/tesseract.exe';
        $text = (new TesseractOCR($imagePath))
            ->executable($tesseractPath)
            ->psm(4)
            ->run();
        echo nl2br($text);
        $lines = [];
        foreach (explode("\n", $text) as $line) {
            $cleanLine = trim($line);
            if ($cleanLine !== '') {
                $lines[] = preg_replace('/\s+/', ' ', $cleanLine);
            }
        }

        // extracting data
        $existingPeriods = range(1, 8);
        $existingPeriods[] = 'HR';

        $periods = [];
        $position = 0;
        foreach ($lines as $line) {
            foreach ($existingPeriods as $possiblePeriod) {
                if (str_starts_with($line, $possiblePeriod)) {
                    $position ++;

                    $pater = '/\d{1,2}:\d{1,2}\s?[AMP]{2}/';
                    preg_match_all($pater, $line, $matches);
                    $matches = $matches[0];

                    // adding warning bell
                    if ($position === 1) {
                        $name = 'Warning Bell';
                        $start_unix = unixCounter($matches[0]) - 300;
                        if ($start_unix >= 43200) {
                            $dayPart = 'PM';
                        }
                        else {
                            $dayPart = 'AM';
                        }

                        $start_time = floor(($start_unix/60)/60) . ':' . (($start_unix/60)%60) . ' ' . $dayPart;
                        $end_time = null;
                        $end_unix = null;
                        $length = null;

                        recordPeriod($periods, 'WB', $position, $name, $start_time, $end_time, $length, $start_unix, $end_unix);
                        $position ++;
                    }

                    //
                    if (gettype($possiblePeriod) === 'integer') {
                        $name = 'Period ' . $possiblePeriod;
                    }
                    else {
                        $name = 'Homeroom';
                    }
                    $start_time = $matches[0];
                    $end_time = $matches[1];
                    $start_unix = unixCounter($start_time);
                    $end_unix = unixCounter($end_time);
                    $length = ($end_unix - $start_unix)/60 . ' min';

                    recordPeriod($periods, $possiblePeriod, $position, $name, $start_time, $end_time, $length, $start_unix, $end_unix);
                    continue 2;
                }
            }
        }


        // filling out broken parts (p 1-8) THINK ABOUT MORE UNIVERSAL ONE !!!!
        $currentPeriods = [];
        foreach ($periods as $period => $object) {
            $currentPeriods[] = $period;
        }

        $existingPeriods[] = 'WB';
        $periodsToAdd = array_diff($currentPeriods, $existingPeriods);
        foreach ($periodsToAdd as $period) {
            $periods[$period] = [
                'start_time' => null,
                'end_time' => null,
                'start_unix' => null,
                'end_unix' => null,
                'length' => null,
                'start_unix' => null,
                'end_unix' => null,
            ];
        }

        $DBC->query('DELETE FROM schedule_custom WHERE `schedule_id` = ?', [$_POST['schedule_id']]);
        foreach ($periods as $object) {
            $DBC->query('INSERT INTO schedule_custom (
                             `position`, `name`, `start_time`, `end_time`, `length`, `start_unix`, `end_unix`, `schedule_id`
                             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $object['position'],
                    $object['name'],
                    $object['start_time'],
                    $object['end_time'],
                    $object['length'],
                    $object['start_unix'],
                    $object['end_unix'],
                    $_POST['schedule_id']
                ]);
        }
    }
}