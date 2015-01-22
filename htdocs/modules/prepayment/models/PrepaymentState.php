<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentState extends ObjectModel
{
    public          $id;
    public          $id_shop;

    public          $id_customer;
    public          $id_currency;
    public          $amount;

    public static $definition = array(
        'table' => 'pp_state',
        'primary' => 'id_pp_state',
        'fields' => array(
            'id_shop' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_customer' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'id_currency' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'amount' => array(
                'type' => ObjectModel::TYPE_FLOAT,
                'required' => true,
                'validate' => 'isFloat',
            )
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (!$this->id)
            return;

        // Update currency to default one
        $defaultCurrencyId = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
        if ($this->id_currency != $defaultCurrencyId)
        {
            $this->amount = Tools::convertPrice($this->amount, $this->id_currency, false /* divide */);
            $this->id_currency = $defaultCurrencyId;
            $this->save();
        }
    }

    public function add($autodate = true, $null_values = false)
    {
        if (!$this->id_shop)
            $this->id_shop = Context::getContext()->shop->id;

        return parent::add($autodate, $null_values);
    }

    public static function getAll()
    {
        $sql = '
            SELECT *
            FROM `'._DB_PREFIX_.self::$definition['table'].'`
            WHERE `id_shop` = '.(int)Context::getContext()->shop->id;
        if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql))
            return ObjectModel::hydrateCollection(__CLASS__, $rows);
        return array();
    }

    public static function getByCustomer($id_customer, $id_shop = null)
    {
        $sql = 'SELECT pp.*
                FROM `'._DB_PREFIX_.self::$definition['table'].'` as pp
                WHERE `id_customer` = '.intval($id_customer).'
                AND `id_shop` = '.(!$id_shop ? (int)Context::getContext()->shop->id : $id_shop);
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if ($res)
            return new PrepaymentState(intval($res[self::$definition['primary']]));
        return null;
    }
}
