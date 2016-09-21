<?php

abstract class Controller extends ControllerCore
{

    function __construct() {
        parent::__construct();

        $devCSS = 'dev.css';
        $privatePath = sprintf('%s/themes/%s/%s', _PS_ROOT_DIR_, _THEME_NAME_, $devCSS);
        $publicPath = sprintf('/themes/%s/%s', _THEME_NAME_, $devCSS);

        if (_DEV_LAYOUT_ && file_exists($privatePath)) {
            $this->addCSS($publicPath);
        }
    }

}
