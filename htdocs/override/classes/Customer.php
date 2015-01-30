<?php


class Customer extends CustomerCore
{
    public $subscriptions = null;
    public $user_subscriptions = null;
    public $tierce_subscriptions = null;
    public $current_subscription = null;
    public $nbPresentOrFutureActives = 0;
    public $is_tierce = false;
    public $conditions = null;


    public function __construct($id = null)
    {
        $this->cresus_id = null;
        $this->cresus_source = null;
        self::$definition['fields']['cresus_id'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId');
        self::$definition['fields']['cresus_source'] = array('type' => self::TYPE_STRING);
        parent::__construct($id);
        $this->getConditions();
    }

//    public function update($nullValues = false) {
//        $saved = parent::update($nullValues);
//
//        if ($saved) {
//            Tools::notifyCustomerChanged($this->id);
//        }
//
//        return $saved;
//    }

    /**
     *    Fonction très importante : Récupère tous les changements de status des commandes comportant un abonnement.
     *    Tant qu'une commande a un statut actif ET qu'elle comporte un abonnement, ce dernier est actif à partir de la première date d'activation de la commande (statut 2 -> Paiement accepté)
     *    Un ajout de statut est irréversible.
     */
    public function manageSubscriptions()
    {
        // cache, on ne récupère pas deux fois les infos.
        if (is_array($this->subscriptions)) {
            return $this->subscriptions;
        }

        $subs = array();
        $this->tierce_subscriptions = $this->getSubscriptionsByUserType('tierce');
        $this->user_subscriptions = $this->getSubscriptionsByUserType('himself');

        // on récupère l'abonnement courant
        if (sizeof($this->tierce_subscriptions) > 0) {
            $subs = array_merge($subs, $this->tierce_subscriptions);
            foreach ($this->tierce_subscriptions as $sub) {
                if ($sub->is_active) {
                    $this->current_subscription = $sub;
                }
            }
        }

        if (count($this->user_subscriptions) > 0) {
            $subs = array_merge($subs, $this->user_subscriptions);
        }

        // si pas d'abonnement tiers actuellement valide, on cherche le courant du visiteur
        if ($this->current_subscription == null) {
            foreach ($this->user_subscriptions as $sub) {
                //if( $date_now >= $sub->start_date && $date_now < $sub->end_date ) $this->current_subscription = $sub;
                if ($sub->is_active) {
                    $this->current_subscription = $sub;
                }
            }
        }

        $this->getConditions();

        // identifie le nombre d'abonnements qui sont présentement actifs ou qui le seront à l'avenir
        // permet d'afficher le bouton "renouveler" quand le visiteur n'a qu'un seul visiteur
        // Permet de masquer le bouton "ajouter un abonnement" quand deux abonnements sont déjà empilés
        if (sizeof($this->user_subscriptions) > 0) {
            $this->nbPresentOrFutureActives = 0;
            foreach ($this->user_subscriptions as $sub) {
                if ($sub->is_active) {
                    $this->nbPresentOrFutureActives++;
                }
                if ($sub->is_future) {
                    $this->nbPresentOrFutureActives++;
                }
            }
        }

        if ($this->current_subscription != null) {
            $this->subscribe();
        } else {
            $this->unsubscribe();
        }

        $this->subscriptions = array();
        if (count($subs) > 0 ) {
            foreach ($subs as $sub) {
                if (!in_array($sub, $this->subscriptions)) {
                    $this->subscriptions[] = $sub;
                }
            }
        }

        return $this->subscriptions;
    }


    /**
     *    Retourne les abonnements dont l'utilisateur courant bénéficie ou les siens
     *
     * @param $type tierce pour avoir les abonnements qu'on lui offre ou 'himself' pour avoir les abonnements qu'il a lui même acheté.
     */
    public function getSubscriptionsByUserType($type)
    {
        if ($type == 'tierce') {
            $institute_user = $this->getInstituteBuyer();
            if ($institute_user) {
                $subscriptions = Customer::getSubscriptionsByUserID($institute_user['id_customer']);
                foreach ($subscriptions as $sub) {
                    if ($sub->is_active) {
                        $this->is_tierce = true;
                    }
                }

                return $subscriptions;
            }
        } elseif ($type = 'himself') {
            $subscriptions = Customer::getSubscriptionsByUserID($this->id);
            $subscriptions = Subscription::manageConflicts($subscriptions);

            return $subscriptions;
        }
    }

    public function getSubscriptions()
    {
        return self::getSubscriptionsByUserID($this->id);
    }


    /**
     * Retourne les abonnements achetés par l'utilisateur demandé
     *
     * @param l'id de l'utilisateur qui a acheté les abonnements
     */
    public static function getSubscriptionsByUserID($user_id)
    {

        $sql = '
			SELECT o.id_order, o.id_customer, oh.date_add, od.product_attribute_id, oh.id_order_history, od.product_name  FROM ps_orders o
				LEFT JOIN ps_order_detail od ON o.id_order = od.id_order
				LEFT JOIN ps_order_history oh ON o.id_order = oh.id_order
				LEFT JOIN ps_order_state os on oh.id_order_state = os.id_order_state
			WHERE o.valid = 1 AND o.id_customer = ' . (int) $user_id . ' AND os.logable = 1 AND ';

        $sql .= '(od.product_id = ' . _ABONNEMENT_PARTICULIER_ . ' OR od.product_id = ' . _ABONNEMENT_INSTITUT_ . ' OR od.product_id = ' . _ABONNEMENT_SOLIDARITE_ . ' OR od.product_id = ' . _ABONNEMENT_MOOC_ . Product::getInstituteProductsAsSql() . ') ';
        $sql .= 'ORDER BY invoice_date desc, date_add asc';

        $subscriptions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $subscriptions = Customer::cleanExtraOrderStatus($subscriptions);

        return $subscriptions;
    }


    /**
     *    Récupère toutes les commandes possédant des abonnements , des produits papier ou des paiements avec BVR
     */
    public static function getAllWithPaperProductOrBVROrder($dateStart = null, $dateEnd = null)
    {
        // récupère toutes les personnes ayant acheté un abonnement institut
        $sql = "
                select
                    c.id_customer,
                    c.email,
                    c.firstname,
                    c.lastname,

                    MAX(o.id_order) as id_last_order,
                    GROUP_CONCAT( '* ', o.reference,' (',o.id_order,')',', active:', o.valid,', ', o.payment ,' : ' , o.total_paid,' ', cur.iso_code ,'.\r\n',
                                   (select GROUP_CONCAT(oda.product_id, ' -> ' ,oda.product_name SEPARATOR '\r\n') from ps_order_detail oda where oda.id_order = o.id_order GROUP BY o.reference),
                                  ')\r\n'
                                   SEPARATOR '\r\n'
                                 ) as commentaires
                FROM ps_customer c
                    LEFT JOIN `ps_orders` o ON (c.`id_customer` = o.`id_customer`)
                    LEFT JOIN `ps_order_detail` od ON (od.`id_order` = o.`id_order`)
                    LEFT JOIN `ps_product_attribute` pa ON (od.`product_attribute_id` = pa.`id_product_attribute`)
                    LEFT JOIN `ps_product_attribute_combination` pac on (pa.`id_product_attribute` = pac.`id_product_attribute` )
	                LEFT JOIN `ps_currency` cur on (cur.`id_currency` = o.`id_currency`)
                WHERE
                (
                    o.current_state = " . Configuration::get('PS_OS_BANKWIRE_BVR') . " OR
                    pac.id_attribute = " . _PAPIER_ . " OR
                    pac.id_attribute = " . _PAPIER_ET_WEB_;
        $sql .= ")";

        if ($dateStart) {
            $sql .= ' AND o.date_add >= "' . $dateStart->format(_DATE_FORMAT_SHORT_) . ' 00:00:00"';
        }
        if ($dateEnd) {
            $sql .= ' AND o.date_add < "' . $dateEnd->format(_DATE_FORMAT_SHORT_) . ' 00:00:00"';
        }

        $sql .= ' GROUP BY c.id_customer';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }


    /**
     * Ne garde que le premier état de l'historique de chaque commande (selon l'ordre défini dans la requette SQL)
     *
     * @return Retourne une liste d'abonnements (objets Subscription)
     */
    private static function cleanExtraOrderStatus($subscriptions)
    {
        $final_subs = array();
        $orders = array();


        foreach ($subscriptions as $subscription) {
            if (!in_array($subscription['id_order'], $orders)) {
                array_push($orders, $subscription['id_order']);
                array_push($final_subs, new Subscription($subscription));
            }
        }

//        echo "<pre>";
//        print_r($final_subs);
//        echo "</pre>";
//

        return $final_subs;
    }


    /**
     *    Récupère un seul et unique utilisateur ayant pu acheter un abonnement pour cette personne
     *    S'il en existe plusieurs, le permier qui est trouvé sera utilisé. Dans la mesure où l'utilisateur bénéficiaire n'a pas de droits sur l'abonnement, il n'importe pas de savoir de qui il le détient.
     *    Une mention est affichée dans la page abonnement si la personne bénéficie d'un abonnement tiers. la propriété ->is_tierce est là pour ca.
     *
     * @return Retourne un tableau représentant la personne ayant acheté un abonnement pour lui
     */
    private function getInstituteBuyer()
    {

        // récupère toutes les personnes ayant acheté un abonnement institut
        $sql = '
		select c.id_customer, c.note, o.id_cart, od.product_attribute_id, o.id_order, od.product_id, o.id_address_delivery, cu.id_customization, c.email, GROUP_CONCAT(cud.value) as emails
			FROM ps_customer c
			LEFT JOIN `ps_orders` o ON (c.`id_customer` = o.`id_customer`)
			LEFT JOIN `ps_order_detail` od ON (od.`id_order` = o.`id_order`)
			LEFT JOIN ps_cart ca ON ca.id_cart = o.id_cart
			LEFT JOIN ps_customization cu ON cu.id_cart = ca.id_cart
			LEFT JOIN ps_customized_data cud ON cud.id_customization = cu.id_customization
		WHERE o.valid=1 AND (1=0 ' . Product::getInstituteProductsAsSql() . ') GROUP BY c.id_customer';

        $acheteurs_tiers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        // Identifie si les conditions sont dans les notes ou dans les champs personnalisés
        // Transforme les conditions sous leur forme originelle pour les mettre dans une cellule "conditions" à raison d'une par ligne
        foreach ($acheteurs_tiers as $key => $acheteur) {
            if (!empty($acheteur['note'])) {
                $acheteurs_tiers[$key]['conditions'] = explode("\n", $acheteur['note']);
            } else {
                $acheteurs_tiers[$key]['conditions'] = explode(',', $acheteur['emails']);
            }
        }

        return $this->verifyAccesses($acheteurs_tiers);
    }


    /**
     * Vérifie si le visiteur courant bénéficie d'avantages achetés par qqn d'autre
     *
     * @param Une liste des personnes ayant acheté des abonnements Insituts
     * @return bool
     */
    private function verifyAccesses($institute_users)
    {
        foreach ($institute_users as $acheteur) {
            foreach($acheteur['conditions'] as $condition){

                if ($condition && ($this->verifyAccount($condition) || $this->verifyIP($condition) || $this->verifyDomain($condition))) {
                    return $acheteur;
                }
            }
        }

        return false;
    }


    /**
     *    Vérifie les droits des abonnements "pro", qui ont droit à 3 comptes
     *    Pour faire cette vérification on autorise les adresses e-mail ajoutées dans les champs personnalisés
     */
    private function verifyAccount($cond)
    {
        $cond = trim($cond, ' ');
        if (!empty($cond) && $cond == $this->email) {
            return true;
        }
    }


    /**
     *    Vérifie que les ip qui sont insérées dans les notes du client dans le BO de prestashop sont strictement égales à l'ip du visiteur courant
     */
    private function verifyIP($cond)
    {
        $cond = trim($cond, ' ');

        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if ($cond == $ip) {
            return true;
        }

        return false;
    }

    private function verifyDomain($cond)
    {
        $cond = trim($cond, ' ');

        $userEmail = explode('@', $this->email);
        $userEmailDomain = array_pop($userEmail);

        if ($cond == $userEmailDomain) {
            return true;
        }

        return false;
    }

    public function getConditions()
    {
        if ($this->conditions) {
            return;
        } // cache

        $sql = 'select c.id_customer, c.note, cu.id_customization, c.email, GROUP_CONCAT(cud.value) as emails
				FROM ps_customer c
				LEFT JOIN `ps_orders` o ON (c.`id_customer` = o.`id_customer`)
				LEFT JOIN `ps_order_detail` od ON (od.`id_order` = o.`id_order`)
				LEFT JOIN ps_cart ca ON ca.id_cart = o.id_cart
				LEFT JOIN ps_customization cu ON cu.id_cart = ca.id_cart
				LEFT JOIN ps_customized_data cud ON cud.id_customization = cu.id_customization
			WHERE o.valid=1 AND c.id_customer =' . (int) $this->id;

        $acheteurs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (sizeof($acheteurs) > 1) {
            $message = "Important ! Nombre d'acheteurs plus grand que 1\n";
            $message .= "Requête pour l'utilisateur " . $this->id . "\n";
            $message .= "$sql\n";
            error_log($message . chr(10) . date(_DATE_FORMAT_) . ",\t ligne :" . __LINE__ . ", " . __FILE__ . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/logs/debug_log.txt');
        }

        $acheteur = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        $conditions = null;
        if (!empty($acheteur['note'])) {
            $conditions = explode("\n", $acheteur['note']);
        } else if (strlen($acheteur['emails']) > 0) {
            $conditions = explode(',', $acheteur['emails']);
        }

        if (isset($conditions) && sizeof($conditions) > 0) // && strpos($conditions[0],'@')
        {
            $this->conditions = $conditions;
        }
    }


    /**
    Abonne l'utilisateur au groupe Abonnés
     */
    public function subscribe()
    {
        if ($this->is_tierce) {
            $group = _PS_SUBSCRIBER_INSTITUTE_;
        } elseif ($this->current_subscription->is_archive) {
            $group = _PS_SUBSCRIBER_ARCHIVES_GROUP_;
        } else {
            $group = _PS_SUBSCRIBER_PAPER_GROUP_;
        }

        if (Customer::getDefaultGroupId($this->id) != $group) {
            $this->updateGroup(array($group));
            Customer::setDefaultGroup($this->id, $group);
        }
    }


    /*
    Retire l'utilisateur du groupe abonnés afin qu'il ai les droits habituels
    */
    public function unsubscribe()
    {
        if (Customer::getDefaultGroupId($this->id) != _PS_DEFAULT_CUSTOMER_GROUP_) {
            $this->updateGroup(array(_PS_DEFAULT_CUSTOMER_GROUP_));
            Customer::setDefaultGroup($this->id, _PS_DEFAULT_CUSTOMER_GROUP_);
        }
    }


    public static function setDefaultGroup($id_customer, $group_id)
    {
        if (!Group::isFeatureActive()) {
            return false;
        }

        self::$_defaultGroupId[(int) $id_customer] = $group_id;
        self::$_defaultGroupId[(int) $id_customer] = Db::getInstance()->execute('
			UPDATE `' . _DB_PREFIX_ . 'customer`
			SET `id_default_group` = ' . $group_id . '
			WHERE `id_customer` = ' . (int) $id_customer);

        return true;
    }


    public function getCurrentSubscription()
    {
        return $this->current_subscription;
    }

    public function getLastSubscription()
    {
        return $this->user_subscriptions[0];
    }


    /**
     *    Récupère tous les bénéficiaires d'un abonnement (une commande comportant un abonnement doit être valide)
     */
    public static function getAllSubscribers($includeBigInstitutes = false, $dateStart = null, $dateEnd = null)
    {
        // récupère toutes les personnes ayant acheté un abonnement institut
        $sql = '
		select c.id_customer as ID, c.email as EMAIL , c.firstname as FNAME, c.lastname as LNAME';

        if ($dateStart) {
            $sql .= ', o.id_order as ID_ORDER';
        }

        $sql .= '
			FROM ps_customer c
			LEFT JOIN `ps_orders` o ON (c.`id_customer` = o.`id_customer`)
			LEFT JOIN `ps_order_detail` od ON (od.`id_order` = o.`id_order`)
			LEFT JOIN ps_cart ca ON ca.id_cart = o.id_cart
		WHERE o.valid=1
			AND (
				od.product_id =' . _ABONNEMENT_PARTICULIER_ . ' OR
				od.product_id =' . _ABONNEMENT_INSTITUT_ . ' OR
				od.product_id =' . _ABONNEMENT_SOLIDARITE_ . ' OR
				od.product_id = ' . _ABONNEMENT_MOOC_;
        ;

        if ($includeBigInstitutes) {
            $sql .= Product::getInstituteProductsAsSql();
        }
        $sql .= ')';

        if ($dateStart) {
            $sql .= ' AND o.date_add >= "' . $dateStart->format(_DATE_FORMAT_SHORT_) . ' 00:00:00"';
        }
        if ($dateEnd) {
            $sql .= ' AND o.date_add < "' . $dateEnd->format(_DATE_FORMAT_SHORT_) . ' 00:00:00"';
        }

        $sql .= ' GROUP BY c.id_customer';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }


    /**
     *    Retourne les adresses email des clients connectés aux newsletter via la création d'un compte ou via le module en page d'accueil
     */
    public static function getNewsletterSubscribers()
    {
        $sql = "select c.email as EMAIL , c.firstname as FNAME, c.lastname as LNAME, c.id_customer as ID FROM ps_customer c WHERE c.newsletter=1";
        $users = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ($users as $key => $user) {
            $customer = new Customer($user['ID']);
            $customer->manageSubscriptions();
            // Ajoute les numéros auquel est abonné l'utilisateur
            $editionsAbonnees = [];
            foreach($customer->subscriptions as $sub) {
                for($i = $sub->first_edition; $i <= $sub->last_edition; $i++) {
                    $editionsAbonnees[] = $i;
                }
            }
            sort($editionsAbonnees);
            if ($editionsAbonnees) {
                $users[$key]['NUMEROS'] = ',' . implode(',', $editionsAbonnees) . ',';
            } else {
                $users[$key]['NUMEROS'] = '';
            }

            // Ajoute les addresses et les NPA
            $addresses = $customer->getAddresses(Context::getContext()->language->id);
            $npas = [];
            $countries = [];
            foreach($addresses as $address) {
                $address = new Address($address['id_address']);
                $npas[] = $address->postcode;
                $countries[] = $address->country;
            }

            if ($npas) {
                $users[$key]['NPA'] = ',' . implode(',', $npas) . ',';
            } else {
                $users[$key]['NPA'] = '';
            }
            if ($countries) {
                $users[$key]['COUNTRY'] = ',' . implode(',', $countries) . ',';
            } else {
                $users[$key]['COUNTRY'] = '';
            }

        }

        $sql = 'select email as EMAIL FROM ps_newsletter WHERE active=1';
        $users2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $users = array_merge($users2, $users);

        return $users;
    }


    public static function getNewsletterUnsubscribed()
    {
        $sql = '
		select c.email as EMAIL , c.firstname as FNAME, c.lastname as LNAME
			FROM ps_customer c
		WHERE c.newsletter=0';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    }


}
