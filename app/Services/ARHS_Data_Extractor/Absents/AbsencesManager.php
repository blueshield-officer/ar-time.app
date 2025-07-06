<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

use App\Services\ARHS_Data_Extractor\FormattingType;
use App\Services\ARHS_Data_Extractor\SchoolStaffUpdater;
use App\Services\GoogleDocumentExtractor;
use App\Services\Notificator;
use App\Services\Utils;

class AbsencesManager
{
    private $DBC;
    private $redis;
    private $cacheOperator;
    private $notificator;
    private $date;
    private $document;
    private $documentIndexCursor;
    private $peopleShortNameToIdCache;

    private $facultyCacheDeleted = false;

    private $additional = [
        'new' => [],
        'old' => [],
        'changes' => []
    ];
    private $roomChanges = [
        'new' => [],
        'old' => [],
        'changes' => []
    ];
    private $absentPeople = [
        'new' => [],
        'old' => [],
        'changes' => []
    ];

    private $possiblePeriodsToNotify = [
        'hr', 1, 2, 3, 4, 5, 6, 7, 8
    ];

    public function __construct($DBC, $redis, $cacheOperator, $notificator)
    {
        $this->DBC = $DBC;
        $this->redis = $redis;
        $this->cacheOperator = $cacheOperator;
        $this->notificator = $notificator;

        ob_start();
    }

    public function setDate($date = null)
    {
        if (is_null($date)) {
            $date = new \DateTime('now');
            $dayData = $this->fetchDayData($date);
            if ($this->isDay($date, $dayData['schedule']) !== true) {
                $date->modify('+1 day');
                $date = $this->fetchDayData($date)['date'];
            } else {
                $date = $dayData['date'];
            }
        }
        $this->date = \DateTime::createFromFormat('Y-m-d', $date);
    }

    public function loadGoogleDocument($id)
    {
        $googleDocumentExtractor = new GoogleDocumentExtractor($_SERVER['DOCUMENT_ROOT'] . '/../credentials.json');
        $this->document = $googleDocumentExtractor->extract($id);
    }

    public function documentFindDateIndex($maxRepeat)
    {
        // TODO сделать чтобы все символы убирались и тд, ну крч по шику
        // Никогда не забудем 2025-05-02
        $dateParams = [
            'day' => $this->date->format('j'),
            'weekDay' => strtolower($this->date->format('l')),
            'month' => strtolower($this->date->format('F')),
            'year' => $this->date->format('Y')
        ];
        for ($i = 0; $i < $maxRepeat; $i++){
            if (!isset($this->document[$i])){
                break;
            }

            $analyzableStr = Utils::symbols(strtolower($this->document[$i]));

            $pattern = ' ' . $dateParams['month'] . ' ' . $dateParams['day'] . ' ';

            if (str_contains($analyzableStr, $pattern) && $analyzableStr !== '') {
                $this->documentIndexCursor = $i;
                echo 'Index is -- ' . $i;
                return true;
            }
        };
        echo 'DATE WAS NOT FOUND';
        echo '<br>';
        echo 'IF YOU SURE IT IS AN ERROR, CHECK SPELLING IN GOOGLE DOC';
        return false;
    }

    public function documentFindTargetStrings($target, $maxRepeat)
    {
        $stringArray = [];

        $breakWordsArray = [
            'months' => [
                'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'
            ],
            'weekDays' => [
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
            ]
        ];

        $targets = [
            'start' => 'the following teachers is/are absent today',
            'teachers' => 'the following staff is/are absent today',
            'staff' => '____________________________',
            'rooms' => '____________________________',
        ];
        $class = $targets[$target];
        for ($i = 0; $i <= $maxRepeat; $i++){
            $this->documentIndexCursor += 1;
            $stringArray[] = $this->document[$this->documentIndexCursor];
            $str = strtolower($this->document[$this->documentIndexCursor]);
            // 2025-12-06, никогда не забудем
            foreach ($breakWordsArray as $breakWords){
                foreach ($breakWords as $breakWord){
                    if (str_contains($str, $breakWord)){
                        break 3;
                    }
                }
            }
            if (str_contains($str, $class)){
                break;
            }
            else if (str_contains($str, '____________________________')){
                $this->documentIndexCursor -= 1;
                break 1;
            }
        }
        unset($stringArray[count($stringArray) - 1]);
        return $stringArray;
    }

    public function loadRoomChangesAndAdditionalNew($array)
    {
        $teacherTrash = [
            'academic lab',
            'acad. lab',
            'theater'
        ];

        $period = 0;
        foreach ($array as $str){
            $str = trim(strtolower($str));
            if (str_contains($str, 'period')){
                $period = substr($str, 7, 1);
                $str = substr($str, 8);
                if ($str === ''){
                    continue;
                }
            }
            if ($period !== 0){
                $directionArray = explode('-', $str);
                foreach ($directionArray as $element){
                    $tempArray = explode(' to ', strtolower($element));
                    $teachers = $this->fixName($tempArray[0]);
                    $location = ucwords(trim($tempArray[1]));
                    foreach ($teachers as $teacher){
                        $teacher = str_ireplace($teacherTrash, '', $teacher);
                        $teacher = preg_replace('/\s+/', ' ', trim($teacher));

                        // conversion from Mr./Ms. to First name
                        $teacher = $this->getTeacherShortNameByHonorific($teacher);
                        $teacherID = $this->getPersonId($teacher);
                        $this->cachePerson($teacherID, $teacher);

                        $this->roomChanges['new'][$period][$teacherID] = $location;
                    }
                }
            }
            else {
                if (!str_contains($str, 'room change')){
                    $this->additional['new'][] = ucwords($str);
                }
            }
        }
    }

    public function loadPeopleNew($teachers, $staff)
    {
        // TODO, good choice, please fix later
        $positionArray = [
            't' => !empty($teachers) ? $this->uniteStringArray($teachers) : null,
            's' => !empty($staff) ? $this->uniteStringArray($staff) : null,
        ];
        // TODO fix this nonsense
        foreach ($positionArray as $position => $str){
            if (is_null($str)){
                continue;
            }

            $lastSpace = null;
            for ($i = 0; substr($str, $i, 1) !== ''; $i++){
                $symbol = substr($str, $i, 1);
                if ($symbol === ' '){
                    $lastSpace = $i;
                }
                else if ($symbol === '.'){
                    if (count(explode('.', $str)) === 2){
                        $lastSpace = 'LAST PERSON';
                        $fullString = trim($str);
                    }
                    else if ($lastSpace === null){
                        continue;
                    }
                    else {
                        $fullString = substr($str, 0, $lastSpace);
                    }

                    $tempArray = explode('(', $fullString);
                    $name = trim($tempArray[0]);
                    $name = str_replace('.', '. ', $name);
                    $name = FormattingType::oneSpace($name);

                    if (isset($tempArray[1])){
                        $periodsStr = strtolower(str_replace(')', '', $tempArray[1]));
                        $periods = [];
                        $allPeriodArray = ['sb', 'hr', '1', '2', '3', '4', '5', '6', '7', '8'];
                        foreach ($allPeriodArray as $period){
                            if (str_contains($periodsStr, $period)){
                                $periods[] = $period;
                            }
                        }
                        if ($periods === ['sb']){
                            $periods = $allPeriodArray;
                        }
                        else if ($periods === []){
                            $periods[] = $periodsStr;
                        }
                    }
                    else {
                        $periods = ['hr', '1', '2', '3', '4', '5', '6', '7', '8'];
                    }
                    // array for better work with periods
                    $teacherID = $this->getPersonId($name, $position);
                    $this->absentPeople['new'][$teacherID] = $periods;
                    if ($lastSpace === 'LAST PERSON'){
                        break;
                    }
                    else {
                        $str = substr($str, $lastSpace);
                        $i = 0;
                        $lastSpace = null;
                    }
                }
            }
        }
    }

    public function loadPeopleOld()
    {
        $result = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT `teacher_id`, `period` FROM `absent_people` WHERE `date` = ?', [$this->date->format('Y-m-d')]
            )
        );

        foreach ($result as $element) {
            $teacherID = $element['teacher_id'];
            $teacherShortName = $this->getTeacherShortNameById($teacherID);
            $this->cachePerson($teacherID, $teacherShortName);

            $this->absentPeople['old'][$teacherID] ??= [];
            $this->absentPeople['old'][$teacherID][] = $element['period'];
        }
    }

    public function loadRoomChangesOld()
    {
        $result = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT `period`, `teacher_id`, `location` FROM `absent_room_changes` WHERE `date` = ?', [$this->date->format('Y-m-d')]
            )
        );
        foreach ($result as $object) {
            $teacherID = $object['teacher_id'];
            $this->cachePerson($teacherID, $this->getTeacherShortNameById($teacherID));
            $this->roomChanges['old'][$object['period']][$teacherID] = $object['location'];
        }
    }

    public function loadAdditionalOld()
    {
        $result = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT `text` FROM `absent_additional` WHERE `date` = ?', [$this->date->format('Y-m-d')]
            )
        );
        foreach ($result as $object) {
            $this->additional['old'][] = $object['text'];
        }
    }

    public function loadRoomChangesDifference()
    {
        $this->roomChanges['changes'] = $this->findDifferenceBetweenOldAndNew($this->roomChanges['old'], $this->roomChanges['new'], 'rooms');
    }

    public function loadPeopleDifference()
    {
        $this->absentPeople['changes'] = $this->findDifferenceBetweenOldAndNew($this->absentPeople['old'], $this->absentPeople['new'], 'people');
    }

    public function updateSQLAdditional()
    {
        if ($this->additional['new'] !== $this->additional['old']) {
            // add new data to SQL about additional
            $this->DBC->query('DELETE FROM `absent_additional` WHERE `date` = ?', [$this->date->format('Y-m-d')]);
            foreach ($this->additional['new'] as $str) {
                $this->DBC->query('INSERT INTO `absent_additional` (`date`, `text`)  VALUES (?, ?)', [$this->date->format('Y-m-d'), $str]);
            }

            $this->cacheOperator->deleteCache('absent_additional', $this->date->format('Y-m-d'));
        }
    }

    public function updateSQLRoomChanges()
    {
        foreach ($this->roomChanges['changes'] as $method => $array) {
            foreach ($array as $periodIndex => $periodArray) {
                foreach ($periodArray as $teacherID => $location) {
                    $params = [$this->date->format('Y-m-d'), $periodIndex, $teacherID, $location];
                    if ($method === 0){
                        $this->DBC->query('DELETE FROM `absent_room_changes` WHERE `date` = ? AND `period` = ? AND `teacher_id` = ? AND `location` = ?', $params);
                    }
                    else {
                        $this->DBC->query('INSERT INTO `absent_room_changes` (`date`, `period`, `teacher_id`, `location`)  VALUES (?, ?, ?, ?)', $params);
                    }
                }
            }
        }

        if (!empty($this->roomChanges['changes'][1]) && !$this->facultyCacheDeleted) {
            $this->cacheOperator->deleteCache('school_faculty_list', $this->date->format('Y-m-d'));
        }

        $this->cacheOperator->deleteCache('absent_room_changes', $this->date->format('Y-m-d'));
    }

    public function updateSQLPeople()
    {
        foreach ($this->absentPeople['changes'] as $method => $array) {
            foreach ($array as $teacherID => $periods) {
                foreach ($periods as $period) {
                    $params = [$this->date->format('Y-m-d'), $period, $teacherID];
                    if ($method === 0){
                        $this->DBC->query('DELETE FROM `absent_people` WHERE `date` = ? AND `period` = ? AND `teacher_id` = ?', $params);
                    }
                    else {
                        $this->DBC->query('INSERT INTO `absent_people` (`date`, `period`, `teacher_id`)  VALUES (?, ?, ?)', $params);
                    }
                }
            }
        }

        if (!empty($this->absentPeople['changes'][1]) && !$this->facultyCacheDeleted) {
            $this->cacheOperator->deleteCache('school_faculty_list', $this->date->format('Y-m-d'));
        }

        $this->cacheOperator->deleteCache('absent_people', $this->date->format('Y-m-d'));

    }

    public function retreat()
    {
        echo '<pre>';
        var_dump($this->absentPeople['changes']);
        echo '</pre>';
        echo '<pre>';
        var_dump($this->roomChanges['changes']);
        echo '</pre>';

    }

    public function notify(){
        // TODO SB period error
        foreach ($this->absentPeople['changes'] as $method => $array) {
            foreach ($array as $teacherID => $periods) {
                $teacherShortName = $this->getTeacherShortNameById($teacherID);
                foreach ($periods as $period) {
                    if (in_array($period, $this->possiblePeriodsToNotify)){
                        $periodStr = $this->periodIDToStr($period);
                        $tokensToNotify = [];
                        $result = $this->DBC->fetchAll(
                            $this->DBC->query(
                                'SELECT `user_id` FROM `users_chosen_people` WHERE `' . $period . '` = ?', [$teacherID]
                            )
                        );
                        $minVisitDate = Notificator::getMinVisitDate();
                        foreach ($result as $userInfo) {
                            $results = $this->DBC->fetchAll(
                                $this->DBC->query(
                                    'SELECT `notification_token` FROM `users_sessions` WHERE `user_id` = ? AND `notification` = ? AND `notification_absence` = ? AND `last_visit` > ?',
                                    [$userInfo['user_id'], 1, 1, $minVisitDate]
                                )
                            );
                            foreach ($results as $result) {
                                $tokensToNotify[] = $result['notification_token'];
                            }
                        }
                        if (!empty($tokensToNotify)) {
                            if ($method === 1){
                                $title = $teacherShortName . ' is out 🟢';
                                $body = 'Your teacher happens to be out for your ' . $periodStr . '.';
                            }
                            else {
                                $title = $teacherShortName . ' is not out anymore 🔴';
                                $body = 'It happens...';
                            }
    //                        echo '<pre>';
    //                        var_dump($tokensToNotify);
    //                        echo '</pre>';

                            foreach ($tokensToNotify as $token) {
                                var_dump($token);
                                $this->notificator->sendNotification($token, $title, $body, null, '/absences/' . $this->date->format('Y-m-d'));
                            }
                        }
                    }
                }
            }
        }
    }


    private function fetchDayData($date)
    {
        return $this->DBC->fetch($this->DBC->query('SELECT * FROM `calendar_schedules` WHERE `date` >= ? ORDER BY `date` ASC LIMIT 1', [$date->format('Y-m-d')]));
    }

    private function isDay($date, $schedule)
    {
        if ((int)$schedule !== 0) {
            $result = $this->DBC->fetchAll($this->DBC->query('SELECT * FROM `bell_schedules` WHERE `schedule_id` = ? ORDER BY `position` ASC', [$schedule]));
            $end_unix = $result[count($result) - 1]['end_unix'];
            if (is_null($end_unix)) {
                $end_unix = $result[count($result) - 1]['start_unix'];
            }
            $end_unix = $end_unix + strtotime(date_format($date, 'Y-m-d'));
            if ($end_unix < time()) {
                return false;
            } else {
                return true;
            }
        } else {
            $end_unix = strtotime(date_format($date, 'Y-m-d')) + 51600;
            if ($end_unix < time()) {
                return false;
            } else {
                return true;
            }
        }
    }

    private function uniteStringArray($stringArray){
        return preg_replace('!\s+!', ' ', trim(join(' ', $stringArray)));
    }

    private function findDifferenceBetweenOldAndNew($arrayOld, $arrayNew, $type)
    {
        $newData = [
            0 => [],
            1 => []
        ];
        if (is_null($arrayOld)){
            $newData[1] = $arrayNew;
            return $newData;
        }
        else {
            // ==== people ====
            if ($type === 'people'){
                foreach ($arrayNew as $teacherID => $periods) {
                    if (isset($arrayOld[$teacherID])) {
                        $difference = array_diff($periods, $arrayOld[$teacherID]);
                        if (!empty($difference)){
                            $newData[1][$teacherID] = $difference;
                        }
                    } else {
                        $newData[1][$teacherID] = $periods;
                    }
                }
                foreach ($arrayOld as $teacherID => $periods) {
                    if (isset($arrayNew[$teacherID])) {
                        $difference = array_diff($periods, $arrayNew[$teacherID]);
                        if (!empty($difference)){
                            $newData[0][$teacherID] = $difference;
                        }
                    } else {
                        $newData[0][$teacherID] = $periods;
                    }
                }
            }
            // ==== roomChanges ====
            else {
                foreach ($arrayNew as $periodIndex => $period) {
                    foreach ($period as $name => $location) {
                        if (isset($arrayOld[$periodIndex][$name])) {
                            if ($arrayOld[$periodIndex][$name] !== $location) {
                                $newData[1][$periodIndex][$name] = $location;
                            }
                        } else {
                            $newData[1][$periodIndex][$name] = $location;
                        }
                    }
                }
                foreach ($arrayOld as $periodIndex => $period) {
                    foreach ($period as $name => $location) {
                        if (!isset($arrayNew[$periodIndex][$name])) {
                            $newData[0][$periodIndex][$name] = $location;
                        }
                    }
                }
            }
        }
        return $newData;
    }

    private function fixName($str)
    {
        $array = explode(',', $str);
        $newArray = [];
        foreach ($array as $item) {
            $corrected_name = preg_replace('/(\.)([A-Za-z0-9])/', '$1 $2', $item);
            $newArray[] = ucwords($this->analyzable(trim($corrected_name)));
        }
        return $newArray;
    }
    private function analyzable($str){
        return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
    }
    private function getTeacherShortNameById($teacherID)
    {
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `short_name` FROM `school_faculty_list` WHERE `teacher_id` = ?', [$teacherID]
            )
        );
        if ($result === false){
            return null;
        }
        return $result['short_name'];
    }
    private function getPersonId($str, $position = 't', $autoAdd = true)
    {
        if (!isset($this->peopleShortNameToIdCache[$str])){
            $SchoolStaffUpdater = new SchoolStaffUpdater($this->DBC, 'googleDoc', $str, $position);
            $this->peopleShortNameToIdCache[$str] = $SchoolStaffUpdater->getPersonID($autoAdd);

        }
        return $this->peopleShortNameToIdCache[$str];
    }

    private function cachePerson($id, $shortName)
    {
        if (!isset($this->peopleShortNameToIdCache[$shortName])){
            $this->peopleShortNameToIdCache[$shortName] = $id;
        }
    }

    private function getTeacherShortNameByHonorific($honorificName)
    {
        $nameArray = explode('.', $honorificName);
        $honorific = trim(strtolower($nameArray[0]));
        $lastName = trim(strtolower($nameArray[1]));

        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT * FROM `school_faculty_list` WHERE `honorific` = ? AND `last_name` = ?', [$honorific, $lastName]
            )
        );
        if ($result !== false){
            return $result['short_name'];
        }
        foreach (array_keys($this->peopleShortNameToIdCache) as $person){
            if (str_contains(strtolower($person), $lastName)){
                $this->DBC->query(
                    'UPDATE `school_faculty_list` SET `honorific` = ? WHERE `short_name` = ?', [ucwords($honorific), $person]
                );
                return $person;
            }
        }
        return 'Unknown';
    }
    private function periodIDToStr($period)
    {
        $period = strtolower($period);
        if ($period === 'hr'){
            return 'Homeroom';
        }
        else if ((int)$period !== 0){
            $period = (int)$period;
            if ($period === 1) $periodsStr = '1st';
            else if ($period === 2) $periodsStr = '2nd';
            else if ($period === 3) $periodsStr = '3rd';
            else $periodsStr = $period . 'th';
            return $periodsStr . ' Period';
        }
        else {
            return $period;
        }
    }

    public function checkOnErrors()
    {
        $output = ob_get_clean();
        $error = error_get_last();
        if ($error !== null && !str_contains($error['message'], '[WebPush]')) {
            var_dump($error);
            $this->DBC->query('INSERT INTO absent_additional (`date`, `text`) VALUES (?, ?)',
                [$this->date->format('Y-m-d'), 'Unexpected error occured during analysis of the Google Document, because of the broken format, please double check it with the original one.']
            );
//            $stmt = $pdo->prepare("INSERT INTO warnings (message, file, line) VALUES (?, ?, ?)");
//            $stmt->execute([$error['message'], $error['file'], $error['line']]);
        }
        else {
            $this->DBC->query(
                'DELETE FROM absent_additional WHERE `date` = ?', [$this->date->format('Y-m-d')]
            );
        }
    }
}