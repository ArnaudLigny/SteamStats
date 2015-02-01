#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    die('CLI only');
}

$time_start = microtime(true);
$i = 0;
$limit = false;

include realpath(__DIR__ . '/../vendor/autoload.php');

$mongo = new MongoClient();
$client = new GuzzleHttp\Client();

$db = $mongo->Steam;
$collection = $db->_apps;
$cursor = $collection->find();
foreach ($cursor as $document) {
    $i++;
    $res = $client->get(
        sprintf('http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1?appid=%s', $document['_id']),
        ['verify' => false]
    );
    $data = json_decode($res->getBody());
    $playerCount = $data->response->player_count;
    // saving in DB
    $cName = 'app_' . $document['_id'];
    $c = $db->$cName;
    $d = array(
        'ts'    => new MongoDate(),
        'count' => $playerCount,
    );
    try {
        $c->insert($d);
    } catch (Exception $e) {
        echo $e->getMessage(), "\n";
    }
    if ($limit && $i == 5) {
        break;
    }
}

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "$time s\n";