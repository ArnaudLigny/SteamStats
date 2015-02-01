<?php
include realpath(__DIR__ . '/../vendor/autoload.php');

$appid = htmlspecialchars($_GET['app']);

$mongo = new Mongo();
$db = $mongo->Steam;
$collection = $db->_apps;
$document = $collection->findOne(array('_id' => intval($appid)), array('name'));
$name = $document['name'];
header('Content-Type: application/json');
echo json_encode($name);