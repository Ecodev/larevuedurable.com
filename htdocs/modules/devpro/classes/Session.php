<?php

class Session{

    /*
     * Passe une variable en session
     * @param   string (nom var)
     * @param   string (valeur)
     * @return  -
     */
    static function write($name,$value){
        $_SESSION['devpro.'.$name] = $value;
    }

    /*
     * Lit une variable en session
     * @param   string (nom var)
     * @return  string (var)
     */
    static function read($name){
        if(isset($_SESSION['devpro.'.$name])){
            return $_SESSION['devpro.'.$name];
        }
    }

    /*
     * Supprime une variable en session
     * @param   string (nom var)
     * @return  -
     */
    static function delete($name){
        if(isset($_SESSION['devpro.'.$name])){
            unset($_SESSION['devpro.'.$name]);
        }
    }

    /*
     * Charge les données en session
     * @param   array ($data)
     * @return  -
     */
    static function loadData($data){
        Session::delete('data');
        Session::write('data',$data);
    }
    
}

?>
