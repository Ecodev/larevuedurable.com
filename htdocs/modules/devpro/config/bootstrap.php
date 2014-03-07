<?php

// config PHP
if(defined('_PS_MODE_DEV_')){
    if(_PS_MODE_DEV_==true){
        ini_set('display_errors',1);
        error_reporting(E_ALL);
    }
}
@session_start();

// charge les traductions
require_once(dirname(__FILE__).'/../lang/fr.php');

// Recharge les classes de PS sauf pour V 1.4 +
$psVersion = round(_PS_VERSION_,1);
$moduleName = $this->name;

if($psVersion<='1.3'){
    if(!function_exists('load_ps_classes')){
        function load_ps_classes($className){
            if(file_exists(dirname(__FILE__).'/../../../classes/'.$className.'.php')){
                require_once(dirname(__FILE__).'/../../../classes/'.$className.'.php');
            }
        }
        spl_autoload_register('load_ps_classes');
    }
}

// Charge les classes de devPRO
require_once(dirname(__FILE__).'/devproClasses.php');

// global
global $currentIndex;
global $cookie;
Session::write('id_lang',$cookie->id_lang);

// Uniquement sur des pages non-ajax
// On peut avoir besoin de ces variable -> direct en session
$isAjax = false;
foreach($_GET as $k=>$v){
    if(substr($k,0,5)=='ajax_'){
        $isAjax = true;
        break;
    }
}
if(!$isAjax){
    // conserve les valeurs $_GET en session
    foreach($_GET as $k=>$v){
        Session::write($k,Tool::esc($v));
    }
    Session::write('currentIndex',$currentIndex);
    Session::write('moduleName',$moduleName);
}

// Charge le css par défaut du FK
Html::cssBo('/modules/devpro/css/default.css');

?>
