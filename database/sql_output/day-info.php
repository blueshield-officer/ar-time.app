<?php

class DataLoad
{
    private $pdo;
    private $dateObject;
    private $currentUNIX;
    private $schedule;
    private $scheduleUNIX;
    private $description;

    private $testDay;
    private $special;


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->dateObject = new DateTime('now');;
        $this->currentUNIX = time();
        $this->description = null;
        $this->testDay = null;
        $this->special = null;
    }

    public function analysis()
    {
        $answer = $this->finalResult();
        if ($answer === false) {
            $this->dateObject->modify('+1 day');
            $answer = $this->finalResult();
        }
        return [
            'schedule' => $this->schedule,
            'description' => $this->description,
            'testDay' => $this->testDay,
            'date' => $this->dateObject->format('m/d/Y'),
            'special' => $this->special,
            'bells' => $answer
        ];
    }

    private function finalResult()
    {
        $this->calendarExtractor();
        return $this->scheduleExtractor();
    }

    private function scheduleExtractor()
    {
        if (is_numeric($this->schedule)) {
            $query = 'SELECT * FROM `classes` WHERE `schedule_id` = ' . $this->schedule . ' ORDER BY `position` ASC';
            $statement = $this->pdo->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll();
            $end_unix = $result[count($result) - 1]['end_unix'];
            if (is_null($end_unix)) {
                $end_unix = $result[count($result) - 1]['start_unix'];
            }
            $end_unix = $end_unix + $this->scheduleUNIX;
            if ($end_unix < $this->currentUNIX) {
                return false;
            } else {
                return $result;
            }
        } else {
            $end_unix = $this->scheduleUNIX + 51600;
            if ($end_unix < $this->currentUNIX) {
                return false;
            } else {
                return 'Special';
            }
        }
    }


    private function calendarExtractor()
    {
        for ($i = $this->dateObject; true; $i->modify('+1 day')) {
            $query = 'SELECT * FROM `calendar` WHERE `date` =' . $this->dateObject->format('Ymd');
            $statement = $this->pdo->prepare($query);
            $statement->execute();
            $result = $statement->fetch();
            if ($result['school'] === 1) {
                $this->schedule = $result['schedule'];
                $this->scheduleUNIX = strtotime($result['date']);
                $this->description = $result['description'];
                $this->testDay = $result['testDay'];
                if (!is_null($result['special'])) {
                    $result['special'] = json_decode($result['special']);
                }
                $this->special = $result['special'];
                $this->dateObject = $i;
                break;
            }
        }
    }

}


//$schedule = new DataLoad($pdo);
//header('Content-type: application/json');
//$data = $schedule->analysis();
//echo json_encode($data);