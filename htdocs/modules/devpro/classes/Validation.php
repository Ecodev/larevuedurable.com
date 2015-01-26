<?php

class Validation extends Form{

    /*
     * Vérifie si le champ est vide
     * @param   var
     * @param   string nom du champ
     * @return  bool
     */
    static function notEmpty($var,$name){
        if(empty($var)){
            return 'error';
        }else{
            return true;
        }
    }

    /*
     * Vérifie s'il s'agit bien d'une date
     * @param   string (date)
     * @return  bool
     */
    static function isDate($date){
        if(Validate::isDate($date)){
            return true;
        }else{
            return 'error';
        }
    }
    
    /*
     * Vérifie s'il s'agit bien d'un email
     * @param   string (email)
     * @return  bool
     */
    static function isEmail($email){
        if(preg_match('/^[a-z0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z0-9]+[._a-z0-9-]*\.[a-z0-9]+$/ui', $email)){
            return true;
        }else{
            return 'error';
        }
    }

    /*
     * Vérifie si l'url est bien valide
     * @param   string (url)
     * @return  bool
     */
    static function isUrl($url){
        if(preg_match('/^([[:alnum:]]|[~:#%&_=\(\)\.\? \+\-@\/])+$/ui',$url)){
            return true;
        }else{
            return 'error';
        }
    }

    /*
     * Vérifie si le captcha est valide
     * @param   string (date)
     * @return  bool
     */
    static function isCaptcha($captcha){
        $captchaSession = Session::read('captcha');
        if($captcha==$captchaSession){
            return true;
        }else{
            return 'error';
        }
    }

    /*
     * Vérifie si le fichier est uploadable
     * @param   string (date)
     * @return  bool
     */
    static function isUpload($var,$name,$parameters){

        global $trad;

        // taille du fichier
        $maxFileSize = $parameters['maxFileSize'];
        $fileSize = filesize($_FILES[$name]['tmp_name']);
        if($fileSize>$maxFileSize){
            echo Tool::msgError( $trad['upload_le_fichier_est_trop_gros']);
            return 'error';
        }

        // type de fichier
        if(isset($parameters['extAllowed']) && is_array($parameters['extAllowed'])){
            $ext=strrchr($_FILES[$name]['name'],'.');
            $ext=substr($ext,1);
            if(!empty($ext)){
                if(!in_array($ext,$parameters['extAllowed'])){
                    $lstExtAllowed='';
                    foreach($parameters['extAllowed'] as $extAllowed){
                        $lstExtAllowed.=$extAllowed.', ';
                    }
                    $lstExtAllowed = substr($lstExtAllowed,0,-2);
                    echo Tool::msgError($trad['upload_type_de_fichier_refuse']);
                    echo Tool::msgError($trad['upload_les_types_de_fichier_accepte'].' : '.$lstExtAllowed);
                    return 'error';
                }
            }
        }

        // transfert du fichier
        if(isset($parameters['filename'])){$filename=Tool::hyphen($parameters['filename']);}else{$filename=Tool::hyphen($_FILES[$name]['name']);}
        if(substr($parameters['destinationPath'],-1)!='/'){$parameters['destinationPath'].='/';}
        // si le champ est rempli
        if(!empty($_FILES[$name]['name'])){
            if(move_uploaded_file($_FILES[$name]['tmp_name'],$parameters['destinationPath'].$filename)){
                    echo Tool::msgConfirm($trad['upload_succes']);
                    return true;
            }else{
                echo Tool::msgError($trad['upload_erreur_transfert_fichier']);
                return 'error';
            }
        }
    }
}

?>
