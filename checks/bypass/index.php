<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    require_once __DIR__ . '/../../app/app.php';
    $pdo = App::getPDO();
    $query = 'SELECT `code` FROM `bypass_code`';
    $stmt = $pdo->prepare($query);
    $connected = $stmt->execute();
    if ($connected) {
        $result = $stmt->fetchAll();
        if ($result !== false) {
            require __DIR__ . '/../../database/sql_output/bypass_code.php';
            $bypass_code = new BypassCode($pdo);
            $bypass_code->fetchSQL();
            $code = $bypass_code->return();
            $sentCode = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_NUMBER_INT);
            if (!isset($smarty)){
                require __DIR__ . '/../../libs/smarty/libs/Smarty.class.php';
                $smarty = new Smarty();
                $smarty->setTemplateDir(__DIR__ . '/../../templates/');
                $smarty->setCompileDir(__DIR__ . '/../../cache/templates_c/');
                $smarty->setConfigDir(__DIR__ . '/../../cache/configs/');
                $smarty->setCacheDir(__DIR__ . '/../../cache/cache/');
            }
            if (in_array($sentCode, $code)) {
                $_SESSION['bypass_code'] = $sentCode;
                $smarty->assign('access', 1);
                $query = 'UPDATE `bypass_code` SET uses = uses + 1 WHERE code = ' . $sentCode;
                $stmt = $pdo->prepare($query);
                $stmt->execute();
            }
            else {
                $_SESSION['bypass_code'] = 0;
                $smarty->assign('access', 0);
            }
            $smarty->display('other/access.tpl');
        }
    }
}
