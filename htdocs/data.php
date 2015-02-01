<?php
include realpath(__DIR__ . '/../vendor/autoload.php');

$appid = htmlspecialchars($_GET['app']);
if (empty($appid)) {
    $appid = 730; // CS:GO
}
$cName = "app_$appid";

$mongo = new Mongo();
$db = $mongo->Steam;
$collection = $db->$cName;
$cursor = $collection->find();
foreach ($cursor as $document) {
   $data[] = array (
      intval($document['ts']->sec . '000'),
      $document['count']
   );
}
//echo '<pre>', print_r($data), '</pre>';
header('Content-Type: application/json');
echo json_encode($data);