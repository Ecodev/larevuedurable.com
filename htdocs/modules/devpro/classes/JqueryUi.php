<?php

class JqueryUi{

    /*
     * Ajoute les librairies JS
     * @param   -
     * @return  -
     */
     static function libJs(){
        $js = Html::js('/modules/devpro/libs/jquery_ui/js/jquery-1.4.4.min.js').
              Html::js('/modules/devpro/libs/jquery_ui/js/jquery-ui-1.8.10.custom.min.js').
              Html::js('/modules/devpro/libs/jquery_ui/js/jquery.ui.datepicker-fr.js');
        echo $js;
    }

     /*
     * Ajoute les librairies CSS
     * @param   -
     * @return  -
     */
     static function libCss(){
         echo Html::css('/modules/devpro/libs/jquery_ui/css/ui-lightness/jquery-ui-1.8.10.custom.css');
     }
    
    /*
     * Affiche un datePicker sur un champ
     * @param   string (id du champ)
     * @return  -
     */
    static function datePicker($id){
         $js = '$.datepicker.setDefaults($.datepicker.regional["'.Language::getIsoById(Session::read('id_lang')).'"]);
                $.datepicker.setDefaults({ dateFormat: "yy-mm-dd" });
                $("#'.$id.'").datepicker();
               ';
         echo $js;
    }
    
}

?>
