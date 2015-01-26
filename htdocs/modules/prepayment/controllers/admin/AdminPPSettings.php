<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class AdminPPSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->className = 'Configuration';
        $this->table = 'configuration';

        parent::__construct();

        // Display block
        $displayBlock = array(
            array('value' => 1, 'name' => $this->l('Not displayed')),
            array('value' => 2, 'name' => $this->l('Left column')),
            array('value' => 3,'name' => $this->l('Right column'))
        );

        // Options list
        $this->fields_options = array(
            'general' => array(
                'title' => $this->l('General settings'),
                'image' => _MODULE_DIR_.'prepayment/medias/img/settings.png',
                'fields' => array(
                    'PP_NEGATIVE_ENABLED' => array(
                        'title' => $this->l('Negative balance'),
                        'desc' => $this->l('Allow customer to have a negative balance on their prepaid account.'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'PP_NEGATIVE_MAX' => array(
                        'title' => $this->l('Maximum negative balance'),
                        'desc' => $this->l('The maximum negative balance authorized.'),
                        'validation' => 'isUnsignedFloat',
                        'cast' => 'floatval',
                        'type' => 'price'
                    ),
                ),
                'submit' => array()
            ),
            'block' => array(
                'title' => $this->l('Videos block'),
                'image' => _MODULE_DIR_.'videosmanager/medias/img/block.png',
                'fields' => array(
                    'PP_BLOCK_DISPLAY' => array(
                        'title' => $this->l('Block position'),
                        'desc' => $this->l('Videos block position.'),
                        'validation' => 'isInt',
                        'cast' => 'intval',
                        'type' => 'select',
                        'list' => $displayBlock,
                        'identifier' => 'value',
                        'visibility' => Shop::CONTEXT_ALL
                    ),
                ),
            ),
        );

        $this->shopLinkType = 'shop';

        // Legal infos
        $iso = $this->context->language->iso_code;
        if ($iso != 'fr' && $iso != 'en')
            $iso = 'en';
        $urlDoc = '../modules/prepayment/readme_'.$iso.'.pdf';
        $this->informations[] = '<b>'.$this->l('Prepayment').'</b> '.$this->l('© 2013').' <b>'.$this->l('DrÿSs\' Agency').'</b><br/><br/>'.
                                $this->l('Please read').' <a href="'.$urlDoc.'" target="_blank">'.$this->l('user documentation').'</a> '.$this->l('about installation/configuration/help to use.').'<br/>'.
                                $this->l('For any bugs, please contact us, we make free patch.').'<br/>'.
                                $this->l('We often provide updates for this module, with brand new features, please contact us for more informations.').'<br/><br/>'.
                                '<a href="mailto:contact@dryss.com">contact@dryss.com</a>';
    }
}
