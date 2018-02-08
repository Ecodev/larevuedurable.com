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

if (class_exists('CustomerFormatterCore') && version_compare(_PS_VERSION_, '1.7.0', '>=')){
    class CustomerFormatter extends CustomerFormatterCore
    {
        public function getFormat(){
            $formats = parent::getFormat();
            if(!(int)Configuration::get('PA_CAPTCHA_REGISTRATION') || !Module::isEnabled('ets_advancedcaptcha'))
                return $formats;        
            $format = array();
            $format['captcha'] = (new FormField)
                ->setName('captcha')
                ->setType('text')
                ->setRequired(true)
                ->setValue(1)
            ;
            $constraints = Customer::$definition['fields'];
            foreach ($format as $field) {
                if (!empty($constraints[$field->getName()]['validate'])) {
                    $field->addConstraint(
                        $constraints[$field->getName()]['validate']
                    );
                }
            }
            return array_merge($formats,$format);
        }
    }
}