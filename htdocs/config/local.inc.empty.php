<?php

/**
 * Prestashop variables (dev / prod) --------------------------------------------
 */
define('_PS_MODE_DEV_', false); // should be obsolete
@ini_set('display_errors', 'off');
define('_PS_DEBUG_SQL_', false);
define('_PS_DISPLAY_COMPATIBILITY_WARNING_', false);
//define('_OVERRIDE_EMAIL_', '');

/**
 * Constantes de projet ----------------------------------------------------------
 */

// Variables pour execution en CLI
define('_DOCUMENT_ROOT_', dirname(dirname(dirname(__FILE__))));
define('_HTTP_HOST_', 'xxxxxxxxxxx');
define('_REQUEST_URI_',  '/');
define('_REMOTE_ADDR', '127.0.0.1');

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

define('_ATTRIBUTE_VERSION_', 6);
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

// API prestashop
define('_PS_IMPORT_FROM_CRESUS_API_KEY_', '');
define('_IMPORTED_ORDER_STATE_', 12);

// Module bon cadeau
define('_GIFT_PRODUCT_ID_', 1028);

// Mailchimp
define('_MAILCHIMP_API_KEY_',  '');
define('_MC_NEWSLETTER_LIST_', '' );
define('_MC_SUBSCRIBERS_LIST_', ''); // subscribers, utilisé pour les relances
define('_MC_RELANCE_CAMPAIGN_', '');

// Notifications lors de modifications sur un client
define('_CUSTOMER_CHANGE_NOTIFICATION_', '');

