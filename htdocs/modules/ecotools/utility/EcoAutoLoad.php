<?php

class EcoAutoLoad
{
    protected static $instance = null;
    protected static $vendorDir = 'vendor';
    protected $modules = [];
    protected $paths = [
        'utility',
        'models',
        'classes',
        'controllers/admin',
        'controllers/front',
        'controllers/local'
    ];

    public static function getInstance($path = null)
    {
        if (self::$instance === null) {
            self::$instance = new EcoAutoLoad();
        }

        if ($path) {
            self::$instance->addPath($path);
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->addModule();
    }

    public function includeComposerAutoload($path)
    {
        $composer = $path . '/' . self::$vendorDir . '/autoload.php';
        if (file_exists($composer)) {
            require_once($composer);
        }
    }

    public function addModule($module = null)
    {

        if ($module == null) {
            $module = dirname(__DIR__);
        }

        if (!in_array($module, $this->modules)) {
            $this->modules[] = $module;
            $this->includeComposerAutoload($module);
        }

    }

    public function addPath($path = null)
    {
        if ($path == null) {
            $path = dirname(__DIR__);
        }

        if (!in_array($path, $this->paths)) {
            $this->paths[] = $path;
        }
    }

    /**
     * Load function, but care, if two classes in different folder are loaded, it will throw a "cannot redeclare class" exception
     * @param $classname
     */
    public function load($classname)
    {
        foreach ($this->modules as $module) {
            foreach ($this->paths as $path) {
                if (file_exists($module . '/' . $path . '/' . $classname . '.php')) {
                    require_once($module . '/' . $path . '/' . $classname . '.php');
                    break;
                }
            }
        }
    }
}

spl_autoload_register(array(EcoAutoLoad::getInstance(), 'load'));

