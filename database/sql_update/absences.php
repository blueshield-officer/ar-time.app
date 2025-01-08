<?php

use App\Services\DBConnection;

if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}

require_once __DIR__ . '/../../libs/google/vendor/autoload.php';
date_default_timezone_set('America/New_York');

class Absences
{
    public function __construct()
    {
        include __DIR__ . '/../../app/app.php';
        $this->pdo = DBConnection::getPDO();


        $this->data = [
            'header' => null,
            'teachers' => [],
            'staff' => [],
            'roomChanges' => []
        ];
        $this->schedule = null;
        $this->date = new \DateTime('now');
        $this->currentUNIX = time();

        $this->index = null;

        $this->text = $this->docExtractor();
        $this->dateChecker();
        $this->headerFinder();
//        $this->index = 728;
        $this->data = null;
        if (!is_null($this->data['header'])) {
            $this->absence('t');
            $this->absence('a');
            $this->roomChanges();
            if (count($this->data['teachers']) === 0 && count($this->data['staff']) === 0 && count($this->data['roomChanges']) === 0) {
                $this->data = null;
            }
        } else {
            $this->data = null;
        }
        $this->SQLchecker();
    }

    private function SQLchecker()
    {
        $this->data = json_encode($this->data);
        $query = 'SELECT `doc_md5` FROM `absence` WHERE `id` = 1';
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetch()["doc_md5"];
        if ($result === '' || $result !== md5($this->data)) {
            $this->SQLupdater();
        }
    }

    private function SQLupdater()
    {
        $query = 'TRUNCATE TABLE `absence`';
        $statement = $this->pdo->prepare($query);
        $statement->execute();

        $query = 'INSERT INTO `absence` (`doc_json`, `doc_md5`,`doc_time_version`) VALUES (?, ?, ?)';
        $statement = $this->pdo->prepare($query);
        $statement->execute([$this->data, md5($this->data), date('h : i a')]);
    }

    private function headerFinder()
    {
        for ($i = 0; $i < 50; $i++) {
            if (str_contains($this->text[$i], $this->date)) {
                $this->data['header'] = $this->text[$i];
                $this->index = $i;
                return;
            }
        }
        $this->data['header'] = NULL;
    }

    private function absence($job)
    {
        if ($job === 't') {
            $current = 'the following teachers is/are absent today:';
            $next = 'the following staff is/are absent today:';
            $dataName = 'teachers';
        } else if ($job === 'a') {
            $current = 'the following staff is/are absent today:';
            $next = 'please note the following room changes';
            $dataName = 'staff';
        } else {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            if (str_contains(strtolower($this->text[$this->index]), $current)) {
                break;
            } else {
                $this->index += 1;
            }
        }
        if (str_contains(strtolower($this->text[$this->index]), $current)) {
            while (true) {
                $this->index += 1;
                if (!str_contains(strtolower($this->text[$this->index]), $next) && !str_contains($this->text[$this->index], '___________')) {
                    $myArray = explode("\t", $this->text[$this->index]);
                    $myArray = array_filter($myArray, function ($element) {
                        if ($element === '') {
                            return false;
                        }
                        return true;
                    });
                    foreach ($myArray as $element) {
                        $this->data[$dataName][] = $element;
                    }
                } else {
                    return;
                }
            }
        } else {
            $this->data[$dataName] = ['NO DATA'];
        }
    }

    private function roomChanges()
    {
        for ($i = 0; $i < 10; $i++) {
            if (str_contains(strtolower($this->text[$this->index]), 'please note the following room changes')) {
                break;
            } else {
                $this->index += 1;
            }
        }
        $period = 1;
        while (true) {
            $this->index += 1;
            if (!str_contains($this->text[$this->index], '___________')) {
                if (str_contains(strtolower($this->text[$this->index]), 'period')) {
                    $period = trim(substr($this->text[$this->index], 7, 1));
                }
                $myArray = explode("-", trim($this->text[$this->index]));
                if ($myArray[0] === '') {
                    $myArray[0] = trim($this->text[$this->index]);
                }
                $myArray = array_filter($myArray, function ($element) {
                    if ($element === '') {
                        return false;
                    }
                    return true;
                });
                foreach ($myArray as $element) {
                    if (str_contains(strtolower($element), 'period')) {
                        $element = trim(substr($element, 8));
                    }
                    if ($element !== '') {
                        $this->data['roomChanges'][$period][] = trim($element);
                    }
                }
            } else {
                return;
            }
        }

    }

    private function docExtractor()
    {
        $client = new Google\Client();
        $client->setAuthConfig(__DIR__ . '/../../../credentials.json');
        $client->setScopes([Google\Service\Docs::DOCUMENTS]);
        $client->setAccessType('offline');
        $service = new Google\Service\Docs($client);
        $id = '1KhmTALivDbAvLusQjZQOOQmPKDm-2Do-gVfvAdf_6WA';
        $doc = $service->documents->get($id);

        $allText = [];
        foreach ($doc->body->content as $structuralElement) {
            if ($structuralElement->paragraph) {
                foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                    if ($paragraphElement->textRun && strlen(trim($paragraphElement->textRun->content)) > 0) {
                        $allText[] = trim($paragraphElement->textRun->content);
                    }
                }
            }
        }
        echo '<pre>';
        var_dump($allText);
        echo '</pre>';
        return $allText;
    }

    private function dateChecker()
    {
        $this->scheduleExtractor();
        if ($this->isDay() === false) {
            date_modify($this->date, '+1 day');
            $this->scheduleExtractor();
        }
        $this->date = date_format($this->date, 'l, F j, Y');
    }

    private function isDay()
    {
        if ((int)$this->schedule !== 0) {
            $query = 'SELECT * FROM `classes` WHERE `schedule_id` = ' . $this->schedule . ' ORDER BY `position` ASC';
            $statement = $this->pdo->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll();
            $end_unix = $result[count($result) - 1]['end_unix'];
            if (is_null($end_unix)) {
                $end_unix = $result[count($result) - 1]['start_unix'];
            }
            var_dump($end_unix);
            $end_unix = $end_unix + strtotime(date_format($this->date, 'Y-m-d'));
            if ($end_unix < $this->currentUNIX) {
                return false;
            } else {
                return true;
            }
        }
        else {
            $end_unix = strtotime(date_format($this->date, 'Y-m-d')) + 51600;
            if ($end_unix < $this->currentUNIX) {
                return false;
            } else {
                return true;
            }
        }
    }


    private function scheduleExtractor()
    {
        while (true) {
            $query = 'SELECT * FROM `calendar` WHERE `date` = ?';
            $statement = $this->pdo->prepare($query);
            $statement->execute([$this->date->format('Y-m-d')]);
            $result = $statement->fetch();
            if ($result['school'] === 1) {
                $this->schedule = $result['schedule'];
                if (!is_null($result['special'])) {
                    $this->schedule = 'special';
                }
                $this->schedule = $result['schedule'];
                break;
            }
            date_modify($this->date, '+1 day');
        }
    }
}


new Absences();