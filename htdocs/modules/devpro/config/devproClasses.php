<?php
$classNames = array('CombinationProduct',
                    'Data',
                    'Debug',
                    'Email',
                    'File',
                    'Form',
                    'Front',
                    'Html',
                    'Jquery',
                    'Jpicker',
                    'JqueryUi',
                    'Session',
                    'Tool',
                    'Validation',
                    'Widget',
                );
foreach($classNames as $className){
    if(!class_exists($className)){
        require_once(_PS_ROOT_DIR_.'/modules/devpro/classes/'.$className.'.php');
    }
}
?>
