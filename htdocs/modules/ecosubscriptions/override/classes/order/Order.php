<?php

class Order extends OrderCore
{

    public $ignore_sub = false;

    public function __construct($id = null)
    {
        $this->ignore_sub = false;
        self::$definition['fields']['ignore_sub'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');

        parent::__construct($id);
    }

}
