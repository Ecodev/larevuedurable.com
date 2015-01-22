<?php
class File{

    /*
     *  Lit le contenu d'un fichier
     *  @param string (nom du fichier)
     *  return string (contenu du fichier)
     */
    static function readContent($file){
        global $trad;
        if(file_exists($file)){
            $fileContent = '';
            $fp = @fopen($file,'r');
            if($fp){
                while(!feof($fp)){$fileContent .= fgets($fp, 4096);}
                return $fileContent;
            }else{
                echo Tool::msgError($trad['impossible_lire_fichier'].' : '.$file);
            }
        }else{
             echo Tool::msgError($trad['fichier_introuvable'].' : '.$file);
        }
    }

    /*
     *  Remplace une chaine de caractères dans un fichier
     *  @param string (emplacement du fichier)
     *  @param string (chaine à remplacer)
     *  @param string (nouvelle chaine)
     *  @param bool (autorise à remplacer si la chaine de remplacement est déjà présente)
     *  return bool
     */
    static function replaceStringInFile($file,$stringSearch,$stringReplace,$allowStringReplaceExist=false){
        global $trad;
        $fileContent = File::readContent($file);
        if(!empty($fileContent)){
             if(!$allowStringReplaceExist){$posStringReplace = strpos($fileContent,$stringReplace);}else{$posStringReplace='notEmpty';}
             if(empty($posStringReplace)){
                 if(empty($posString)){$posString = strpos($fileContent,$stringSearch);}
                 if(!empty($posString)){
                     $fileContentNew = str_replace($stringSearch,$stringSearch.$stringReplace,$fileContent);
                     @chmod($file,0777);
                     $fp = @fopen($file,'w+');
                     if($fp){
                        if(!fwrite($fp,$fileContentNew)){
                           echo Tool::msgError($trad['impossible_mettre_a_jour_fichier'].' : '.$file.'<br/>'.$trad['verifiez_le_fichier_chmod_777']);
                        }else{
                           fclose($fp);
                           return true;
                        }
                     }else{
                         echo Tool::msgError($trad['impossible_ouvrir_le_fichier'].' : '.$file.'<br/>'.$trad['verifiez_le_fichier_chmod_777']);
                         return false;
                     }
                 }else{
                      echo Tool::msgError($trad['impossible_localiser_chaine_dans_fichier'].' : '.$file);
                      return false;
                 }
            }else{
                echo Tool::msgError($trad['le_fichier_contient_deja_les_modifications'].' : '.$file);
                return false;
            }
        }
    }

}
?>