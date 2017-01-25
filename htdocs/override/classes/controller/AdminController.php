<?php

class AdminController extends AdminControllerCore
{
    protected $_pagination = array(1000, 300, 100, 50, 20);

    public function setMedia()
    {
        $admin_webpath = str_ireplace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);
        $this->addCSS(__PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_print.css', 'print');
        parent::setMedia();
    }

}
