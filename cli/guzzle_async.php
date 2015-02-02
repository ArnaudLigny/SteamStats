#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    //die('CLI only');
}

$time_start = microtime(true);

include realpath(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;

// Create a client with an optional base_url
$client = new GuzzleHttp\Client(['base_url' => 'http://httpbin.org']);

// We want to send this array of requests
$requests = [
    $client->createRequest('GET', '/get'),
    $client->createRequest('DELETE', '/delete'),
    $client->createRequest('PUT', '/put', ['body' => 'test'])
];

// Note: sendAll accepts an array or Iterator
$client->sendAll($requests, [
      // Call this function when each request completes
    'complete' => function (GuzzleHttp\Event\CompleteEvent $event) {
        echo 'Completed request to ' . $event->getRequest()->getUrl() . "\n";
        echo 'Response: ' . $event->getResponse()->getBody() . "\n\n";
    },
    // Call this function when a request encounters an error
    'error' => function (GuzzleHttp\Event\ErrorEvent $event) {
        echo 'Request failed: ' . $event->getRequest()->getUrl() . "\n";
        echo $event->getException();
    },
    // Maintain a maximum pool size of 25 concurrent requests.
    'parallel' => 25
]);

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "$time s\n";