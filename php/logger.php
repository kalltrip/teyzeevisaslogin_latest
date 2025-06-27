<?php
function write_log($filename, $data) {
    $logFile = __DIR__ . "/logs/$filename";
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $logData = "[" . date("Y-m-d H:i:s") . "] " . print_r($data, true) . PHP_EOL;
    file_put_contents($logFile, $logData, FILE_APPEND);
}
?>
