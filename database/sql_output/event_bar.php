<?php
$query = 'SELECT `event_color`, `event_text` FROM `event_bar` WHERE id = 1';
$statement = $pdo->prepare($query);
$statement->execute();
$event_bar = $statement->fetch();
if ($event_bar !== false) {
  $smarty->assign('event_bar', json_encode($event_bar));
}
else {
  $smarty->assign('event_bar', '');
}
