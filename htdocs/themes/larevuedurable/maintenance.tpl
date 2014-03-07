{*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}" lang="{$lang_iso}">
	<head>
		<title>{$meta_title|escape:'htmlall':'UTF-8'}</title>	
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{if isset($meta_description)}
		<meta name="description" content="{$meta_description|escape:'htmlall':'UTF-8'}" />
{/if}
{if isset($meta_keywords)}
		<meta name="keywords" content="{$meta_keywords|escape:'htmlall':'UTF-8'}" />
{/if}
		<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />
		<link rel="shortcut icon" href="{$favicon_url}" />
		<link href="{$css_dir}maintenance.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div id="maintenance">
			 <!--p><img src="{$logo_url}" {if $logo_image_width}width="{$logo_image_width}"{/if} {if $logo_image_height}height="{$logo_image_height}"{/if} alt="logo" /><br /><br /></p-->
			 <!--p id="message">
			 
				{l s='In order to perform website maintenance, our online store will be temporarily offline.'}<br /><br />
				{l s='We apologize for the inconvenience and ask that you please try again later.'}
			 </p-->
			 
			 <style>
				 p{
					 font-size:16px;
					 line-height:1.7em;
					 color:#444;
					 }
					 
				 .body{
					 width:750px;
					 margin:auto;
				 }
			 
			 </style>
			 
			 <div class='body'>
			 
	 			 <p><img src="{$logo_url}" {if $logo_image_width}width="{$logo_image_width}"{/if} {if $logo_image_height}height="{$logo_image_height}"{/if} alt="logo" /><br /><br /></p>
			 
				 <p>Cher visiteur,</p>
				 
				 <p>Vous êtes bien sur le site de LaRevueDurable. Une intervention technique très malencontreuse sur un serveur clef nous condamne à être hors-service depuis 
				 mercredi dernier.</p>
				 
				 <p>Nous espérons que ce site sera rendu à nouveau fonctionnel d'ici peu, mais ne sommes pas maîtres du jeu.</p>
				 
				 <p>En attendant, vous pouvez :</p>
				 
				 <p>
				 Ecouter l'émission de la RTS sur notre dossier consacré aux technologies de l'information et de la communication 
				 (la panne est arrivée juste après cette émission: sans doute une vengeance du système informatique qui ne supporte pas la critique) :<br/> 
				 <a href='http://www.rts.ch/audio/la-1ere/programmes/cqfd/5549128-la-face-cachee-de-l-immaterialite-05-02-2014.html'>www.rts.ch/audio/la-1ere/programmes/cqfd/5549128-la-face-cachee-de-l-immaterialite-05-02-2014.html</a>
				 </p>
				 			 
				 <p>Regarder ce soir Couleurs locales sur la RTS notre action sur les grands parents et le climat.</p>
				 
				 <p>Ecrire à sylvia.generoso@larevuedurable.com pour lui demander de vous prévenir dès que le site sera à nouveau en ligne.</p>
				 
				 <p>Avec toutes nos plus plates excuses pour cette situation,</p>
				 
				 <p>Toute l'équipe de LaRevueDurable</p>
			 </div>
			 
			 <span style="clear:both;">&nbsp;</span>
		</div>
	</body>
</html>
