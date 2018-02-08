<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  @version  Release: $Revision$
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;
 
class Ets_advancedcaptcha extends Module
{    
    private $_hooks = array('displayPaCaptcha','displayHeader','displayCustomerAccountForm','displayBackOfficeHeader');
    public $is17;
    public function __construct()
	{
		$this->name = 'ets_advancedcaptcha';
        $this->tab = 'front_office_features';
		$this->version = '1.0.5';
		$this->author = 'ETS-Soft';
		$this->need_instance = 0;
        $this->is17 = version_compare(_PS_VERSION_, '1.7.0', '>=');
        $this->bootstrap = true;
        $this->module_key = '23c752bc1234e5c89322801b5d4a9117';
		parent::__construct();
		$this->displayName = $this->l('Advanced Captcha');
		$this->description = $this->l('Protect your store from spam messages and spam user accounts');
        if(version_compare(_PS_VERSION_, '1.5.6.0', '>') || version_compare(_PS_VERSION_, '1.5.0.0', '<'))
            $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => _PS_VERSION_);
    }

    public function overrideDir()
    {
        if (!$this->is17)
            return true;
            
         /*@override class*/   
        $dir = _PS_ROOT_DIR_.'/override/classes/form';
        if(!is_dir($dir)){
            @mkdir($dir, 0777);
        }
        if (is_dir($dir)){
            if (($dest =  $dir.'/CustomerForm.php') && !file_exists($dest)){
                $source = dirname(__FILE__).'/classes/CustomerForm.php';
                Tools::copy($source, $dest);
            }
            if (($dest =  $dir.'/CustomerFormatter.php') && !file_exists($dest)){
                $source = dirname(__FILE__).'/classes/CustomerFormatter.php';
                Tools::copy($source, $dest);
            }
        }
        
        /*@override controller*/
        if (($dir = _PS_ROOT_DIR_.'/override/controllers/front') && is_dir($dir)){
            if (($dest =  $dir.'/AuthController.php') && !file_exists($dest)){
                $source = dirname(__FILE__).'/classes/AuthController.php';
                Tools::copy($source, $dest);
            }
            if (($dest =  $dir.'/ContactController.php') && !file_exists($dest)){
                $source = dirname(__FILE__).'/classes/ContactController.php';
                Tools::copy($source, $dest);
            }
        }
        
        /*@overide module*/
        $dir = _PS_ROOT_DIR_.'/override/modules/contactform';
        if (!is_dir($dir)){
            @mkdir($dir, 0777);
        }
        if (is_dir($dir)){
            $source = dirname(__FILE__).'/override/modules/contactform/contactform.php';
            $dest =  $dir.'/contactform.php';
            Tools::copy($source, $dest);
        }
        return true;
    }
    
    public function unstallUnLink()
    {
        $res = true;
        if (!$this->is17)
            return $res;
        if (($dir = _PS_ROOT_DIR_.'/override/modules/contactform')&& is_dir($dir)) {
            if(($file = $dir.'/contactform.php') && file_exists($file))
                $res &= unlink($file);
            $res &= rmdir($dir);
        }
        return $res;
    }
    
    /**
	 * @see Module::install()
	 */
    public function install()
	{
        $res1 = $this->overrideDir();
        $res = parent::install();
        foreach($this->_hooks as $hook){
            $res1 &= (bool)$this->registerHook($hook);
        }
        Configuration::updateValue('PA_CAPTCHA_CONTACT',1); 
        Configuration::updateValue('PA_CAPTCHA_REGISTRATION',1);   
        Configuration::updateValue('PA_CAPTCHA_TYPE','colorful');
        Configuration::updateValue('PA_GOOGLE_CAPTCHA_SITE_KEY','');
        Configuration::updateValue('PA_GOOGLE_CAPTCHA_SECRET_KEY','');
        
        return $res && $res1;
    }
    /**
	 * @see Module::uninstall()
	 */
	public function uninstall()
	{
	    Configuration::deleteByName('PA_CAPTCHA_CONTACT');
        Configuration::deleteByName('PA_CAPTCHA_REGISTRATION');
        Configuration::deleteByName('PA_CAPTCHA_TYPE');
        Configuration::deleteByName('PA_GOOGLE_CAPTCHA_SITE_KEY','');
        Configuration::deleteByName('PA_GOOGLE_CAPTCHA_SECRET_KEY','');
        $res = $this->unstallUnLink();
        $res &= parent::uninstall();
        return  $res;
    }
    
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::strtolower(trim(Tools::getValue('controller'))) == 'adminmodules' && Tools::getValue('configure') == $this->name){
            if ($this->is17)
                $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/pa-admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/pa-admin.css', 'all');
        }
    }
        
    public function hookDisplayHeader()
    {
        $page = Tools::strtolower(trim(Tools::getValue('controller')));
        if (!in_array($page, array('contact','authentication','order','order-opc')) 
        || ($page == 'contact' && !(int)Configuration::get('PA_CAPTCHA_CONTACT')) 
        || ($page != 'contact' && !(int)Configuration::get('PA_CAPTCHA_REGISTRATION')))
            return;
        
        $this->context->controller->addJS($this->_path.'views/js/pa-captcha.js');
        $this->context->controller->addCSS($this->_path.'views/css/pa-captcha.css', 'all');
        $this->context->smarty->assign(array('PA_GOOGLE_CAPTCHA_SITE_KEY'=>Configuration::get('PA_GOOGLE_CAPTCHA_SITE_KEY')));
        if(Configuration::get('PA_CAPTCHA_TYPE')=='google')
            return $this->display(__FILE__, 'render-js.tpl');               
    }
        
    public function getContent()
    {
        $errors = '';
        if (Tools::isSubmit('submitUpdate'))
        {
            if(Tools::getValue('PA_CAPTCHA_TYPE') == 'google'){
                $site_key = Tools::getValue('PA_GOOGLE_CAPTCHA_SITE_KEY')?Tools::getValue('PA_GOOGLE_CAPTCHA_SITE_KEY'):'';
                $secret_key = Tools::getValue('PA_GOOGLE_CAPTCHA_SECRET_KEY')?Tools::getValue('PA_GOOGLE_CAPTCHA_SECRET_KEY'):'';
                if(!$site_key || !$secret_key){
                    $errors.= $this->l('Site key and Secret key are required fields');
                }else{
                    Configuration::updateValue('PA_GOOGLE_CAPTCHA_SITE_KEY',$site_key);
                    Configuration::updateValue('PA_GOOGLE_CAPTCHA_SECRET_KEY',$secret_key);
                }
            }
            if(!$errors)
            {
                Configuration::updateValue('PA_CAPTCHA_CONTACT', (int)Tools::getValue('PA_CAPTCHA_CONTACT') ? 1 : 0);
                Configuration::updateValue('PA_CAPTCHA_REGISTRATION', (int)Tools::getValue('PA_CAPTCHA_REGISTRATION') ? 1 : 0);
                Configuration::updateValue('PA_CAPTCHA_TYPE', Tools::strtolower(trim(Tools::getValue('PA_CAPTCHA_TYPE')))); 
            }                       
        }
        if (version_compare(_PS_VERSION_, '1.6.0', '>='))
            $postUrl = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        else
            $postUrl = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');
        $this->smarty->assign(
            array(                
                'PA_CAPTCHA_CONTACT' => $errors && Tools::isSubmit('PA_CAPTCHA_CONTACT') ? Tools::getValue('PA_CAPTCHA_CONTACT') : (int)Configuration::get('PA_CAPTCHA_CONTACT'),
                'PA_CAPTCHA_REGISTRATION' =>$errors && Tools::isSubmit('PA_CAPTCHA_REGISTRATION') ? Tools::getValue('PA_CAPTCHA_REGISTRATION') : (int)Configuration::get('PA_CAPTCHA_REGISTRATION'),
                'PA_CAPTCHA_TYPE' =>$errors && Tools::isSubmit('PA_CAPTCHA_TYPE') ? Tools::getValue('PA_CAPTCHA_TYPE') : Configuration::get('PA_CAPTCHA_TYPE'),
                'PA_GOOGLE_CAPTCHA_SITE_KEY'=>$errors && Tools::isSubmit('PA_GOOGLE_CAPTCHA_SITE_KEY') ? Tools::getValue('PA_GOOGLE_CAPTCHA_SITE_KEY') : Configuration::get('PA_GOOGLE_CAPTCHA_SITE_KEY'),
                'PA_GOOGLE_CAPTCHA_SECRET_KEY'=>$errors && Tools::isSubmit('PA_GOOGLE_CAPTCHA_SECRET_KEY') ? Tools::getValue('PA_GOOGLE_CAPTCHA_SECRET_KEY') : Configuration::get('PA_GOOGLE_CAPTCHA_SECRET_KEY'),
                'setting_updated' => Tools::isSubmit('submitUpdate') ? true : false,
                'captchaTypes' => array(
                    array(
                        'type' => 'colorful',
                        'name' => $this->l('Colorful'),
                    ),
                    array(
                        'type' => 'basic',
                        'name' => $this->l('Basic'),
                    ),                    
                    array(
                        'type' => 'complex',
                        'name' => $this->l('Complex'),
                    ),
                    array(
                        'type' => 'google',
                        'name' => $this->l('Google'),
                    ),
                ),
                'pa_img_dir' => $this->_path.'views/img/',
                'postUrl' => $postUrl,
                'ps_version_1_7' => $this->is17,
                'ps_version_1_6' => version_compare(_PS_VERSION_, '1.6.0', '>='),
                'errors'=>$errors
            )
        );        
        return $this->display(__FILE__, 'admin-config.tpl');
    }
    
    public function hookDisplayPaCaptcha()
    {
        $rand = md5(rand());
        $page = Tools::strtolower(trim(Tools::getValue('controller')));
        if (!in_array($page, array('contact','authentication','order','order-opc'))){
            return; 
        }elseif($page == 'contact' && (int)Configuration::get('PA_CAPTCHA_CONTACT')){
            return $this->CaptchaPro($page, $rand);
        }
        elseif($page != 'contact' && (int)Configuration::get('PA_CAPTCHA_REGISTRATION')){
            return $this->CaptchaPro($page, $rand);
        }
    }
    
    public function CaptchaPro($page, $rand)
    {
        $this->smarty->assign(array(
            'captcha_page' => $page,
            'captcha_image' => $this->context->link->getModuleLink('ets_advancedcaptcha', 'captcha', array('page' => $page, 'rand' => $rand)),
            'rand' => $rand,
            'modules_dir'=>_MODULE_DIR_,
            'ps_version_1_7' => $this->is17,
            'ps_version_1_6' => version_compare(_PS_VERSION_, '1.6.0', '>='),
            'PA_CAPTCHA_TYPE' => Configuration::get('PA_CAPTCHA_TYPE'),
            'PA_GOOGLE_CAPTCHA_SITE_KEY'=>Configuration::get('PA_GOOGLE_CAPTCHA_SITE_KEY'),
            'PA_GOOGLE_CAPTCHA_SECRET_KEY'=>Configuration::get('PA_GOOGLE_CAPTCHA_SECRET_KEY')
            
        ));        
        return $this->display(__FILE__, 'front-captcha.tpl');
    }
    
    public function hookDisplayCustomerAccountForm($params)
    { 
        return $this->hookDisplayPaCaptcha();
    }
}