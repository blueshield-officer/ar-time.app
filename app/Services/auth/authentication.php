<?php

use App\Services\auth\Actions;
use App\Services\auth\Checks;
use App\Services\auth\SecurityOperator;

require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    // METHOD CHECK
    if (isset($_POST['method']) && ($_POST['method'] === 'login' || $_POST['method'] === 'register')){
        $method = $_POST['method'];
    }
    else {
        echo 'Method not filled in';
        die();
    }

    // DATA RECORD
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $keep_me_login = $_POST['keep_me_login'] ?? '';

    // PASSWORD CHECK !!!!!!
    $password_valid = Checks::password_valid($email, $password, $method);
    if ($password_valid !== true){
        echo $password_valid;
        die();
    }
    if ($method === 'register'){
        $password_repeat = $_POST['password_repeat'] ?? '';
        if ($password_repeat !== $password){
            echo 'Passwords do not match';
            die();
        }
    }

    // EMAIL CHECK !!!!!!
    $email_valid = Checks::email_valid($email);
    if ($email_valid !== true){
        echo $email_valid;
        die();
    }
    // is email in db and etc
    $email_usable = Checks::email_usable($email, $method);
    if ($email_usable !== true){
        echo $email_usable;
        die();
    }

    if ($method === 'login'){
        $check_login = Checks::check_login($email, $password);
        if ($check_login !== true){
            echo $check_login;
            die();
        }
        Actions::login($email, $keep_me_login);
        echo 'You are logged in!';
        die();
    }
    else {
        // here must go register actions
        Actions::register($email, SecurityOperator::password_hash($password));
        echo 'you are registered, now please login!';
        die();
    }
}
else {
    header('Location: /');
}