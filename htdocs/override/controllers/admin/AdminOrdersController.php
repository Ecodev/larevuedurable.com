<?php

class AdminOrdersController extends AdminOrdersControllerCore
{










    public function ajaxProcessChangeDate()
    {
        if ($this->tabAccess['edit'] === '1') {

            $order = new Order((int) Tools::getValue('order'));

            if (!Validate::isLoadedObject($order)) {
                die ('error:update');
            }

            $newDateFull = DateTime::createFromFormat(_DATE_FORMAT_, Tools::getValue('newDate'));
            $newDateShort = DateTime::createFromFormat(_DATE_FORMAT_SHORT_, Tools::getValue('newDate'));

            $newDate = $newDateFull ? $newDateFull : $newDateShort;

            if (!$newDate) {
                die('error:La date est incorrecte');
            } else {
                $order->date_add = $newDate->format(_DATE_FORMAT_);
                $order->save();
            }

            die('ok');
        }
    }

    public function ajaxProcessChangeCustomer()
    {
        if ($this->tabAccess['edit'] === '1') {

            $order = new Order((int) Tools::getValue('order'));

            if (!Validate::isLoadedObject($order)) {
                die ('error:could not load order');
            }

            $customer = new Customer((int) Tools::getValue('newCustomer'));

            if (!Validate::isLoadedObject($customer)) {
                die ('error:could not load customer');
            }

            $order->id_customer = $customer->id;
            $order->save();

            die('ok');
        }
    }

}
