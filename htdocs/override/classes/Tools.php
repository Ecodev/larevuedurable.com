<?php

class Tools extends ToolsCore
{

    /**
     * @Deprecated, Kepts for compatibility purpose, but prefer user EcoTools
     * @param $hook
     * @param string $message
     * @return bool
     */
	public static function createHookIfNoExist( $hook, $message='' )
	{
		try{
			$request = "
			SELECT name
			FROM "._DB_PREFIX_."hook 
			WHERE "._DB_PREFIX_."hook.name LIKE '".$hook."'";
			$result = Db::getInstance()->ExecuteS($request);

			if( !$result ) {
				$request = "INSERT INTO "._DB_PREFIX_."hook (name, title) VALUES ('".$hook."', 'Net-lead : ".$message.".') ";
				$result2 = Db::getInstance()->ExecuteS($request);
			}
		}catch(Exception $e){
			return false;
		}
		return true;

	}

	public static function date_to_timestamp ($date) {
	    return preg_match('/^\s*(\d\d\d\d)-(\d\d)-(\d\d)\s*(\d\d):(\d\d):(\d\d)/', $date, $m)
	           ?  mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1])
	           : 0;
	}

	public static function arrayToString($array, $deep=0)
	{
		$string = '';
		$tab = "";
		for($i=0; $i<=$deep; $i++)
		{
			$tab.="\t";
		}
		foreach($array as $key => $arr)
		{
			if(is_array($arr))
				$string.= $tab.$key." => \n".Tools::arrayToString($arr, $deep+1);
			else
				$string.= $tab.$key.' => '.$arr.",\n";
		}
		return $string;
	}



	// public static function ps_round($value, $precision = 0)
	// {
	// 	static $method = null;
	// 	if ($method == null) $method = (int)Configuration::get('PS_PRICE_ROUND_MODE');
	//         if ($method == PS_ROUND_CHF_5CTS) return (round(20 * $value) / 20);
	// 	return parent::ps_round($value, $precision);
	// }


    /**
     * returns the rounded value of $value to specified precision, according to your configuration;
     *
     * @note : PHP 5.3.0 introduce a 3rd parameter mode in round function
     * @note : rounded value for Swiss billing (5cts)
     *
     * @param float $value
     * @param int $precision
     * @return float
     */
    public static function ps_round($value, $precision = 0)
    {
        static $method = null;

        if ($method == null)
            $method = (int)Configuration::get('PS_PRICE_ROUND_MODE');

        if ($method == PS_ROUND_UP)
            return self::quarterRound(Tools::ceilf($value, $precision));
        elseif ($method == PS_ROUND_DOWN)
            return self::quarterRound(Tools::floorf($value, $precision));
        return self::quarterRound(round($value, $precision));
    }


    private static function swissRound($amount){
        return round(20 * $amount) / 20;
    }

    private static function quarterRound($amount){
        return round(4 * $amount) / 4;
    }

    public static function dateMonths()
    {
        $tab = array();
        for ($i = 1; $i != 13; $i++)
            $tab[$i] = strftime( '%B', mktime( 0, 0, 0, $i, 1 ) );
        return $tab;
    }

    public static function notifyCustomerChanged($id_customer)
    {
        if ($id_customer) {
            $customer = new Customer($id_customer);
            $sql = "select GROUP_CONCAT(DISTINCT id_order) AS ids, GROUP_CONCAT(DISTINCT reference) AS refs from ps_orders where id_customer = ".$id_customer." group by id_customer;";
            $orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

			if (count($orders) > 0) {
	            $subject = "Changement d'adresse : " . $customer->id . ', '. $customer->firstname . ' ' . $customer->lastname . ', ' . $customer->email;
	            $vars = array(
	                '{id}' => $customer->id,
	                '{firstname}' => $customer->firstname,
	                '{lastname}' => $customer->lastname,
	                '{email}' => $customer->email,
	                '{order_ids}' => $orders[0]['ids'],
	                '{order_refs}' => $orders[0]['refs'],
	            );

	            Mail::send(Context::getContext()->language->id, 'customer_notification', $subject, $vars, explode(',', _CUSTOMER_CHANGE_NOTIFICATION_), null, null, null, null, null);
	       	}
        }
    }


    public static function addIsGiftProperty($products)
    {
        $productsIds = array_map(function ($p) {
            return $p['id_product'];
        }, $products);

        if (count($productsIds)) {

            $sql = 'SELECT active FROM ps_module WHERE name like "belvg_giftcert"';
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

            if ($result && $result['active']) {

                $sql = 'SELECT id_product FROM ' . belvg_giftcert::getTableName() . '_product WHERE `id_product` IN (' . implode(',', $productsIds) . ') AND `id_shop` = 1';
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

                $gifts = [];
                foreach ($result as $gift) {
                    $gifts[$gift['id_product']] = true;
                }

                foreach ($products as $key => $product) {
                    if (isset($gifts[$product['id_product']])) {
                        $products[$key]['is_gift'] = true;
                    } else {
                        $products[$key]['is_gift'] = false;
                    }
                }
            } else {
                foreach($products as $key => $p) {
                    $products[$key]['is_gift'] = false;
                }
            }
        }

        return $products;
    }
}
