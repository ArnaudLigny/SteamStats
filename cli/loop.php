<?php
include realpath(__DIR__ . '/../vendor/autoload.php');

$loop = React\EventLoop\Factory::create();
    $loop->addPeriodicTimer(30, function () {
    echo "Test : " . time() . "\n";
});
$loop->run();