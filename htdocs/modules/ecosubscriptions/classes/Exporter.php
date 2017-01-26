<?php

class Exporter
{

    public static function export($config)
    {
        $time = new DateTime();
        $time = $time->format('Y-m-d-H\hi\ms\s');
        $filename = dirname(__FILE__) . '/../data/files/import/to_cresus/' . $time . '.csv';
        $filename_link = '/modules/ecosubscriptions/data/files/import/to_cresus/' . $time . '.csv';

        file_put_contents($filename, '');
        $file = fopen($filename, 'w');

        try {
            $date_start = new DateTime(Tools::getValue('exportFrom'));
            $date_end = new DateTime(Tools::getValue('exportTo'));
        } catch (Exception $ex) {
            $message = "Erreur, une date n'est pas valide : \n";
            $message .= '<pre>';
            $message .= $ex->getMessage();
            $message . '</pre>';
            echo $message;
            error_log($message . chr(10) . __LINE__ . ", " . __FILE__ . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/logs/api_log.txt');

            return -4;
        }

        $customers = Customer::getAllWithPaperProductOrBVROrder($date_start, $date_end->modify('+1 day'));

        $last_saved_export_date = new DateTime(Configuration::get('ECODEV_LAST_EXPORT_DATE'));
        if ($date_end->modify('+1 day') > $last_saved_export_date) {
            Configuration::updateValue('ECODEV_LAST_EXPORT_DATE', $date_end->modify('-1 day')->format(_DATE_FORMAT_SHORT_));
        }

        $data[$config['dernier_num']] = '';
        $data[$config['duration']] = '';
        $data[$config['type']] = '';
        $data[$config['abonnement']] = '';

        $data[$config['id']] = utf8_decode('Numéro');
        $data[$config['source']] = '';
        $data[$config['title_inv']] = 'Titre';
        $data[$config['lastname_inv']] = 'Nom';
        $data[$config['firstname_inv']] = utf8_decode('Prénom');
        $data[$config['email']] = utf8_decode('TélEmail');

        $data[$config['company_inv']] = 'Firme';
        $data[$config['address_inv']] = 'Adresse';
        $data[$config['address2_inv']] = 'Adresse2';
        $data[$config['npa_inv']] = 'NPA';
        $data[$config['localite_inv']] = utf8_decode('Localité');;
        $data[$config['country_inv']] = 'Pays';
        $data[$config['country_code_inv']] = utf8_decode('PaysAbréviation');

        $data[$config['title_livr']] = 'LivrTitre';
        $data[$config['lastname_livr']] = 'LivrNom';
        $data[$config['firstname_livr']] = utf8_decode('LivrPrénom');
        $data[$config['company_livr']] = 'LivrFirme';
        $data[$config['address_livr']] = 'LivrAdresse';
        $data[$config['npa_livr']] = 'LivrNPostal';
        $data[$config['localite_livr']] = utf8_decode('LivrLocalité2');
        $data[$config['country_livr']] = 'LivrPays';

        $data[$config['coche']] = 'Coche';
        $data[$config['comments']] = '';

        fputcsv($file, $data, chr(9));

        foreach ($customers as $c) {
            $order = null;
            $address_livr = null;
            $address_inv = null;

            $data = array();
            $customer = new Customer($c['id_customer']);
            $order = new Order($c['id_last_order']);
            $gender = self::convertToCresusGender($customer->id_gender);
            $address_inv = new Address($order->id_address_invoice);
            $country_inv = new Country($address_inv->id_country);

            if ($order->id_address_invoice != $order->id_address_delivery) {
                $address_livr = new Address($order->id_address_delivery);
                $country_livr = new Country($address_livr->id_country);
            }

            // empty fields for export
            $data[$config['dernier_num']] = '';
            $data[$config['duration']] = '';
            $data[$config['type']] = '';
            $data[$config['abonnement']] = '';

            // customer item in prestashop
            $data[$config['id']] = ($customer->cresus_id) ? $customer->cresus_id : '';
            $data[$config['source']] = ($customer->cresus_source) ? $customer->cresus_source : '';

            // invoice
            $data[$config['title_inv']] = $gender;
            $data[$config['lastname_inv']] = utf8_decode($address_inv->lastname);
            $data[$config['firstname_inv']] = utf8_decode($address_inv->firstname);
            $data[$config['email']] = $customer->email;
            $data[$config['company_inv']] = utf8_decode($address_inv->company);
            $data[$config['address_inv']] = utf8_decode($address_inv->address1);
            $data[$config['address2_inv']] = utf8_decode($address_inv->address2);
            $data[$config['npa_inv']] = $address_inv->postcode;
            $data[$config['localite_inv']] = utf8_decode($address_inv->city);
            $data[$config['country_inv']] = utf8_decode($country_inv->name[1]);
            $data[$config['country_code_inv']] = $country_inv->iso_code;

            // delivery
            if ($order->id_address_invoice != $order->id_address_delivery) {
                $data[$config['title_livr']] = $gender;
                $data[$config['lastname_livr']] = utf8_decode($address_livr->lastname);
                $data[$config['firstname_livr']] = utf8_decode($address_livr->firstname);
                $data[$config['company_livr']] = utf8_decode($address_livr->company);
                $data[$config['address_livr']] = (!empty($address_livr->address2)) ? utf8_decode($address_livr->address1 . ' - ' . $address_livr->address2) : utf8_decode($address_livr->address1);
                $data[$config['npa_livr']] = $address_livr->postcode;
                $data[$config['localite_livr']] = utf8_decode($address_livr->city);
                $data[$config['country_livr']] = utf8_decode($country_livr->name[1]);
            } else {
                $data[$config['title_livr']] = '';
                $data[$config['lastname_livr']] = '';
                $data[$config['firstname_livr']] = '';
                $data[$config['company_livr']] = '';
                $data[$config['address_livr']] = '';
                $data[$config['npa_livr']] = '';
                $data[$config['localite_livr']] = '';
                $data[$config['country_livr']] = '';
            }

            $data[$config['coche']] = 1;
            $data[$config['comments']] = utf8_decode($c['commentaires']);

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
            case 'Mme' :
                $gender = 'Madame';
                break;
            default :
                $gender = '';
        }

        return $gender;
    }

}
