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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version  Release: $Revision$
 *  International Registered Trademark & Property of PrestaShop SA
 */

class ContactController extends ContactControllerCore
{
    public function postProcess()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<') && Module::isEnabled('ets_advancedcaptcha')) {
            if (Tools::isSubmit('submitMessage')) {
                if (!(int)Configuration::get('PA_CAPTCHA_CONTACT')) {
                    return parent::postProcess();
                }
                if (Configuration::get('PA_CAPTCHA_TYPE') == 'google') {
                    if (Tools::getIsset('g-recaptcha-response')) {
                        $recaptcha = Tools::getValue('g-recaptcha-response') ? Tools::getValue('g-recaptcha-response') : '';
                        if ($recaptcha) {
                            $secret = Configuration::get('PA_GOOGLE_CAPTCHA_SECRET_KEY');
                            $response = json_decode(Tools::file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $recaptcha . "&remoteip=" . Tools::getRemoteAddr()), true);
                            if ($response['success'] == false) {
                                $this->errors[] = Tools::displayError('reCaptcha is invalid.');
                            }
                        } else {
                            $this->errors[] = Tools::displayError('reCaptcha is invalid.');
                        }
                    } else {
                        $this->errors[] = Tools::displayError('reCaptcha error');
                    }
                } else {
                    $security = Tools::strtolower(trim(Tools::getValue('controller'))) == 'contact' && isset($this->context->cookie->security_capcha_code_contact) && $this->context->cookie->security_capcha_code_contact ? Tools::strtolower($this->context->cookie->security_capcha_code_contact) : false;
                    $pa_captcha = Tools::getIsset('pa_captcha') && Tools::getValue('pa_captcha') ? Tools::strtolower(trim(Tools::getValue('pa_captcha'))) : false;
                    if (!$security || ($security != $pa_captcha)) {
                        $this->errors[] = Tools::displayError('Security code does not match');
                    }
                }
                if (!count($this->errors))
                    parent::postProcess();
            }
        }
    }

    public function initContent()
    {
        parent::initContent();
        if (version_compare(_PS_VERSION_, '1.7.0', '<') && Module::isInstalled('ets_advancedcaptcha')) {
            if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
                if (file_exists(_PS_THEME_DIR_ . 'modules/ets_advancedcaptcha/views/templates/front/front-contact-form.tpl'))
                    $view = _PS_THEME_DIR_ . 'modules/ets_advancedcaptcha/views/templates/front/front-contact-form.tpl';
                else
                    $view = _PS_MODULE_DIR_ . 'ets_advancedcaptcha/views/templates/front/front-contact-form.tpl';
            } else {
                if (file_exists(_PS_THEME_DIR_ . 'modules/ets_advancedcaptcha/views/templates/front/front-contact-form-v_1_5.tpl'))
                    $view = _PS_THEME_DIR_ . 'modules/ets_advancedcaptcha/views/templates/front/front-contact-form-v_1_5.tpl';
                else
                    $view = _PS_MODULE_DIR_ . 'ets_advancedcaptcha/views/templates/front/front-contact-form-v_1_5.tpl';
            }

            $this->setTemplate($view);
        }
    }
}
