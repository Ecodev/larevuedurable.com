<?php

class Front extends Module{

     /*
     * Charge un élément sur le front
     * @param   string (nom du fichier php/tpl sans l'extension)
     * @param   string (nom du hook)
     * @return  tpl
     */
    function loadTpl($file,$hookName=null){

        global $cookie;
        global $smarty;
        Session::write('hookName',$hookName);
        
        if(file_exists(dirname(__FILE__).'/../../'.$this->name.'/form/front/'.$file.'.php')){
            include dirname(__FILE__).'/../../'.$this->name.'/form/front/'.$file.'.php';
        }else{
            echo '<span class="fk_error">'.$this->l('Le fichier "'.$file.'.php" est absent dans le dossier "/modules/'.$this->name.'/front"</span>');
        }
        
        if(Tool::getPsVersion()<'1.4'){
           return $this->display(__FILE__,'/../../'.$this->name.'/form/front/tpl/'.$file.'.tpl');
        }else{
           return $this->display(_PS_MODULE_DIR_.$this->name,'/form/front/tpl/'.$file.'.tpl');
        }
    }
}

?>
