<?php
if ($_GET['au7hEfudnhUUy8'] !== '667a4af374347aaebc6d04d8572fbb85250cf8b51c773f3f8dc8b4be867a9a02'){
    die();
}
require_once __DIR__ . '/../../app/app.php';
class UpdateBypassCode
{
    private $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateBypassCode (){
        $query = 'UPDATE `bypass_code` SET `code` = (?), `uses` = 0 WHERE `id` = "public"';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([random_int(100000, 999999)]);
    }
}

$byPassCode = new UpdateBypassCode(App::getPDO());
$byPassCode->updateBypassCode();