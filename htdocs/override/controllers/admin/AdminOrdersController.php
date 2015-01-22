<?php
class AdminOrdersController extends AdminOrdersControllerCore
{

    public function postProcess()
    {
        // If id_order is sent, we instanciate a new Order object
        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0)
        {
            $order = new Order(Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($order))
                throw new PrestaShopException('Can\'t load Order object');
        }


        if (Tools::isSubmit('submitDate') && isset($order)) {

            $newDateFull = DateTime::createFromFormat(_DATE_FORMAT_, Tools::getValue('newOrderDate'));
            $newDateShort = DateTime::createFromFormat(_DATE_FORMAT_SHORT_, Tools::getValue('newOrderDate'));

            $newDate = $newDateFull ? $newDateFull : $newDateShort;

            if (!$newDate) {
                $this->errors[] = Tools::displayError('La date est incorrecte');
            } else {
                $order->date_add = $newDate->format(_DATE_FORMAT_);
                $order->save();
            }
        }

       parent::postProcess();
    }

}