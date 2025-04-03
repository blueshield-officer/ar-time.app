<?php

namespace App\Services;

use function Brotli\uncompress;

class SmartyVars
{
    private $DBC;
    private $redis;
    private $cacheOperator;
    private $localDate;
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
            $this->localDate = date($dateArray[$type]);
        }
        else {
            $this->localDate = (\DateTime::createFromFormat('m-' . (($type === 'MDY') ? 'd-' : '') . 'Y', $date))->format($dateArray[$type]);
        }

        $this->varsArray = [];

        $this->loadDependencies();
    }

    public function loadDependencies()
    {
        $this->varsArray['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $this->varsArray['theme'] = $_COOKIE['theme'];
        $this->varsArray['loggedIn'] = Session::isLogedIn();
        $this->varsArray['userName'] = $_SESSION['userName'];
        $this->varsArray['userEmail'] = $_SESSION['userEmail'];
    }
    public function loadBase(){
        $tableObject = null;
        $currentDayInfo = $this->cacheOperator->fetch('calendar_schedules', date('Y-m-d'), false);
        if (!is_null($currentDayInfo)) {
            $schedule = $currentDayInfo['schedule'];
            $tableObject = $this->cacheOperator->fetch('bell_schedules', $schedule, false);
            if (!is_null($tableObject)) {
                $tableObject = json_encode($tableObject[array_key_first($tableObject)][$schedule]);
            }
            else {
                $tableObject = 'special';
            }
        }
        $this->varsArray['currentDate'] =  date('Y-m-d');
        $this->varsArray['localDate'] = $this->localDate;
        $this->varsArray['currentTable'] = $tableObject;
    }
    public function loadPages()
    {
        $pages = [
            'Home' =>       ['/', 'house-fill.svg', 20, false],
            'Calendar' =>   ['/calendar', 'calendar-fill.svg', 18, false],
            'Absences' =>   ['/absences', 'person-fill.svg', 20, true],
            'Schedules' =>  ['/schedules', 'table-solid.svg', 19, false],
            'Countdowns' => ['/countdowns', 'hourglass-start-solid.svg', 15, true],
            'Roadmap' =>    ['/roadmap', 'tools.svg', 20, true],
            'Notifications' => ['/notifications', 'bell-fill.svg', 19, false],
            'Settings' =>   ['/settings', 'gear-fill.svg', 20, true]
        ];
        if (!Session::isLogedIn()) {
            unset($pages['Notifications']);
        }
        $this->varsArray['pages'] = $pages;
    }

    public function loadDayInfo()
    {
        $schedule = $this->cacheOperator->fetch('calendar_schedules', $this->localDate, false);
        if (!is_null($schedule)) {
            $schedule['description'] = null;
            // TODO Чёта не фурычит
            if (!is_null($schedule['schedule'])){
                $table = $this->cacheOperator->fetch('bell_schedules', $schedule['schedule'], false);
                if (!is_null($table)) {
                    $this->varsArray['bells'] = $table[array_key_first($table)][$schedule['schedule']];
                }
                if ($schedule['schedule'] === 'special'){
                    $schedule['description'] = 'Mysterious schedule.';
                }
                else {
                    $schedule['description'] = $this->cacheOperator->fetch('bell_schedules_descriptions', $schedule['schedule'], false);
                }

                if ($schedule['schedule'] === 'fid'){
                    $schedule['schedule'] = strtoupper($schedule['schedule']);
                }
                else {
                    $schedule['schedule'] = ucwords($schedule['schedule']);
                }
            }
        }
        $this->varsArray['schedule'] = $schedule;
    }
    public function loadEvents()
    {
        $this->varsArray['events'] = $this->cacheOperator->fetch('calendar_events', $this->localDate, false);
    }
    public function loadSports()
    {
        $this->varsArray['sports'] = $this->cacheOperator->fetch('calendar_sports', $this->localDate, false);
    }
    public function loadDayOff(){
        $this->varsArray['dayOff'] = $this->cacheOperator->fetch('calendar_day_off', $this->localDate, false);
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
    public function loadNextSchedule()
    {
        $earlyDismissalData = $this->cacheOperator->fetch('early_dismissal_schedules', null, false);
        $earlyDismissalSchedulesQueryString = array_map(fn($schedule) => 'schedule='.$schedule, array_keys($earlyDismissalData));
        $this->varsArray['nextSchedule'] = [
            'nonA' => $this->cacheOperator->fetch('calendar_schedules', 'next:schedule!=a', false),
            'earlyDismissal' => $this->cacheOperator->fetch('calendar_schedules', 'next:' . join('OR', $earlyDismissalSchedulesQueryString), false),
        ];
        $this->varsArray['earlyDismissalData'] = $earlyDismissalData;
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
    public function loadRoadMap()
    {
        $this->varsArray['roadmap'] = $this->cacheOperator->fetch('info_roadmap', null, false);
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
                'date' => $row['login_datetime']
            ], $this->cacheOperator->fetch('users_sessions', $_SESSION['userId'], false));
        }
        $this->varsArray['settings'] = [
            'sessions' => $sessions ?? null
        ];
    }
    public function getVarsArray(){
        return $this->varsArray;
    }
}