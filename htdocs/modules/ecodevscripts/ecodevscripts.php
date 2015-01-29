<?php

require_once(dirname(__FILE__) . '/EcodevExporter.php');
require_once(dirname(__FILE__) . '/EcodevImporter.php');


class EcodevScripts extends Module
{
    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'ecodevscripts';
        $this->tab = 'ecodev';
        $this->version = '1.0';
        $this->_errors = array();

        parent::__construct();

        $this->displayName = $this->l("Importeur / Exporteur");
        $this->description = $this->l("");

        $count = 0;
        if (!defined('DERNIER_NUM')) {
            define('DERNIER_NUM', $count++);
        }
        if (!defined('RENEW')) {
            define('RENEW', $count++);
        }
        if (!defined('DURATION')) {
            define('DURATION', $count++);
        }
        if (!defined('TYPE')) {
            define('TYPE', $count++);
        }
        if (!defined('ABONNEMENT')) {
            define('ABONNEMENT', $count++);
        }
        if (!defined('REPLACE')) {
            define('REPLACE', $count++);
        }
        if (!defined('ID')) {
            define('ID', $count++);
        }
        if (!defined('SOURCE')) {
            define('SOURCE', $count++);
        }
        if (!defined('TITLE')) {
            define('TITLE', $count++);
        }
        if (!defined('LASTNAME')) {
            define('LASTNAME', $count++);
        }
        if (!defined('FIRSTNAME')) {
            define('FIRSTNAME', $count++);
        }
        if (!defined('TITLE_INV')) {
            define('TITLE_INV', $count++);
        }
        if (!defined('LASTNAME_INV')) {
            define('LASTNAME_INV', $count++);
        }
        if (!defined('FIRSTNAME_INV')) {
            define('FIRSTNAME_INV', $count++);
        }
        if (!defined('EMAIL')) {
            define('EMAIL', $count++);
        }
        if (!defined('COMPANY_INV')) {
            define('COMPANY_INV', $count++);
        }
        if (!defined('ADDRESS_INV')) {
            define('ADDRESS_INV', $count++);
        }
        if (!defined('ADDRESS2_INV')) {
            define('ADDRESS2_INV', $count++);
        }
        if (!defined('NPA_INV')) {
            define('NPA_INV', $count++);
        }
        if (!defined('LOCALITE_INV')) {
            define('LOCALITE_INV', $count++);
        }
        if (!defined('COUNTRY_INV')) {
            define('COUNTRY_INV', $count++);
        }
        if (!defined('COUNTRY_CODE_INV')) {
            define('COUNTRY_CODE_INV', $count++);
        }
        if (!defined('TITLE_LIVR')) {
            define('TITLE_LIVR', $count++);
        }
        if (!defined('LASTNAME_LIVR')) {
            define('LASTNAME_LIVR', $count++);
        }
        if (!defined('FIRSTNAME_LIVR')) {
            define('FIRSTNAME_LIVR', $count++);
        }
        if (!defined('COMPANY_LIVR')) {
            define('COMPANY_LIVR', $count++);
        }
        if (!defined('ADDRESS_LIVR')) {
            define('ADDRESS_LIVR', $count++);
        }
        if (!defined('NPA_LIVR')) {
            define('NPA_LIVR', $count++);
        }
        if (!defined('LOCALITE_LIVR')) {
            define('LOCALITE_LIVR', $count++);
        }
        if (!defined('COUNTRY_LIVR')) {
            define('COUNTRY_LIVR', $count++);
        }
        if (!defined('COCHE')) {
            define('COCHE', $count++);
        }
        if (!defined('COMMENTS')) {
            define('COMMENTS', $count++);
        }
    }


    private function _postProcess($msg, $success)
    {
        if ($success) {
            $this->_html .= '<div class="conf confirm"> ' . $this->l($msg) . '</div>';
        } else {
            $this->_html .= '<div class=" error"> ' . $this->l($msg) . '</div>';
        }

    }


    private function _displayForm()
    {
        global $date_now;

        $last_export_date = Configuration::get('ECODEV_LAST_EXPORT_DATE') ? Configuration::get('ECODEV_LAST_EXPORT_DATE') : '2013-01-01';
        $last_export_date = new DateTime($last_export_date);

        $this->_html .= '<form action="' . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend><img src="../img/admin/contact.gif" />' . $this->l('Transférer contacts de Crésus vers Prestashop') . '</legend>
				<table>
					<tr>
						<td width="50%">
							<p><input type="file" name="fichierCresus"/></p>
						</td> 
						<td width="50%">
							<p><input type="submit" name="importerArticles" value="Lancer l\'importation" class="button"/></p>
						</td> 
					</tr>
				</table>
			</fieldset>
		</form><br/>
		<form action="' . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '" method="post">
			<fieldset>
				<legend><img src="../img/admin/contact.gif" />' . $this->l('Transférer les nouveaux contacts de Prestashop vers Crésus') . '</legend>

				<p>L\'export le plus récent ayant été fait jusqu\'au :<strong> ' . $last_export_date->format(_DATE_FORMAT_SHORT_) . '</strong>. Nous sommes le <strong>' . $date_now->format(_DATE_FORMAT_SHORT_) . '</strong>.</p>
				<p>	
					Exporter du <input type="text" name="exportDu" value="' . Tools::getValue('exportDu', $last_export_date->modify('+1 day')->format(_DATE_FORMAT_SHORT_)) . '" /> à 00h00:00
					au <input type="text" name="exportAu" value="' . Tools::getValue('exportAu', $date_now->modify('-1 day')->format(_DATE_FORMAT_SHORT_)) . '" /> à 23h59:59.  &nbsp;&nbsp;&nbsp;<i>(Format des dates : aaaa-mm-jj)</i>
				</p>
				<p><input type="submit" name="exporterArticles" value="Lancer l\'exportation" class="button"/></p>
			</fieldset>
		</form>
		<br/>
		<!--form action="' . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '" method="post">
			<fieldset>
				<legend><img src="../img/admin/contact.gif" />' . $this->l('Faire les liaisons avec les articles liés') . '</legend>
				<p><input type="submit" name="lierArticles" value="Lancer la liaison" class="button"/></p>
			</fieldset>
		</form-->
		
		';
    }

    public function getContent()
    {

        $this->_html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('lierArticles')) {
            if ($this->linkAccessories()) {
                $this->_postProcess('Produits liés', true);
            } else {
                $this->_postProcess('Une erreur empêche de faire la liaison des produits entre eux', false);
            }

        } elseif (Tools::isSubmit('exporterArticles')) {
            $result = EcodevExporter::export();
            if ($result == -1) {
                $this->_postProcess('Vous ne pouvez pas exporter les clients d\'aujourd\'hui. ', false);
            }
            if ($result == -2) {
                $this->_postProcess('La date de départ n\'est pas valide', false);
            }
            if ($result == -3) {
                $this->_postProcess('La date de fin n\'est pas valide', false);
            }
            if ($result == -4) {
                $this->_postProcess('Exception générique. Voir le message ci-dessous : ', false);
            } elseif ($result == 10) {
                $this->_postProcess('Importation réussie', true);
            } else {

                $this->_postProcess("Importation réussie. <a href='$result'>Téléchargez le fichier</a>.", true);
            }

        } elseif (Tools::isSubmit('importerArticles')) {

            $errors = array();
            $importer = new EcodevAPIImporter();
            $errors = $importer->import();
            if(count($errors)>0){
                $message = "Les utilisateurs suivants n'ont pas été importés : ";
                $this->_postProcess($message."<br/>".implode('<br/>',$errors), false);
            }else {
                $this->_postProcess('Importation réussie', true);
            }
        }

        $this->_displayForm();

        return $this->_html;
    }


    /*
        public function linkAccessories()
        {
            $db = DB::getInstance();
            $context = Context::getContext();
            //$context->controller = new MySubscriptionController();

            // reset bd
            $db->delete('accessory');

            // récup des produits par catégorie (tient compte des multiples appartement aux catégories)
            $revues = $db->executeS('SELECT p.id_product as id, reference FROM ps_product p
                                        JOIN ps_category_product cat on cat.id_product = p.id_product
                                        WHERE cat.`id_category` ='._CATEGORY_FULL_BOOK_);

            $articles = $db->executeS('SELECT p.id_product as id, reference FROM ps_product p
                                        JOIN ps_category_product cat on cat.id_product = p.id_product
                                        WHERE cat.`id_category` ='._CATEGORY_LITTLE_ARTICLES_);

                    $accessory_revues = array();
            $accessory_articles = array();

            // prépare les liaisons dans des tableaux annexes pour limiter le nombre de requêtes sql
            foreach($revues as $revue)
            {
                foreach( $articles as $article)
                {
                    if($revue['reference'] == substr($article['reference'], 0, 3))
                    {

                        if( !isset($accessory_revues[$revue['id']]) || !is_array($accessory_revues[$revue['id']]) ) $accessory_revues[$revue['id']] = array();
                        array_push($accessory_revues[$revue['id']], $article);

                        if(  !isset($accessory_articles[$article['id']]) ||  !is_array($accessory_articles[$article['id']]) ) $accessory_articles[$article['id']] = array();
                        array_push($accessory_articles[$article['id']], $revue);
                    }
                }
            }

            // fait les liaisons dans les deux sens
            foreach($accessory_revues as $id => $el)
            {
                $product = new Product($id);
                $product->setWsAccessories($el);
            }

            foreach($accessory_articles as $id => $el)
            {
                $product = new Product($id);
                $product->setWsAccessories($el);
            }

            return true;
        }

        */



}