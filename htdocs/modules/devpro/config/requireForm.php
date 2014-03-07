<?php

include dirname(__FILE__).'/beforeForm.php';

$connected_bo = Tools::getValue('token');
// Effectue des includes de forme en fonction du get
$form = Tools::getValue('form');
if(!empty($form)){
    if(file_exists(dirname(__FILE__).'/../../'.$moduleName.'/form/bo/'.$form.'.php')){
        include dirname(__FILE__).'/../../'.$moduleName.'/form/bo/'.$form.'.php';
    }else{
        if(!empty($connected_bo)){
            echo '<span class="fk_error">'.$this->l('Le formulaire "'.$form.'.php" est absent dans le dossier "/modules/'.$moduleName.'/form/bo"</span>');
        }
    }
}else{
    // formulaire par défaut
    if(Module::isInstalled($this->name)){
        // form via tab
        if(substr(Tools::getValue('tab'),0,5)=='Admin' && Tools::getValue('tab')!='AdminModules' && Tools::getValue('tab')!='AdminPayment'  && Tools::getValue('tab')!='AdminModulesPositions'){ // accès via sous-onglet
            if(file_exists(dirname(__FILE__).'/../../'.$moduleName.'/form/bo/tab_default.php')){
                include dirname(__FILE__).'/../../'.$moduleName.'/form/bo/tab_default.php';
            }else{
                echo '<span class="devpro_error">'.$this->l('Le formulaire "tab_default.php" est absent dans le dossier "/modules/'.$moduleName.'/form/bo"</span>');
            }
        // form via modules
        }else{
            if(file_exists(dirname(__FILE__).'/../../'.$moduleName.'/form/bo/default.php')){
                if(!empty($connected_bo)){
                    // 1.3
                    if(Tool::getPsVersion()<'1.4'){
                        if(isset($_GET['configure']) && $_GET['configure']==$this->name){
                           include dirname(__FILE__).'/../../'.$moduleName.'/form/bo/default.php'; 
                        }
                    // 1.4
                    }else{
                        if(isset($_GET['configure']) && $_GET['module_name']==$this->name){
                           include dirname(__FILE__).'/../../'.$moduleName.'/form/bo/default.php';
                        }
                    }  
                }
            }else{
                 if(!empty($connected_bo)){
                    echo '<span class="devpro_error">'.$this->l('Le formulaire "default.php" est absent dans le dossier "/modules/'.$moduleName.'/form/bo"</span>');
                 }
            }
        }
    }
}

include dirname(__FILE__).'/afterForm.php';

?>
