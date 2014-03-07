<?php

class Form extends Module{

    private $_table;
    private $_data;
    private $_fields_simple;
    private $_fields_multilang;
    private $_fieldset;
    public  $_errors;
    public  $_errors_fields;
    public  $_nb_fields_with_validation=0;
    public  $_tiny_mce_init;

    /*
     * Initialise un formulaire
     * @param   string (url action)
     * @param   string (nom table)
     * @param   méthode (post)
     * @param   array (champs & champs multilingues)
     * @param   string (légende)
     * @param   array (class, style etc..)
     * @return  -
     */
    function init($table,$action=null,$method='post',$fields=array(),$legend=null,$parameters=array()){

        if($_POST){$this->_data = $_POST;}  // conserver le post dans _data

        $this->_table = $table;
        if(!isset($fields['fields_simple'])){$fields['fields_simple']=array();}
        $this->_fields_simple = $fields['fields_simple'];
        if(!isset($fields['fields_multilang'])){$fields['fields_multilang']=array();}
        if(isset($fields['fields_multilang'])){$this->_fields_multilang = $fields['fields_multilang'];}else{$this->_fields_multilang = array();}

        // formulaire d'upload ?
        if(isset($parameters['upload']) && $parameters['upload']==true){$enctype = 'enctype="multipart/form-data"';}else{$enctype='';}

        if($action==null){$action = $_SERVER['REQUEST_URI'];}

        $html = '';
        if(!empty($legend)){
            $html.='<fieldset><legend>'.$legend.'</legend>';
            $this->_fieldset = true;
        }

        $class = $this->classInput($parameters);
        $html .= '<form id="form_'.$table.'" name="form_'.$table.'" '.$enctype.' method="'.$method.'" action="'.$action.'" '.$class.'>
                 <div><input type="hidden" name="table" id="table" value="'.$table.'"/></div>
                 ';
        return $html;
    }

    /*
     * Champ text
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   string (valeur)
     * @param   array (paramètres)
     * @param   bool (champ de type password)
     * @return  string (html)
     */
    public function text($name,$label=null,$parameters=null,$typePassword=false){

        if(isset($this->_data[$name])){ $value = $this->_data[$name];}

        $value = @$parameters['value'];
        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $afterInput = @$parameters['afterInput'];
        $beforeInput = @$parameters['beforeInput'];
        $clearDivDisabled = @$parameters['clearDivDisabled'];

        if($typePassword){$inputType='password';}else{$inputType='text';}

        // pour gérer les champs multilingues
        global $cookie;
        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(false);
        $iso = Language::getIsoById(intval($cookie->id_lang));
        // Crée une chaine des champs multilingues titre¤description¤etc....
        $divLangName = '';
        foreach($this->_fields_multilang as $dLangName){
            $divLangName .= $dLangName.'¤';
        }
        $divLangName = substr($divLangName,0,-2);
        
        $html = '<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>';

        if(in_array($name,$this->_fields_multilang)){
            $errorMsg = $this->validation(@$parameters['validation'],'body_'.$name.'_'.$cookie->id_lang);
            $html .= '<div id="div_'.$name.'" '.$styleDiv.'>';
                $valueDefaultIsEmpty=0;
                foreach($languages as $language){
                    if(empty($value)){$value=$this->getContentField($name.'_'.$language['id_lang']);$valueDefaultIsEmpty=1;}
                    $html .= '
                    <div id="'.$name.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">'.
                        $this->label($label,$name).'
                        '.$beforeInput.'<input type="'.$inputType.'" id="body_'.$name.'_'.$language['id_lang'].'" name="body_'.$name.'_'.$language['id_lang'].'" value="'.$value.'" class="'.$class.'" '.$style.' />&nbsp;'.$afterInput.
                        $errorMsg.'
                    </div>';
                    if($valueDefaultIsEmpty){$value='';}
                }
            $html .= $this->displayFlags($languages,$defaultLanguage,$divLangName,$name, true);
            if(!$clearDivDisabled){$html .='<div class="clear pspace"></div>';}
            $html .= '</div>';
        }else{
            if(empty($value)){$value=$this->getContentField($name);}
            $errorMsg = $this->validation(@$parameters['validation'],$name);
            $html .=
            '<div id="div_'.$name.'" '.$styleDiv.'>'.
                $this->label($label,$name).'
                '.$beforeInput.'<input type="'.$inputType.'" id="'.$name.'" value="'.$value.'" name="'.$name.'" class="'.$class.'" '.$style.'/>&nbsp;'.$afterInput.
                $errorMsg;
                if(!$clearDivDisabled){$html .='<div class="clear pspace"></div>';}
            $html .='</div>';
        }
        return $html;
    }

    /*
     * Champ hidden
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   string (valeur)
     * @param   array (paramètres)
     * @return  string (html)
     */
    public function hidden($name,$label=null,$parameters=null){

        if(isset($this->_data[$name])){$value = $this->_data[$name];}

        $value = @$parameters['value'];
        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $afterInput = @$parameters['afterInput'];
        $beforeInput = @$parameters['beforeInput'];

        if(empty($value)){$value=$this->getContentField($name);}
        $errorMsg = $this->validation(@$parameters['validation'],$name);
        if(!empty($label)){
            $html =
            '<div id="div_'.$name.'" '.$styleDiv.'>'.
                $this->label($label,$name).'
                '.$beforeInput.'<input type="hidden" id="'.$name.'" value="'.$value.'" name="'.$name.'" class="'.$class.'" '.$style.'/>&nbsp;'.$afterInput.
                $errorMsg.'
                <div class="clear pspace"></div>
            </div>';
        }else{
           $html = $beforeInput.'<input type="hidden" id="'.$name.'" value="'.$value.'" name="'.$name.'" class="'.$class.'" '.$style.'/>&nbsp;'.$afterInput;
        }
        
        return $html;
    }
    
     /*
     * Champ textarea
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   array (parametres)
     * @return  string (html)
     */
    public function textarea($name,$label=null,$parameters=null,$textareaSimple=false){

        $value = @$parameters['value'];
        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];

    	// pour gérer les champs multilingues
        global $cookie;
        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(false);
        $iso = Language::getIsoById(intval($cookie->id_lang));
        // Crée une chaine des champs multilingues titre¤description¤etc....
        $divLangName = '';
        foreach($this->_fields_multilang as $dLangName){
            $divLangName .= $dLangName.'¤';
        }
        $divLangName = substr($divLangName,0,-2);

        $js = '';
        if(!$textareaSimple){
            if(Tool::getPsVersion()>='1.43'){
                if($this->_tiny_mce_init==0){
                    $isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
                    $ad = dirname($_SERVER["PHP_SELF"]);
                    $js = '
                    <script type="text/javascript">
                    var iso = \''.$isoTinyMCE.'\' ;
                    var pathCSS = \''._THEME_CSS_DIR_.'\' ;
                    var ad = \''.$ad.'\' ;
                    </script>
                    <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
                    <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>
                    <script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>';
                    $this->_tiny_mce_init=1;
                }
            }elseif(Tool::getPsVersion()>'1.34' && Tool::getPsVersion()<'1.43'){
               if($this->_tiny_mce_init==0){
                   $js = '
                   <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
                        <script type="text/javascript">
                            tinyMCE.init({
                                mode : "textareas",
                                theme : "advanced",
                                plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
                                // Theme options
                                theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
                                theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
                                theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
                                theme_advanced_toolbar_location : "top",
                                theme_advanced_toolbar_align : "left",
                                theme_advanced_statusbar_location : "bottom",
                                theme_advanced_resizing : false,
                                content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
                                document_base_url : "'.__PS_BASE_URI__.'",
                                width: "600",
                                height: "auto",
                                font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
                                elements : "nourlconvert,ajaxfilemanager",
                                file_browser_callback : "ajaxfilemanager",
                                entity_encoding: "raw",
                                convert_urls : false,
                                language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
                            });
                            function ajaxfilemanager(field_name, url, type, win) {
                                var ajaxfilemanagerurl = "'.dirname($_SERVER["PHP_SELF"]).'/ajaxfilemanager/ajaxfilemanager.php";
                                switch (type) {
                                    case "image":
                                        break;
                                    case "media":
                                        break;
                                    case "flash":
                                        break;
                                    case "file":
                                        break;
                                    default:
                                        return false;
                            }
                            tinyMCE.activeEditor.windowManager.open({
                                url: "'.dirname($_SERVER["PHP_SELF"]).'/ajaxfilemanager/ajaxfilemanager.php",
                                width: 782,
                                height: 440,
                                inline : "yes",
                                close_previous : "no"
                            },{
                                window : win,
                                input : field_name
                            });
                        }
                </script>';
                $this->_tiny_mce_init=1;
                }
            // 1.3 et -
            }else{
                if($this->_tiny_mce_init==0){
                    $js = '
                    <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
                    <script type="text/javascript">
                    function tinyMCEInit(element){
                        $().ready(function() {
                                $(element).tinymce({
                                        // Location of TinyMCE script
                                        script_url : \''.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js\',
                                        // General options
                                        theme : "advanced",
                                        plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
                                        // Theme options
                                        theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                                        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
                                        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
                                        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
                                        theme_advanced_toolbar_location : "top",
                                        theme_advanced_toolbar_align : "left",
                                        theme_advanced_statusbar_location : "bottom",
                                        theme_advanced_resizing : false,
                                        content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
                                        width: "582",
                                        height: "auto",
                                        font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
                                        elements : "nourlconvert",
                                        convert_urls : false,
                                        language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
                                });
                        });
                    }
                    tinyMCEInit(\'textarea.rte\');
                    </script>
                    <script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>';
                    $this->_tiny_mce_init=1;
                }
            }
        } // if !$textareaSimple
        $html = $js.$this->label($label,$name);

        if(in_array($name,$this->_fields_multilang)){
            $html .= '<div id="div_'.$name.'" '.$styleDiv.'>';
            $valueDefaultIsEmpty=0;
            foreach($languages as $language){
                $errorMsg = $this->validation(@$parameters['validation'],$name.'_'.$language['id_lang']);
                if(empty($value)){$value=$this->getContentField($name.'_'.$language['id_lang']);$valueDefaultIsEmpty=1;}
                $html .= '
                <div id="'.$name.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
                    <textarea cols="70" rows="30" id="body_'.$name.'_'.$language['id_lang'].'" name="body_'.$name.'_'.$language['id_lang'].'" class="rte '.$class.'" '.$style.'>'.$value.'</textarea>
                </div>';
                if($valueDefaultIsEmpty){$value='';}
            }
            $html .= $this->displayFlags($languages, $defaultLanguage, $divLangName,$name, true).
                     $errorMsg.'
                     <div class="clear pspace"></div>';
            $html .= '</div>';
        }else{
            if(empty($value)){$value=$this->getContentField($name);}
            $errorMsg = $this->validation(@$parameters['validation'],$name);
            $html .= '
            <div id="div_'.$name.'" '.$styleDiv.'>
                <textarea cols="70" rows="30" id="'.$name.'" name="'.$name.'" class="rte '.$class.'" '.$style.'>'.$value.'</textarea>'.
                $errorMsg.'
                <div class="clear pspace"></div>
            </div>';
        }

        return $html;
    }

    /*
     * Champ select
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   array (liste des valeurs)
     * @param   string (nom de l'id)
     * @param   string (nom du champ a afficher dans le select)
     * @param   array (paramètre)
     * @return  string (html)
     */
    public function select($name,$label=null,$values=array(),$display_field=null,$parameters=array()){

        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $afterInput = @$parameters['afterInput'];
        $beforeInput = @$parameters['beforeInput'];
        $disabledDivClear = @$parameters['disabledDivClear'];

        $input = array();
        $input['type'] = 'select';
        $input['values'] = $values;
        $input['display_field'] = $display_field;
       
        $html =
        '<div id="div_'.$name.'" '.$styleDiv.'>'.
            $this->label($label,$name).
            $beforeInput.'
            <select id="'.$name.'" name="'.$name.'" class="'.$class.'" '.$style.'>'.
                $this->getContentField($name,$input).'
            </select>'.
            $afterInput;
            if(!$disabledDivClear){$html.='<div class="clear pspace"></div>';}
        $html.='
        </div>';
        return $html;
    }

    /*
     * Champ select pour les catégories
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   array (paramètre)
     * @param   bool (chemin complet catégorie + parent)
     * @return  string (html)
     */
    public function selectCategories($name,$label=null,$parameters=array(),$fullPathParent=false){

        global $trad;
        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];

        $depth = 0;
        $categTree = Category::getRootCategory()->recurseLiteCategTree($depth);

        if(!function_exists('constructTreeNode')){ // évite le conflit avec la recréation de la fonction si plusieurs listes déroulantes sont crée
            function constructTreeNode($node,$space=null,$fullPathParent){
                if($fullPathParent){
                    $Category = new Category($node['id']);
                    $ret = '<option value="'.$node['id'].'">'.Tools::getPath(intval($Category->id),$space.$node['name']).'</option>';
                }else{
                    $ret = '<option value="'.$node['id'].'">'.$space.$node['name'].'</option>';
                }
                if(!empty($node['children'])){
                    global $lstSpace;
                    if(!$fullPathParent){$lstSpace.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';}
                    foreach ($node['children'] AS $child){
                        $ret .= constructTreeNode($child,$lstSpace,$fullPathParent);
                    }
                }
                return $ret;
            }
        }

        $html =
        '<div id="div_'.$name.'" '.$styleDiv.'>'.
            $this->label($label,$name).'
            <select id="'.$name.'" name="'.$name.'" class="'.$class.'" '.$style.'>
                <option value="">'.$trad['toutes_les_categories'].'</option>';
                foreach($categTree['children'] AS $child){
                    $html .= constructTreeNode($child,'',$fullPathParent);
                }
              $html .=
             '</select>
             <div class="clear pspace"></div>
        </div>';
        return $html;
    }

    /*
     * Checkbox
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   string (valeur)
     * @param   array (paramètres)
     * @return  string (html)
     */
    public function checkbox($name,$label=null,$value=null,$parameters=array()){

        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $beforeInput = @$parameters['beforeInput'];
        $afterInput = @$parameters['afterInput'];
        $checkedList = @$parameters['checked'];
        $disabledDivClear = @$parameters['disabledDivClear'];
        if(!is_array($checkedList)){$checkedList = array(@$parameters['checked']);}

        // une checkbox cochée vaut 1 par défaut
        if(empty($value)){$value = 1;}

        $input['type'] = 'checkbox';
        $input['value'] = $value;
        $input['checkedList'] = $checkedList;
        $html = '<div id="div_'.$name.'" '.$styleDiv.'>'.$this->label($label,$name).'&nbsp;'.$beforeInput.'<input type="checkbox" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$this->getContentField($name,$input).' class="'.$class.'" '.$style.'/>&nbsp;'.$afterInput.'</div>';
        if(!$disabledDivClear){$html.='<div class="clear pspace"></div>';}
        return $html;
   }
   
     /*
     * Radio
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   array (valeur)
     * @param   array (paramètres)
     * @return  string (html)
     */
    public function radio($name,$label=null,$values=null,$parameters=array()){

        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $beforeInput = @$parameters['beforeInput'];
        $afterInput = @$parameters['afterInput'];
        $checked = @$parameters['checked'];

        $input['type'] = 'radio';
        $input['checked'] = $checked;

        $html= '<div id="div_'.$name.'" '.$styleDiv.'>'.$this->label($label,$name).'&nbsp;'.$beforeInput.'&nbsp;';
        foreach($values as $k=>$v){
            $input['value'] = $k;
            $html .= '<input type="radio" name="'.$name.'" value="'.$k.'" '.$this->getContentField($name,$input).' />&nbsp;'.$v.'&nbsp;';
        }
        $html.=$afterInput.'
                </div>
                <div class="clear pspace"></div>';
        return $html;
   }

    /*
     * Champ file
     * @param   string (nom du champ)
     * @param   string (label)
     * @param   string (valeur)
     * @param   array (paramètres)
     * @return  string (html)
     */
    public function file($name,$label=null,$parameters=null){

        if(isset($this->_data[$name])){ $value = $this->_data[$name];}

        $value = @$parameters['value'];
        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $afterInput = @$parameters['afterInput'];
        $beforeInput = @$parameters['beforeInput'];
        $parameters['validation'] = array('isUpload');

        if(empty($value)){$value=$this->getContentField($name);}

        $maxFileSize = @$parameters['maxFileSize'];
        if(empty($maxFileSize)){$parameters['maxFileSize'] = 1000000;} // 1 mega
        $errorMsg = $this->validation($parameters['validation'],$name,$parameters);

        $html = '
        <div id="div_'.$name.'" '.$styleDiv.'>'.
            $this->label($label,$name).'
            '.$beforeInput.'
            <input type="hidden" name="MAX_FILE_SIZE" value="'.$maxFileSize.'">
            <input type="file" id="'.$name.'" value="'.$value.'" name="'.$name.'" class="'.$class.'" '.$style.'/>&nbsp;'.$afterInput.
            $errorMsg.'
            <div class="clear pspace"></div>
        </div>';
        
       return $html;
   }

   /*
    * Affiche le label
    * @param   string/array (label)
    * @param   string (field name)
    * @return  string (html)
    */
   public function label($label,$name){
       if(!empty($label)){
           if(is_array($label)){
               $a_label = $label['label'];
               if(isset($label['style'])){
                 $a_style = $this->css($label['style']);
               }else{
                 $a_style = '';
               }
               return '<label for="'.$name.'" '.$a_style.'">'.$a_label.'&nbsp;</label>';
           }else{
               return '<label for="'.$name.'" style="margin-right:5px;">'.$label.'&nbsp;</label>';
           }
       }
   }

   /*
     * Champ submit
     * @param   string (nom du champ)
     * @param   string (label).
     * @param   array (parameters ex. css)
     * @return  string (html)
     */
    public function submit($name='submit',$label=null,$parameters=null){

        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $formDisabled = @$parameters['formDisabled'];
        if($formDisabled){$formTagClose='';}else{$formTagClose='</form>';}

        if(empty($label)){
            $label = $this->l('Sauver');
        }
        if($this->_fieldset && !$formDisabled){$fieldset='</fieldset>';}else{$fieldset ='';}

        $style = $this->css(@$parameters['style']);
        $class = $this->classInput($parameters);
        if(empty($parameters['class'])){$class='class="button"';}

        // Crée un espace pour l'alignement du bouton si on est dans le BO
        $labelSpace = '';
        if(!empty($label) && Tools::getValue('token') && empty($parameters['labelDisabled'])){ $labelSpace = '<label>&nbsp;</label>';}

        return  '<div id="div_'.$name.'" '.$styleDiv.'>'.$labelSpace.'<input type="submit" name="'.$name.'" value="'.$label.'" '.$style.' '.$class.' /></div>'.$formTagClose.' '.$fieldset;
    }

    /*
     * Ferme le formulaire
     * @param   -
     * @return  string (html)
     */
    public function end(){
        return '</form>';
    }

   /*
     * Récupère les attributs CSS
     * @param   array (paramètres)
     * @return  string (html)
    */
    public function css($parameters){
        if(!is_array($parameters)){$parameters=array();}
        $style = '';
        foreach($parameters as $k=>$v){
            $style .= $k.':'.$v.';';
        }
        $style = 'style="'.$style.'"';
        return $style;
    }

    /*
     * Valide les erreurs du formulaires
     * @param   array (paramètres de validation)
     * @param   string (nom du champ)
     * @param   array  (paramètres)
     * @return  string (error)
    */
    public function validation($validations,$name,$parameters=null){
        if($_POST){
            if(isset($_POST[$name]) || isset($_FILES[$name])){
                if(!is_array($validations)){$validations=array();}
                if(!empty($validations)){
                    $this->_nb_fields_with_validation++;
                    foreach($validations as $rule){
                        if($rule!='isUpload'){
                            $checkValidation = Validation::$rule(Tools::getValue($name),$name);
                        }else{
                            $checkValidation = Validation::$rule(Tools::getValue($name),$name,$parameters);
                        }
                        if($checkValidation==='error'){
                            $this->_errors_fields[] = $name;
                            Session::write('formError',true); // erreur détectée dans le formulaire
                            $errorMsg = '';
                            switch($rule){
                                case 'notEmpty':
                                    $errorMsg = $this->l('Veuillez remplir ce champ');
                                    break;
                                case 'isDate':
                                    $errorMsg = $this->l('Veuillez indiquer une date valide');
                                    break;
                                case 'isEmail':
                                    $errorMsg = $this->l('Veuillez indiquer un email valide');
                                    break;
                                case 'isCaptcha':
                                    $errorMsg = $this->l('Le code anti-bot n\'est pas valide');
                                    break;
                                case 'isUrl':
                                    $errorMsg = $this->l('L\' url n\'est pas valide');
                                    break;
                                case 'isUpload':
                                    $errorMsg = $this->l('Le fichier ne peut être transféré');
                                    break;
                            }
                            return '<div class="devpro_error">'.$this->label('&nbsp;','&nbsp;').$errorMsg.'</div>';
                        }else{
                            if(empty($this->_errors_fields)){
                                Session::write('formError',false);
                            }
                        }
                    }
                }
            } // endif $_POST[$name]
        } // endif $_POST
    }

   /*
     * Récupère la classe
     * @param   array (attributs)
     * @return  string (html)
    */
    public function classInput($parameters){
        return 'class="'.@$parameters['class'].'"';
    }
    
    /*
     * Conserve/Charge le contenu d'un champ
     * @param   nom du champ
     * @param   array values (uniquement pour input type SELECT)
     * @param   array (paramètres)
     * @return  -
     */
    function getContentField($name,$input=null,$parameters=null){

        $data = Session::read('data');
        if(empty($data)){$data=array();}

        if(empty($input['type'])){
        
            // Conservation lors du post
            // champ standard
            if(isset($_POST[$name])){
                return $_POST[$name];
            // champ multilingue
            }else{
                if(isset($_POST['body_'.$name])){
                    return $_POST['body_'.$name];
                }
            }

            // Si c'est un champ simple chargé avec Configuration::get('xxx')
            if(!is_array($data)){
                $value = $data;
                $data = array();
                $data[0][$name] = $value;
            }

            // Si c'est un ensemble de champ simples
            if(!isset($data[0])){
                $dataArray = $data;
                $data = array();
                $data[0] = $dataArray;
            }
           
            // Remplissage des champs automatique via requête
            foreach($data as $fields){

                // Vérifie s'il s'agit bien d'enregistrement multilingues
                $multilang = 0;
                $nb_records = count($data);

                if(is_array($fields)){

                    //if(array_key_exists('id_lang',$fields) && $nb_records>1){ // perturbe
                    if(array_key_exists('id_lang',$fields)){
                        $multilang = 1;
                    }

                    foreach($fields as $fieldname=>$content){
                        // si multiligue
                        if($multilang){
                            if($fieldname.'_'.$fields['id_lang'] == $name){
                                return $content;
                            }else{
                                // si un champ non-multiligue est mélangé aux multiligue
                                if(in_array($name,$this->_fields_simple)){
                                    return @$fields[$name];
                                }
                            }
                        // champ simple
                        }else{
                            if($fieldname == $name){
                                return $content;
                            }
                        }
                    }
                }
            }
        // si c'est un input du type select
        }elseif($input['type']=='select'){
            $options = '';
            $display_field = $input['display_field'];
            foreach($input['values'] as $field=>$value){
                $selected = '';
                // conserve le POST
                if(isset($_POST[$name]) && $_POST[$name]==$value[$name]){$selected = 'selected';}
                /* ex Array([0] => Array([id_currency] => 4 [name] => Franc) [1] => Array... */
                if(isset($data[0][$name]) && empty($data[$name])){
                    if($data[0][$name]==$value[$name]){$selected='selected';}
                    $options .= '<option value="'.$value[$name].'" '.$selected.'>'.$value[$display_field];
                // Array simple à 1 niveau
                }else{
                    // si le champ est multilingue sélectionne l'élément par défaut
                    if(isset($data[0][$name])){
                        if($data[0][$name]==$field){$selected='selected';}
                    // si le champ est simple sélectionne l'élément par défaut
                    }else{
                        if($data[$name]==$field){$selected='selected';}
                    }
                    $options .= '<option value="'.$field.'" '.$selected.'>'.$value;
                }     
            }
            return $options;
        // si c'est une checkbox
        }elseif($input['type']=='checkbox'){
            if(empty($input['checkedList'][0])){
                if(isset($data[0][$name]) && $data[0][$name]==1){
                    return 'checked';
                }
                // données sur un niveau $data au lieu de $data[0]
                if(isset($data[$name]) && $data[$name]==1){
                    return 'checked';
                }
            }else{
                if(in_array($input['value'],$input['checkedList'])){return 'checked';}
            }
        }elseif($input['type']=='radio'){
            $radioValue = Tools::getValue($name);
            if(isset($data[$name]) && $data[$name]==$input['value']){
                 return 'checked';
            }else{
                if(!empty($radioValue) && $input['value']===$radioValue){
                    return 'checked';
                }
            }
        }
    }

  /*
   * Affiche un captcha
   * @param   -
   * @return  -
   */
   public function captcha($name,$label=null,$parameters=null){

        $value = @$parameters['value'];
        $style = $this->css(@$parameters['style']);
        $styleDiv = $this->css(@$parameters['styleDiv']);
        $class = @$parameters['class'];
        $afterInput = @$parameters['afterInput'];
        $beforeInput = @$parameters['beforeInput'];

        // génère un nouveau captcha si le formulaire à été validé correctement
        $captcha = Session::read('captcha');
        if(empty($captcha)){
            $captcha = rand();
            Session::write('captcha',$captcha);
        }

        if(empty($value)){$value=$this->getContentField($name);}
        $errorMsg = $this->validation(@$parameters['validation'],$name);
        $html =
        '<div id="div_'.$name.'" '.$styleDiv.'>'.
            $this->label($label,$name).'
            '.$beforeInput.'<input type="text" id="'.$name.'" value="'.$value.'" name="'.$name.'" class="'.$class.'" '.$style.'/>&nbsp;<span id="captcha_'.$name.'">'.$captcha.'</span>&nbsp;'.$afterInput.
            $errorMsg.'
        </div>';
        
        return $html;
   }

   /*
    * Si le formulaire est posté
    * @param   string (nom du bouton)
    * @return  bool
    */
    function isSubmit($name){
        if(Tools::isSubmit($name)){
            if($this->_nb_fields_with_validation==0){
                return true;
            }else{
                $formError = Session::read('formError');
                if(empty($formError)){
                    Session::delete('captcha');
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

   /*
    * Affiche un message de confirmation
    * @param   string (message)
    * @param   array (module & form)
    * @return  -
   */
   public function msgConfirm($msg,$redirect=null){
        $msg = '<div class="conf confirm">'.Html::img('/modules/devpro/img/ok.png').'&nbsp;'.$msg.'</div>';
        Session::write('msgConfirm',$msg);
        if(empty($redirect['form'])){$redirect['form']='default';}
        if(!empty($redirect)){ Tool::redirectBo(array('module'=>$redirect['module'],'form'=>$redirect['form']));}
   }

   /*
    * Affiche un message d'erreur
    * @param   string (message)
    * @param   string (module & form)
    * @return  -
   */
   public function msgError($msg,$redirect=null){
        $msg = '<div class="alert error">'.Html::img('/modules/devpro/img/error.png').'&nbsp;'.$msg.'</div>';
        Session::write('msgError',$msg);
        if(empty($redirect['form'])){$redirect['form']='default';}
        if(!empty($redirect)){ Tool::redirectBo(array('module'=>$redirect['module'],'form'=>$redirect['form']));}
   }

   /*
    * Affiche un message d'avertissement
    * @param   string (message)
    * @param   string (module & form)
    * @return  -
   */
   public function msgAlert($msg,$redirect=null){
        $msg = '<div class="warning warn">'.Html::img('/modules/devpro/img/error.png').'&nbsp;'.$msg.'</div>';
        Session::write('msgAlert',$msg);
        if(empty($redirect['form'])){$redirect['form']='default';}
        if(!empty($redirect)){ Tool::redirectBo(array('module'=>$redirect['module'],'form'=>$redirect['form']));}
   }
    
}

?>
