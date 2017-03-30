<?php

require_once(dirname(__FILE__) . '/PSWebServiceLibrary.php');

class EcodevAPIImporter
{
    public $debug = 0; // 1=presatshop webservice output
    public $debug2 = 0; // 0=No output, 1=ligne output, 2=full outputs
    public $siteurl;
    public $apikey;
    public $webService;
    public $contactErrors = array();
    public $contactSuccess = array();
    public $xml = null;

    public function import()
    {
        $target_path = $this->createConnexion();
        $row = 1;
        $handle = fopen($target_path, "r");

        while (($user = fgetcsv($handle, 0, "\t")) !== false) {
            if ($row > 1) {
                try {

                    if ($this->debug2 == 2) {
                        echo '<h1>***********************************************************************</h1>';
                    }
                    if ($this->debug2 >= 1) {
                        //echo '* '.(utf8_encode(implode(', ', $user))) . '<br/>';
                        echo '<pre>';
                        print_r($user);
                    }

                    if (!$this->importUser($user)) {
                        array_push($this->contactErrors, "Cet utilisateur n'a pas été importé (pas de problèmes de web service détecté).");
                    }

                } catch (PrestaShopWebserviceException $ex) {
                    $shortMsg = $user[EMAIL] . " (" . $user[ID] . ", " . $user[SOURCE]. ") - " . $ex->getMessage();
                    array_push($this->contactErrors, $shortMsg);

                    $message = "Erreur Web service : \n";
                    $message .= '<pre>' . $shortMsg . '</pre>';
                    $message .= "\nTrace : \n\n " . $ex->getTraceAsString();
                    if ($this->debug2 == 2) {
                        echo $message;
                    }
                    error_log($message . chr(10) . __LINE__ . ", " . __FILE__ . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/../logs/importer_api.txt');

                } catch (Exception $ex) {
                    $shortMsg = $user[EMAIL] . " (" . $user[ID] . ", " . $user[SOURCE]. ") - " . $ex->getMessage();
                    array_push($this->contactErrors, $shortMsg);

                    $message = "Erreur Générale : \n";
                    $message .= '<pre>' . $shortMsg . '</pre>';
                    $message .= "\nTrace : \n\n " . $ex->getTraceAsString();
                    if ($this->debug2 == 2) {
                        echo $message;
                    }
                    error_log($message . chr(10) . __LINE__ . ", " . __FILE__ . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/../logs/importer_api.txt');
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
            $target_path = dirname(__FILE__) . '/data/files/de_cresus/' . date('Y-m-d_H-i-s') . '.csv';
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
     *    Importe un utilisateur et crée les paniers et commandes nécessaires avec les bonnes dates
     *
     * @param user ligne csv splitée dans un tableau provenant de cresus
     *
     * @return bool true si l'utilisateur a été importé, sinon false.
     */
    public function importUser($user)
    {
        if ($this->debug2 == 2) {
            echo '<h1>Create User</h1>';
        }
        $customer_id = $this->createUser($user);
        if ($customer_id > 0) {
            $address_id = $this->createAddress($user, $customer_id);

            if ($this->debug2 == 2) {
                echo '<h1>Create cart</h1>';
            }
            $start_date_1 = $this->getStartDate($user, 2);
            $cart_id_1 = $this->createCart($user, $customer_id, $start_date_1);

            if ($this->debug2 == 2) {
                echo '<h1>Create order</h1>';
            }
            $this->createOrder($user, $customer_id, $cart_id_1, $start_date_1, $address_id);

            // En cas de renouvellement
            if ($user[RENEW] == 1) {

                $start_date_2 = clone $start_date_1;
                $start_date_2->modify('+1 minute');

                if ($this->debug2 == 2) {
                    echo '<h1>Create new subscription cart</h1>';
                }
                $cart_id_2 = $this->createCart($user, $customer_id, $start_date_2);
                if ($this->debug2 == 2) {
                    echo '<h1>Create new subscription order</h1>';
                }
                $this->createOrder($user, $customer_id, $cart_id_2, $start_date_2, $address_id);
            }

            return true;
        }

        return false;
    }

    /**
     *    Insert User
     */
    public function createUser($user)
    {
        if(empty($user[EMAIL])) {
            throw new PrestaShopWebserviceException('Pas d\'e-mail');
        }

        $xml = $this->webService->get(array(
            'resource'      => 'customers',
            'display'       => '[id]',
            'filter[email]' => trim($user[EMAIL], ' ')
        ));

        $nbCustomers = count($xml->children()->children());
        if ($nbCustomers > 1) {
            throw new PrestaShopWebserviceException("Plus d'un client avec cet e-mail.");

        // if a single user is found, use this user if option replace is setted to 1. Else return error and stop import for this user.
        } else if ($nbCustomers == 1) {

            if($user[REPLACE] === 0) {
                throw new PrestaShopWebserviceException("Un client existe déjà avec cet e-mail et l'option de remplacement n'est pas définie sur 1.");
            }

            $opt = array(
                'resource' => 'customers',
                'id'       => (int) $xml->customers->customer->id
            );
            $xml = $this->webService->get($opt);
            $xml->customer->cresus_id = $user[ID];
            $xml->customer->cresus_source = $user[SOURCE];
            $xml->customer->associations->groups->group->id = 3;

            $opt = array(
                'resource' => 'customers',
                'id' => (int) $xml->customer->id,
                'putXml'  => $xml->asXML()
            );

            $customer = $this->webService->edit($opt);

            $customer_id = (int) $customer->customer->id;
            if ($this->debug2 == 2) {
                echo "User successfully added : " . $customer_id . "<br/>";
            }

            return $customer_id;


        // if no user found, create it
        } else {

            try {
                if ($user[TITLE_INV] == 'Monsieur') {
                    $id_gender = 1;
                } else if ($user[TITLE_INV] == 'Madame') {
                    $id_gender = 2;
                } else {
                    $id_gender = '';
                }

                $xml = $this->webService->get(array('url' => $this->siteurl . '/api/customers?schema=synopsis'));
                if (isset($user[ID])) $xml->customer->cresus_id = $user[ID];
                if (isset($user[SOURCE])) $xml->customer->cresus_source = $user[SOURCE];
                $xml->customer->passwd = '8Y58n7nj';
                $xml->customer->id_gender = $id_gender;
                $xml->customer->lastname = utf8_encode($user[LASTNAME_INV]);
                $xml->customer->firstname = utf8_encode($user[FIRSTNAME_INV]);
                $xml->customer->email = trim($user[EMAIL], ' ');
                $xml->customer->active = 1;
                $xml->customer->associations->groups->group->id = 3;

                $opt = array(
                    'resource' => 'customers',
                    'postXml'  => $xml->asXML()
                );

                $customer = $this->webService->add($opt);

                $customer_id = (int) $customer->customer->id;
                if ($this->debug2 == 2) {
                    echo "User successfully added : " . $customer_id . "<br/>";
                }

                return $customer_id;

            } catch (PrestaShopWebserviceException $ex) {
                throw new PrestaShopWebserviceException("Erreur à la création du client (" . $ex->getMessage() . ')');
            }
        }

    }

    public function createAddress($user, $id_customer)
    {
        $opt = array(
            'resource' => 'countries',
            'display'  => '[id]'
        );
        if (!empty($user[COUNTRY_INV])) {
            $opt['filter[name]'] = $user[COUNTRY_INV];
        } elseif (!empty($user[COUNTRY_CODE_INV])) {
            $opt['filter[iso_code]'] = $user[COUNTRY_CODE_INV];
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
            $xml->address->company = utf8_encode(str_replace('&', ' and ', $user[COMPANY_INV]));
            $xml->address->lastname = utf8_encode($user[LASTNAME_INV]);
            $xml->address->firstname = utf8_encode($user[FIRSTNAME_INV]);
            $xml->address->address1 = utf8_encode($user[ADDRESS_INV]);
            $xml->address->address2 = utf8_encode($user[ADDRESS2_INV]);
            $xml->address->postcode = utf8_encode($user[NPA_INV]);
            $xml->address->city = utf8_encode($user[LOCALITE_INV]);

            $opt = array(
                'resource' => 'addresses',
                'postXml'  => $xml->asXML()
            );
            $address = $this->webService->add($opt);
            $address_id = (int) $address->address->id;
            if ($this->debug2 == 2) {
                echo "Address successfully added : " . $address_id . "<br/>";
            }

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

            $duration = empty($user[DURATION]) ? 2 : $user[DURATION];
            $type = empty($user[TYPE]) ? 'w' : $user[TYPE];
            $productId = empty($user[ABONNEMENT]) ? 8 : $user[ABONNEMENT];

            if ($user[SOURCE]=='EUR') {
                $id_currency = 2;
            } else if ($user[SOURCE]=='CHF') {
                $id_currency = 1;
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
                'postXml'  => $xml->asXML()
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
                'id'       => $cart_id,
                'putXml'   => $cart->asXML()
            );
            $this->webService->edit($opt);
            if ($this->debug2 == 2) {
                echo "Cart successfully added : " . $cart_id . "<br/>";
            }

            return $cart_id;

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la modification de la date du panier (" . $ex->getMessage() . ')');
        }
    }

    /**
     *    Insert order
     */
    public function createOrder($user, $customer_id, $cart_id, $start_date, $address_id)
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
                'postXml'  => $xml->asXML()
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
                'id'       => $order_id
            );
            $updatedOrder = $this->webService->get($opt);
            $updatedOrder->order->date_add = $start_date->format(_DATE_FORMAT_);
            $opt = array(
                'resource' => 'orders',
                'id'       => $order_id,
                'putXml'   => $updatedOrder->asXML()
            );
            $this->webService->edit($opt);

            if ($this->debug2 == 2) {
                echo "Order successfully added : $order_id <br/>";
            }

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la modification de la date de la commande (" . $ex->getMessage() . ')');
        }
    }


    public function getStartDate($user)
    {
        try {
            $endCresusNumber = (int) $user[DERNIER_NUM];
            $startUserNumber = $endCresusNumber - ((int) $user[DURATION] * 4) + 1;

            $lastNumber = Product::getLastMagazineUntil($startUserNumber);
            if ($startUserNumber > (int) $lastNumber['reference']) $startUserNumber = (int) $lastNumber['reference'];

            $reference = (strlen($startUserNumber) == 2) ? '0' . $startUserNumber : $startUserNumber;
            if ($this->debug2 == 2) {
                echo "Start user number : " . $startUserNumber . '<br/>';
            }

            $opt = array(
                'resource'          => 'products',
                'display'           => '[date_parution]',
                'filter[reference]' => $reference
            );

            $product = $this->webService->get($opt);
            $date_parution = $product->products->product->date_parution;

            if ($this->debug2 == 2) {
                echo "Product date_parution successfully retrieved : " . $date_parution . "<br/>";
            }

            return new DateTime($date_parution);

        } catch (PrestaShopWebserviceException $ex) {
            throw new PrestaShopWebserviceException("Erreur à la récupération de la date (" . $ex->getMessage() . ')');
        }
    }

    public function getCombinationId($product_id, $duration, $type)
    {

        if ($duration == 1) {
            $attribute_duration = _UN_AN_;
        } else if ($duration == 2) {
            $attribute_duration = _DEUX_ANS_;
        } else {
            $attribute_duration = _DEUX_ANS_;
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

        $product_id = empty($product_id) ? 8 : $product_id ;

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
