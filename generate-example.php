<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/vendor/autoload.php';

// create a log channel
$log = new Logger('test');
//$log->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));
$log->pushHandler(new StreamHandler('generated_log.log', Logger::DEBUG));

$levels = array_map('strtolower', array_keys(Logger::getLevels()));

for ($i = 0; $i < ($argv[1] ?? pow(10,9001)); $i++) {
    $log->{$levels[array_rand($levels)]}('Test message');
}
