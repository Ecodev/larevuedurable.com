<?php

class Subscription
{
    public $duration;
    public $first_edition;
    public $last_edition;
    public $product_attributes_name;
    public $number_of_editions;

    /**
     * @var bool
     */
    public $is_ignored;

    /**
     * @var bool
     */
    public $is_active;

    /**
     * @var bool
     */
    public $is_future;

    /**
     * @var bool
     */
    public $is_archive;

    /**
     * @var bool
     */
    public $is_paper;

    // related objects
    public $customer;
    public $product;
    public $order;
    public $order_detail;
    public $order_history;
    public $combination;

    public function __construct($abonnement)
    {
        $this->product = new Product($abonnement['product_id']);
        $this->order = new Order($abonnement['id_order']);
        $this->order_detail = new Order($abonnement['id_order_detail']);
        $this->order_history = new OrderHistory($abonnement['id_order_history']);
        $this->customer = new Customer($abonnement['id_customer']);
        $combination = new Combination($abonnement['product_attribute_id']);

        $this->is_ignored = $this->order->ignore_sub;

        $this->product_attributes_name = $abonnement['product_name'];
        $attributs = $combination->getWsProductOptionValues();

        foreach ($attributs as $attribut) {
            if ($attribut['id'] == _UN_AN_) {
                $this->number_of_editions = 4;
            } elseif ($attribut['id'] == _DEUX_ANS_) {
                $this->number_of_editions = 8;
            } elseif ($attribut['id'] == _UN_NUMERO_) { // Un numéro, pas mois, pas années
                $this->number_of_editions = 1;
            } elseif ($attribut['id'] == _SIX_MOIS_) { // ! mois, pas années
                $this->number_of_editions = 2;
            } elseif ($attribut['id'] == _TROIS_ANS_) {
                $this->number_of_editions = 12;
            } elseif ($attribut['id'] == _QUATRE_ANS_) {
                $this->number_of_editions = 16;
            } elseif ($attribut['id'] == _CINQ_ANS_) {
                $this->number_of_editions = 20;
            }
        }

        // récupère la dernière revue publiée et récupère le numéro de référence pour en faire le numéro de départ
        // !!! au moment de l'achat
        $magazineOrder = Product::getLastMagazineReleased($this->order);
        $this->first_edition = (int) $magazineOrder['reference'];
        $this->last_edition = $this->first_edition + $this->number_of_editions - 1;

        $this->is_archive = false;
        $this->is_paper = false;

        // Récupère le type d'abonnement ( incluant le web avec archives ou seulement le papier )
        foreach ($attributs as $attribut) {

            if ($attribut['id'] == _WEB_ || $attribut['id'] == _PAPIER_ET_WEB_) {
                $this->is_archive = true;
            }

            if ($attribut['id'] == _PAPIER_ || $attribut['id'] == _PAPIER_ET_WEB_) {
                $this->is_paper = true;
            }
        }

        $this->defineStatus();
    }

    public function defineStatus()
    {
        $actualMagazine = Product::getLastMagazineReleased(null);
        $actualMagazineNumber = (int) $actualMagazine['reference'];

        if ($actualMagazineNumber >= $this->first_edition && $actualMagazineNumber <= $this->last_edition) {
            $this->is_active = true;
            $this->is_future = false;
        } elseif ($actualMagazineNumber < $this->first_edition) {
            $this->is_active = false;
            $this->is_future = true;
        } elseif ($actualMagazineNumber == null || $actualMagazineNumber > $this->last_edition) {
            $this->is_active = false;
            $this->is_future = false;
        }
    }

    public function setStartEdition($date)
    {
        $this->first_edition = $date;
        $this->last_edition = $this->first_edition + $this->number_of_editions - 1;
    }

    /**
     * Enchaine les abonnements les uns après les autres
     */
    public static function manageConflicts($subs)
    {
        if (sizeof($subs) > 1) {
            for ($i = sizeof($subs) - 1; $i >= 1; $i--) {
                $premier_abo = $subs[$i]; // le plus *ancien* des deux
                $deuxieme_abo = $subs[$i - 1]; // le plus *récent* des deux

                if ($deuxieme_abo->first_edition <= $premier_abo->last_edition) {
                    $deuxieme_abo->setStartEdition($premier_abo->last_edition + 1);
                }
            }
        }

        foreach ($subs as $sub) {
            $sub->defineStatus();
        }

        return $subs;
    }

    public function getStartDate()
    {
        return Product::getParutionDateByRef($this->first_edition);
    }

    public function getEndDate()
    {
        return Product::getParutionDateByRef($this->first_edition + $this->number_of_editions);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->product_attributes_name;
    }


    public static function getNextRemindDate() {
        $magazine = Product::getLastMagazineReleased();
        $nextProductParutionDate = new DateTime(Product::getParutionDateByRef((int) $magazine['reference'] + 1));
        return $nextProductParutionDate->modify('-10 day');
    }

}
