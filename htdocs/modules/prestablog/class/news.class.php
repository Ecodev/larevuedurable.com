<?php
/*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*/

class	NewsClass extends ObjectModel
{
	public	$id;
	public	$title;
	public	$langues;
	public	$paragraph;
	public	$content;
	public	$date;
	public	$meta_title;
	public	$meta_description;
	public	$meta_keywords;
	public	$link_rewrite;
	public	$categories = array();
	public	$nb_products_row;
	public	$products_liaison = array();
	public	$slide = 0;
	public	$actif = NULL;
	public	$actif_langue = 0;
	
	protected	$table = 'prestablog_news';
	protected	$identifier = 'id_prestablog_news';
	
	public static	$table_static = 'prestablog_news';
	public static	$identifier_static = 'id_prestablog_news';
	
	public static $definition = array(
		'table' => 'prestablog_news',
		'primary' => 'id_prestablog_news',
		'multilang' => true,
		'fields' => array(
			'date' =>				array('type' => self::TYPE_DATE,		'validate' => 'isDateFormat',		'required' => true),
			'langues' =>			array('type' => self::TYPE_STRING,		'validate' => 'isSerializedArray',	'required' => true),
			'slide' =>				array('type' => self::TYPE_BOOL,		'validate' => 'isBool',				'required' => true),
			'actif' =>				array('type' => self::TYPE_BOOL,		'validate' => 'isBool',				'required' => true),
			'nb_products_row' =>	array('type' => self::TYPE_INT,			'validate' => 'isUnsignedId'),
			
			// Lang fields
			'title' =>				array('type' => self::TYPE_STRING,	'lang' => true, 'validate' => 'isGenericName',	'required' => true, 'size' => 255),
			'meta_title' =>			array('type' => self::TYPE_STRING,	'lang' => true, 'validate' => 'isGenericName',	'size' => 255),
			'meta_description' =>	array('type' => self::TYPE_STRING,	'lang' => true, 'validate' => 'isGenericName',	'size' => 255),
			'meta_keywords' =>		array('type' => self::TYPE_STRING,	'lang' => true, 'validate' => 'isGenericName',	'size' => 255),
			'link_rewrite' =>		array('type' => self::TYPE_STRING,	'lang' => true, 'validate' => 'isLinkRewrite',	'required' => true, 'size' => 255),
			'content' =>			array('type' => self::TYPE_HTML,	'lang' => true, 'validate' => 'isString',	'required' => true),
			'paragraph' =>			array('type' => self::TYPE_HTML,	'lang' => true, 'validate' => 'isString'),
		)
	);
	
	//~ public function __construct() {
		//~ parent::__construct();
	//~ }
	
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
	
	static public function getCountListeAllNoLang(
										$only_actif = 0, 
										$only_slide = 0, 
										$date_debut = NULL,
										$date_fin = NULL, 
										$Categorie = NULL
									)
	{
		$actif='';
		if ($only_actif)
			$actif = 'AND n.`actif` = 1';
		$slide='';
		if ($only_slide)
			$slide = 'AND n.`slide` = 1';
			
		$categorie='';
		if ($Categorie)
			$categorie = 'AND cc.`categorie` = '.$Categorie;
		
		$between_date='';
		if (!empty($date_debut) && !empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) BETWEEN \''.$date_debut.'\' AND \''.$date_fin.'\'';
		elseif (empty($date_debut) && !empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) <= \''.$date_fin.'\'';
		elseif (!empty($date_debut) && empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) >= \''.$date_debut.'\'';
		
		$Value = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT count(DISTINCT n.id_prestablog_news) As `count`
		FROM `'._DB_PREFIX_.self::$table_static.'` n
		LEFT JOIN `'._DB_PREFIX_.'prestablog_correspondancecategorie` cc
			ON (n.`'.self::$identifier_static.'` = cc.`news`)
		WHERE n.`'.self::$identifier_static.'` > 0
		'.$actif.'
		'.$slide.'
		'.$categorie.'
		'.$between_date);
		
		return $Value["count"];
	}
	
	static public function getTitleNews($id, $id_lang)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$Value = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
			SELECT nl.`title`
			FROM `'._DB_PREFIX_.self::$table_static.'` n
			JOIN `'._DB_PREFIX_.self::$table_static.'_lang` nl 
				ON (n.`'.self::$identifier_static.'` = nl.`'.self::$identifier_static.'`)
			WHERE 
				nl.`id_lang` = '.(int)($id_lang).'
			AND	n.`'.self::$identifier_static.'` = '.(int)$id);
		
		return $Value["title"];
	}
	
	static public function getProductLinkListe($news, $active=false) {
		$Return1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	`id_product`
		FROM `'._DB_PREFIX_.self::$table_static.'_product`
		WHERE `'.self::$identifier_static.'` = '.(int)($news));
		
		$Return2 = array();
		foreach($Return1 As $Key => $Value) {
			$Product = new Product((int)$Value["id_product"]);
			
			if((int)$Product->id)
				if($active) {
					if($Product->active)
						$Return2[] = $Value["id_product"];
				}
				else
					$Return2[] = $Value["id_product"];
			else
				NewsClass::removeProductLinkDeleted((int)$Value["id_product"]);
		}
		return $Return2;
	}
	
	static public function removeProductLinkDeleted($product) {
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
						DELETE FROM `'._DB_PREFIX_.self::$table_static.'_product` 
						WHERE `id_product` = '.(int)$product);
	}
	
	static public function updateProductLinkNews($news, $product) {
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
						INSERT INTO `'._DB_PREFIX_.self::$table_static.'_product` 
							(`'.self::$identifier_static.'`, `id_product`) 
						VALUES ('.(int)$news.', '.(int)$product.')');
	}
	
	static public function removeAllProductsLinkNews($news) {
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
						DELETE FROM `'._DB_PREFIX_.self::$table_static.'_product` 
						WHERE `'.self::$identifier_static.'` = '.(int)$news);
	}
	
	static public function removeProductLinkNews($news, $product) {
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
						DELETE FROM `'._DB_PREFIX_.self::$table_static.'_product` 
						WHERE `'.self::$identifier_static.'` = '.(int)$news.' AND `id_product` = '.(int)$product);
	}
	
	static public function getCountListeAll(
										$id_lang = NULL, 
										$only_actif = 0, 
										$only_slide = 0, 
										$date_debut = NULL,
										$date_fin = NULL, 
										$Categorie = NULL,
										$actif_langue = 0
									)
	{
		$actif='';
		if ($only_actif)
			$actif = 'AND n.`actif` = 1';
		$actif_lang='';
		if ($actif_langue)
			$actif_lang = 'AND nl.`actif_langue` = 1';
		$slide='';
		if ($only_slide)
			$slide = 'AND n.`slide` = 1';
			
		$categorie='';
		if ($Categorie)
			$categorie = 'AND cc.`categorie` = '.$Categorie;
		
		$between_date='';
		if (!empty($date_debut) && !empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) BETWEEN \''.$date_debut.'\' AND \''.$date_fin.'\'';
		elseif (empty($date_debut) && !empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) <= \''.$date_fin.'\'';
		elseif (!empty($date_debut) && empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) >= \''.$date_debut.'\'';
		
		$lang='';
		if (empty($id_lang))
			$lang = 'AND nl.`id_lang` = '.(int)Configuration::get('PS_LANG_DEFAULT');
		elseif ($id_lang == 0)
			$lang = '';
		else
			$lang = 'AND nl.`id_lang` = '.(int)$id_lang;


		$Value = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT count(DISTINCT nl.id_prestablog_news) As `count`
		FROM `'._DB_PREFIX_.self::$table_static.'_lang` nl
		LEFT JOIN `'._DB_PREFIX_.self::$table_static.'` n
			ON (n.`'.self::$identifier_static.'` = nl.`'.self::$identifier_static.'`)
		LEFT JOIN `'._DB_PREFIX_.'prestablog_correspondancecategorie` cc
			ON (n.`'.self::$identifier_static.'` = cc.`news`)
		WHERE 1=1 
		'.$lang.'
		'.$actif.'
		'.$actif_lang.'
		'.$slide.'
		'.$categorie.'
		'.$between_date);
		
		return $Value["count"];
	}
	
	static public function getListe(
										$id_lang = NULL, 
										$only_actif = 0, 
										$only_slide = 0, 
										$ConfigTheme, 
										$limit_start = 0, 
										$limit_stop = NULL, 
										$tri_champ = 'n.`date`', 
										$tri_ordre = 'desc', 
										$date_debut = NULL,
										$date_fin = NULL, 
										$Categorie = NULL,
										$actif_langue = 0
									)
	{
		$context = Context::getContext();
		$Module = new PrestaBlog();
		
		$Liste = array();
		
		$actif='';
		if ($only_actif)
			$actif = 'AND n.`actif` = 1';
		$actif_lang='';
		if ($actif_langue)
			$actif_lang = 'AND nl.`actif_langue` = 1';
		$slide='';
		if ($only_slide)
			$slide = 'AND n.`slide` = 1';
			
		$categorie='';
		if ($Categorie)
			$categorie = 'AND cc.`categorie` = '.$Categorie;
			
		$between_date='';
		if (!empty($date_debut) && !empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) BETWEEN \''.$date_debut.'\' AND \''.$date_fin.'\'';
		elseif (empty($date_debut) && !empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) <= \''.$date_fin.'\'';
		elseif (!empty($date_debut) && empty($date_fin))
			$between_date = 'AND TIMESTAMP(n.`date`) >= \''.$date_debut.'\'';
		
		$limit='';
		if (!empty($limit_stop))
			$limit = 'LIMIT '.(int)$limit_start.', '.(int)$limit_stop;
		
		$lang='';
		if (empty($id_lang))
			$lang = 'AND nl.`id_lang` = '.(int)(int)Configuration::get('PS_LANG_DEFAULT');
		elseif ($id_lang == 0)
			$lang = '';
		else
			$lang = 'AND nl.`id_lang` = '.(int)$id_lang;
		
		$Liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	DISTINCT(nl.`id_prestablog_news`), n.*, nl.*,
				LEFT(nl.`title`, '.$ConfigTheme->title_length.') As title
		FROM `'._DB_PREFIX_.self::$table_static.'_lang` nl
		LEFT JOIN `'._DB_PREFIX_.self::$table_static.'` n
			ON (n.`'.self::$identifier_static.'` = nl.`'.self::$identifier_static.'`)
		LEFT JOIN `'._DB_PREFIX_.'prestablog_correspondancecategorie` cc
			ON (n.`'.self::$identifier_static.'` = cc.`news`)
		WHERE 1=1 
		'.$lang.'
		'.$actif.'
		'.$actif_lang.'
		'.$slide.'
		'.$categorie.'
		'.$between_date.'
		ORDER BY '.$tri_champ.' '.$tri_ordre.'
		'.$limit);
		
		if(sizeof($Liste)) {
			foreach($Liste As $Key => $Value) {
				$Liste[$Key]["categories"] = CorrespondancesCategoriesClass::getCategoriesListeName((int)$Value["id_prestablog_news"], (int)$context->language->id, 1);

				$Liste[$Key]["paragraph"] = $Value["paragraph"];
				$Liste[$Key]["paragraph_crop"] = $Value["paragraph"];
				
				if(		(strlen(trim($Value["paragraph"])) == 0)
					&&	(strlen(trim(strip_tags($Value["content"]))) >= 1)
					) {
					$Liste[$Key]["paragraph_crop"] = trim(strip_tags($Value["content"]));
				}
				
				if(strlen(trim($Liste[$Key]["paragraph_crop"])) > $ConfigTheme->intro_length) {
					$Liste[$Key]["paragraph_crop"] = substr($Liste[$Key]["paragraph_crop"], 0, (int)$ConfigTheme->intro_length).' [...]';
				}
				if(file_exists($Module->ModulePath.'/themes/'.Configuration::get($Module->name.'_theme').'/up-img/'.$Value[self::$identifier_static].'.jpg'))
					$Liste[$Key]["image_presente"] = 1;
				if(strlen(trim(strip_tags($Value["content"]))) >= 1)
					$Liste[$Key]["link_for_unique"] = 1;
					

			}
		}
		
		return $Liste;
	}
	
	public function registerTablesBdd() {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'` (
		`'.$this->identifier.'` int(10) unsigned NOT NULL auto_increment,
		`date` datetime NOT NULL,
		`langues` text NOT NULL,
		`nb_products_row` int(10) unsigned NOT NULL,
		`actif` tinyint(1) NOT NULL DEFAULT \'1\',
		`slide` tinyint(1) NOT NULL DEFAULT \'0\',
		PRIMARY KEY (`'.$this->identifier.'`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'_lang` (
		`'.$this->identifier.'` int(10) unsigned NOT NULL,
		`id_lang` int(10) unsigned NOT NULL,
		`title` varchar(255) NOT NULL,
		`paragraph` text NOT NULL,
		`content` text NOT NULL,
		`meta_description` text NOT NULL,
		`meta_keywords` text NOT NULL,
		`meta_title` text NOT NULL,
		`link_rewrite` text NOT NULL,
		`actif_langue` tinyint(1) NOT NULL DEFAULT \'1\',
		PRIMARY KEY (`'.$this->identifier.'`, `id_lang`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'_product` (
		`'.$this->identifier.'_product` int(10) unsigned NOT NULL auto_increment,
		`'.$this->identifier.'` int(10) unsigned NOT NULL,
		`id_product` int(10) unsigned NOT NULL,
		PRIMARY KEY (`'.$this->identifier.'_product`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		$Langues = Language::getLanguages(true);
		if(sizeof($Langues)) {
			$LangueUse = Array();
			foreach($Langues As $Value) {
				$LangueUse[] = $Value["id_lang"];
			}
			
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				INSERT INTO `'._DB_PREFIX_.$this->table.'` 
					(`'.$this->identifier.'`, `date` , `langues` , `nb_products_row`, `actif`, `slide`) 
				VALUES
					(1, NOW(), \''.serialize($LangueUse).'\', 3, 1, 1),
					(2, NOW(), \''.serialize($LangueUse).'\', 3, 1, 1)'))
				return false;
			
			$title = Array (
				1 => "Lorem Ipsum is simply",
				2 => "Morbi ac felis a purus ac non ipsum"
			);
			
			$paragraph = Array (
				1 => "Morbi ac felis a purus ac non ipsum','Lorem Ipsum is simply dummy text of the printing and type setting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
				2 => "Lorem Ipsum is simply dummy text of the printing and type setting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book."
			);
			
			$content = Array (
				1 => "<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p><h2>Excepteur sint occaecat cupidatat non proident, sunt in</h2><p>culpa qui officia deserunt mollit anim id est laborum.Section 1.10.32 of de Finibus Bonorum et Malorum, written by Cicero in 45 BCSed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores</p><h3>Eos qui ratione voluptatem sequi nesciunt.</h3><ul><li>Neque porro quisquam est, qui dolorem ipsum quia dolorsit amet,</li><li>consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut</li><li> labore et dolore magnam aliquam quaerat voluptatem.</li><li>Ut enim ad minima veniam, quis nostrum exercitationem</li><li>ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?</li></ul><p> </p><p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?1914 translation by H. RackhamBut I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know</p><p>how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p><p>To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?</p>",
				2 => "<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p><h2>Excepteur sint occaecat cupidatat non proident, sunt in</h2><p>culpa qui officia deserunt mollit anim id est laborum.Section 1.10.32 of de Finibus Bonorum et Malorum, written by Cicero in 45 BCSed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores</p><h3>Eos qui ratione voluptatem sequi nesciunt.</h3><p> Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?1914 translation by H. RackhamBut I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know</p><p>how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p><p>To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?</p>"
			);
			
			$meta_description = Array (
				1 => "Lorem Ipsum is simply",
				2 => "Morbi ac felis a purus ac non ipsum"
			);
			
			$meta_keywords = Array (
				1 => "Lorem, Ipsum, simply",
				2 => "Morbi felis, purus, ac, non ipsum"
			);
			
			$meta_title = Array (
				1 => "Lorem Ipsum is simply",
				2 => "Morbi ac felis a purus ac non ipsum"
			);
			
			$link_rewrite = Array (
				1 => "lorem-ipsum-is-simply",
				2 => "morbi-ac-felis-a-purus-ac-non-ipsum"
			);
			
			$sql_values = 'VALUES ';
			for($i=1; $i<=2; $i++) {
				foreach($Langues As $Value) {
					$sql_values.= '
						(
							'.$i.', 
							'.$Value["id_lang"].', 
							\''.pSQL($title[$i]).'\', 
							\''.pSQL($paragraph[$i]).'\',
							\''.$content[$i].'\',
							\''.pSQL($meta_description[$i]).'\',
							\''.pSQL($meta_keywords[$i]).'\',
							\''.pSQL($meta_title[$i]).'\',
							\''.pSQL($link_rewrite[$i]).'\',
							1
						),';
				}
			}
			$sql_values = rtrim($sql_values, ',');
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				INSERT INTO `'._DB_PREFIX_.$this->table.'_lang` 
					(
						`'.$this->identifier.'`, 
						`id_lang`, 
						`title`, 
						`paragraph`, 
						`content`,
						`meta_description`,
						`meta_keywords`,
						`meta_title`,
						`link_rewrite`,
						`actif_langue`
					)
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
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE `'._DB_PREFIX_.$this->table.'_product`'))
			return false;
			
		return true;
	}
	
	public function changeEtat($Field) {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			UPDATE `'._DB_PREFIX_.$this->table.'` SET `'.$Field.'`=CASE `'.$Field.'` WHEN 1 THEN 0 WHEN 0 THEN 1 END 
			WHERE `'.$this->identifier.'`='.intval($this->id))
			)
			return false;
		return true;
	}
	
	public function razEtatLangue($id_news) {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			UPDATE `'._DB_PREFIX_.$this->table.'_lang` SET `actif_langue` = 0
			WHERE `'.$this->identifier.'`= '.(int)($id_news))
			)
			return false;
		
		return true;
	}
	
	public function changeActiveLangue($id_news, $id_lang) {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			UPDATE `'._DB_PREFIX_.$this->table.'_lang` SET `actif_langue` = 1
			WHERE `'.$this->identifier.'`= '.(int)($id_news).'
			AND `id_lang` = '.(int)($id_lang))
			)
			return false;
		
		return true;
	}
}
