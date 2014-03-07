<?php

class Data{

    /*
     * Vérifie si les données existent en base
     * @param   string (table)
     * @param   string (col)
     * @param   string (val)
     * @return  bool
     */
    static function exist($table,$col,$val){
        $data = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_ .$table.' WHERE '.$col.'="'.$val.'"');
        if(empty($data)){
            return false;
        }else{
            return true;
        }
    }

    /*
     * Crée une pagination de données
     * @param   int (nb. résultats)
     * @param   int (nb. limit max pagination)
     * @param   string (url)
     * @return  string (html)
     */
    static function pagination($nbResults,$limit_pagin_max,$url){
        $res_per_page = Session::read('res_per_page');
        $nbPages = $nbResults/$res_per_page;
        global $trad;
        $html = '<div id="devpro_pagination">'.$trad['pages'].' ';
        for($i=0;$i<$nbPages;$i++){
            $pageNo = $i+1;
            $limit_pagin1 = $i*$res_per_page;
            $limit_pagin2 = Tools::getValue('limit_pagin2');
            $current_page = Tools::getValue('current_page');
            if(empty($current_page)){$current_page=1;}
            if($pageNo==$current_page){$class='current_page';}else{$class='';}
            $html .= '<a class="'.$class.'" href="'.$url.'&limit_pagin1='.$limit_pagin1.'&limit_pagin2='.$limit_pagin2.'&current_page='.$pageNo.'">'.$pageNo.'</a> | ';
        }
        $html = substr($html,0,-2);
        // nb enregistrements affichés dans le tableau
        $url_redirect_js = $url.'&limit_pagin1='.@$limit_pagin1.'&limit_pagin2='.@$limit_pagin2.'&res_per_page=';
        $html .= '
        <script language="javascript">
            $(document).ready(function(){
               $("#pagination_res_per_page").change(function(){
                  window.location = $(this).val();
               });
            });
        </script>
        <select id="pagination_res_per_page" name="res_per_page">
            <option value="'.$url_redirect_js.'1" ';if($res_per_page==1){$html.='selected';}$html.='>1</option>
            <option value="'.$url_redirect_js.'20" ';if($res_per_page==20){$html.='selected';}$html.='>20</option>
            <option value="'.$url_redirect_js.'50" ';if($res_per_page==50){$html.='selected';}$html.='>50</option>
            <option value="'.$url_redirect_js.'100" ';if($res_per_page==100){$html.='selected';}$html.='>100</option>
            <option value="'.$url_redirect_js.'300" ';if($res_per_page==300){$html.='selected';}$html.='>300</option>
            <option value="'.$url_redirect_js.'1000000" ';if($res_per_page==1000000){$html.='selected';}$html.='>'.$trad['tous'].'</option>
        </select>';
        $html .= '</div>';
        return $html;
    }

}

?>
