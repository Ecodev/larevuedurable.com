<?php

require_once __DIR__ . '/AbstractDatabase.php';

class ExecuteSql extends AbstractDatabase
{
    protected $sql;

    public function __construct($params)
    {
        if (isset($params[0]) && !empty($params[0])) {
            $this->sql = $params[0];
        }
    }

    public function main()
    {
        self::executeSql($this->sql);
    }
}
