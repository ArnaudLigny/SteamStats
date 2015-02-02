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

//$requests = [
//    $client->createRequest('GET', 'http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1?appid=730'),
//];
$i = 0;
foreach ($apps as $app) {
    $i++;
    $requests[] = $client->createRequest('GET', 'http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1?appid=' . $app['_id']);
    if ($i == 10) {
        break;
    }
}

// Results is a GuzzleHttp\BatchResults object.
$results = GuzzleHttp\Pool::batch($client, $requests);

// Can be accessed by index.
//echo $results[0]->getStatusCode();

// Can be accessed by request.
//echo $results->getResult($requests[0])->getStatusCode();

// Retrieve all successful responses
foreach ($results->getSuccessful() as $response) {
    //echo $response->getStatusCode() . "\n";
    //echo $response->getBody() . "\n";
}

// Retrieve all failures.
foreach ($results->getFailures() as $requestException) {
    echo $requestException->getMessage() . "\n";
}

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "$time s\n";