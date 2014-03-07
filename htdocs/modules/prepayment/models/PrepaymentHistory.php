<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentHistory extends ObjectModel
{
    public          $id;
    public          $id_shop;

    public          $id_customer;
    public          $id_currency;
    public          $id_order;
    public          $date;
    public          $amount;

    public static $definition = array(
        'table' => 'pp_history',
        'primary' => 'id_pp_history',
        'fields' => array(
            'id_shop' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_customer' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ),
            'id_currency' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ),
            'id_order' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ),
            'date' => array(
                'type' => ObjectModel::TYPE_DATE,
                'required' => true,
            ),
            'amount' => array(
                'type' => ObjectModel::TYPE_FLOAT,
                'required' => true,
                'validate' => 'isFloat',
            )
        )
    );

    public static function getAll()
    {
        $sql = '
            SELECT *
            FROM `'._DB_PREFIX_.self::$definition['table'].'`
            AND `id_shop` = '.(int)Context::getContext()->shop->id;
        if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql))
            return ObjectModel::hydrateCollection(__CLASS__, $rows);
        return array();
    }

    public static function getByCustomer($id_customer)
    {
        $sql = 'SELECT pp.*
                FROM `'._DB_PREFIX_.self::$definition['table'].'` as pp
                WHERE `id_customer` = '.intval($id_customer).'
                AND `id_shop` = '.(int)Context::getContext()->shop->id.'
                ORDER BY `date` DESC';
        if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql))
            return ObjectModel::hydrateCollection(__CLASS__, $rows);
        return array();
    }

    public static function getByOrder($id_order)
    {
        $sql = 'SELECT pp.*
                FROM `'._DB_PREFIX_.self::$definition['table'].'` as pp
                WHERE `id_order` = '.intval($id_order).'
                AND `id_shop` = '.(int)Context::getContext()->shop->id;
        if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql))
            return ObjectModel::hydrateCollection(__CLASS__, $rows);
        return array();
    }

    public function getOrder()
    {
        $orders = Order::getCustomerOrders($this->id_customer);
        foreach ($orders as $order)
            if ($order['id_order'] == $this->id_order)
                return $order;
        return null;
    }

    public function add($autodate = true, $null_values = false)
    {
        if (!$this->id_shop)
            $this->id_shop = Context::getContext()->shop->id;

        if (!parent::add($autodate, $null_values))
            return false;

        $state = PrepaymentState::getByCustomer($this->id_customer);
        $amount = $this->amount;
        if (!$state)
        {
            $state = new PrepaymentState();
            $state->id_customer = $this->id_customer;
            $state->id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        elseif ($this->id_currency != $state->id_currency)
            $amount = Tools::convertPrice($amount, $this->id_currency, false /* divide */);
        $state->amount += $amount;
        $state->save();
        return true;
    }

    public function delete()
    {
        $obj = new PrepaymentHistory($this->id);
        $amount = $obj->amount;
        if (!parent::delete())
            return false;
        $state = PrepaymentState::getByCustomer($obj->id_customer, $obj->id_shop);
        if ($state && $state->id)
        {
            if ($obj->id_currency != $state->id_currency)
                $amount = Tools::convertPrice($amount, $obj->id_currency, false /* divide */);
            $state->amount -= $amount;
            $state->update();
        }
        return true;
    }

    public function update($null_values = false)
    {
        // Save old values before update
        $oldObj = new PrepaymentHistory($this->id);
        $oldAmount = $oldObj->amount;

        // Make update
        if (!parent::update($null_values))
            return false;

        // Update customer state
        $state = PrepaymentState::getByCustomer($this->id_customer, $this->id_shop);
        $amount = $this->amount;
        if ($state && $state->id)
        {
            if ($this->id_currency != $state->id_currency)
            {
                $oldAmount = Tools::convertPrice($oldAmount, $this->id_currency, false /* divide */);
                $amount = Tools::convertPrice($amount, $this->id_currency, false /* divide */);
            }
            $state->amount -= $oldAmount;
            $state->amount += $amount;
            $state->update();
        }
        return true;
    }
}
