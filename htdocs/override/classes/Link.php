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

    public function getProductLink($product, $alias = null, $category = null, $ean13 = null, $id_lang = null, $id_shop = null, $ipa = 0, $force_routes = false)
    {
        $dispatcher = Dispatcher::getInstance();

        if (!$id_lang)
            $id_lang = Context::getContext()->language->id;

        if (!$id_shop)
            $shop = Context::getContext()->shop;
        else
            $shop = new Shop($id_shop);

        $url = ($this->ssl_enable) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $url .= $shop->getBaseURI().$this->getLangLink($id_lang);        

        if (!is_object($product))
        {
            if (is_array($product) && isset($product['id_product']))
                $product = new Product($product['id_product'], false, $id_lang);
            else if (is_numeric($product) || !$product)
                $product = new Product($product, false, $id_lang);
            else
                throw new PrestaShopException('Invalid product vars');
        }

        // Set available keywords
        $params = array();
        $params['id'] = $product->id;
        $params['rewrite'] = (!$alias) ? $product->getFieldByLang('link_rewrite') : $alias;
        $params['ean13'] = (!$ean13) ? $product->ean13 : $ean13;
        $params['meta_keywords'] =	Tools::str2url($product->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($product->getFieldByLang('meta_title'));

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'manufacturer'))
            $params['manufacturer'] = Tools::str2url($product->isFullyLoaded ? $product->manufacturer_name : Manufacturer::getNameById($product->id_manufacturer));

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'supplier'))
            $params['supplier'] = Tools::str2url($product->isFullyLoaded ? $product->supplier_name : Supplier::getNameById($product->id_supplier));

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'price'))
            $params['price'] = $product->isFullyLoaded ? $product->price : Product::getPriceStatic($product->id, false, null, 6, null, false, true, 1, false, null, null, null, $product->specificPrice);

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'tags'))
            $params['tags'] = Tools::str2url($product->getTags($id_lang));

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'category'))
            $params['category'] = Tools::str2url($product->category);

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'reference'))
            $params['reference'] = Tools::str2url($product->reference);

        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'categories'))
        {
            $params['category'] = (!$category) ? $product->category : $category;
            $cats = array();
            foreach ($product->getParentCategories() as $cat)
                if (!in_array($cat['id_category'], Link::$category_disable_rewrite))//remove root and home category from the URL
                    $cats[] = $cat['link_rewrite'];
            $params['categories'] = implode('/', $cats);
        }
        $anchor = $ipa ? $product->getAnchor($ipa) : '';

        return $url.$dispatcher->createUrl('product_rule', $id_lang, $params, $force_routes, $anchor);
    }

    public function getCMSLink($cms, $alias = null, $ssl = false, $id_lang = null)
    {
        $base = (($this->ssl_enable) ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);

        if (!$id_lang)
            $id_lang = Context::getContext()->language->id;
        $url = $base.__PS_BASE_URI__.$this->getLangLink($id_lang);

        if (!is_object($cms))
            $cms = new CMS($cms, $id_lang);

        // Set available keywords
        $params = array();
        $params['id'] = $cms->id;
        $params['rewrite'] = (!$alias) ? (is_array($cms->link_rewrite) ? $cms->link_rewrite[(int)$id_lang] : $cms->link_rewrite) : $alias;

        if (isset($cms->meta_keywords) && !empty($cms->meta_keywords))
            $params['meta_keywords'] = is_array($cms->meta_keywords) ?  Tools::str2url($cms->meta_keywords[(int)$id_lang]) :  Tools::str2url($cms->meta_keywords);
        else
            $params['meta_keywords'] = '';

        if (isset($cms->meta_title) && !empty($cms->meta_title))
            $params['meta_title'] = is_array($cms->meta_title) ? Tools::str2url($cms->meta_title[(int)$id_lang]) : Tools::str2url($cms->meta_title);
        else
            $params['meta_title'] = '';

        return $url.Dispatcher::getInstance()->createUrl('cms_rule', $id_lang, $params, $this->allow);
    }

    public function getModuleLink($module, $controller = 'default', array $params = array(), $ssl = false, $id_lang = null)
    {
        $base = (($this->ssl_enable) ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);

        if (!$id_lang)
            $id_lang = Context::getContext()->language->id;
        $url = $base.__PS_BASE_URI__.$this->getLangLink($id_lang);

        // Set available keywords
        $params['module'] = $module;
        $params['controller'] = $controller ? $controller : 'default';

        // If the module has its own route ... just use it !
        if (Dispatcher::getInstance()->hasRoute('module-'.$module.'-'.$controller, $id_lang))
            return $this->getPageLink('module-'.$module.'-'.$controller, $ssl, $id_lang, $params);
        else
            return $url.Dispatcher::getInstance()->createUrl('module', $id_lang, $params, $this->allow);
    }
}

