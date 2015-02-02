#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    //die('CLI only');
}

$time_start = microtime(true);

include realpath(__DIR__ . '/../vendor/autoload.php');

$mongo = new MongoClient();
$db = $mongo->Steam;
$collection = $db->_apps;
$apps = $collection->find();

$client = new GuzzleHttp\Client();

function getCount($client, $app) {
    $res = $client->get(
        sprintf('http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1?appid=%s', $app['_id']),
        ['verify' => false]
    );
    $data = json_decode($res->getBody());
    return $playerCount = $data->response->player_count;
}

$loop = React\EventLoop\Factory::create();

$i = 0;

foreach ($apps as $app) {
    $i++;
    $tasks[$app['_id']] = function ($callback) use ($loop, $app, $client)  {
        try {
            $count = getCount($client, $app);
            $callback(array(
                'app'     => $app['_id'],
                'players' => $count,
                'ts'      => time(),
            ));
        } catch (\Exception $e) {
            $callback(array(
                'app'     => $app['_id'],
            ));
        }
    };
    if ($i == 10) {
        $loop->stop();
        break;
    }
}

$callback = function (array $results) {
    $log = new Monolog\Logger('file');
    $log->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/../var/async.log', Monolog\Logger::INFO));
    foreach ($results as $result) {
        if ($result) {
            $log->addInfo(json_encode($result));
        } else {
            $log->addError(json_encode($result));
        }
    }
};

React\Async\Util::parallel($tasks, $callback);
$loop->run();

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "$time s\n";