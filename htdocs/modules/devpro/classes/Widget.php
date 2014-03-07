<?php
class Widget{

    /*
     * Affiche une barre de partage pour les réseaux sociaux
     * @param   -
     * @return  string
     */
    static public function addThis(){
        return '
        <div id="addthis" class="addthis_toolbox addthis_default_style ">
        <a class="addthis_button_preferred_1"></a>
        <a class="addthis_button_preferred_2"></a>
        <a class="addthis_button_preferred_3"></a>
        <a class="addthis_button_preferred_4"></a>
        <a class="addthis_button_compact"></a>
        <a class="addthis_counter addthis_bubble_style"></a>
        </div>
        <div class="clear"></div>
        <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4db82b377d0568fb"></script>';
    }

}
?>
