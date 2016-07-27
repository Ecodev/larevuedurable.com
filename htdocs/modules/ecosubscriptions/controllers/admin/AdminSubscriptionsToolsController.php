<?php

require_once(dirname(__FILE__) . '/../../autoload.php');

class AdminSubscriptionsToolsController extends AbstractAdminSubscriptionsController
{

    private $confTVADate = 'TVA_LAST_EXPORT_DATE';
    private $tvaPath = '/../../data/files/tva';
    private $publicPath = '/modules/ecosubscriptions/data/files/tva';

    public function __construct()
    {
        $this->className = 'AdminSubscriptionsTools';
        parent::__construct();
    }

    public function initContent()
    {
        global $date_now;
        $confName = $this->context->controller->module->prefixConfiguration . $this->confTVADate;
        $last_export_date = Configuration::get($confName) ? Configuration::get($confName) : '2013-01-01';
        $last_export_date = new DateTime($last_export_date);

        $this->context->smarty->assign(array(
            'dateNow' => $date_now->format(_DATE_FORMAT_SHORT_),
            'exportTVAFrom' => $last_export_date->modify('+1 day')->format(_DATE_FORMAT_SHORT_),
            'exportTVATo' => Tools::getValue('exportTVATo', $date_now->modify('-1 day')->format(_DATE_FORMAT_SHORT_))
        ));

        parent::initContent();
    }

    public function exportTVA()
    {
        $from = null;
        $to = null;

        // Validate Dates
        try {
            $from = new DateTime(Tools::getValue('exportTVAFrom'));
            $to = new DateTime(Tools::getValue('exportTVATo'));
        } catch (Exception $e) {
            $this->errors[] = 'Une date est invalide : ' . $e->getMessage();
        }

        $confName = $this->context->controller->module->prefixConfiguration . $this->confTVADate;
        Configuration::updateValue($confName, $to->format(_DATE_FORMAT_));

        $result = $this->getTva($from, $to);

        // Save in file and suggest download
        if ($result) {
            $filename = sprintf("%s_tva_du_%s_au_%s.csv", (new DateTime())->format('Y-m-d-H\hi\ms\s'), $from->format('Y-m-d'), $to->format('Y-m-d'));

            $fp = fopen(__DIR__ . $this->tvaPath . '/' . $filename, 'w');
            fputcsv($fp, array_keys($result[0]));

            foreach ($result as $fields) {
                fputcsv($fp, $fields);
            }

            fclose($fp);

            $this->confirmations[] = sprintf('<a href="%s">Téléchargez le fichier</a>', $this->publicPath . '/' . $filename);
        } else {
            $this->errors[] = "Aucun résulat n'a pu être récupéré. Veuillez contacter l'administrateur";
        }

    }

    private function getTVA($from, $to)
    {
        if ($from && $to) {

            try {
                $sql = sprintf("select o.id_order, o.reference, o.date_add, o.date_upd, sl.name, o.payment, c.id_customer, c.email, o.total_paid, cur.sign
                        from ps_orders o
                        join ps_customer c on o.id_customer = c.id_customer
                        join ps_order_state s on s.id_order_state = o.current_state
                        join ps_order_state_lang sl on sl.id_order_state = s.id_order_state and sl.id_lang = 1
                        join ps_currency cur on cur.id_currency = o.id_currency and o.id_currency = 1
                        where o.current_state in (19,20,22,24) 
                        and o.date_upd >= '%s' and o.date_upd < '%s'
                        order by o.date_upd asc;
                        ", $from->format(_DATE_FORMAT_), $to->format(_DATE_FORMAT_));

                $result = DB::getInstance()->executeS($sql);

                if (!$result) {
                    $this->errors[] = "Le script n'a pas pu récupérer la TVA, veuillez contacter l'administrateur.";
                    EcoUtility::log(['Erreur a la récupération de la TVA', 'Sql : ' . $sql], __FILE__, __LINE__);
                } else {
                    return $result;
                }

            } catch (PrestaShopDatabaseException $e) {
                $this->errors[] = "Le script n'a pas pu récupérer la TVA, veuillez contacter l'administrateur.";
                EcoUtility::log('Erreur a la récupération de la TVA. Erreur : ' . $e->getMessage(), __FILE__, __LINE__);
            }
        }

        return null;
    }

    public function regeneratePagination()
    {

        $sql = "UPDATE " . _DB_PREFIX_ . "product SET
            numero = CAST(SUBSTR(reference, 1, 3) AS int),
            page = CAST(SUBSTR(reference, 5, 3) AS int)
            WHERE reference REGEXP \"^[0-9]{3}-[0-9]{2,3}$\";";

        $sql .= "UPDATE " . _DB_PREFIX_ . "product SET
            numero = CAST(SUBSTR(reference, 1, 3) AS int),
            page = 0
            WHERE reference REGEXP \" ^ [0 - 9]{3}$\";";

        DB::getInstance()->execute($sql);
    }

    public function computeSubscribers()
    {

        $subscribers = Customer::getAllSubscribers();

        $archiveOnly = [];
        $paperOnly = [];
        $paperAndArchive = [];
        $archiveOnlyActually = [];
        $paperOnlyActually = [];
        $paperAndArchiveActually = [];

        $standard = [];
        $pro = [];
        $soli = [];
        $standardActually = [];
        $proActually = [];
        $soliActually = [];

        $active = [];
        $future = [];
        $past = [];

        $all = [];

        foreach ($subscribers as $customer) {
            $customer = new Customer($customer['ID']);
            $customer->manageSubscriptions();

            foreach ($customer->user_subscriptions as $sub) {
                if ($sub->is_archive && !$sub->is_paper) {
                    array_push($archiveOnly, $sub);
                    if ($sub->is_active) {
                        array_push($archiveOnlyActually, $sub);
                    }
                }
                if ($sub->is_paper && !$sub->is_archive) {
                    array_push($paperOnly, $sub);
                    if ($sub->is_active) {
                        array_push($paperOnlyActually, $sub);
                    }
                }
                if ($sub->is_paper && $sub->is_archive) {
                    array_push($paperAndArchive, $sub);
                    if ($sub->is_active) {
                        array_push($paperAndArchiveActually, $sub);
                    }
                }

                if ($sub->product->id == _ABONNEMENT_PARTICULIER_) {
                    array_push($standard, $sub);
                    if ($sub->is_active) {
                        array_push($standardActually, $sub);
                    }
                }
                if ($sub->product->id == _ABONNEMENT_INSTITUT_) {
                    array_push($pro, $sub);
                    if ($sub->is_active) {
                        array_push($proActually, $sub);
                    }
                }
                if ($sub->product->id == _ABONNEMENT_SOLIDARITE_) {
                    array_push($soli, $sub);
                    if ($sub->is_active) {
                        array_push($soliActually, $sub);
                    }
                }

                if ($sub->is_active) {
                    array_push($active, $sub);
                }
                if ($sub->is_future) {
                    array_push($future, $sub);
                }
                if (!$sub->is_active && !$sub->is_future) {
                    array_push($past, $sub);
                }

                array_push($all, $sub);
            }
        }

        $this->context->smarty->assign(array(
            'computed' => true,
            'all' => $all,
            'archiveOnlyActually' => $archiveOnlyActually,
            'paperOnlyActually' => $paperOnlyActually,
            'paperAndArchiveActually' => $paperAndArchiveActually,
            'archiveOnly' => $archiveOnly,
            'paperOnly' => $paperOnly,
            'paperAndArchive' => $paperAndArchive,
            'standardActually' => $standardActually,
            'proActually' => $proActually,
            'soliActually' => $soliActually,
            'standard' => $standard,
            'pro' => $pro,
            'soli' => $soli,
            'active' => $active,
            'future' => $future,
            'past' => $past,
        ));
    }

}
