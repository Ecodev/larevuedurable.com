<?php

require_once __DIR__ . '/AbstractDatabase.php';

class LoadDump extends AbstractDatabase
{
    protected $dumpFile;

    public function __construct($params)
    {
        if (isset($params[0]) && !empty($params[0])) {
            $this->dumpFile = $params[0];
        }
    }

    public function main()
    {
        $defaultFileName = "db.backup.gz";

        if (!$this->dumpFile) {
            echo "File name [" . $defaultFileName . "] : ";
            $this->dumpFile = rtrim(fgets(STDIN));

            if (empty($this->dumpFile)) {
                $this->dumpFile = "./" . $defaultFileName;
            }
        }

        self::loadDump($this->dumpFile);
    }
}
