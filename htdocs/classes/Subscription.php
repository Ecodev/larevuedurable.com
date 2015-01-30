<?php

class Subscription
{
    // info
    public $duration;
    public $product_attributes_name;
    public $first_edition; // revue de départ
    public $last_edition;
    public $number_of_editions; // nombre de revues inclues dans l'abo

    // ids to related objects
    public $id_order;
    public $id_order_history;
    public $product_attribute_id;

    /**
     * @var boolean
     */
    public $is_active;

    /**
     * @var boolean
     */
    public $is_future;

    /**
     * @var boolean
     */
    public $is_archive;

    /**
     * @var boolean
     */
    public $is_paper;

    public $type;

    // related objects
    public $order_history;
    public $order;
    public $customer;

    public function __construct($abonnement)
    {
        $this->product_attributes_name = $abonnement['product_name'];
        $this->id_order = $abonnement['id_order'];
        $this->order = new Order($this->id_order);
        $this->id_order_history = $abonnement['id_order_history'];
        $this->order_history = new OrderHistory($this->id_order_history);
        $this->customer = new Customer($abonnement['id_customer']);

        // Récupère les information de durée de l'abonnement
        $this->product_attribute_id = $abonnement['product_attribute_id'];
        $combination = new Combination($this->product_attribute_id);
        $attributs = $combination->getWsProductOptionValues();

        foreach ($attributs as $attribut) {
            if ($attribut['id'] == _UN_AN_) {
                $this->number_of_editions = 4;
            } elseif ($attribut['id'] == _DEUX_ANS_) {
                $this->number_of_editions = 8;
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
        $this->__toString();
    }

    public function defineStatus()
    {
        $actualMagazine = Product::getLastMagazineReleased(null);
        $actualMagazineNumber = (int) $actualMagazine['reference'];
        // infos sur l'état du produit , passé, actif ou futur
        if ($actualMagazineNumber >= $this->first_edition && $actualMagazineNumber <= $this->last_edition) {
            $this->is_active = true;
            $this->is_future = false;
        } else if ($actualMagazineNumber < $this->first_edition) {
            $this->is_active = false;
            $this->is_future = true;
        } else if ($actualMagazineNumber == null || $actualMagazineNumber > $this->last_edition) {
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
     *    Enchaine les abonnements les uns après les autres
     */
    public static function manageConflicts($subs, $manageConflicts = false)
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

    public function __toString()
    {
        return $this->product_attributes_name;
    }

    public function getStartDate()
    {
        return Product::getParutionDateByRef($this->first_edition);

    }

    public function getEndDate()
    {
        return Product::getParutionDateByRef($this->first_edition+$this->number_of_editions);
    }

}