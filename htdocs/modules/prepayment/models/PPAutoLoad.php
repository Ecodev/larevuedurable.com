<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PPAutoLoad
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new PPAutoLoad();
        return self::$instance;
    }

    public function load($classname)
    {
        $currentDir = dirname(__FILE__);
        $path = array(
            $currentDir, /* models */
            $currentDir.'/../controllers/admin/',
            $currentDir.'/../controllers/front/'
        );

        foreach ($path as $dir)
            if (file_exists($dir.'/'.$classname.'.php'))
                require_once($dir.'/'.$classname.'.php');
    }
}
