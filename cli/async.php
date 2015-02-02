#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    //die('CLI only');
}

include realpath(__DIR__ . '/../vendor/autoload.php');

$mongo = new MongoClient();
$db = $mongo->Steam;
$collection = $db->_apps;
$apps = $collection->find();

$client = new GuzzleHttp\Client();

function getCount($client, $appid) {
    $res = $client->get(
        sprintf('http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1?appid=%s', $appid),
        ['verify' => false]
    );
    $data = json_decode($res->getBody());
    return $playerCount = $data->response->player_count;
}

foreach ($apps as $app) {
    $tasks[$app['_id']] = function ($callback) use ($app, $client)  {
        try {
            $count = getCount($client, $app);
            $callback(array(
                'id'     => $app['_id'],
                'result' => $count . ' (' . time() . ')',
            ));
        } catch (\Exception $e) {
            $callback(array());
        }
    };
}

$callback = function (array $results) {

    $log = new Monolog\Logger('file');
    $log->pushHandler(new Monolog\Handler\StreamHandle(realpath(__DIR__ . '/../var/async.log'), Logger::INFO));

    foreach ($results as $result) {
        if ($result) {
            $log->addWarning(print_r($result, true));
        } else {
            $log->addError(print_r($result, true));
        }
    }
};

React\Async\Util::parallel($tasks, $callback);