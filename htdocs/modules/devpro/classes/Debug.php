<?php

class debug{

    /*
     * Affiche le contenu d'une variable
     * @param   var
     * @return  -
     */
    public static function e($var){
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    /*
     * Affiche le contenu d'une variable Ajax via FirePHP
     * @param   var
     * @return  -
     */
    public static function firePHP($var){
        require_once(_PS_MODULE_DIR_.'devpro/libs/firephp/FirePHP.class.php');
        $FirePHP = FirePHP::getInstance(true);
        $FirePHP->fb($var);
    }
    
}

?>
