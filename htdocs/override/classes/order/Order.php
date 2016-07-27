<?php

class Order extends OrderCore
{

    /**
     * Used recover data to allow getFileController to work fine in order to allow user to download virtual products directly from product detail page.
     * @param $productIds
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getByProductIds($productIds)
    {

        if (!Context::getContext()->customer->id) {
            return [];
        }

        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $sql = "SELECT o.id_order, o.secure_key, od.download_hash ";
        $sql .= "FROM " . _DB_PREFIX_ . "order_detail od " ;
        $sql .= "JOIN " . _DB_PREFIX_ . "orders o ON o.id_order = od.id_order " ;
        $sql .= "WHERE product_id IN (" . implode(',', $productIds) . ") ";
        $sql .= "AND o.valid = 1 ";
        $sql .= "AND o.id_customer = " . Context::getContext()->customer->id;

        return DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }



































































































































































}
