<?php

class Html extends Module{

    /*
     * Attache une feuille CSS pour la partie publique
     * @param   string (url vers le fichier)
     * @param   bool (préfix uri)
     * @return  string
     */
    static function css($url,$uri=true){
        if(substr($url,0,1)=='/'){$url=substr($url,1);}
        if($uri){
            $url = __PS_BASE_URI__.$url;
        }
        // W3C
        global $css_files;
        $css_files[$url] = 'all';
        return '<link href="'.$url.'" rel="stylesheet" type="text/css" />';
    }

     /*
     * Ajoute un fichier JS
     * @param   string (url vers le fichier)
     * @param   bool (préfix uri)
     * @return  string
     */
    static function js($url,$uri=true){
        if(substr($url,0,1)=='/'){$url=substr($url,1);}
        if($uri){
            $url = __PS_BASE_URI__.$url;
        }
    	$js_file = '<script type="text/javascript" src="'.$url.'"></script>';
        echo  $js_file;
    }

    /*
     * Ouvre la balise pour ajouter du code JS
     * @param   -
     * @return  string
     */
    static function jsStartScript(){
        echo '<script type="text/javascript">';
    }

    /*
     * Ouvre la balise pour ajouter du code JS
     * @param   -
     * @return  string
     */
    static function jsCloseScript(){
        echo '</script>';
    }

    /*
     * Attache une feuille CSS pour le BO
     * @param   string (nom du fichier avec extension placés dans /modules/votremodule/css/
     * @param   bool (préfix uri)
     * @return  string
     */
    static function cssBo($url,$uri=true){
        if(substr($url,0,1)=='/'){$url=substr($url,1);}
        // non W3C
        if($uri){
            $url = __PS_BASE_URI__.$url;
        }
        // connecté dans le back-office
        $connected_bo = Tools::getValue('token');
        if($connected_bo){
            echo '<link type="text/css" rel="stylesheet" href="'.$url.'" />';
        }
    }
    
    /*
     * Affiche un lien dans backoffice
     * @param   string (texte du lien)
     * @param   array (paramètres clé=>val)
     * @param   array (img=>nom de l'images sous /modules/votremodule/img & title=>titre image
     * @param   bool (retourner uniquement l'url
     * @return  string
     */
    static function linkBo($label=null,$params=null,$img=null,$url_only=false){

        global $currentIndex;
        $module_name = @$params['module'];
        $tab = @$params['tab'];
        $url_full = @$params['url'];
        $class = @$params['class'];
        if(!empty($class)){$class='class="'.$class.'"';}

        // Paramètres gets
        $parameters = '';
        if(!empty($params)){
            foreach($params as $p=>$v){
                $parameters .= '&'.$p.'='.$v;
            }
            if(!empty($module_name)){$parameters .= '&module_name='.$module_name;}
        }

        // target
        if(isset($params['target']) && !empty($params['target'])){$target = 'target="'.$params['target'].'"';}else{$target='';}

        // si un paramètre msg existe on va le faire afficher
        if(isset($params['msg'])){
            $msg = '<div class="conf confirm">'.Html::img('/modules/devpro/img/ok.png').'&nbsp;'.$params['msg'].'</div>';
            Session::write('msgConfirm',$msg);
        }
     
        $js = '';
        // message de confirmation sur le lien
        if(isset($params['confirm'])){$js = 'onclick="return confirm(\''.Tool::esc($params['confirm']).'\')"';}
        // si c'est un lien delete
        if($img['img']=='del.png'){ $js = 'onclick="return confirm(\'Supprimer ?\')"';}

        // lien module
        if(!empty($module_name) && empty($tab) && empty($url_full)){
            $url = Tool::getHttpHost().$currentIndex.'&configure='.$module_name.'&token='.Tools::getValue('token').$parameters;
        // lien général back-office
        }elseif(!empty($tab) && empty($url_full)){
            $url = '?'.$parameters;
        // lien personnalisé
        }elseif(!empty($url_full)){
             $url = $url_full;
        }

        if(!empty($img)){$img= '<img src="../modules/'.$module_name.'/img/'.$img['img'].'" title="'.@$img['title'].'" style="vertical-align:bottom" />&nbsp;';}
        $link = '<a href="'.$url.'" '.$target.' '.$js.' '.$class.'>'.$img.$label.'</a>';

        if($url_only){
            return $url;
        }else{
            return $link;
        }
        
    }

     /*
     * Affiche une image
     * @param   string url vers le fichier
     * @param   string (title)
     * @param   bool (image du type icone)
     * @param   bool (racine du site par défaut)
     * @return  string
     */
    static function img($url,$title=null,$ico=false,$uriPath=true){
        $style = '';
        if($ico){$style = 'style="vertical-align:bottom"';}
        if($uriPath){
            return '<img src="'.__PS_BASE_URI__.$url.'" title="'.$title.'" '.$style.'/>';
        }else{
            return '<img src="'.$url.'" title="'.$title.'" '.$style.'/>';
        }
    }

}

?>
