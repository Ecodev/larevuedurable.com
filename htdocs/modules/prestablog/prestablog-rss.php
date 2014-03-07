<?php
/*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/prestablog.php');
include(dirname(__FILE__).'/class/categories.class.php');

if(Tools::getValue("rss") && !CategoriesClass::IsCategorieValide((int)Tools::getValue("rss")))
	header('Location: '.__PS_BASE_URI__.'404.php');
else
	header("Content-type: application/xml; charset=utf-8");

$cookieLifetime = (time() + (((int)Configuration::get('PS_COOKIE_LIFETIME_FO') > 0 ? (int)Configuration::get('PS_COOKIE_LIFETIME_FO') : 1)* 3600));
$cookie = new Cookie('ps', '', $cookieLifetime);

$PrestaBlog = New PrestaBlog();
$PrestaBlog->InitLangueModule((int)$cookie->id_lang);
$isoLang = LanguageCore::getIsoById((int)$cookie->id_lang);

$News = NewsClass::getListe(
								(int)($cookie->id_lang), 
								1, // actif only
								0, // homeslide
								PrestaBlog::_getConfigXmlTheme(Configuration::get('prestablog_theme')), 
								NULL, // limit start
								NULL, // limit stop
								'n.`date`', 
								'desc',
								NULL, // date début
								Date('Y-m-d H:i:s'), // date fin
								(Tools::getValue("rss") ? (int)Tools::getValue("rss") : NULL),
								1
							);
							

echo '
<rss version="2.0">
<channel>
	<title>'.$PrestaBlog->RssLangue["channel_title"].'</title>
	<pubDate>'.date("r").'</pubDate>
	<link>'.Tools::getShopDomainSsl(true).__PS_BASE_URI__.'</link>'.(Tools::getValue("rss") ? '
	<category>'.CategoriesClass::getCategoriesName((int)$cookie->id_lang, (int)Tools::getValue("rss")).'</category>' : '').'
	<image>
		<url>'.Tools::getShopDomainSsl(true).__PS_BASE_URI__.'img/logo.jpg</url>
		<title>'.$PrestaBlog->RssLangue["channel_title"].'</title>
		<link>'.Tools::getShopDomainSsl(true).__PS_BASE_URI__.'</link>
	</image>';

if(sizeof($News)) {
	foreach($News As $NewsItem) {
		echo '
	<item>
		<title>'.$NewsItem["title"].'</title> 
		<pubDate>'.date("r", strtotime($NewsItem["date"])).'</pubDate>';
		if(sizeof($NewsItem["categories"])) {
			foreach($NewsItem["categories"] As $KeyCat => $ValCat) {
			echo '
		<category>'.$ValCat.'</category>';
			}
		}
		
		echo'
		<link>'.htmlentities(PrestaBlog::prestablog_url(
			array(
					"id"		=> $NewsItem["id_prestablog_news"],
					"seo"		=> $NewsItem["link_rewrite"],
					"titre"		=> $NewsItem["title"]
				)
)).'</link> 
		<description>'.nl2br($NewsItem["paragraph_crop"]).'</description> 
	</item>';
	}
}
echo'
</channel>
</rss>';

?>
