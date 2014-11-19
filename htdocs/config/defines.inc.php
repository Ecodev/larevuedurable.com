<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
/* Debug only */
include_once('local.inc.php');
if (_PS_MODE_DEV_)
{
	@ini_set('display_errors', 'on');	
	define('_PS_DEBUG_SQL_', true);
	/* Compatibility warning */
	define('_PS_DISPLAY_COMPATIBILITY_WARNING_', true);
}
else
{
	@ini_set('display_errors', 'off');
	define('_PS_DEBUG_SQL_', false);
	/* Compatibility warning */
	define('_PS_DISPLAY_COMPATIBILITY_WARNING_', false);
}

define('_PS_DEBUG_PROFILING_', false);
define('_PS_MODE_DEMO_', false);

$currentDir = dirname(__FILE__);

if (!defined('PHP_VERSION_ID'))
{
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

/* Directories */
define('_PS_ROOT_DIR_', realpath($currentDir.'/..'));
define('_PS_CLASS_DIR_',            _PS_ROOT_DIR_.'/classes/');
define('_PS_CONTROLLER_DIR_',       _PS_ROOT_DIR_.'/controllers/');
define('_PS_FRONT_CONTROLLER_DIR_', _PS_ROOT_DIR_.'/controllers/front/');
define('_PS_ADMIN_CONTROLLER_DIR_', _PS_ROOT_DIR_.'/controllers/admin/');
define('_PS_OVERRIDE_DIR_', _PS_ROOT_DIR_.'/override/');
define('_PS_TRANSLATIONS_DIR_', _PS_ROOT_DIR_.'/translations/');
define('_PS_DOWNLOAD_DIR_',         _PS_ROOT_DIR_.'/download/');
define('_PS_MAIL_DIR_',             _PS_ROOT_DIR_.'/mails/');
define('_PS_PDF_DIR_', _PS_ROOT_DIR_.'/pdf/');
define('_PS_ALL_THEMES_DIR_',       _PS_ROOT_DIR_.'/themes/');
define('_PS_IMG_DIR_',              _PS_ROOT_DIR_.'/img/');
if (!defined('_PS_MODULE_DIR_'))
	define('_PS_MODULE_DIR_',              _PS_ROOT_DIR_.'/modules/');
define('_PS_CAT_IMG_DIR_',          _PS_IMG_DIR_.'c/');
define('_PS_STORE_IMG_DIR_',		_PS_IMG_DIR_.'st/');
define('_PS_PROD_IMG_DIR_',         _PS_IMG_DIR_.'p/');
define('_PS_SCENE_IMG_DIR_',        _PS_IMG_DIR_.'scenes/');
define('_PS_SCENE_THUMB_IMG_DIR_',  _PS_IMG_DIR_.'scenes/thumbs/');
define('_PS_MANU_IMG_DIR_',         _PS_IMG_DIR_.'m/');
define('_PS_SHIP_IMG_DIR_',         _PS_IMG_DIR_.'s/');
define('_PS_SUPP_IMG_DIR_',         _PS_IMG_DIR_.'su/');
define('_PS_COL_IMG_DIR_',			_PS_IMG_DIR_.'co/');
define('_PS_OS_IMG_DIR_',			_PS_IMG_DIR_.'os/');
define('_PS_TMP_IMG_DIR_',          _PS_IMG_DIR_.'tmp/');
define('_PS_UPLOAD_DIR_',			_PS_ROOT_DIR_.'/upload/');
define('_PS_TOOL_DIR_', _PS_ROOT_DIR_.'/tools/');
define('_PS_GEOIP_DIR_',            _PS_TOOL_DIR_.'geoip/');
define('_PS_SWIFT_DIR_', _PS_TOOL_DIR_.'swift/');
define('_PS_GENDERS_DIR_',            _PS_IMG_DIR_.'genders/');
define('_PS_FPDF_PATH_',            _PS_TOOL_DIR_.'fpdf/'); // @deprecated will be removed in 1.6
define('_PS_TCPDF_PATH_',            _PS_TOOL_DIR_.'tcpdf/');
define('_PS_TAASC_PATH_',            _PS_TOOL_DIR_.'taasc/');
define('_PS_PEAR_XML_PARSER_PATH_', _PS_TOOL_DIR_.'pear_xml_parser/');
define('_PS_CACHE_DIR_',			_PS_ROOT_DIR_.'/cache/');
/* BO THEMES */
if (defined('_PS_ADMIN_DIR_'))
	define('_PS_BO_ALL_THEMES_DIR_',			_PS_ADMIN_DIR_.'/themes/');

/* settings php */
define('_PS_TRANS_PATTERN_',            '(.*[^\\\\])');
define('_PS_MIN_TIME_GENERATE_PASSWD_', '360');
if (!defined('_PS_MAGIC_QUOTES_GPC_'))
	define('_PS_MAGIC_QUOTES_GPC_',         get_magic_quotes_gpc());

define('_CAN_LOAD_FILES_', 1);

/* Order states
Order states has been moved in config.inc.php file for backward compatibility reasons */

/* Tax behavior */
define('PS_PRODUCT_TAX', 0);
define('PS_STATE_TAX', 1);
define('PS_BOTH_TAX', 2);

define('_PS_PRICE_DISPLAY_PRECISION_', 2);
define('PS_TAX_EXC', 1);
define('PS_TAX_INC', 0);

define('PS_ORDER_PROCESS_STANDARD', 0);
define('PS_ORDER_PROCESS_OPC', 1);

define('PS_ROUND_UP', 0);
define('PS_ROUND_DOWN', 1);
define('PS_ROUND_HALF', 2);

/* Registration behavior */
define('PS_REGISTRATION_PROCESS_STANDARD', 0);
define('PS_REGISTRATION_PROCESS_AIO', 1);

/* Carrier::getCarriers() filter */
// these defines are DEPRECATED since 1.4.5 version
define('PS_CARRIERS_ONLY', 1);
define('CARRIERS_MODULE', 2);
define('CARRIERS_MODULE_NEED_RANGE', 3);
define('PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE', 4);
define('ALL_CARRIERS', 5);

/* SQL Replication management */
define('_PS_USE_SQL_SLAVE_', 0);

/* PS Technical configuration */
define('_PS_ADMIN_PROFILE_', 1);

/* Stock Movement */
define('_STOCK_MOVEMENT_ORDER_REASON_', 3);
define('_STOCK_MOVEMENT_MISSING_REASON_', 4);

/**
 * @deprecated 1.5.0.1
 * @see Configuration::get('PS_CUSTOMER_GROUP')
 */
define('_PS_DEFAULT_CUSTOMER_GROUP_', 3);

define('_PS_CACHEFS_DIRECTORY_', _PS_ROOT_DIR_.'/cache/cachefs/');

/* Geolocation */
define('_PS_GEOLOCATION_NO_CATALOG_', 0);
define('_PS_GEOLOCATION_NO_ORDER_', 1);

define('MIN_PASSWD_LENGTH', 8);

define('_PS_SMARTY_NO_COMPILE_', 0);
define('_PS_SMARTY_CHECK_COMPILE_', 1);
define('_PS_SMARTY_FORCE_COMPILE_', 2);

define('_PS_SMARTY_CONSOLE_CLOSE_', 0);
define('_PS_SMARTY_CONSOLE_OPEN_BY_URL_', 1);
define('_PS_SMARTY_CONSOLE_OPEN_', 2);

define('_PS_JQUERY_VERSION_', '1.7.2');





/****************************************************************
	Constantes pour le projet LRD
*****************************************************************/


/**
*	exemples de manipulation de date (objet DateTime)
*	$dateB = clone $dateA;
*	$dateSecure = new DateTime($dateNow->format('Y-m-d H:i:s'));
*	$dateSecure->modify('+'.JOURS_SECURITE.' day');
*/
$date_now = new DateTime();

// constantes de format de dates
define('_DATE_FORMAT_', 'Y-m-d H:i:s');
define('_DATE_FORMAT_SHORT_', 'Y-m-d');


// Groupes de clients
define('_PS_SUBSCRIBER_ARCHIVES_GROUP_', 5); 
define('_PS_SUBSCRIBER_PAPER_GROUP_', 6);
define('_PS_SUBSCRIBER_INSTITUTE_', 4); 

/** 
*	La table ps_product_attribute_combination comporte les liaisons déclinaisons -> attributs
*	- 1 an : attribut 23
*	- 2 ans : attribut 24
*	- etc...
*	
*	- Papier : attribut 26
*	- Web : attribut 27
*	- web et papier : attribut 28
*/
define('_SIX_MOIS_', 46);
define('_UN_AN_', 23);
define('_DEUX_ANS_', 24);
define('_TROIS_ANS_', 40);
define('_QUATRE_ANS_', 41);
define('_CINQ_ANS_', 42);

define('_PAPIER_', 26);
define('_WEB_', 27);
define('_PAPIER_ET_WEB_', 28);


// Types d'abonnements
define('_ABONNEMENT_PARTICULIER_', 8);
define('_ABONNEMENT_INSTITUT_', 32);
define('_ABONNEMENT_SOLIDARITE_', 31); 
define('_ABONNEMENT_MOOC_', 971);


// Permet d'identifier un produit institutionnel, qui peut être utilisé par qqch d'autre, ne l'ayant pas acheté 
define('_MANUFACTURE_INSTITUTE_', 3);


// Catégorie de tous les numéros des revues complètes, ce sont elles qui feront fois pour les dates et ainsi déterminer les numéros associés aux abonnement
define('_CATEGORY_FULL_BOOK_' , 21);
define('_CATEGORY_LITTLE_ARTICLES_' , 22);


// mailchimp // ids obtenus en appelant la liste des campagnes et des lists puis en prenant le champ id

// API prestashop
define('_PS_IMPORT_FROM_CRESUS_API_KEY_', 'OX7P1KT26VMCR2N6FPRA3NB1JZKXRSAF');

// Module bon cadeau
define('_GIFT_PRODUCT_ID_', 1028);

if(_PS_MODE_DEV_)
{
	define('_MAILCHIMP_API_KEY_',  DEV_MAILCHIMP_API_KEY_);
	define('_MC_NEWSLETTER_LIST_', DEV_MC_NEWSLETTER_LIST_ );
	define('_MC_SUBSCRIBERS_LIST_',DEV_MC_SUBSCRIBERS_LIST_); // subscribers, utilisé pour les relances
	define('_MC_RELANCE_CAMPAIGN_',DEV_MC_RELANCE_CAMPAIGN_);
	define('_CUSTOMER_CHANGE_NOTIFICATION_', DEV_CUSTOMER_CHANGE_NOTIFICATION);
}
else 
{
	define('_MAILCHIMP_API_KEY_',   LRD_MAILCHIMP_API_KEY_);
	define('_MC_NEWSLETTER_LIST_',  LRD_MC_NEWSLETTER_LIST_ );
	define('_MC_SUBSCRIBERS_LIST_', LRD_MC_SUBSCRIBERS_LIST_); // subscribers, utilisé pour les relances
	define('_MC_RELANCE_CAMPAIGN_', LRD_MC_RELANCE_CAMPAIGN_);
    define('_CUSTOMER_CHANGE_NOTIFICATION_', LRD_CUSTOMER_CHANGE_NOTIFICATION);
}


