<?php

class Importer
{
    public $debug = true;
    public $siteurl;
    public $apikey;
    public $webService;
    public $contactErrors = array();
    public $contactSuccess = array();
    public $xml = null;
    private $config;

    public function import($config)
    {
        $this->config = $config;

        $target_path = $this->createConnexion();
        $row = 1;
        $handle = fopen($target_path, "r");

        while (($user = fgetcsv($handle, 0, "\t")) !== false) {
            if ($row > 1) {
                try {

                    if (!$this->importUser($user)) {
                        array_push($this->contactErrors, "Cet utilisateur n'a pas été importé (pas de problèmes de web service détecté).");
                    }

                } catch (PrestaShopWebserviceException $ex) {
                    $shortMsg = $user[$config['email']] . " (" . $user[$config['id']] . ", " . $user[$config['source']] . ") - " . $ex->getMessage();
                    array_push($this->contactErrors, $shortMsg);

                    $message = "Erreur Web service : \n";
                    $message .= '<pre>' . $shortMsg . '</pre>';
                    $message .= "\nTrace : \n\n " . $ex->getTraceAsString();
                    error_log($message . chr(10) . __LINE__ . ", " . __FILE__ . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/importer_api.txt');

                } catch (Exception $ex) {
                    $shortMsg = $user[$config['email']] . " (" . $user[$config['id']] . ", " . $user[$config['source']] . ") - " . $ex->getMessage();
                    array_push($this->contactErrors, $shortMsg);

                    $message = "Erreur Générale : \n";
                    $message .= '<pre>' . $shortMsg . '</pre>';
                    $message .= "\nTrace : \n\n " . $ex->getTraceAsString();
                    error_log($message . chr(10) . __LINE__ . ", " . __FILE__ . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/importer_api.txt');
                }
            }
            $row++;
        }

        return $this->contactErrors;
    }

    private function createConnexion()
    {
        try {
            $extensions = array(
                '.csv',
                '.txt'
            );
            $extension = strrchr($_FILES['fichierCresus']['name'], '.');
            if (!in_array($extension, $extensions)) {
                return -4;
            }
            $target_path = dirname(__FILE__) . '/../data/files/import/from_cresus/' . date('Y-m-d_H-i-s') . '.csv';
            if (!move_uploaded_file($_FILES['fichierCresus']['tmp_name'], $target_path)) {
                return -1;
            }

            $protocol = Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://';
            $this->siteurl = $protocol . Configuration::get('PS_SHOP_DOMAIN');
            $this->apikey = _PS_IMPORT_FROM_CRESUS_API_KEY_;

            $this->webService = new PrestaShopWebservice($this->siteurl, $this->apikey, $this->debug);

            return $target_path;

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException('Problème à la connexion au web service (' . $ex->getMessage() . ')');
        } catch (Exception $ex) {
            throw new Exception('Exception générale à l\'initialisation de l\'importation (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Importe un utilisateur et crée les paniers et commandes nécessaires avec les bonnes dates
     * @param user ligne csv splitée dans un tableau provenant de cresus
     * @return bool true si l'utilisateur a été importé, sinon false.
     */
    public function importUser($user)
    {
        $customer = $this->getUser($user);

        if (!$customer) {
            $customer = $this->createUser($user);
            if ($customer) {
                $address_id = $this->createAddress($user, $customer->id);
            }

        } elseif ($user[$this->config['id']] != $customer->cresus_id || $user[$this->config['source']] != $customer->cresus_source) {
            $this->updateUser($user, $customer);
        }

        if ($customer) {
            $start_date_1 = $this->getStartDate($user, 2);
            $cart_id_1 = $this->createCart($user, $customer->id, $start_date_1);

            if (!isset($address_id)) {
                $address_id = $this->getAddressId($user, (int) $customer->id);
            }

            $this->createOrder($user, $customer->id, $cart_id_1, $start_date_1, $address_id);

            return true;
        }

        return false;
    }

    public function getUser($user)
    {
        if (empty($user[$this->config['email']])) {
            throw new PrestaShopWebserviceException("Pas d'e-mail");
        }

        $xml = $this->webService->get(array(
            'resource' => 'customers',
            'display' => '[id,cresus_id,cresus_source]',
            'filter[email]' => trim($user[$this->config['email']], ' ')
        ));

        $nbCustomers = count($xml->children()->children());

        if ($nbCustomers > 1) {
            throw new PrestaShopWebserviceException("Plus d'un client avec cet e-mail.");

        } elseif ($nbCustomers == 1) {
            return $xml->children()->children()[0];
        }

        return null;
    }

    public function updateUser($user, $customer)
    {
        $opt = array(
            'resource' => 'customers',
            'id' => (int) $customer->id
        );

        $xml = $this->webService->get($opt);
        $xml->customer->cresus_id = $user[$this->config['id']];
        $xml->customer->cresus_source = $user[$this->config['source']];
        $xml->customer->associations->groups->group->id = 3;

        $opt = array(
            'resource' => 'customers',
            'id' => (int) $xml->customer->id,
            'putXml' => $xml->asXML()
        );

        $customer = $this->webService->edit($opt);
        $customer_id = (int) $customer->customer->id;

        return $customer_id;
    }

    /**
     *    Insert User
     */
    public function createUser($user)
    {
        try {
            if ($user[$this->config['title_inv']] == 'Monsieur') {
                $id_gender = 1;
            } else {
                if ($user[$this->config['title_inv']] == 'Madame') {
                    $id_gender = 2;
                } else {
                    $id_gender = '';
                }
            }

            $xml = $this->webService->get(array('url' => $this->siteurl . '/api/customers?schema=synopsis'));
            if (isset($user[$this->config['id']])) {
                $xml->customer->cresus_id = $user[$this->config['id']];
            }
            if (isset($this->config['source'])) {
                $xml->customer->cresus_source = $user[$this->config['source']];
            }
            $xml->customer->passwd = '8Y58n7nj';
            $xml->customer->id_gender = $id_gender;
            $xml->customer->lastname = utf8_encode($user[$this->config['lastname_inv']]);
            $xml->customer->firstname = utf8_encode($user[$this->config['firstname_inv']]);
            $xml->customer->email = trim($user[$this->config['email']], ' ');
            $xml->customer->active = 1;
            $xml->customer->associations->groups->group->id = 3;

            $opt = array(
                'resource' => 'customers',
                'postXml' => $xml->asXML()
            );


            return $this->webService->add($opt)->customer;

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la création du client (" . $ex->getMessage() . ')');
        }

    }

    public function createAddress($user, $id_customer)
    {
        $opt = array(
            'resource' => 'countries',
            'display' => '[id]'
        );
        if (!empty($user[$this->config['country_inv']])) {
            $opt['filter[name]'] = $user[$this->config['country_inv']];
        } elseif (!empty($user[$this->config['country_code_inv']])) {
            $opt['filter[iso_code]'] = $user[$this->config['country_code_inv']];
        } else {
            throw new PrestaShopWebserviceException("Pas de pays fourni.");
        }

        $countries = $this->webService->get($opt);
        $country_id = (int) $countries->countries->country->id;

        try {
            $xml = $this->webService->get(array('url' => $this->siteurl . '/api/addresses?schema=synopsis'));
            $xml->address->id_customer = $id_customer;
            $xml->address->id_country = $country_id;
            $xml->address->alias = 'Adresse';
            $xml->address->company = utf8_encode(str_replace('&', ' and ', $user[$this->config['company_inv']]));
            $xml->address->lastname = utf8_encode($user[$this->config['lastname_inv']]);
            $xml->address->firstname = utf8_encode($user[$this->config['firstname_inv']]);
            $xml->address->address1 = utf8_encode($user[$this->config['address_inv']]);
            $xml->address->address2 = utf8_encode($user[$this->config['address2_inv']]);
            $xml->address->postcode = utf8_encode($user[$this->config['npa_inv']]);
            $xml->address->city = utf8_encode($user[$this->config['localite_inv']]);

            $opt = array(
                'resource' => 'addresses',
                'postXml' => $xml->asXML()
            );
            $address = $this->webService->add($opt);
            $address_id = (int) $address->address->id;

            return $address_id;
        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la création de l'adresse (" . $ex->getMessage() . ')');
        }
    }

    /**
     *    Insert Cart
     */
    public function createCart($user, $customer_id, $start_date)
    {
        try {
            $xml = $this->webService->get(array('url' => $this->siteurl . '/api/carts?schema=synopsis'));

            $duration = empty($user[$this->config['duration']]) ? 2 : $user[$this->config['duration']];
            $type = empty($user[$this->config['type']]) ? 'w' : $user[$this->config['type']];
            $productId = empty($user[$this->config['abonnement']]) ? 8 : $user[$this->config['abonnement']];

            if ($user[$this->config['source']] == 'EUR') {
                $id_currency = 2;
            } else {
                if ($user[$this->config['source']] == 'CHF') {
                    $id_currency = 1;
                }
            }

            $xml->cart->associations->cart_rows->cart_row->id_product_attribute = $this->getCombinationId($productId, $duration, $type);
            $xml->cart->id_currency = $id_currency;
            $xml->cart->id_customer = $customer_id;
            $xml->cart->id_guest = 0;
            $xml->cart->id_lang = 1;
            $xml->cart->associations->cart_rows->cart_row->id_product = $productId;
            $xml->cart->associations->cart_rows->cart_row->quantity = 1;

            $opt = array(
                'resource' => 'carts',
                'postXml' => $xml->asXML()
            );
            $cart = $this->webService->add($opt);
            $cart_id = (int) $cart->cart->id;

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la création du panier (" . $ex->getMessage() . ')');
        }

        try {
            // overwrite add date
            $cart->cart->date_add = $start_date->format(_DATE_FORMAT_);
            $opt = array(
                'resource' => 'carts',
                'id' => $cart_id,
                'putXml' => $cart->asXML()
            );
            $this->webService->edit($opt);

            return $cart_id;

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la modification de la date du panier (" . $ex->getMessage() . ')');
        }
    }

    /**
     *    Insert order
     */
    public function createOrder($user, $customer_id, $cart_id, $start_date, $address_id = null)
    {
        try {

            /* @var $cart CartCore */
            $cart = new Cart($cart_id);
            Context::getContext()->currency = new Currency($cart->id_currency);

            $xml = $this->webService->get(array('url' => $this->siteurl . '/api/orders?schema=synopsis'));
            $xml->order->id_address_delivery = $address_id;
            $xml->order->id_address_invoice = $address_id;
            $xml->order->id_cart = $cart_id;
            $xml->order->id_currency = $cart->id_currency;
            $xml->order->id_lang = 1;
            $xml->order->id_customer = $customer_id;
            $xml->order->id_carrier = 5;
            $xml->order->module = 'bankwire';
            $xml->order->payment = 'Virement bancaire';
            $xml->order->total_paid = $cart->getOrderTotal(true);
            $xml->order->current_state = _IMPORTED_ORDER_STATE_;
            $xml->order->total_paid_real = $cart->getOrderTotal(true);
            $xml->order->total_products = 0;
            $xml->order->total_products_wt = 0;
            $xml->order->conversion_rate = 0;

            $opt = array(
                'resource' => 'orders',
                'postXml' => $xml->asXML()
            );
            $order = $this->webService->add($opt);
            $order_id = $order->order->id;

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la création de la commande (" . $ex->getMessage() . ')');
        }

        try {
            // overwrite add date
            $opt = array(
                'resource' => 'orders',
                'id' => $order_id
            );
            $updatedOrder = $this->webService->get($opt);
            $updatedOrder->order->date_add = $start_date->format(_DATE_FORMAT_);
            $opt = array(
                'resource' => 'orders',
                'id' => $order_id,
                'putXml' => $updatedOrder->asXML()
            );
            $this->webService->edit($opt);

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la modification de la date de la commande (" . $ex->getMessage() . ')');
        }
    }

    public function getAddressId($user, $customer_id)
    {
        $xml = $this->webService->get(array(
            'resource' => 'addresses',
            'display' => '[id]',
            'filter[id_customer]' => $customer_id
        ));

        $nb = count($xml->children()->children());

        if ($nb >= 1) {
            return (int) $xml->children()->children()[0]->id;

        } elseif ($nb == 0) {
            return $this->createAddress($user, $customer_id);
        }
    }

    public function getStartDate($user)
    {
        try {

            $endCresusNumber = (int) $user[$this->config['dernier_num']];
            if ($user[$this->config['duration']] === '1numero') {
                $startUserNumber = $endCresusNumber;
            } else {
                $startUserNumber = $endCresusNumber - ((int) $user[$this->config['duration']] * 4) + 1;
            }

            $lastNumber = Product::getLastMagazineUntil($startUserNumber);

            if ($startUserNumber > (int) $lastNumber['reference']) {
                $startUserNumber = (int) $lastNumber['reference'];
            }

            $reference = (strlen($startUserNumber) == 2) ? '0' . $startUserNumber : $startUserNumber;

            $opt = array(
                'resource' => 'products',
                'display' => '[date_parution]',
                'filter[reference]' => $reference
            );

            $product = $this->webService->get($opt);
            $date_parution = $product->products->product->date_parution;

            return new DateTime($date_parution);

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la récupération de la date (" . $ex->getMessage() . ')');
        }
    }

    public function getCombinationId($product_id, $duration, $type)
    {

        if ($duration === 1) {
            $attribute_duration = _UN_AN_;
        } else if ($duration === 2) {
            $attribute_duration = _DEUX_ANS_;
        } else if ($duration === '1numero') {
            $attribute_duration = _UN_NUMERO_;
        } else {
            $attribute_duration = _UN_AN_;
        }

        if ($type == 'w') {
            $attribute_version = _WEB_;
        } else if ($type == 'wp' || $type == 'pw') {
            $attribute_version = _PAPIER_ET_WEB_;
        } else if ($type == 'p') {
            $attribute_version = _PAPIER_;
        } else {
            $attribute_version = _WEB_;
        }

        $product_id = empty($product_id) ? 8 : $product_id;

        $sql = "SELECT pa.id_product_attribute
                FROM ps_product_attribute pa
                    INNER JOIN ps_product_attribute_combination pac ON pac.id_product_attribute = pa.id_product_attribute
                    INNER JOIN ps_attribute a ON pac.id_attribute = a.id_attribute
                WHERE pac.id_attribute in (" . $attribute_duration . ", " . $attribute_version . ", 39) and pa.id_product = " . $product_id . "
                GROUP BY pa.id_product_attribute
                HAVING COUNT(DISTINCT a.id_attribute) = 3;
                ";

        $combination = DB::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $combination['id_product_attribute'];

    }

}
