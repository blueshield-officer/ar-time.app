<?php

namespace App\Services;

use App\Services\Accounts\Accounts;
use App\Services\Accounts\AuthManager;

class FetchHandler
{
    private $DBC;
    private $redis;
    private $json;

    private $requestTitle;
    private $rowsNames;
    private $requestContent;

    private $tableName;
    private $dataTypes;


    private $requirement;

    private $tableRows = [];
    private $params = [];


    private $user_token = null;
    private $user_id = null;

    private $validRequests = [
        'account' => [
            'users_sessions' => [
                'keepMyNotifAlive' => [
                    'rows' => ['last_visit'],
                    'dataTypes' => [null]
                ],
                'logOut' => [
                    'rows' => ['user_id'],
                    'dataTypes' => [null]
                ],
                'notificationToken' => [
                    'rows' => ['notification', 'notification_token'],
                    'dataTypes' => ['string', null]
                ],
                'notificationAbsence' => [
                    'rows' => ['notification_absence'],
                    'dataTypes' => ['tinyint']
                ],
                'notificationReport' => [
                    'rows' => ['notification_report'],
                    'dataTypes' => ['tinyint']
                ],
                'notificationReportTime' => [
                    'rows' => ['notification_report_time'],
                    'dataTypes' => ['time']
                ],
                'notificationGuider' => [
                    'rows' => ['notification_guider'],
                    'dataTypes' => ['tinyint']
                ]
            ],
            'users_accounts' => [

            ],
            'users_chosen_people' => [
                'accountHomeroom' => [
                    'rows' => ['hr'],
                    'dataTypes' => ['int', null]
                ],
                'accountCustom' => [
                    'rows' => ['custom'],
                    'dataTypes' => ['int', null]
                ]
            ]
        ],
        'none' => [
            'users_change_password_tokens' => [
                'requestChangePassword' => [
                    'rows' => ['custom'],
                    'dataTypes' => ['email']
                ]
            ],
            'session' => [
                'localDate' => [
                    'rows' => ['date'],
                    'dataTypes' => ['date']
                ]
            ]
        ]
    ];
    public function __construct($DBC, $redis, $json, $requirement)
    {
        $this->DBC = $DBC;
        $this->redis = $redis;
        $this->json = $json;
        $this->requirement = $requirement;

        if ($requirement === 'account'){
            $userData = Session::getAccountSessionData();

            $this->user_token = $userData['token'];
            $this->user_id = $userData['id'];
        }
        $this->rowsNames = null;

        $this->finishLoadingChoices();
    }

    public function process(){
        if (gettype($this->json) !== 'string') {
            $this->invalidRequset();
        }
        $data = json_decode($this->json, true);

        if (json_last_error() !== JSON_ERROR_NONE){
            $this->invalidRequset();
        }

        if (!isset($data['title']) || (!isset($data['content']) && $data['content'] !== null)){
            $this->invalidRequset();
        }

        $this->requestTitle = $data['title'];
        $this->requestContent = $data['content'];

        foreach ($this->validRequests as $requirement => $data) {
            if ($this->requirement === $requirement) {
                foreach ($data as $tableName => $params) {
                    if (isset($this->validRequests[$this->requirement][$tableName][$this->requestTitle])) {
                        $this->tableName = $tableName;
                        $this->rowsNames = $params[$this->requestTitle]['rows'];
                        $this->dataTypes = $params[$this->requestTitle]['dataTypes'];
                    };
                }
            }
        }

        if ($this->rowsNames === null){
            $this->invalidRequset();
        }

        if (is_int($this->requestContent)){
            if (
                !(
                    (in_array('int', $this->dataTypes)) ||
                    (in_array('4int', $this->dataTypes) && $this->is4Int($this->requestContent)) ||
                    (in_array('tinyint', $this->dataTypes) && $this->isTinyInt($this->requestContent))
                )
            ) {
                $this->invalidRequset();
            }
        }

        else if (is_string($this->requestContent)){
            if (
                !(
                    (in_array('date', $this->dataTypes) && Utils::isYmdTypeDate($this->requestContent)) ||
                    (in_array('time', $this->dataTypes) && Utils::isTime($this->requestContent)) ||
                    (in_array('string', $this->dataTypes)) ||
                    (in_array('email', $this->dataTypes) && Utils::validateEmail($this->requestContent))
                )
            ) {
                $this->invalidRequset();
            }
        }

        else if (is_object($this->requestContent)){
            if (
                !(
                (in_array('object', $this->dataTypes))
                )
            ) {
                $this->invalidRequset();
            }
        }

        else if (is_null($this->requestContent)){
            if (!in_array(null, $this->dataTypes)){
                $this->invalidRequset();
            }
        }

        else {
            $this->invalidRequset();
        }


        // exceptions
        if ($this->requestTitle === 'notificationToken'){
            $status = ($this->requestContent === null) ? 0 : 1;
            $this->params[] = $status;
            if ($status === 0) {
                $this->requestContent = null;
            }
            else {
                $webPush = new Notificator();
                if ($webPush->testNotification($this->requestContent) === false){
                    $this->invalidRequset();
                }
            }
        }
        else if ($this->requestTitle === 'keepMyNotifAlive'){
            $this->requestContent = date('Y-m-d H:i:s');
        }
        else if ($this->requestTitle === 'logOut') {
            $authManager = new AuthManager($this->DBC);
            $authManager->logOut();
            exit();
        }
        else if ($this->requestTitle === 'requestChangePassword') {
            $email = $this->requestContent;
            $userID = Accounts::getAccountID($this->DBC, $email);
            if ($userID !== false){
                $result = $this->DBC->fetch(
                    $this->DBC->query(
                        'SELECT * FROM users_change_password_tokens WHERE user_id = ? AND timestamp_expires >= ? AND used = ?', [$userID, date('Y-m-d H:i:s'), 0]
                    )
                );
                if ($result === false){
                    $token = Utils::generateRandomSymbols(64);
                    $this->sendRequestSQL(
                        'INSERT INTO users_change_password_tokens (`user_id`, `email`, `timestamp_expires`, `token`) VALUES (?, ?, ?, ?)',
                        [$userID, $email, date('Y-m-d H:i:s', strtotime('+5 minutes')), $token]
                    );
                    // TODO чота придумать а то костыль пиздец
                    $mailer = new Mailer(Controller::renderStatic('mail/passwordChange.tpl', [
                        'changed' => false,
                        'token' => $token])
                    );
                    $mailer->sendMail($email, 'Reset Password Link');

                    $this->returnMessage(true, 'Email with password reset link has been sent to your email.');
                }
                else {
                    $this->returnMessage(false, 'Email with reset link was already sent.');;
                }
            }
            else {
                $this->returnMessage(false, 'Email does not exist.');
            }
            exit();
        }
        else if ($this->requestTitle === 'localDate'){
            $date = new \DateTime('now');
            $stringDateArray = explode('-', $this->requestContent);
            $date->setDate($stringDateArray[0], $stringDateArray[1], $stringDateArray[2]);
            Session::setLocalDate($date);
            $this->successRequest();
        }
        else if ($this->tableName === 'users_chosen_people' && !is_null($this->requestContent)){
            $result = $this->DBC->fetch(
                $this->DBC->query(
                    'SELECT 1 FROM school_faculty_list WHERE teacher_id = ? LIMIT 1', [$this->requestContent]
                )
            );
            if ($result === false){
                $this->invalidRequset();
            }
        }

        $this->tableRows = $this->rowsNames;
        $this->params[] = $this->requestContent;

        $this->sendRequestSQL();
        $this->successRequest();
    }

    private function sendRequestSQL($customQuery = null, $customParams = null)
    {
        if (is_null($customQuery) && is_null($customParams)){
            if ($this->tableName === 'users_sessions') {
                $this->tableRows = join(', ', array_map(fn($item) => '`' . $item . '` = ?', $this->tableRows));
                $this->params[] = $this->user_id;
                $this->params[] = $this->user_token;
                $result = $this->DBC->query(
                    'UPDATE ' . $this->tableName . ' SET ' . $this->tableRows . ' WHERE `user_id` = ? AND `token_cookie` = ?', $this->params
                );
            }
            else {
                $this->tableRows = join(', ', array_map(fn($item) => '`' . $item . '` = ?', $this->tableRows));
                $this->params[] = $this->user_id;
                $result = $this->DBC->query(
                    'UPDATE ' . $this->tableName . ' SET ' . $this->tableRows . ' WHERE `user_id` = ?', $this->params
                );
            }
        }
        else {
            $result = $this->DBC->query(
                $customQuery, $customParams
            );
        }
    }

    private function isTinyInt($object) {
        return $object === 1 || $object === 0;
    }

    private function is4Int($object)
    {
        return (is_int($object) && strlen($object) === 4);
    }

    private function finishLoadingChoices() {
        foreach (range(1, 8) as $i) {
            $this->validRequests['account']['users_chosen_people']['accountPeriod' . $i] = [
                'rows' => [$i],
                'dataTypes' => ['int', null]
            ];
        }
    }

    private function invalidRequset(){
        $this->returnMessage(false, 'Invalid request.');
        exit();
    }

    private function successRequest()
    {
        $this->returnMessage(true, 'Request has been sent and processed.');
        exit();
    }

    private function returnMessage($success, $message)
    {
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }
}