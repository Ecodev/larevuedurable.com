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

if (class_exists('CustomerFormCore') && version_compare(_PS_VERSION_, '1.7.0', '>=')){
    class CustomerForm extends CustomerFormCore
    {
        public function validate()
        {
            if (Tools::isSubmit('submitCreate') && (int)Configuration::get('PA_CAPTCHA_REGISTRATION') && Module::isEnabled('ets_advancedcaptcha'))
            {
                $captchaField = $this->getField('captcha');
                if(Configuration::get('PA_CAPTCHA_TYPE') == 'google'){
                    if(Tools::getIsset('g-recaptcha-response')){
                        $recaptcha = Tools::getValue('g-recaptcha-response')?Tools::getValue('g-recaptcha-response'):'';
                        if($recaptcha){
                            $secret = Configuration::get('PA_GOOGLE_CAPTCHA_SECRET_KEY');
                            $response = json_decode(Tools::file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$recaptcha."&remoteip=".Tools::getRemoteAddr()), true);
                            if($response['success'] == false)
                              $captchaField->addError($this->translator->trans('reCaptcha is invalid.', array(), 'Module.Pa_captcha.Error'));
                        }else
                            $captchaField->addError($this->translator->trans('reCaptcha is invalid.', array(), 'Module.Pa_captcha.Error'));
                    }else
                        $captchaField->addError($this->translator->trans('reCaptcha is invalid.', array(), 'Module.Pa_captcha.Error'));
                }else{
                    $security = Context::getContext()->cookie->security_capcha_code_registration;
                    if (!$security || Tools::strtolower(trim(Tools::getValue('pa_captcha'))) != Tools::strtolower($security))
                        $captchaField->addError($this->translator->trans('Security code doesn\'t match.', array(), 'Module.Pa_captcha.Error'));
                }
            }
            return parent::validate();
        }
    }
}