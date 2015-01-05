<?php

require_once __DIR__ . '/AbstractDatabase.php';

class LoadRemoteDump extends AbstractDatabase
{

    protected $remote;

    public function __construct($params)
    {
        if (isset($params[0]) && !empty($params[0])) {
            $this->remote = $params[0];
        }
    }

    public function main()
    {

        if (!$this->remote) {
            echo "Remote host (eg: remote.hostname.com) : ";
            $this->remote = rtrim(fgets(STDIN));
        }

        if (!empty($this->remote)) {

            self::loadRemoteDump($this->remote);
            self::updateDataBase();

            echo "updating files ... \n";
            self::executeLocalCommand("./bin/download.sh");

            echo "Deleting cache files ... \n";
            self::executeLocalCommand("./bin/empty-cache.sh");

        } else {
            throw new InvalidArgumentException('Host not specified');
        }

    }

    protected static function updateDataBase()
    {
        $domain = _HTTP_HOST_;
        $basedir = _REQUEST_URI_;
        $username = _DB_USER_;
        $database = _DB_NAME_;
        $host = _DB_SERVER_;

        $sql = <<<STRING
        UPDATE ps_configuration SET value = "$domain" WHERE ps_configuration.name IN ("PS_SHOP_DOMAIN", "PS_SHOP_DOMAIN_SSL");
        UPDATE ps_shop_url SET domain = "$domain", domain_ssl = "$domain", physical_uri = "$basedir";
STRING;

        echo "database updated\n";
        self::executeLocalCommand("mysql --host $host --user $username $database -e '$sql'");
    }

}