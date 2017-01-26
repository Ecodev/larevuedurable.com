<?php

require_once(dirname(__FILE__) . '/../../autoload.php');

class AdminSubscriptionsTransferController extends AbstractAdminSubscriptionsController
{
    public function __construct()
    {
        global $date_now;

        $this->className = 'AdminSubscriptionsTransfer';
        $this->context = Context::getContext();

        $last_export_date = Configuration::get('ECODEV_LAST_EXPORT_DATE') ? Configuration::get('ECODEV_LAST_EXPORT_DATE') : '2013-01-01';
        $last_export_date = new DateTime($last_export_date);

        $this->context->smarty->assign(array(
            'lastExportDate' => $last_export_date->format(_DATE_FORMAT_SHORT_),
            'dateNow' => $date_now->format(_DATE_FORMAT_SHORT_),
            'exportFrom' => Tools::getValue('exportDu', $last_export_date->modify('+1 day')->format(_DATE_FORMAT_SHORT_)),
            'exportTo' => Tools::getValue('exportAu', $date_now->modify('-1 day')->format(_DATE_FORMAT_SHORT_))
        ));

        parent::__construct();
    }

    public function export()
    {
        $result = Exporter::export($this->getConfig());

        if ($result == -1) {
            $this->errors[] = 'Vous ne pouvez pas exporter les clients d\'aujourd\'hui.';
        }
        if ($result == -2) {
            $this->errors[] = 'La date de départ n\'est pas valide';
        }
        if ($result == -3) {
            $this->errors[] = 'La date de fin n\'est pas valide';
        }
        if ($result == -4) {
            $this->errors[] = 'Exception générique. Voir le message ci-dessous : ';
        } elseif ($result == 10) {
            $this->confirmations[] ='Importation réussie';
        } else {

            $this->confirmations[] ="Importation réussie. <a href='$result'>Téléchargez le fichier</a>.";
        }
    }

    public function import()
    {
        $importer = new Importer();
        $errors = $importer->import($this->getConfig());
        if (count($errors) > 0) {
            $message = "Les utilisateurs suivants n'ont pas été importés : ";
            $this->errors[] = $message . "<br/>" . implode('<br/>', $errors);
        } else {
            $this->confirmations[] ='Importation réussie';
        }
    }

    public function getConfig()
    {
        $config = [
            'dernier_num',
            'duration',
            'type',
            'abonnement',
            'id',
            'source',
            'title_inv',
            'lastname_inv',
            'firstname_inv',
            'email',
            'company_inv',
            'address_inv',
            'address2_inv',
            'npa_inv',
            'localite_inv',
            'country_inv',
            'country_code_inv',
            'title_livr',
            'lastname_livr',
            'firstname_livr',
            'company_livr',
            'address_livr',
            'npa_livr',
            'localite_livr',
            'country_livr',
            'coche',
            'comments'
        ];

        return array_flip($config);

    }
}
