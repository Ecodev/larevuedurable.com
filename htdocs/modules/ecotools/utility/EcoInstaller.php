<?php

class EcoInstaller
{
    private $newClassesDir = 'data/newClasses';
    private $sqlSchemaPath = 'data/sql/schema';
    private $sqlUpdatePath = 'data/sql/update_';
    private $confXmlPath = 'ecoconfig.xml';
    private $adminCssOverride = '/../../../administrator/themes/default/css/overrides.css';

    /**
     * @var SimpleXMLElement
     */
    protected $xml;

    /**
     * @var ModuleCore
     */
    protected $mI;

    public $errors = [];

    public function __construct(ModuleCore $moduleInstance)
    {
        $this->xml = simplexml_load_file(_PS_MODULE_DIR_ . $moduleInstance->name . '/' . $this->confXmlPath);
        $this->mI = $moduleInstance;
    }

    public function install()
    {
        // Install composer dependencies
        if ($this->installComposer() !== true) {
            $this->errors[] = "EcoInstaller could not install composer dependencies";

            return false;
        }

        // Install custom classes
        if ($this->installCustomClasses() !== true) {
            $this->errors[] = "EcoInstaller could not install custom classes";

            return false;
        }

        // Add hooks values
        if ($this->installHooks() !== true) {
            $this->errors[] = "EcoInstaller could not install Hooks";

            return false;
        }

        // Add SQL schema
        if ($this->installDatabase() !== true) {
            $this->errors[] = "EcoInstaller could not install database";

            return false;
        }

        // Add SQL schema
        if ($this->installSql() !== true) {
            $this->errors[] = "EcoInstaller could not install the SQL";

            return false;
        }

        // Add configuration values
        if ($this->installConfiguration() !== true) {
            $this->errors[] = "EcoInstaller could not install the configuration options";

            return false;
        }
        // Add admin tab
        if ($this->installTab() !== true) {
            $this->errors[] = "EcoInstaller could not install tabs";

            return false;
        }

        // Add metas infos
        if ($this->installMetas() !== true) {
            $this->errors[] = "EcoInstaller could not install metas";

            return false;
        }

        // Add quick access
        if ($this->installQuickAccess() !== true) {
            $this->errors[] = "EcoInstaller could not install quick accesses";

            return false;
        }

        // Add required folders
        if ($this->installFolders() !== true) {
            $this->errors[] = "EcoInstaller could not install folders";

            return false;
        }

        // Add files
        if ($this->installFiles() !== true) {
            $this->errors[] = "EcoInstallercould not add files";

            return false;
        }

        return true;
    }

    public function uninstall()
    {

        // Delete configuration values
        if ($this->uninstallConfiguration() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall configuration options";

            return false;
        }

        // Delete admin tab
        if ($this->uninstallTab() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall tabs";

            return false;
        }

        // Delete metas infos
        if ($this->uninstallMetas() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall metas";

            return false;
        }

        // Delete quick access
        if ($this->uninstallQuickAccess() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall quick accesses";

            return false;
        }

        // Delete files
        if ($this->uninstallFiles() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall files";

            return false;
        }

        // Delete folders
        if ($this->uninstallFolders() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall folders";

            return false;
        }

        // Delete folders
        if ($this->uninstallDatabase() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall database";

            return false;
        }

        // Install custom classes
        if ($this->uninstallCustomClasses() !== true) {
            $this->errors[] = "EcoInstaller could not uninstall custom classes";

            return false;
        }

        return true;
    }

    protected function installCustomClasses()
    {
        $error = false;
        $phpFiles = $this->getNewClassesFiles();

        // nothing to install, everything is ok, return confirmation
        if (empty($phpFiles)) {
            return true;
        }

        foreach ($phpFiles as $file) {

            $filename = basename($file);
            $destDir = sprintf('%s/../../../classes/', dirname(__FILE__));
            $destFile = $destDir . $filename;

            // test if not overriding anything
            if (file_exists($destFile)) {
                $this->errors[] = "EcoInstaller could not copy new class $filename because file already existing in destination";
                $error = true;
            } else {
                // copy
                if (!copy($file, $destFile)) {
                    $this->errors[] = "EcoInstaller could not copy new class $filename";
                    $error = true;
                }
            }
        }

        return !$error;
    }

    protected function uninstallCustomClasses()
    {
        $error = false;

        $phpFiles = $this->getNewClassesFiles();

        // nothing to install, everything is ok, return confirmation
        if (empty($phpFiles)) {
            return true;
        }

        foreach ($phpFiles as $file) {

            $filename = basename($file);
            $destDir = sprintf('%s/../../../classes/', dirname(__FILE__));
            $destFile = $destDir . $filename;

            // unlink if file exist
            if (file_exists($destFile) && !unlink($destFile)) {
                $this->errors[] = "EcoInstaller could not remove class $filename";
                $error = true; // don't return to avoid interruption on uninstall
            }
        }

        return !$error;
    }

    protected function getNewClassesFiles()
    {
        $newClassesPath = sprintf('%s/../../%s/%s', dirname(__FILE__), $this->mI->name, $this->newClassesDir);

        // if
        if (!is_dir($newClassesPath)) {
            return [];
        }

        $newClasses = scandir($newClassesPath);

        if (!$newClasses) {
            $this->errors[] = "EcoInstaller could read new class folder : $newClassesPath";

            return false;
        }

        $phpFiles = [];
        foreach ($newClasses as $file) {
            $path = $newClassesPath . '/' . $file;
            if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $phpFiles[] = $path;
            }
        }

        return $phpFiles;
    }

    protected function installComposer()
    {
        $install = $this->xml->composer;
        $modulePath = sprintf('%s/../../%s', dirname(__FILE__), $this->mI->name);

        // if need to install
        if ($install == '1' || $install == 'true') {

            // if we have information to install
            if (file_exists($modulePath . '/composer.lock')) {
                $installCmd = sprintf('cd %s; composer install', $modulePath);
                exec($installCmd, $output, $return);

                if ($return === 0) {
                    return true;
                }
            }

            return false;
        } else {
            return true;
        }
    }

    protected function installHooks()
    {
        // Register each hooks found in XML configuration
        foreach ($this->xml->hooks as $hooks) {
            foreach ($hooks as $hook) {

                if (!$this->mI->registerHook((string) $hook) && !(int) $hook['optionnal']) {
                    $this->errors[] = "EcoInstaller could not register hook : " . $hook;

                    return false;
                }

                // Position in first
                if ((int) $hook['first']) {
                    $id_hook = Hook::getIdByName((string) $hook);

                    $sql = 'SELECT MAX(`position`) AS position
                        FROM `' . _DB_PREFIX_ . 'hook_module`
                        WHERE `id_hook` = ' . (int) $id_hook;
                    if (!$position = Db::getInstance()->getValue($sql)) {
                        break;
                    }
                    for ($i = $position; $i > 1; $i--) {
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'hook_module`
                            SET `position` = ' . $i . '
                            WHERE `position` = ' . ($i - 1) . '
                            AND `id_module` != ' . $this->mI->id . '
                            AND `id_hook` = ' . (int) $id_hook;
                        Db::getInstance()->execute($sql);
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'hook_module`
                            SET `position` = ' . ($i - 1) . '
                            WHERE `id_module` = ' . $this->mI->id . '
                            AND `id_hook` = ' . (int) $id_hook;
                        if (!Db::getInstance()->execute($sql)) {
                            $this->errors[] = "EcoInstaller could not set hook position";

                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    protected function installDatabase()
    {
        $fullSuccess = true;
        foreach ($this->xml->database->children() as $element) {
            $success = true;
            if ($element->getName() == "table") {
                $fullSuccess = $this->addTable($element);
            } elseif ($element->getName() == "column") {
                $fullSuccess = $this->addColumn($element);
            }

            if (!$success) {
                $fullSuccess = false;
            }
        }

        return $fullSuccess;
    }

    protected function addTable($name)
    {
        return DB::getInstance()->execute("CREATE TABLE IF NOT EXISTS $name (id int(10))");
    }

    protected function addColumn($column)
    {
        $dbName = _DB_NAME_;
        $tableName = $column->table;
        $columnName = $column->name;
        $type = $column->type;

        $sqlTest = "SELECT COUNT(*) as number
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = '$dbName'
                        AND TABLE_NAME = '" . _DB_PREFIX_ . "$tableName'
                        AND COLUMN_NAME = '$columnName'";

        $sqlAdd = "ALTER TABLE " . _DB_PREFIX_ . "$tableName ADD COLUMN $columnName $type;";

        try {
            $result = DB::getInstance()->getRow($sqlTest);

            if ((int) $result['number'] === 0) {
                return DB::getInstance()->execute($sqlAdd);
            }

            return true;

        } catch (PrestaShopDatabaseException $e) {
            return false;
        }

        return true;
    }

    protected function uninstallDatabase()
    {
        $fullSuccess = true;
        foreach ($this->xml->database->children() as $element) {
            $success = true;
            if ($element->getName() == "table") {
                $fullSuccess = $this->removeTable($element);
            } elseif ($element->getName() == "column") {
                $fullSuccess = $this->removeColumn($element);
            }

            if (!$success) {
                $fullSuccess = false;
            }
        }

        return $fullSuccess;
    }

    protected function removeTable($element)
    {
        $remove = isset($element['uninstall']) ? (int) $element['uninstall'] : true;

        if ($remove) {
            return DB::getInstance()->execute("DROP TABLE IF EXISTS $element");
        }

        return true;
    }

    protected function removeColumn($element)
    {
        $remove = isset($element['uninstall']) ? (int) $element['uninstall'] : true;

        if ($remove) {
            $tableName = $element->table;
            $columnName = $element->name;

            $sqlAdd = "ALTER TABLE " . _DB_PREFIX_ . "$tableName DROP COLUMN IF EXISTS $columnName;";

            return DB::getInstance()->execute($sqlAdd);
        }

        return true;
    }

    protected function installSql()
    {
        // Detect previous version
        $previousVersion = strval(Configuration::get($this->mI->prefixConfiguration . 'VERSION'));

        // SQL creation
        if ($previousVersion && $previousVersion != $this->mI->version) {
            // Previous version detected, run migrations
            if ($this->installSqlUpdate($previousVersion) !== true) {
                return false;
            }
        } elseif (!$previousVersion) {
            // First install, create SQL schema
            if ($this->installSqlCreate() !== true) {
                return false;
            }

            // SQL datas: insert value for each insert found in XML configuration
            foreach ($this->xml->sql->insert as $sql) {
                // Replace DB prefix
                $query = str_replace('_DB_PREFIX_', _DB_PREFIX_ . strtolower($this->mI->prefixConfiguration), (string) $sql);
                // Execute SQL request
                if (!Db::getInstance()->Execute($query)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function installSqlCreate()
    {
        $fileName = dirname(__FILE__) . '/../../' . $this->mI->name . '/' . $this->sqlSchemaPath;

        if (!$this->installSqlFile( $fileName . '.sql')) {
            return false;
        }

        return true;
    }

    protected function installSqlUpdate($previousVersion)
    {
        $versionA = explode('.', $previousVersion);
        $versionB = explode('.', $this->mI->version);
        $versionA[0] = intval($versionA[0]);
        $versionA[1] = intval($versionA[1]);
        $versionB[0] = intval($versionB[0]);
        $versionB[1] = intval($versionB[1]);

        for ($i = $versionA[0]; $i <= $versionB[0]; $i++) {
            if ($i > $versionA[0]) {
                $startJ = 0;
                $fileName = $this->sqlUpdatePath . ($i - 1) . '.' . $j . '_' . $i . '.' . $startJ . '.sql';
                $fileName = dirname(__FILE__) . '/../../' . $this->mI->name . '/' . $fileName;

                if (file_exists($fileName)) {
                    if ($this->installSqlFile($fileName) !== true) {
                        return false;
                    }
                }
            } else {
                $startJ = $versionA[1];
            }

            $endJ = ($i == $versionB[0] ? $versionB[1] : 9);
            for ($j = $startJ; $j < $endJ; $j++) {
                $fileName = $this->sqlUpdatePath . $i . '.' . $j . '_' . $i . '.' . ($j + 1) . '.sql';
                $fileName = dirname(__FILE__) . '/../../' . $this->mI->name . '/' . $fileName;

                if (file_exists($fileName)) {
                    if ($this->installSqlFile($fileName) !== true) {
                        return false;
                    }
                } else {
                    continue;
                }
            }
        }

        return true;
    }

    private function installSqlFile($fileName)
    {
        $query = '';

        // Open & read input
        if (($fdi = fopen($fileName, 'r')) === false) {
            return false;
        }
        while (($line = fgets($fdi)) !== false) {
            $query .= $line;
        }

        // Replace DB prefix
        $query = str_replace('_DB_PREFIX_', _DB_PREFIX_, $query);

        // Execute SQL request
        if (!Db::getInstance()->Execute($query)) {
            return false;
        }

        return true;
    }

    protected function installConfiguration()
    {
        // Update value for each configuration found in XML configuration
        foreach ($this->xml->confs->conf as $conf) {
            Configuration::updateValue($this->mI->prefixConfiguration . (string) $conf->name, (string) $conf->value);
        }

        // Add module version
        Configuration::updateValue($this->mI->prefixConfiguration . 'VERSION', $this->mI->version);

        return true;
    }

    protected function installTab()
    {
        $mainIdTab = 0;
        $i = 0;

        // Create tab for each tab found in XML configuration
        foreach ($this->xml->tabs->tab as $tab) {
            $i18n = array();
            foreach ($tab->langs->lang as $lang) {
                $i18n[(string) $lang['iso']] = (string) $lang;
            }
            $newTab = new Tab();
            foreach (Language::getLanguages(false /* active */) as $lang) {
                if ($iso = Language::getIsoById($lang['id_lang'])) {
                    $newTab->name[$lang['id_lang']] = array_key_exists($iso, $i18n) ? $i18n[$iso] : current($i18n);
                }
            }
            $newTab->class_name = (string) $tab->class;
            $newTab->id_parent = (int) $tab['main'] ? 0 : $mainIdTab;
            $newTab->module = $this->mI->name;
            $newTab->add();
            $this->addTabIcon($tab);

            if ((int) $tab['main']) {
                $mainIdTab = $newTab->id;
                if ((int) $tab['first']) {
                    $currentTabs = Tab::getTabs(1 /* id_lang */, $newTab->id_parent);
                    for ($i = count($currentTabs); $i; $i--) {
                        $newTab->updatePosition(0 /* way */, $i - 1 /* position */);
                    }
                }
            }
        }

        return true;
    }

    protected function installMetas()
    {
        // Create meta for meta found in XML configuration
        foreach ($this->xml->metas->meta as $meta) {
            $i18n = array();
            foreach ($meta->langs->lang as $lang) {
                $i18n[(string) $lang['iso']] = $lang;
            }
            $newMeta = new Meta();
            foreach (Language::getLanguages(false /* active */) as $lang) {
                $idLang = (int) $lang['id_lang'];
                $isoLang = Language::getIsoById($idLang);
                $key = array_key_exists($isoLang, $i18n) ? $isoLang : key($i18n);
                $newMeta->title[$idLang] = (string) $i18n[$key]->title;
                $newMeta->description[$idLang] = (string) $i18n[$key]->description;
                $newMeta->url_rewrite[$idLang] = (string) $i18n[$key]->url_rewrite;
            }
            $newMeta->page = (string) $meta->page;
            $newMeta->add();
        }

        return true;
    }

    protected function installQuickAccess()
    {
        // Create quick access for each quick access found in XML configuration
        foreach ($this->xml->quicks->quick as $quick) {
            $i18n = array();
            foreach ($quick->langs->lang as $lang) {
                $i18n[(string) $lang['iso']] = (string) $lang;
            }
            $newQuick = new QuickAccess();
            foreach (Language::getLanguages(false /* active */) as $lang) {
                if ($iso = Language::getIsoById($lang['id_lang'])) {
                    $newQuick->name[$lang['id_lang']] = array_key_exists($iso, $i18n) ? $i18n[$iso] : current($i18n);
                }
            }
            $newQuick->link = (string) $quick->link;
            $newQuick->new_window = (int) $quick['blank'];
            $newQuick->add();
        }

        return true;
    }

    protected function uninstallConfiguration()
    {
        // Delete each configuration found in XML configuration
        foreach ($this->xml->confs->conf as $conf) {
            Configuration::deleteByName($this->mI->prefixConfiguration . (string) $conf->name);
        }

        return true;
    }

    protected function uninstallTab()
    {
        // Delete tab for each tab found in XML configuration
        foreach ($this->xml->tabs->tab as $xmltab) {
            $tab = new Tab((int) Tab::getIdFromClassName((string) $xmltab->class));
            $tab->delete();
            $this->removeTabIcon($xmltab);
        }

        return true;
    }

    protected function uninstallMetas()
    {
        // Delete meta for each meta found in XML configuration
        foreach ($this->xml->metas->meta as $meta) {
            $query = '
            SELECT `id_meta`
            FROM `' . _DB_PREFIX_ . Meta::$definition['table'] . '`
            WHERE `page` = \'module-' . $this->mI->name . '-' . pSql((string) $meta->page) . '\'';
            if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query)) {
                $metas = ObjectModel::hydrateCollection('Meta', $rows);
                if ($metas && is_array($metas)) {
                    foreach ($metas as $mt) {
                        $mt->delete();
                    }
                }
            }
        }

        return true;
    }

    protected function uninstallQuickAccess()
    {
        // Delete quick access for each quick access found in XML configuration
        foreach ($this->xml->quicks->quick as $quick) {
            $query = '
            SELECT `id_quick_access`
            FROM `' . _DB_PREFIX_ . QuickAccess::$definition['table'] . '`
            WHERE `link` = \'' . pSql((string) $quick->link) . '\'';
            if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query)) {
                $quicks = ObjectModel::hydrateCollection('QuickAccess', $rows);
                if ($quicks && is_array($quicks)) {
                    foreach ($quicks as $qk) {
                        $qk->delete();
                    }
                }
            }
        }

        return true;
    }

    /**
     * Edits /administrator/theme/default/override.css to add icon
     */
    protected function addTabIcon($tab)
    {

        if ($tab->icon) {
            $file = dirname(__FILE__) . $this->adminCssOverride;
            $str = @file_get_contents($file);
            $str .= $this->getTabCssClass($tab);
            file_put_contents($file, $str);
        }
    }

    protected function removeTabIcon($tab)
    {
        if ($tab->icon) {
            $file = dirname(__FILE__) . $this->adminCssOverride;
            $str = file_get_contents($file);
            $str = str_replace($this->getTabCssClass($tab), "", $str);
            file_put_contents($file, $str);
        }
    }

    protected function getTabCssClass($tab)
    {
        $controller = $tab->class;
        $icon = $tab->icon;

        return "\n.icon-$controller:before{content:\"\\$icon\";}";
    }

    /**
     * Add required folders
     */
    protected function installFolders()
    {
        foreach ($this->xml->folders->children() as $folder) {
            $dir = PS_ROOT_DIR_ . '/' . $folder;
            @mkdir($dir, 0755, true);
        }

        return true;
    }

    // Add files updates
    protected function installFiles()
    {

        foreach ($this->xml->files->children() as $file) {
            $filepath = PS_ROOT_DIR_ . '/' . $file->attributes()['location'];

            if (!file_exists($filepath) || (!isset($file->attributes()['prepend']) || isset($file->attributes()['prepend']) && $file->attributes()['prepend'] == false)) {
                file_put_contents($filepath, $file, FILE_APPEND | LOCK_EX);
            } elseif (file_exists($filepath) && isset($file->attributes()['prepend']) && $file->attributes()['prepend'] == true) {
                $fileContent = file_get_contents($filepath);

                $phpTag = '';
                if (substr($fileContent, 0, 5) == "<?php") {
                    $phpTag = '<?php';
                }

                $fileContent = $phpTag . chr(10) . $file . str_replace("<?php", "", $fileContent);
                file_put_contents($filepath, $fileContent);
            }
        }

        return true;
    }

    // Remove required folders
    protected function uninstallFolders()
    {

        foreach ($this->xml->folders->children() as $folder) {
            $dir = PS_ROOT_DIR_ . '/' . $folder;
            if (isset($folder->attributes()['remove']) && $folder->attributes()['remove'] == true) {
                @rmdir($dir);
            }
        }

        return true;
    }

    // Remove files updates
    protected function uninstallFiles()
    {

        foreach ($this->xml->files->children() as $file) {
            $filepath = PS_ROOT_DIR_ . '/' . $file->attributes()['location'];

            if (file_exists($filepath)) {
                if (isset($file->attributes()['remove']) && $file->attributes()['remove'] == true) {
                    @unlink($filepath);
                } else {
                    $fileContent = @file_get_contents($filepath);
                    $fileContent = str_replace($file, '', $fileContent);
                    @file_put_contents($filepath, $fileContent);
                }
            }
        }

        return true;
    }

}
