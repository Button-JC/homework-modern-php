<?php
include_once "LogCounter.php";
include_once "UserFilter.php";
const TIME_REPORT_DELAY = 0.1;

// read filename from arguments or use default name
if (isset($argv) && array_key_exists(1, $argv)) {
    $logFile = $argv[1];
} else {
    $logFile = "example.log";
}

/**
 * Callback function used for time limited output
 * @param $data array with level=>count data
 */
function echoProgress(array $data)
{
    global $time;
    if (!isset($time) || microtime(true) >= $time + TIME_REPORT_DELAY) {    // limits display frequency
        $time = microtime(true);

        // Print sum to console
        printSumToConsole($data);
    }
}

/** Prints progress to console in form of changing sum
 * @param array $data with level=>count data
 */
function printSumToConsole(array $data): void
{
    $total_count = 0;
    foreach ($data as $count) {
        $total_count += $count;
    }
    echo("\rZaznamu vyhodnoceno: " . $total_count);
}

// Create LogCounter instance
try {
    $logCounter = new LogCounter($logFile);


    // Demonstration of Ignore filter
    $ignoreAlerts = new UserFilter();
    $ignoreAlerts->setIgnorePattern('/test\.ALERT/');

    // Demonstration of Replacement filter
    $countNoticeTestMessageAsInfo = new UserFilter();
    $countNoticeTestMessageAsInfo->setReplacePattern('(NOTICE: Test message)', "INFO");

    // Add filters to LogCounter
    $logCounter->addUserFilter($ignoreAlerts);
    $logCounter->addUserFilter($countNoticeTestMessageAsInfo);

    // Step counter
    foreach ($logCounter->stepFileReading() as $data) {
        echoProgress($data);
    }
    if (!isset($data)) {
        echo("No result have been generated.");
    } else {
        // Override progress with final value
        printSumToConsole($data);
        echo "\n-----------" . PHP_EOL;

        // Display final statistic
        foreach ($data as $level => $count) {
            echo "$level: $count" . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo("Error reading log file. " . $e->getMessage());
}

