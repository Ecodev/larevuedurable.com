<?php

require_once(dirname(__FILE__) . '/../ecotools/utility/EcoAutoLoad.php');
EcoAutoLoad::getInstance()->addModule(__DIR__);
EcoAutoLoad::getInstance()->addPath('classes/api');
EcoAutoLoad::getInstance()->addPath('libraries/PSWebServiceLibrary');
