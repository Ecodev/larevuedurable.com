<?php

// Security
if (!defined('_PS_VERSION_'))
{
    exit;
}

// Include auto-loader
include_once(dirname(__FILE__) . '/utility/EcoAutoLoad.php');

class EcoTools extends Module
{

    public function __construct()
    {
        $this->name = 'ecotools';
        $this->version = '1.5.4';
        $this->module_key = '558c106df8f63e19b875defc695733c3';
        $this->ps_versions_compliancy['min'] = '1.5.4';
        $this->author = "Samuel Baptista";
        $this->year = 2015;
        $this->bootstrap = true;

        parent::__construct();

        $this->prefixConfiguration = 'ECO_';
        $this->displayName = 'EcoTools';
        $this->description = $this->l("Installe des outils destinés au développement");
        $this->confirmUninstall = $this->l('La désinstallation va supprimer certains fichiers et répertoires. Êtes-vous sur de vouloir continuer ?');
    }

    public function hookActionEmailAddAfterContent($params)
    {
        if (defined('_OVERRIDE_EMAIL_') && _OVERRIDE_EMAIL_ !== '')
        {
            $params['template_html'] = EcoUtility::replaceMailColor($params['template_html']);
        }
    }

    /**
     * Install module : create tabs, add hooks and update database
     * @return bool
     */
    public function install()
    {
        if (!parent::install())
        {
            $this->_errors[] = "Prestashop n'a pas pu installer le module correctement";

            return false;
        }

        $ecoInstaller = new EcoInstaller($this);
        if (!$ecoInstaller->install())
        {
            $this->_errors[] = "EcoInstaller n'a pas pu installer le module correctement";

            return false;
        }

        return true;
    }

    /**
     * Uninstall module : undo everything that is made on install
     * @return bool
     */
    public function uninstall()
    {
        $ecoInstaller = new EcoInstaller($this);
        if (!$ecoInstaller->uninstall())
        {
            $this->_errors[] = "EcoInstaller n'a pas pu désinstaller le module correctement";

            return false;
        }

        if (!parent::uninstall())
        {
            $this->_errors[] = "Prestashop n'a pas pu désinstaller le module correctement";

            return false;
        }

        return true;
    }
}
