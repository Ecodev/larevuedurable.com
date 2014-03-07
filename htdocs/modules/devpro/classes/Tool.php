<?php

class Tool{

    /*
     * Trouve le nom du module courant
     * @param   -
     * @return  string (nom du module)
     */
    static function moduleName(){
        return Session::read('moduleName');
    }

    /*
     * Trouve le lastID
     * @param   string (nom table)
     * @return  int (id)
     */
    static function lastID($table){
       $res = Db::getInstance()->getRow('SELECT max(id_'.$table.') as nextID FROM '._DB_PREFIX_.$table);
       return $res['nextID'];
    }

    /*
     * Trouve le prochain ID
     * @param   string (nom table)
     * @return  int (id)
     */
    static function nextID($table){
       return Tool::lastID($table)+1;
    }

    /*
     * Echappe une chaine
     * @param   string (chaine)
     * @return  string (chaine escape)
     */
    static function esc($val){
        return mysql_real_escape_string($val);
    }

     /*
     * Redirige dans le back-office
     * @param   array (paramètres)
     * @return  -
     */
    static function redirectBo($params){
        Tools::redirectLink(Html::linkBo(null,$params,null,true));
    }

    /*
     * Nom de l'hôte
     * @param  bool (préfix http)
     * @param  bool (html présent)
     * @return string (host)
     */
    static public function getHttpHost($http=true,$entities=true){
        $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
        if($entities)
            $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
        if($http)
            $host = 'http://'.$host;
        return $host;
    }

    /*
     * Convertis une date US en FR
     * @param  string (date US)
     * @param  string (délimiteur)
     * @return string (date FR)
     */
    static public function dateUsToFr($date,$delimiter='-'){
        $arr = explode('-',$date);
        $y = $arr[0];
        $m = $arr[1];
        $d = $arr[2];
        return $d.'-'.$m.'-'.$y;
    }

    /*
     * Retourne la version de PS en float
     * 1.4.3 == 1.43 pour faire une comparaison sur la grandeur
     * @param  -
     * @return float (1 décimal)
     */
    public static function getPsVersion(){
        $mainVersion = substr(_PS_VERSION_,0,1);
        $subVersion = str_replace('.','',substr(_PS_VERSION_,2,5));
        $version = $mainVersion.'.'.$subVersion;
        return $version;
    }

    /*
     * Retourne une chaine avec trait d'union
     * @param  string
     * @return string
     */
    static function hyphen($string){
        $string = mb_strtolower($string,'UTF-8');
        $string = str_replace(' ', '-', $string);
        $string = str_replace(
        array(
        'à', 'â', 'ä', 'á', 'ã', 'å',
        'î', 'ï', 'ì', 'í',
        'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
        'ù', 'û', 'ü', 'ú',
        'é', 'è', 'ê', 'ë',
        'ç', 'ÿ', 'ñ',
        ),
        array(
        'a', 'a', 'a', 'a', 'a', 'a',
        'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'o', 'o',
        'u', 'u', 'u', 'u',
        'e', 'e', 'e', 'e',
        'c', 'y', 'n',
        ),
        $string
        );
        $string = str_replace("'",'-',$string);
        $string = str_replace('(','-',$string);
        $string = str_replace(')','-',$string);
        $string = str_replace('--','-',$string);
        return $string;
    }

    /*
     * Nettoie une chaine de caractères
     * @param string (chaine)
     * @return string
     */
    public function sanitize($string){
        $string = Tools::htmlentitiesDecodeUTF8($string);
        $string = strip_tags($string);
        $string = str_replace(CHR(13).CHR(10),"",$string); // enlève les retours chariot
        $string = preg_replace('/<br\\s*?\/??>/i','', $string);
        $string = trim($string);
        $string = trim($string);
        $string = str_replace("\r",'',$string);
        $string = str_replace("\n",'',$string);
        return $string;
    }

    /*
     * Affiche un message d'erreur
     * @param string (message)
     * @return string
     */
    public static function msgWarn($msg){
        return '<div class="warn">'.Html::img('/modules/devpro/img/error.png').'&nbsp;'.$msg.'</div>';
    }

    /*
     * Affiche un message d'erreur
     * @param string (message)
     * @return string
     */
    public static function msgError($msg){
        return '<div class="alert error">'.Html::img('/modules/devpro/img/error.png').'&nbsp;'.$msg.'</div>';
    }

    /*
     * Affiche un message de confirmation
     * @param string (message)
     * @return string
     */
    public static function msgConfirm($msg){
        return '<div class="conf confirm"">'.Html::img('/modules/devpro/img/ok.png').'&nbsp;'.$msg.'</div>';
    }

    /*
     * Affiche une flèche de tri
     * @param string (nom du champ en session pour le trie)
     * @param string (tri du champ en session ASC / DESC)
     * @param string (nom du champ de la colonne du tableau)
     * @return string (html)
     */
    public static function imgArrow($order_by,$asc_desc,$field_column){
        if($order_by!=$field_column){
            if($asc_desc=='DESC'){$asc_desc='ASC';}else{$asc_desc='DESC';};
        }
        return Html::img('modules/devpro/img/arrow_'.strtolower($asc_desc).'.gif');
    }

    /*
     * Copie un répertoire
     * @param string (répertoire source)
     * @param string (répertoire destination)
     * return -
     */
    static function copyFolder($source,$target){
        global $trad;
        if(is_dir($source)){
            if(mkdir($target)){
                $d =dir($source);
                while(FALSE!==($entry=$d->read())){
                    if($entry=='.'||$entry =='..') {
                        continue;
                    }
                    $Entry = $source . '/' . $entry;
                    if(is_dir($Entry)){
                        self::copyFolder($Entry,$target.'/'.$entry);
                        continue;
                    }
                    copy($Entry,$target.'/'.$entry );
                }
                $d->close();
            }else{
                echo Tool::msgError($trad['verifiez_le_dossier_chmod_777'].' : /'.$target);
            }
        }else{
            if(!copy($source,$target)){
                echo Tool::msgError($trad['verifiez_le_dossier_chmod_777'].' : /'.$target);
            }
        }
    }

    /*
     * Applati un multi-array sur 1 seul niveau
     * @param array
     * return flat array
     */
    static function arrayFlat($a){
        $ab = array(); if(!is_array($a)) return $ab;
        foreach($a as $value){
            if(is_array($value)){
                $ab = array_merge($ab,Tool::arrayFlat($value));
            }else{
                array_push($ab,$value);
            }
        }
        return $ab;
    }

}

?>
