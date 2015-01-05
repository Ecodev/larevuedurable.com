<?php

$settingsFile = dirname(__FILE__) . "/../htdocs/config/settings.inc.php";
$localFile = dirname(__FILE__) . "/../htdocs/config/local.inc.php";
$taskFile = dirname(__FILE__) . "/" . $argv[1] . '.php';

require_once($settingsFile);
require_once($localFile);
require_once($taskFile);

$params = array_slice($argv, 2, count($argv)-1);

$class = $argv[1];
$obj = new $class($params);

try {
    echo $obj->main();
} catch (Exception $e) {
    echo 'ERROR - ' . $e->getMessage();
}

echo "\n";