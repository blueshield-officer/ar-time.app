<?php
require __DIR__ . '/../../libs/simplehtmldom/simple_html_dom.php';
function loadData() {
  $url = "https://www.archbishopryan.com/apps/bell_schedules/printerfriendly.jsp";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  $response = curl_exec($ch);
  if(curl_error($ch)) {
    var_dump(curl_error($ch));
  }
  curl_close($ch);
  return trim($response);
}

function writeFile($websiteData){
  $f = fopen(__DIR__ . "/../data-txt/bell_schedules.txt", "w");
  fwrite($f, $websiteData);
  fclose($f);
}

writeFile(loadData());
function tableDataExctractor($table)
{
  $name = nameNormalizer($table->find("caption a", 0)->plaintext);
  $name = match($name) {
      'a' => 1,
      'b' => 2,
      'c' => 3,
      'd' => 4,
      'e' => 5,
      'f' => 6,
      'g' => 7,
      'h' => 8,
      'p' => 9,
      'r' => 10,
      't' => 11,
      'v' => 12,
    };
  $info = [
    'name' => $name,
    'periods' => []
  ];
  $tempArray = $table->find('tbody tr');
  foreach ($tempArray as $row) {
      $info["periods"][] = [
        "name" => $row->find('td', 0)->plaintext,
        "startTime" => $row->find('td', 1)->plaintext,
        "endTime" => $row->find('td', 2)->plaintext,
        "length" => $row->find('td', 3)->plaintext,
        "startUnix" => unixCounter($row->find('td', 1)->plaintext),
        "endUnix" => unixCounter($row->find('td', 2)->plaintext)
      ];
  }
  return $info;
}

function nameNormalizer($text)
{
  $text = strtolower($text);
  $text = str_replace("schedule", "", $text);
  return trim($text);
}
function unixCounter($timeString)
{
  $tempArray = str_split($timeString);
  if (empty($tempArray) || !is_numeric($tempArray[0])) {
    return null;
  }
  list($time, $dayPart) = explode(" ", $timeString);
  list($hours, $minutes) = explode(":", $time);
  if($dayPart === "PM"){
    $hours += 0;
    if ($hours !== 12){
      $hours += 12;
    }
  }
  return $hours * 60 * 60 + $minutes * 60;
}

$file = __DIR__ . '/../data-txt/bell_schedules.txt';
var_dump($file);
$html = file_get_html($file);
$tables = $html->find('table.bell-schedule');
$bellsData = [];
if (count($tables) > 0) {
  foreach ($tables as $element) {
    $bellsData[] = tableDataExctractor($element);
  }
};

function classesSQL($pdo, $bellsData)
{
  $query = 'TRUNCATE TABLE `classes`';
  $statement = $pdo->prepare($query);
  $statement->execute();
  $query = 'INSERT INTO `classes` (`position`, `name`, `start_time`, `end_time`, `length`, `start_unix`, `end_unix`, `schedule_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
  $statement = $pdo->prepare($query);
  foreach ($bellsData as $elementFirst) {
    $name = $elementFirst['name'];
    $i = 0;
    foreach ($elementFirst['periods'] as $elementSecond) {
      $i++;
      $statement->execute([$i, strtolower($elementSecond['name']), $elementSecond['startTime'], $elementSecond['endTime'],  $elementSecond['length'], $elementSecond['startUnix'], $elementSecond['endUnix'], $name]);
    }
  }
}

if(defined('STDIN')){
  require __DIR__ . '/../../app/app.php';
  classesSQL(App::getPDO(), $bellsData);
}
