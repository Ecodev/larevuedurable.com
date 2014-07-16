<?php

class Address extends AddressCore
{
    public function save($null_values = false, $autodate = true) {
        $saved = parent::save();

        if ($saved) {
           Tools::notifyCustomerChanged($this->id_customer);
        }

        return $saved;
    }
}