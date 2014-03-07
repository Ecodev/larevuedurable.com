<?php

class Jpicker{

    /*
     * Démarre la librairie Jquerypicker
     * @param   -
     * @return  -
     */
    static function init(){
        // prends en charge automatiquement Jquery UI
        Jpicker::libCss();
        Jpicker::libJs();
    }

     /*
     * Ajoute les librairies JS
     * @param   -
     * @return  -
     */
     static function libJs(){
        $js = Html::js('/modules/devpro/libs/jquery_jpicker/jpicker-1.1.6.min.js');
        echo $js;
    }

    /*
     * Ajoute les librairies CSS
     * @param   -
     * @return  -
     */
     static function libCss(){
         $js = Html::css('/modules/devpro/libs/jquery_jpicker/css/jPicker-1.1.6.min.css');
         echo $js;
     }

     /*
     * Input avec sélecteur de couleur
     * @param   string (input id)
     * @return  -
     */
     static function colorPicker($id){
        $js = "$('#".$id."').jPicker({
                 images:
                 {
                    clientPath: '".__PS_BASE_URI__."modules/devpro/libs/jquery_jpicker/images/'
                 }
               });";
        echo $js;
     }

}

?>
