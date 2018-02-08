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

if (!defined('_PS_VERSION_')) {
    exit;
}
if (version_compare(_PS_VERSION_, '1.7.0', '>=') && class_exists('Contactform')){
    class ContactformOverride extends Contactform
    {
        public function __construct()
        {
            parent::__construct();
            if((int)Configuration::get('PA_CAPTCHA_CONTACT') && Module::isEnabled('ets_advancedcaptcha'))
                $this->name = 'ets_advancedcaptcha';
        }
        public function renderWidget($hookName = null, array $configuration = array())
        {
            if (!(int)Configuration::get('PA_CAPTCHA_CONTACT') || !Module::isEnabled('ets_advancedcaptcha')){
                return parent::renderWidget();
            }
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            $rand = md5(rand());
            $page = Tools::strtolower(trim(Tools::getValue('controller')));
            $this->smarty->assign(array(
                'captcha_image' => $this->context->link->getModuleLink('ets_advancedcaptcha', 'captcha', array('page' => $page, 'rand' => $rand)),
                'rand' => $rand,
                'modules_dir'=>_MODULE_DIR_,
                'PA_CAPTCHA_CONTACT' => (int)Configuration::get('PA_CAPTCHA_CONTACT'),
                'PA_CAPTCHA_TYPE' => Configuration::get('PA_CAPTCHA_TYPE'),
                'PA_GOOGLE_CAPTCHA_SITE_KEY'=>Configuration::get('PA_GOOGLE_CAPTCHA_SITE_KEY'),
                'PA_GOOGLE_CAPTCHA_SECRET_KEY'=>Configuration::get('PA_GOOGLE_CAPTCHA_SECRET_KEY')
            ));
            return $this->display(_MODULE_DIR_.'ets_advancedcaptcha/ets_advancedcaptcha.php', 'contactform_v_1_7.tpl');
        }
        public function sendMessage()
        {
            if (!(int)Configuration::get('PA_CAPTCHA_CONTACT') || !Module::isEnabled('ets_advancedcaptcha')){
                return parent::sendMessage();
            }
            $captcha_type = Configuration::get('PA_CAPTCHA_TYPE');
            if ($captcha_type !== 'google'){
                $security = Tools::strtolower(trim(Tools::getValue('controller'))) == 'contact' ? $this->context->cookie->security_capcha_code_contact : '';
                if (!$security || Tools::strtolower(trim(Tools::getValue('pa_captcha'))) != Tools::strtolower($security))
                    $this->context->controller->errors[] = $this->l('Security code does not match.');
            }else{
                if (Tools::getIsset('g-recaptcha-response')){
                    $recaptcha = Tools::getValue('g-recaptcha-response')?Tools::getValue('g-recaptcha-response'):'';
                    if ($recaptcha){
                        $secret = Configuration::get('PA_GOOGLE_CAPTCHA_SECRET_KEY');
                        $response = json_decode(Tools::file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$recaptcha."&remoteip=".Tools::getRemoteAddr()), true);
                        if ($response['success'] == false)
                          $this->context->controller->errors[] = $this->l('Security check failed');
                    }else
                        $this->context->controller->errors[] = $this->l('Security check failed');
                }else
                    $this->context->controller->errors[] = $this->l('Security check failed');
            }
            parent::sendMessage();           
        }
    }
}