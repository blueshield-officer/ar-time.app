<?php
$rootPassword = '1';
$method = strtolower($_SERVER['REQUEST_METHOD']);
if ($method == 'post') {
    $password = $_POST['password'];
    $status = (int)$_POST['status'];
    if ($password == '') {
        exit();
    }
    if ($password != $rootPassword) {
        exit();
    }
    echo 'good';
} else {
    ?>
    <form method="POST">
        <label>Password</label>
        <input name="password" type="password">
        <label>Status</label>
        <input name="status" type="text">
        <input type="submit">
    </form>
    <?php
}
