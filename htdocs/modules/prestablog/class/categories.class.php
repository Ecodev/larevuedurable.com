<?php
/*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*/
class	CategoriesClass extends ObjectModel
{
	public		$id;
	public		$title;
	public		$meta_title;
	public		$actif = 1;
	
	protected 	$table = 'prestablog_categorie';
	protected 	$identifier = 'id_prestablog_categorie';
	
	protected static	$table_static = 'prestablog_categorie';
	protected static	$identifier_static = 'id_prestablog_categorie';
	
	public static $definition = array(
		'table' => 'prestablog_categorie',
		'primary' => 'id_prestablog_categorie',
		'multilang' => true,
		'fields' => array(
			'actif' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			
			// Lang fields
			'title' =>			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
			'meta_title' =>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
		)
	);
	
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (key_exists($key, $this) AND $key != 'id_'.$this->table)
				$this->{$key} = $value;

		/* Multilingual fields */
		if (sizeof($this->fieldsValidateLang))
		{
			$languages = Language::getLanguages(false);
			foreach ($languages AS $language)
				foreach ($this->fieldsValidateLang AS $field => $validation)
					if (isset($_POST[$field.'_'.(int)($language['id_lang'])]))
						$this->{$field}[(int)($language['id_lang'])] = $_POST[$field.'_'.(int)($language['id_lang'])];
		}
	}
	
	public function registerTablesBdd() {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'` (
		`'.$this->identifier.'` int(10) unsigned NOT NULL auto_increment,
		`actif` tinyint(1) NOT NULL DEFAULT \'1\',
		PRIMARY KEY (`'.$this->identifier.'`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'_lang` (
		`'.$this->identifier.'` int(10) unsigned NOT NULL,
		`id_lang` int(10) unsigned NOT NULL,
		`title` varchar(255) NOT NULL,
		`meta_title` varchar(255) NOT NULL,
		PRIMARY KEY (`'.$this->identifier.'`, `id_lang`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
			
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		INSERT INTO `'._DB_PREFIX_.$this->table.'` 
			(`'.$this->identifier.'`, `actif`) 
		VALUES (1,1)'))
			return false;
		
		$Langues = Language::getLanguages(true);
		if(sizeof($Langues)) {
			$sql_values = 'VALUES ';
			foreach($Langues As $Value) {
				$sql_values.= '
					(
						1, 
						'.$Value["id_lang"].', 
						\'Default\' 
					),';
			}
			$sql_values = rtrim($sql_values, ',');
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				INSERT INTO `'._DB_PREFIX_.$this->table.'_lang` 
					(`'.$this->identifier.'`, `id_lang`, `title`) 
				'.$sql_values))
					return false;
		}
		
		return true;
	}
	
	public function deleteTablesBdd() {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE `'._DB_PREFIX_.$this->table))
			return false;
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE `'._DB_PREFIX_.$this->table.'_lang`'))
			return false;
			
		return true;
	}
	
	static public function getCategoriesName($id_lang = NULL, $id_prestablog_categorie)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Row = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT	cl.title
		FROM `'._DB_PREFIX_.self::$table_static.'_lang` cl
		WHERE cl.id_lang = '.(int)($id_lang).'
		AND cl.`'.self::$identifier_static.'` = '.(int)$id_prestablog_categorie);
		
		if(sizeof($Row))
			return $Row["title"];
		else
			return false;
	}
	
	static public function getCategoriesMetaTitle($id_lang = NULL, $id_prestablog_categorie)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Row = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT	cl.meta_title
		FROM `'._DB_PREFIX_.self::$table_static.'_lang` cl
		WHERE cl.id_lang = '.(int)($id_lang).'
		AND cl.`'.self::$identifier_static.'` = '.(int)$id_prestablog_categorie);
		
		if(sizeof($Row))
			return $Row["meta_title"];
		else
			return false;
	}
	
	static public function getListeNoLang($only_actif = 0)
	{
		$actif="";
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';
			
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	c.*
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		WHERE c.`'.self::$identifier_static.'` > 0
		'.$actif);
		
		return $Liste;
	}
	
	static public function getListe($id_lang = NULL, $only_actif = 0)
	{
		$actif="";
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';
			
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	c.*, cl.*
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		JOIN `'._DB_PREFIX_.self::$table_static.'_lang` cl ON (c.`'.self::$identifier_static.'` = cl.`'.self::$identifier_static.'`)
		WHERE cl.id_lang = '.(int)($id_lang).'
		'.$actif);
		
		return $Liste;
	}
	
	public function changeEtat($Field) {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			UPDATE `'._DB_PREFIX_.$this->table.'` SET `'.$Field.'`=CASE `'.$Field.'` WHEN 1 THEN 0 WHEN 0 THEN 1 END 
			WHERE `'.$this->identifier.'`='.intval($this->id))
			)
			return false;
		return true;
	}
	
	static public function IsCategorieValide($Categorie) {
		$Row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	*
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		WHERE c.`'.self::$identifier_static.'` = '.(int)$Categorie.'
		AND c.`actif`=1');
		
		if(sizeof($Row))
			return true;
		else
			return false;
	}
}
