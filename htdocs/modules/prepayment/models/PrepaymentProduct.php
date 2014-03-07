<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentProduct extends ObjectModel
{
    public          $id;
    public          $id_shop;

    public          $id_product;
    public          $reduction;
    public          $reduction_type;

    public static $definition = array(
        'table' => 'pp_product',
        'primary' => 'id_pp_product',
        'fields' => array(
            'id_shop' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_product' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'reduction' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
            ),
            'reduction_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isReductionType'
            ),
        )
    );

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
        {
            $price = array();
            foreach ($rows as $k => $row)
                $price[$k] = Product::getPriceStatic($row['id_product']);
            asort($price);
            $tab = array();
            foreach ($price as $k => $pr)
                $tab[] = $rows[$k];
            return ObjectModel::hydrateCollection(__CLASS__, $tab);
        }
        return array();
    }

    public static function getByProduct($id_product)
    {
        $sql = 'SELECT pp.*
                FROM `'._DB_PREFIX_.self::$definition['table'].'` as pp
                WHERE `id_product` = '.intval($id_product).'
                AND `id_shop` = '.(int)Context::getContext()->shop->id;
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if ($res)
            return new PrepaymentProduct(intval($res[self::$definition['primary']]));
        return null;
    }

    public function getReduction($price, $id_currency = null)
    {
        if ($this->reduction_type == 'amount')
            return Tools::convertPrice($this->reduction, (int)$id_currency);
        return $price * $this->reduction / 100;
    }
}
