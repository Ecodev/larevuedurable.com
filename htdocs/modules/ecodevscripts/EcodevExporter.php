<?php

class EcodevExporter
{

    public static function export()
    {
        $time = new DateTime();
        $time = $time->format('Y-m-d-H\hi\ms\s');
        $filename = __DIR__.'/pour_cresus/'.$time.'.csv';
        $filename_link = '/modules/ecodevscripts/pour_cresus/'.$time.'.csv';
        file_put_contents($filename, '');
        $file = fopen($filename, 'w');

        try {
            $date_start = new DateTime(Tools::getValue('exportDu'));
            $date_end = new DateTime(Tools::getValue('exportAu'));
        } catch (Exception $ex) {
            $message = "Erreur, une date n'est pas valide : \n";
            $message .= '<pre>';
            $message .= $ex->getMessage();
            $message.'</pre>';
            echo $message;
            error_log($message.chr(10).__LINE__.", ".__FILE__.chr(10).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/log/_api_import_error_log.txt');

            return -4;
        }

        $customers = Customer::getAllWithPaperProductOrBVROrder( $date_start, $date_end->modify('+1 day'));


        $last_saved_export_date = new DateTime(Configuration::get('ECODEV_LAST_EXPORT_DATE'));
        if ($date_end->modify('+1 day') > $last_saved_export_date) {
            Configuration::updateValue('ECODEV_LAST_EXPORT_DATE', $date_end->modify('-1 day')->format(_DATE_FORMAT_SHORT_));
        }


        $data[DERNIER_NUM] = '';
        $data[RENEW] = '';
        $data[DURATION] = '';
        $data[TYPE] = '';
        $data[ABONNEMENT] = '';

        $data[ID] = utf8_decode('Numéro');
        $data[SOURCE] = '';
        $data[TITLE] = '';
        $data[LASTNAME] = '';
        $data[FIRSTNAME] = '';

        $data[TITLE_INV] = 'Titre';
        $data[LASTNAME_INV]= 'Nom';
        $data[FIRSTNAME_INV]= utf8_decode('Prénom');
        $data[EMAIL]= utf8_decode('TélEmail');

        $data[COMPANY_INV]='Firme';
        $data[ADDRESS_INV]='Adresse';
        $data[ADDRESS2_INV]='Adresse2';
        $data[NPA_INV]='NPA';
        $data[LOCALITE_INV]= utf8_decode('Localité');;
        $data[COUNTRY_INV]= 'Pays';
        $data[COUNTRY_CODE_INV]=utf8_decode('PaysAbréviation');

        $data[TITLE_LIVR]= 'LivrTitre';
        $data[LASTNAME_LIVR]= 'LivrNom';
        $data[FIRSTNAME_LIVR]= utf8_decode('LivrPrénom');
        $data[COMPANY_LIVR]= 'LivrFirme';
        $data[ADDRESS_LIVR]= 'LivrAdresse';
        $data[NPA_LIVR]= 'LivrNPostal';
        $data[LOCALITE_LIVR]= utf8_decode('LivrLocalité2');
        $data[COUNTRY_LIVR]= 'LivrPays';

        $data[COCHE]= 'Coche';
        $data[COMMENTS]= '' ;

        fputcsv($file, $data, chr(9));

        foreach ($customers as $c)
        {
            $order = null;
            $address_livr = null;
            $address_inv = null;

            $data = array();
            $customer = new Customer($c['id_customer']);
            $order = new Order($c['id_last_order']);
            $gender = self::convertToCresusGender($customer->id_gender);
            $address_inv = new Address($order->id_address_invoice);
            $country_inv = new Country($address_inv->id_country);

            if($order->id_address_invoice != $order->id_address_delivery)
            {
                $address_livr = new Address($order->id_address_delivery);
                $country_livr = new Country($address_livr->id_country);
            }

            // empty fields for export
            $data[DERNIER_NUM] = '';
            $data[RENEW] = '';
            $data[DURATION] = '';
            $data[TYPE] = '';
            $data[ABONNEMENT] = '';

            // customer item in prestashop
            $data[ID] = ($customer->cresus_id) ? $customer->cresus_id : '';
            $data[SOURCE] = ($customer->cresus_source) ? $customer->cresus_source : '';
            $data[TITLE] = $gender;
            $data[LASTNAME] = utf8_decode($customer->lastname);
            $data[FIRSTNAME] = utf8_decode($customer->firstname);

            // invoice
            $data[TITLE_INV] = $gender;
            $data[LASTNAME_INV]= utf8_decode($address_inv->lastname);
            $data[FIRSTNAME_INV]= utf8_decode($address_inv->firstname);
            $data[EMAIL]= $customer->email;
            $data[COMPANY_INV]= utf8_decode($address_inv->company);
            $data[ADDRESS_INV]= utf8_decode($address_inv->address1);
            $data[ADDRESS2_INV]=utf8_decode($address_inv->address2);
            $data[NPA_INV]= $address_inv->postcode;
            $data[LOCALITE_INV]= utf8_decode($address_inv->city);
            $data[COUNTRY_INV]= utf8_decode($country_inv->name[1]);
            $data[COUNTRY_CODE_INV]=$country_inv->iso_code;

            // delivery
            if($order->id_address_invoice != $order->id_address_delivery)
            {
                $data[TITLE_LIVR]= $gender;
                $data[LASTNAME_LIVR]= utf8_decode($address_livr->lastname);
                $data[FIRSTNAME_LIVR]=utf8_decode($address_livr->firstname);
                $data[COMPANY_LIVR]= utf8_decode($address_livr->company);
                $data[ADDRESS_LIVR]=  (!empty($address_livr->address2)) ? utf8_decode($address_livr->address1.' - '.$address_livr->address2) : utf8_decode($address_livr->address1);
                $data[NPA_LIVR]= $address_livr->postcode;
                $data[LOCALITE_LIVR]= utf8_decode($address_livr->city);
                $data[COUNTRY_LIVR]= utf8_decode($country_livr->name[1]);
            }
            else
            {
                $data[TITLE_LIVR]= '';
                $data[LASTNAME_LIVR]= '';
                $data[FIRSTNAME_LIVR]='';
                $data[COMPANY_LIVR]= '';
                $data[ADDRESS_LIVR]=  '';
                $data[NPA_LIVR]= '';
                $data[LOCALITE_LIVR]= '';
                $data[COUNTRY_LIVR]= '';
            }

            $data[COCHE] = 1;
            $data[COMMENTS]= utf8_decode($c['commentaires']) ;


/*            $payment = 'Moyen de paiement : '.utf8_decode($order->payment);
            $total = 'Total cmd : '.$order->total_paid.' '.$currency->iso_code;

            $order_detail = '';
            foreach ($order->getProducts() as $product) {
                $order_detail .= $product['product_name'];
            }
            $comments = $payment.",\r\n".$total.",\r\n".$order_detail;
            $data[COMMENTS] = utf8_decode($comments);*/
            fputcsv($file, $data, chr(9));
        }

        return $filename_link;
    }

    private static function convertToCresusGender($gender_id)
    {
        $gender = new Gender($gender_id);
        switch ($gender->name[1]) {
        case 'M.' :
            $gender = 'Monsieur';
            break;
        case 'Mlle' :
            $gender = 'Madame';
            break;
        case 'Mme' :
            $gender = 'Madame';
            break;
        default :
            $gender = '';
        }

        return $gender;
    }

}