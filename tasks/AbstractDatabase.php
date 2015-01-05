<?php

require_once __DIR__ . '/Utility.php';

/**
 * Tool to reload the entire local database from remote database for a given site
 * require_oncements:
 * - ssh access to remote server (via ~/.ssh/config)
 * - both local and remote sites must be accesible via: /sites/MY_SITE
 * - both local and remote config/autoload/local.php files must contains the database connection info
 * - both local and remote database must be configured with ~/.pgpass file for prompt-free access
 */
abstract class AbstractDatabase extends Utility
{

    /**
     * Dump data from database on $remote server
     * @param string $remote
     * @param string $dumpFile path
     */
    private static function dumpDataRemotely($remote, $dumpFile)
    {
        $sshCmd = <<<STRING
        ssh $remote "cd /sites/$remote/ && php tasks/index.php CreateDump $dumpFile"
STRING;

        echo "dumping data $dumpFile on $remote...\n";
        self::executeLocalCommand($sshCmd);
    }

    /**
     * Dump data from database
     * @param string $siteLocal
     * @param string $dumpFile path
     */
    public static function dumpData($dumpFile)
    {
        $username = _DB_USER_;
        $database = _DB_NAME_;
        $host = _DB_SERVER_;
        $pass = _DB_PASSWD_;

        echo "dumping $dumpFile...\n";
        $dumpCmd = "mysqldump --host $host --user $username  $database ";
        if ($pass) {
            $dumpCmd .= "--password=" . $pass . " ";
        }
        $dumpCmd .= "| gzip > \"$dumpFile\"";
        self::executeLocalCommand($dumpCmd);
    }

    /**
     * Copy a file from $remote
     * @param string $remote
     * @param string $dumpFile
     */
    private static function copyFile($remote, $dumpFile)
    {
        $copyCmd = <<<STRING
        scp $remote:$dumpFile $dumpFile
STRING;

        echo "copying dump to $dumpFile ...\n";
        self::executeLocalCommand($copyCmd);
    }

    /**
     * Load SQL dump in local database
     * @param string $siteLocal
     * @param string $dumpFile
     */
    public static function loadDump($dumpFile)
    {
        $username = _DB_USER_;
        $database = _DB_NAME_;
        $host = _DB_SERVER_;

        echo "loading dump $dumpFile...\n";
        if (!is_readable($dumpFile)) {
            throw new \Exception("Cannot read dump file \"$dumpFile\"");
        }

        self::executeLocalCommand("gunzip -c \"$dumpFile\" | mysql --host $host --user $username $database");
    }

    public static function loadRemoteDump($remote)
    {
        $siteLocal = trim(`git rev-parse --show-toplevel`);

        $dumpFile = "/tmp/$remote." . exec("whoami") . ".backup.gz";
        self::dumpDataRemotely($remote, $dumpFile);
        self::copyFile($remote, $dumpFile);
        self::loadDump($siteLocal, $dumpFile);

        echo "database loaded\n";
    }

}