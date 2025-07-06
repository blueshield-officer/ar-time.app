<?php

namespace App\Services;

use App\Services\Accounts\GoogleAuthRedirect;
use function Brotli\uncompress;

class SmartyVars
{
    private $DBC;
    private $redis;
    private $cacheOperator;
    private $localDate;
    private $localDateGlobal;

    private $varsArray;

    public function __construct($date = null, $type = 'MDY')
    {
        $this->DBC = DBC::getInstance();
        $this->redis = RedisOperator::getInstance();
        $this->cacheOperator = new CacheOperator($this->DBC, $this->redis);

        $dateArray = [
            'MY' => 'Y-m',
            'MDY' => 'Y-m-d',
        ];

        if (is_null($date)) {
            $this->localDate = (Session::getLocalDate())->format($dateArray[$type]);
            $this->localDateGlobal = (Session::getLocalDate())->format('Y-m-d');
        }
        else {
            $dateObject = \DateTime::createFromFormat('m-' . (($type === 'MDY') ? 'd-' : '') . 'Y', $date);
            $this->localDate = $dateObject->format($dateArray[$type]);
            $this->localDateGlobal = $dateObject->format('Y-m-d');
            if ($type !== 'MY') {
                Session::setLocalDate($dateObject);
            }
        }

        $this->varsArray = [];

        $this->loadDependencies();
    }

    private function loadDependencies()
    {
        $this->varsArray['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $this->varsArray['theme'] = $_COOKIE['theme'];
        $this->varsArray['design'] = $_COOKIE['design'];
        $this->varsArray['loggedIn'] = Session::isLogedIn();
        if ($this->varsArray['loggedIn']){
            $this->varsArray['userName'] = $_SESSION['userName'];
            $this->varsArray['userEmail'] = $_SESSION['userEmail'];
        }
    }
    public function loadSessionParams()
    {
        $userData = Session::getAccountSessionData();
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `notification`, `notification_absence`, `notification_report`, `notification_report_time`, `notification_guider` FROM `users_sessions` WHERE `token_cookie` = ? AND `user_id` = ?', [$userData['token'], $userData['id']]
            )
        );
        $this->varsArray['sessionParams'] = $result;
    }
    // TODO Feels weak
    public function loadAdditionalAccountInfo(){
        $userData = Session::getAccountSessionData();
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `graduation_year`, `type` FROM `users_accounts` WHERE `user_id` = ?', [$userData['id']]
            )
        );
        $this->varsArray['additionalAccountInfo'] = $result;

        $grade = null;
        if ($result['graduation_year'] !== null){
            $date = new \DateTime();
            $date->setDate($date->format('Y'), 6, 20);
            if ($date < new \DateTime()) {
                $date->modify('+1 year');
            }

            $yearDifference = $result['graduation_year'] - $date->format('Y');
            $grade = match ($yearDifference){
                0 => 'senior',
                1 => 'junior',
                2 => 'sophomore',
                3 => 'freshman'
            };
        }
        $this->varsArray['additionalAccountInfo']['grade'] = $grade;
    }
    public function loadBase(){
        $tableObject = null;
        $currentDayInfo = $this->cacheOperator->fetch('calendar_schedules', date('Y-m-d'), false);
        if (!is_null($currentDayInfo)) {
            $schedule = $currentDayInfo['schedule'];
            $tableObject = $this->cacheOperator->fetch('bell_schedules', $schedule, false);

            if (!is_null($tableObject)) {
                $tableObject = json_encode($tableObject);
            }
            else {
                $tableObject = 'special';
            }
        }
        $this->varsArray['currentDate'] = date('Y-m-d');
        $this->varsArray['currentDateObject'] = \DateTime::createFromFormat('Y-m-d', $this->varsArray['currentDate']);
        $this->varsArray['localDate'] = $this->localDate;
        $this->varsArray['localDateGlobal'] = $this->localDateGlobal;
        $this->varsArray['localDateObject'] = \DateTime::createFromFormat('Y-m-d', $this->localDate);
        $this->varsArray['localDateJS'] = $this->localDate;
        $this->varsArray['currentTable'] = $tableObject;
    }

    public function setNextDateToLocalDate()
    {
        $this->localDate = $this->cacheOperator->fetch('calendar_schedules', 'next', false, false)['date'];

        $this->varsArray['localDate'] = $this->localDate;
    }


    public function loadPages()
    {
        $pages = [
            'Home' =>       ['/', 'house-fill.svg', 20, false],
            'Calendar' =>   ['/calendar', 'calendar-fill.svg', 18, false],
            'Absences' =>   ['/absences', 'person-fill.svg', 20, true],
            'Schedules' =>  ['/schedules', 'table-solid.svg', 19, false],
            'Countdowns' => ['/countdowns', 'hourglass-start-solid.svg', 15, true],
            'Development' =>    ['/development', 'tools.svg', 20, true],
            'Notifications' => ['/notifications', 'bell-fill.svg', 19, false],
            'Settings' =>   ['/settings', 'gear-fill.svg', 20, true]
        ];
        if (!Session::isLogedIn()) {
            unset($pages['Notifications']);
        }
        $this->varsArray['pages'] = $pages;
    }

    private function formatScheduleObject($schedule){
        if (!is_null($schedule)) {
            $table = $this->cacheOperator->fetch('bell_schedules', $schedule['schedule'], false);
            if (!is_null($table)) {
                $this->varsArray['bells'] = $table;
            }
            if ($schedule['schedule'] === 'special') {
                $schedule['description'] = 'Mysterious schedule.';
            } else {
                $schedule['description'] = $this->cacheOperator->fetch('bell_schedules_descriptions', $schedule['schedule'], false);
            }

            if ($schedule['schedule'] === 'fid') {
                $schedule['schedule'] = strtoupper($schedule['schedule']);
            } else {
                $schedule['schedule'] = ucwords($schedule['schedule']);
            }
        }
        return $schedule;
    }

    public function loadDayInfo($json = false)
    {
        $schedules = $this->cacheOperator->fetch('calendar_schedules', $this->localDate, false);
        if (count(explode('-', $this->localDate)) === 2) {
            foreach ($schedules as $index => $schedule){
                $schedules[$index] = $this->formatScheduleObject($schedule);
            }
            if ($json === true)
            {
                $schedules = json_encode($schedules);
            };
            $this->varsArray['schedules'] = $schedules;
        }
        else {
            $schedule = $this->formatScheduleObject($schedules);
            if ($json === true) $schedule = json_encode($schedule);
            $this->varsArray['schedule'] = $schedule;
        }
    }

    public function loadEvents($json = false)
    {
        $this->varsArray['events'] = $this->cacheOperator->fetch('calendar_events', $this->localDate, $json);
    }
    public function loadSports($json = false)
    {
        $this->varsArray['sports'] = $this->cacheOperator->fetch('calendar_sports', $this->localDate, $json);
    }
    public function loadDayOffs($json = false){
        $this->varsArray['dayOffs'] = $this->cacheOperator->fetch('calendar_day_off', $this->localDate, $json);
    }
    public function loadNextDayOff()
    {
        $this->varsArray['nextDayOff'] = [
            'freshmen' => $this->cacheOperator->fetch('calendar_day_off', 'next:9=1', false),
            'sophomores' => $this->cacheOperator->fetch('calendar_day_off', 'next:10=1', false),
            'juniors' => $this->cacheOperator->fetch('calendar_day_off', 'next:11=1', false),
            'seniors' => $this->cacheOperator->fetch('calendar_day_off', 'next:12=1', false),
        ];
    }
    public function loadNextSchedules()
    {
        // load schedules data
        $earlyDismissalData = $this->cacheOperator->fetch('early_dismissal_schedules_pack', null, false);
        foreach ($earlyDismissalData as $index => $data){
            $this->varsArray[$index] = $data;
        }
    }
    public function loadAbsentCombined(){
        $newArray = [];
        $absentPeople = $this->cacheOperator->fetch('absent_people', $this->localDate, false);
        $roomChanges = $this->cacheOperator->fetch('absent_room_changes', $this->localDate, false);

        if (!is_null($roomChanges)) {
            foreach ($roomChanges as $person => $periods){
                foreach ($periods as $period => $location) {
                    $newArray[$person][$period] = $location;
                }
            }
        }
        arsort($newArray);

        if (!is_null($absentPeople)){
            foreach ($absentPeople as $person => $periods){
                foreach ($periods as $period){
                    if (!isset($newArray[$person][$period])){
                        $newArray[$person][$period] = null;
                    }
                }
            }
        }
//        var_dump($newArray);
//        die();

        function hasOnlyNullValues($periods) {
            return count(array_filter($periods, function($value) {
                    return $value !== null;
                })) === 0;
        }

        $nonNullEntries = array_filter($newArray, fn($periods) => !hasOnlyNullValues($periods), ARRAY_FILTER_USE_BOTH);
        $nullEntries = array_filter($newArray, fn($periods) => hasOnlyNullValues($periods), ARRAY_FILTER_USE_BOTH);
        $newArray = $nonNullEntries + $nullEntries;


        if (empty($newArray)){
            $newArray = null;
        }

        $this->varsArray['absentCombined'] = $newArray;
    }
    public function loadAbsentAdditional()
    {
        $this->varsArray['absentAdditional'] = $this->cacheOperator->fetch('absent_additional', $this->localDate, false);
    }
    public function loadAbsentPeople()
    {
        $this->varsArray['absentPeople'] = $this->cacheOperator->fetch('absent_people', $this->localDate, false);
    }
    public function loadRoomChanges()
    {
        $this->varsArray['absentRoomChanges'] = $this->cacheOperator->fetch('absent_room_changes', $this->localDate, false);
    }
    public function loadSchedules()
    {
        $this->varsArray['bells'] = $this->cacheOperator->fetch('bell_schedules', null, false);
    }
    public function loadSchedulesDescriptions()
    {
        $this->varsArray['bellsDescriptions'] = $this->cacheOperator->fetch('bell_schedules_descriptions', null, false);
    }
    public function loadRoadMap()
    {
        $this->varsArray['roadmap'] = $this->cacheOperator->fetch('info_roadmap', null, false);
    }
    public function loadChangelog()
    {
        $this->varsArray['changelog'] = $this->cacheOperator->fetch('info_changelog', null, false);
    }
    public function loadHolidays()
    {
        $this->varsArray['holidays'] = $this->cacheOperator->fetch('calendar_holidays', null, false);
    }
    public function loadAcademics()
    {
        $this->varsArray['academics'] = $this->cacheOperator->fetch('calendar_academics', null, false);
    }
    public function loadSettings()
    {
        if (Session::isLogedIn()){
            $sessions = array_map(fn($row) => [
                'userAgent' => $row['user_agent'],
            ], $this->cacheOperator->fetch('users_sessions', $_SESSION['userId'], false));
        }
        $this->varsArray['settings'] = [
            'sessions' => $sessions ?? null
        ];
    }
    public function loadSchoolFaculty()
    {
        $this->varsArray['faculty'] = $this->cacheOperator->fetch('school_faculty_list', null, false);
        $this->varsArray['facultyJSON'] = json_encode($this->varsArray['faculty']);
    }
    public function loadUserSchoolFaculty()
    {
        $this->varsArray['userFaculty'] = $this->cacheOperator->fetch('users_chosen_people', null, false, false);
    }
    public function loadRetreats($json = false)
    {
        $this->varsArray['retreats'] = $this->cacheOperator->fetch('calendar_retreats', $this->localDate, $json);
    }
    public function getVarsArray(){
        return $this->varsArray;
    }
}