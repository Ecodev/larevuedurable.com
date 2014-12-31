#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . "/class/MCAPI.class.php");
require_once(dirname(__FILE__) . "/class/Relance.php");

$script = new Relance();

$num = isset($_REQUEST['num']) ? (int) $_REQUEST['num'] : null;
$script->relancer($num);

