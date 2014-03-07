<?php
/*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*/
include('../../config/config.inc.php');
include('../../init.php');

$context = Context::getContext();
$currentLang = (int)$context->language->id;

switch(Tools::getValue('do')) {
	case "loadProductsLink" :
		$PrestaBlog = new PrestaBlog();
		if(		Tools::getValue('req') != ''
			&&	Tools::getValue('token') == Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)Tools::getValue('idE'))
			) {
				$listProductLinked = array();
				$listProductLinked = preg_split("/;/", rtrim(Tools::getValue('req'), ';'));
				
				if(sizeof($listProductLinked)) {
					foreach($listProductLinked As $ProductLink) {
						$productSearch = new Product((int)$ProductLink, false, $currentLang);
						$productCover = Image::getCover($productSearch->id);
						$image_product = new Image((int)$productCover["id_image"]);
						$imageThumbPath = ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.$image_product->getExistingImgPath().'.jpg', 'product_mini_'.$productSearch->id.'.jpg', 45, 'jpg');
						
						echo '
								<tr class="'.($productSearch->active?'':'disabled_product ').'noInlisted_'.$productSearch->id.'">
									<td class="center">'.$productSearch->id.'</td>
									<td class="center">'.$imageThumbPath.'</td>
									<td>'.$productSearch->name.'</td>
									<td class="center">
										<img src="../modules/prestablog/img/disabled.gif" rel="'.$productSearch->id.'" class="delinked" />
									</td>
								</tr>'."\n";
					}
					echo '
						<script type="text/javascript">
							$("img.delinked").click(function() {
								var idP = $(this).attr("rel");
								$("#currentProductLink input.linked_"+idP).remove();
								$(".noInlisted_"+idP).remove();
								ReloadLinkedProducts();
								ReloadLinkedSearchProducts();
							});
						</script>'."\n";
				}
				else
					echo '<tr><td colspan="4" class="center">'.$PrestaBlog->MessageCallBack['no_result_linked'].'</td></tr>'."\n";
		}
		else
			echo '<tr><td colspan="4" class="center">'.$PrestaBlog->MessageCallBack['no_result_linked'].'</td></tr>'."\n";
		
		break;
	
	case "searchProducts" :
		if(		Tools::getValue('req') != "" 
			&&	Tools::getValue('idN') >= 0
			&&	Tools::getValue('token') == Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)Tools::getValue('idE'))
			) {
			if(strlen(Tools::getValue('req')) >= 3) {
				$listProductLinked = array();
				
				if(Tools::getValue('listLinkedProducts') != "")
					$listProductLinked = preg_split("/;/", rtrim(Tools::getValue('listLinkedProducts'), ';'));
				
				$resultSearch = array();
				$PrestaBlog = new PrestaBlog();
				$rSQL_search = '';
				$rSQL_lang = '';
				
				$Query = strtoupper(pSQL(Trim(Tools::getValue('req'))));
				$Querys = array_filter(explode(" ", $Query));
				
				$list_champs_product_lang = array(
					"description",
					"description_short",
					"link_rewrite",
					"name",
					"meta_title",
					"meta_description",
					"meta_keywords"
				);
				
				foreach($Querys As $key => $value) {
					foreach($list_champs_product_lang As $valueC)
						$rSQL_search .= ' UPPER(pl.`'.$valueC.'`) LIKE \'%'.$value.'%\' '."\n".' OR';
				}
				
				if(Tools::getValue('lang') != "")
					$currentLang = (int)Tools::getValue('lang');
				
				$rSQL_lang = 'AND pl.`id_lang` = '.$currentLang;
				
				$rSQL_search = ' WHERE ('.rtrim($rSQL_search, "OR").') '.$rSQL_lang;
				
				$rSQL_pLink = '';
				
				foreach($listProductLinked As $ProductLink)
					$rSQL_pLink .= ' AND pl.`id_product` <> '.(int)$ProductLink;
				
				$rSQL_search .= $rSQL_pLink;
				
				$rSQL	=	'SELECT DISTINCT(pl.`id_product`)
							FROM 	`'._DB_PREFIX_.'product_lang` AS pl
							'.$rSQL_search.'
							ORDER BY pl.`name`
							LIMIT 0, 10 ;';
				
				$resultSearch = Db::getInstance()->ExecuteS($rSQL);
				
				if(sizeof($resultSearch)) {
					foreach($resultSearch As $value) {
						$productSearch = new Product((int)$value["id_product"], false, $currentLang);
						$productCover = Image::getCover($productSearch->id);
						$image_product = new Image((int)$productCover["id_image"]);
						$imageThumbPath = $imageThumbPath = ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.$image_product->getExistingImgPath().'.jpg', 'product_mini_'.$productSearch->id.'.jpg', 45, 'jpg');
						
						echo '	<tr class="'.($productSearch->active?'':'disabled_product ').'Outlisted noOutlisted_'.$productSearch->id.'">
									<td class="center">
										<img src="../modules/prestablog/img/linked.png" rel="'.$productSearch->id.'" class="linked" />
									</td>
									<td class="center">'.$productSearch->id.'</td>
									<td class="center" style="width:50px;">'.$imageThumbPath.'</td>
									<td>'.$productSearch->name.'</td>
								</tr>'."\n";
					}
					
					echo '
						<script type="text/javascript">
							$("img.linked").click(function() {
								var idP = $(this).attr("rel");
								$("#currentProductLink").append(\'<input type="text" name="productsLink[]" value="\'+idP+\'" class="linked_\'+idP+\'" />\');
								$(".noOutlisted_"+idP).remove();
								ReloadLinkedProducts();
								ReloadLinkedSearchProducts();
							});
						</script>'."\n";
				}
				else
					echo '
						<tr class="warning">
							<td colspan="4" class="center">'.$PrestaBlog->MessageCallBack['no_result_search'].'</td>
						</tr>'."\n";
						
			}
			else {
				$PrestaBlog = new PrestaBlog();
				echo '
					<tr class="warning">
						<td colspan="4" class="center">'.$PrestaBlog->MessageCallBack['no_result_search'].'</td>
					</tr>'."\n";
			}
		}
		
		break;
	case "search" :
		break;
		
	default :
		break;
}
?> 
