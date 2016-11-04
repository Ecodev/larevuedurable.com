<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Link extends LinkCore
{

    public function getPageLink($controller, $ssl = false, $id_lang = null, $request = null, $request_url_encode = false)
    {
        $controller = Tools::strReplaceFirst('.php', '', $controller);

        if (!$id_lang)
            $id_lang = (int)Context::getContext()->language->id;

        if (!is_array($request))
        {
            // @FIXME html_entity_decode has been added due to '&amp;' => '%3B' ...
            $request = html_entity_decode($request);
            if ($request_url_encode)
                $request = urlencode($request);
            parse_str($request, $request);
        }

        $uri_path = Dispatcher::getInstance()->createUrl($controller, $id_lang, $request);
        $url = ($this->ssl_enable) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $url .= __PS_BASE_URI__.$this->getLangLink($id_lang).ltrim($uri_path, '/');

        return $url;
    }

    public function getCategoryLink($category, $alias = null, $id_lang = null, $selected_filters = null)
    {
        if (!$id_lang)
            $id_lang = Context::getContext()->language->id;

        $url = ($this->ssl_enable) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $url .= __PS_BASE_URI__.$this->getLangLink($id_lang);

        if (!is_object($category))
            $category = new Category($category, $id_lang);

        // Set available keywords
        $params = array();
        $params['id'] = $category->id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] =	Tools::str2url($category->meta_keywords);
        $params['meta_title'] = Tools::str2url($category->meta_title);

        // Selected filters is used by the module blocklayered
        $selected_filters = is_null($selected_filters) ? '' : $selected_filters;

        if (empty($selected_filters))
            $rule = 'category_rule';
        else
        {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selected_filters;
        }

        return $url.Dispatcher::getInstance()->createUrl($rule, $id_lang, $params, $this->allow);
    }

    public function getCMSCategoryLink($category, $alias = null, $id_lang = null)
    {
        if (!$id_lang)
            $id_lang = Context::getContext()->language->id;

        $url = ($this->ssl_enable) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $url .= __PS_BASE_URI__.$this->getLangLink($id_lang);

        if (!is_object($category))
            $category = new CMSCategory($category, $id_lang);

        // Set available keywords
        $params = array();
        $params['id'] = $category->id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] =	Tools::str2url($category->meta_keywords);
        $params['meta_title'] = Tools::str2url($category->meta_title);

        return $url.Dispatcher::getInstance()->createUrl('cms_category_rule', $id_lang, $params, $this->allow);
    }


}

