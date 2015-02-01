#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    die('CLI only');
}

include realpath(__DIR__ . '/../vendor/autoload.php');

// loading Steam apps
$client = new GuzzleHttp\Client();
$res = $client->get(
    'http://api.steampowered.com/ISteamApps/GetAppList/v0001/',
    ['verify' => false]
);
$data = json_decode($res->getBody());
$apps = $data->applist->apps->app;
// saving in DB
$mongo = new MongoClient();
$db = $mongo->Steam;
$collection = $db->_apps;
foreach($apps as $app) {
    $document = array(
        '_id' => $app->appid,
        'name' =>  $app->name,
    );
    try {
        $collection->insert($document);
    } catch (Exception $e) {
        // MongoWriteConcernException
        echo $e->getMessage(), "\n";
    }
}