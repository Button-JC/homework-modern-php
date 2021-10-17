<?php
include_once "LogCounter.php";
include_once "UserFilter.php";
const TIME_REPORT_DELAY = 0.1;

// read filename from arguments or use default
if (isset($argv) && array_key_exists(1, $argv)) {
    $logFile = $argv[1];
} else {
    $logFile = "example.log";
}
function echoProgress($data)
{
    global $time;
    if (microtime(true) >= $time + TIME_REPORT_DELAY) {
        $time = microtime(true);
        $total_count = 0;
        foreach ($data as $level => $count) {
            $total_count += $count;
        }
        echo("\rZaznamu zpracovano: " . $total_count);

    }
}

$logCounter = new LogCounter($logFile);
$logCounter->setWatcher("echoProgress");

$ignoreAlerts = new UserFilter();
$ignoreAlerts->setIgnorePattern('/test\.ALERT/');

$countNoticeTestMessageAsInfo = new UserFilter();
$countNoticeTestMessageAsInfo->setReplacePattern('(NOTICE: Test message)',"INFO");

$logCounter->addUserFilter($ignoreAlerts);
$logCounter->addUserFilter($countNoticeTestMessageAsInfo);

try {
    $data = $logCounter->readFile();
    echoProgress($data);
    echo "\n-----------" . PHP_EOL;
    foreach ($data as $level => $count) {
        echo "$level: $count" . PHP_EOL;
    }
} catch (Exception $e) {
    echo("Error reading log file. " . $e->getMessage());
}

