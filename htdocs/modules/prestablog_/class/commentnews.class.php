<?php
/*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*/
class	CommentNewsClass extends ObjectModel
{
	public		$id;
	public		$news;
	public		$date;
	public		$name;
	public		$url;
	public		$comment;
	public		$actif = 0;
	
	protected 	$table = 'prestablog_commentnews';
	protected 	$identifier = 'id_prestablog_commentnews';
	
	protected static	$table_static = 'prestablog_commentnews';
	protected static	$identifier_static = 'id_prestablog_commentnews';
	
	public static $definition = array(
		'table' => 'prestablog_commentnews',
		'primary' => 'id_prestablog_commentnews',
		'fields' => array(
			'date' =>			array('type' => self::TYPE_DATE,		'validate' => 'isDateFormat',	'required' => true),
			'news' =>			array('type' => self::TYPE_INT,			'validate' => 'isUnsignedId',	'required' => true),
			'actif' =>			array('type' => self::TYPE_INT,			'validate' => 'isInt'),
			'name' =>			array('type' => self::TYPE_STRING,		'validate' => 'isGenericName',	'required' => true, 'size' => 255),
			'url' =>			array('type' => self::TYPE_STRING,		'validate' => 'isUrlOrEmpty',	'size' => 255),
			'comment' =>		array('type' => self::TYPE_HTML,		'validate' => 'isString',	'size' => 255),
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
			`news` int(10) unsigned NOT NULL,
			`date` datetime NOT NULL,
			`name` varchar(255) NOT NULL,
			`url` varchar(255) NOT NULL,
			`comment` text NOT NULL,
			`actif` int(1) NOT NULL DEFAULT \'-1\',
			PRIMARY KEY (`'.$this->identifier.'`))
			ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
			
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			CREATE TABLE `'._DB_PREFIX_.$this->table.'_abo` (
			`'.$this->identifier.'_abo` int(10) unsigned NOT NULL auto_increment,
			`news` int(10) unsigned NOT NULL,
			`id_customer` int(10) unsigned NOT NULL,
			PRIMARY KEY (`'.$this->identifier.'_abo`))
			ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		return true;
	}
	
	public function deleteTablesBdd() {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE `'._DB_PREFIX_.$this->table))
			return false;
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE `'._DB_PREFIX_.$this->table.'_abo'))
			return false;
			
		return true;
	}
	
	static public function getCountListeAll($only_actif = NULL, $only_news = NULL)
	{
		$actif="";
		if ((int)$only_actif>-2)
			$actif = 'AND cn.`actif` = '.(int)$only_actif;
		$news="";
		if ($only_news)
			$news = 'AND cn.`news` = '.(int)$only_news;
		
		$Value = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
			SELECT count(cn.`'.self::$identifier_static.'`) As `count`
			FROM `'._DB_PREFIX_.self::$table_static.'` cn
			WHERE 1=1
			'.$news.'
			'.$actif.'
			ORDER BY cn.`date` DESC');
		
		return $Value["count"];
	}
	
	static public function getListe($only_actif = NULL, $only_news = NULL)
	{
		$actif="";
		if ((int)$only_actif>-2)
			$actif = 'AND cn.`actif` = '.(int)$only_actif;
		$news="";
		if ($only_news)
			$news = 'AND cn.`news` = '.(int)$only_news;
		
		$Liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	cn.*
		FROM `'._DB_PREFIX_.self::$table_static.'` cn
		WHERE 1=1
		'.$news.'
		'.$actif.'
		ORDER BY cn.`date` DESC');
		
		return $Liste;
	}
	
	static public function getNewsFromComment($id_comment) {
		$Row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT	c.`news`
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		WHERE c.`'.self::$identifier_static.'`='.(int)$id_comment);
		
		return $Row["news"];
	}
	
	static public function getListeNonLu($only_news = NULL)
	{
		$news="";
		if ($only_news)
			$news = 'AND c.`news` = '.(int)$only_news;
		
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	c.*
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		WHERE c.`actif` = -1
		'.$news.'
		ORDER BY c.`date` DESC');
		
		return $Liste;
	}
	
	static public function getListeDisabled($only_news = NULL)
	{
		$news="";
		if ($only_news)
			$news = 'AND c.`news` = '.(int)$only_news;
		
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	c.*
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		WHERE c.`actif` = 0
		'.$news.'
		ORDER BY c.`date` DESC');
		
		return $Liste;
	}
	
	static public function insertComment(
										$news,
										$date,
										$name,
										$url,
										$comment,
										$actif=-1
										)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			INSERT INTO `'._DB_PREFIX_.self::$table_static.'` 
				(
					`news`,
					`date`,
					`name`,
					`url`,
					`comment`,
					`actif`
				)
			VALUES 
				(
					'.(int)$news.', 
					\''.pSQL($date).'\', 
					\''.pSQL($name).'\', 
					\''.pSQL($url).'\', 
					\''.pSQL($comment).'\', 
					'.(int)$actif.'
				)');
	}
	
	static public function listeCommentAbo($news=null)
	{
		$where_news='';
		
		if(isset($news))
			$where_news='WHERE	`news` = '.(int)$news;
			
		$Liste = Db::getInstance()->ExecuteS('
				SELECT	`id_customer`
				FROM `'._DB_PREFIX_.self::$table_static.'_abo`
				'.$where_news);
				
		$Liste2=array();
		if(sizeof($Liste)) {
			foreach($Liste As $Value) {
				$Liste2[] = $Value["id_customer"];
			}
		}
		return $Liste2;
	}
	
	static public function listeCommentMailAbo($news=null)
	{
		$where_news='';
		
		if(isset($news))
			$where_news='WHERE	A.`news` = '.(int)$news;
		
		$Liste = Db::getInstance()->ExecuteS('
			SELECT	DISTINCT A.`id_customer`, C.`email`
			FROM `'._DB_PREFIX_.self::$table_static.'_abo` AS A
			LEFT JOIN `'._DB_PREFIX_.'customer` AS C
				ON (A.`id_customer` = C.`id_customer`)
			'.$where_news);
				
		$Liste2=array();
		if(sizeof($Liste)) {
			foreach($Liste As $Value) {
				$Liste2[$Value["id_customer"]] = $Value["email"];
			}
		}
		return $Liste2;
	}
	
	static public function insertCommentAbo(
										$news,
										$id_customer
										)
	{
		$Abo = Db::getInstance()->ExecuteS('
				SELECT	*
				FROM `'._DB_PREFIX_.self::$table_static.'_abo`
				WHERE	`news` = '.(int)$news.'
					AND	`id_customer` = '.(int)$id_customer);
		
		if(!sizeof($Abo))
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			INSERT INTO `'._DB_PREFIX_.self::$table_static.'_abo` (`news`,`id_customer`)
			VALUES ('.(int)$news.', '.(int)$id_customer.')');
	}
	
	static public function deleteCommentAbo(
										$news,
										$id_customer
										)
	{
		return Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.self::$table_static.'_abo`
				WHERE	`news` = '.(int)$news.'
					AND	`id_customer` = '.(int)$id_customer);
	}
	
	public function changeEtat($Field, $force_value) {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				UPDATE `'._DB_PREFIX_.$this->table.'` SET `'.$Field.'`='.(int)$force_value.'
				WHERE `'.$this->identifier.'`='.intval($this->id))
				)
				return false;
		return true;
	}
}
