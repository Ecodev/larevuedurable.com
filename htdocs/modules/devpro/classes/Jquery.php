<?php

class Jquery{

    /*
     * Démarre la librairie Jquery
     * @param   -
     * @return  -
     */
    static function init(){
        // prends en charge automatiquement Jquery UI
        JqueryUi::libCss();
        JqueryUi::libJs();
    }

    /*
     * Démarre $(document).ready
     * @param   -
     * @return  -
     */
    static function start(){
        $js = '$(document).ready( function(){';
        echo $js;
    }

     /*
     * Termine $(document).ready
     * @param   -
     * @return  -
     */
    static function close(){
        $js = '});';
        echo $js;
    }

}

?>
