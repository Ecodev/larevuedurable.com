<?php

class Utility {

    /**
     * Execute a shell command and throw exception if fails
     * @param string $command
     * @throws \Exception
     */
    protected static function executeLocalCommand($command)
    {
        $return_var = null;
        $fullCommand = "$command 2>&1";
        passthru($fullCommand, $return_var);
        if ($return_var) {
            throw new \Exception('FAILED executing: ' . $command);
        }
    }

    /**
     * Execute PHP code on $remote server
     * @param string $php code (without special escaping, nor '<?php')
     */
    /*
    private static function executeRemotePhp($remote, $php)
    {
        // Create temp file with PHP code
        $tempFile = '/tmp/lrd.remotephp.' . exec('whoami') . '.php';
        $php = "<?php " . $php . "\n //unlink('$tempFile');";
        file_put_contents($tempFile, $php);

        // Push temp file on remote and delete local copy
        self::executeLocalCommand("scp $tempFile $remote:$tempFile");

        // Execute remote code (who will delete itself)
        $sshCmd = <<<STRING
        ssh $remote "php $tempFile"
STRING;
        self::executeLocalCommand($sshCmd);

        // Delete file only after executing command in case we actualy are on the same machine
        unlink($tempFile);
    }
    */

}