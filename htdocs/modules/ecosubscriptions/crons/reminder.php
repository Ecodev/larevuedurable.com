#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__) . "/../autoload.php");

$script = new Reminder();

$num = isset($_REQUEST['num']) ? (int) $_REQUEST['num'] : null;
$script->send($num);

