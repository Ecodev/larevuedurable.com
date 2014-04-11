<?php

class PrestaBlogDefaultModuleFrontController extends ModuleFrontController
{
    private $assignPage = 0;
    private $PrestaBlog = array();
    private $News = array();
    private $NewsCountAll;
    private $path;
    private $Pagination = array();
    private $ConfigTheme;

    public function getTemplatePath()
    {
        return _PS_MODULE_DIR_ . $this->module->name . '/themes/' . Configuration::get('prestablog_theme') . '/tpl/';
    }

    public function __construct()
    {
        parent::__construct();

        include_once(_PS_MODULE_DIR_ . 'prestablog/prestablog.php');
        include_once(_PS_MODULE_DIR_ . 'prestablog/class/news.class.php');
        include_once(_PS_MODULE_DIR_ . 'prestablog/class/categories.class.php');
        include_once(_PS_MODULE_DIR_ . 'prestablog/class/correspondancescategories.class.php');
        include_once(_PS_MODULE_DIR_ . 'prestablog/class/commentnews.class.php');
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->addCSS(_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/css/module.css', 'all');
        $this->context->controller->addCSS(_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/css/sexy-bookmarks-style.css', 'all');
        $this->context->controller->addJS(_MODULE_DIR_ . 'prestablog/js/sexy-bookmarks-public.min.js');
    }

    public function init()
    {
        parent::init();

        $base = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));

        $base .= __PS_BASE_URI__;

        if (Tools::getValue("rss")) {
            if (CategoriesClass::IsCategorieValide((int) Tools::getValue("rss"))) {
                Tools::redirectLink($base . 'modules/prestablog/prestablog-rss.php?rss=' . (int) Tools::getValue("rss"));
            } elseif (Tools::getValue("rss") == 'all') {
                Tools::redirectLink($base . 'modules/prestablog/prestablog-rss.php');
            } else {
                Tools::redirect('404.php');
            }
        }

        $this->PrestaBlog = new PrestaBlog();
        $this->ConfigTheme = PrestaBlog::_getConfigXmlTheme(Configuration::get('prestablog_theme'));

        // assignPage (1 = 1 news page, 2 = news listes, 0 = rien)
        $this->context->smarty->assign(array(
            'prestablog_theme_dir' => _MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/', 'md5pic' => md5(time())
        ));

        if (Tools::getValue('id') && $id_prestablog_news = (int) Tools::getValue('id')) {
            $this->assignPage = 1;
            $this->News = new NewsClass($id_prestablog_news, (int) $this->context->cookie->id_lang);
            if (!$this->News->actif) {
                Tools::redirect('404.php');
            }
        } elseif (Tools::getValue('a') && Configuration::get('prestablog_comment_subscription')) {
            if (!$this->context->cookie->isLogged()) {
                Tools::redirect('index.php?controller=authentication&back=' . urlencode('index.php?fc=module&module=prestablog&controller=default&a=' . Tools::getValue('a')));
            }
            //Tools::redirect('authentication.php?back=index.php?fc=module&module=prestablog&a='.Tools::getValue('a'));

            $this->News = new NewsClass((int) Tools::getValue('a'), (int) $this->context->cookie->id_lang);

            if ($this->News->actif) {
                CommentNewsClass::insertCommentAbo($this->News->id, $this->context->cookie->id_customer);
            }

            Tools::redirect(PrestaBlog::prestablog_url(array(
                "id" => $this->News->id, "seo" => $this->News->link_rewrite, "titre" => $this->News->title
            )));
        } elseif (Tools::getValue('d') && Configuration::get('prestablog_comment_subscription')) {
            if ($this->context->cookie->isLogged()) {
                $this->News = new NewsClass((int) Tools::getValue('d'), (int) $this->context->cookie->id_lang);
                if ($this->News->actif) {
                    CommentNewsClass::deleteCommentAbo($this->News->id, $this->context->cookie->id_customer);
                }
            }

            Tools::redirect(PrestaBlog::prestablog_url(array(
                "id" => $this->News->id, "seo" => $this->News->link_rewrite, "titre" => $this->News->title
            )));
        } else {
            $this->assignPage = 2;
            $Categorie = null;
            $SecteurName = "";
            $Year = null;
            $Month = null;

            if (Tools::getValue('c')) {
                $Categorie = (int) Tools::getValue('c');
                $SecteurName = '<a href="' . PrestaBlog::prestablog_url(array(
                        "c" => $Categorie, "titre" => CategoriesClass::getCategoriesName((int) $this->context->cookie->id_lang, $Categorie)
                    )) . '">' . CategoriesClass::getCategoriesName((int) $this->context->cookie->id_lang, $Categorie) . '</a>';
            }

            if (Tools::getValue('y')) {
                $Year = Tools::getValue('y');
                $SecteurName .= $Year;
            }

            if (Tools::getValue('m')) {
                $Month = Tools::getValue('m');
                $SecteurName .= ($SecteurName != '' ? ' > ' : '') . '<a href="' . PrestaBlog::prestablog_url(array(
                        "y" => $Year, "m" => $Month
                    )) . '">' . $this->PrestaBlog->MoisLangue[$Month] . '</a>';
            }

            if (Tools::getValue('p')) {
                if ($SecteurName == "") {
                    $SecteurName = $this->PrestaBlog->LSecteurAll;
                }
                $SecteurName .= ' > ' . $this->PrestaBlog->LPage . ' ' . Tools::getValue('p');
            }

            $this->context->smarty->assign(array(
                'prestablog_categorie'      => $Categorie,
                'prestablog_categorie_name' => CategoriesClass::getCategoriesName((int) $this->context->cookie->id_lang, $Categorie),
                'prestablog_month'          => $Month, 'prestablog_year' => $Year
            ));

            if (Tools::getValue('m') && Tools::getValue('y')) {
                $DateDebut = Date('Y-m-d H:i:s', mktime(0, 0, 0, $Month, +1, $Year));
                $DateFin = Date('Y-m-d H:i:s', mktime(0, 0, 0, $Month + 1, +1, $Year));
                if ($DateFin > Date('Y-m-d H:i:s')) {
                    $DateFin = Date('Y-m-d H:i:s');
                }
            } else {
                $DateDebut = null;
                $DateFin = Date('Y-m-d H:i:s');
            }

            // edit sam : ajouté pour différencier les news des events
            if ($Categorie == 3) {
                $this->getEvents($DateDebut, $DateFin, $Categorie);
            } else {
                $this->getNews($DateDebut, $DateFin, $Categorie);
            }

            $this->context->smarty->assign(array(
                'currentCategoryID' => $Categorie, 'SecteurName' => $SecteurName
            ));
        }

        if ($this->assignPage == 1) {
            $this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsNewsOnly((int) $this->context->cookie->id_lang, (int) Tools::getValue('id')));
        } elseif ($this->assignPage == 2 && Tools::getValue('c')) {
            $this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsNewsCat((int) $this->context->cookie->id_lang, (int) Tools::getValue('c')));
        } elseif ($this->assignPage == 2 && (Tools::getValue('y') || Tools::getValue('m'))) {
            $this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsNewsDate());
        } else {
            $this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsPage((int) $this->context->cookie->id_lang));
        }
    }

    // edit sam : ajouté
    public function getEvents($DateDebut, $DateFin, $Categorie)
    {
        $this->NewsCountAll = NewsClass::getCountListeAll(
            (int) ($this->context->cookie->id_lang),
            1, // actif only
            0, // homeslide
            $DateFin, // date début
            null, // date fin
            $Categorie,
            1 // langue active sur news
        );

        $this->News = NewsClass::getListe(
            (int) ($this->context->cookie->id_lang),
            1, // actif only
            0, // homeslide
            $this->ConfigTheme,
            (int) Tools::getValue('start'), // limit start
            (int) Configuration::get('prestablog_nb_liste_page'), // limit stop
            'n.`date`',
            'asc',
            $DateFin, // date début
            null, // date fin
            $Categorie,
            1 // langue active sur news
        );
    }

    // edit sam : ajouté
    public function getNews($DateDebut, $DateFin, $Categorie)
    {
        $this->NewsCountAll = NewsClass::getCountListeAll((int) ($this->context->cookie->id_lang), 1, // actif only
            0, // homeslide
            $DateDebut, // date début
            $DateFin, // date fin
            $Categorie, 1 // langue active sur news
        );

        $this->News = NewsClass::getListe((int) ($this->context->cookie->id_lang), 1, // actif only
            0, // homeslide
            $this->ConfigTheme, (int) Tools::getValue('start'), // limit start
            (int) Configuration::get('prestablog_nb_liste_page'), // limit stop
            'n.`date`', 'desc', $DateDebut, // date début
            $DateFin, // date fin
            $Categorie, 1 // langue active sur news
        );
    }

    public function initContent()
    {
        parent::initContent();

        /* affichage du menu cat */
        if ($this->assignPage == 1 && Configuration::get('prestablog_menu_cat_blog_article')) // page article unique
        {
            $this->VoirListeCatMenu();
        }
        if ($this->assignPage == 2) { // page liste articles
            if (Configuration::get('prestablog_menu_cat_blog_index')
                && !Tools::getValue('c')
                && !Tools::getValue('y')
                && !Tools::getValue('m')
                && !Tools::getValue('p')
            ) {
                $this->VoirListeCatMenu();
            } elseif (Configuration::get('prestablog_menu_cat_blog_list')
                && (Tools::getValue('c')
                    || Tools::getValue('y')
                    || Tools::getValue('m')
                    || Tools::getValue('p'))
            ) {
                $this->VoirListeCatMenu();
            }
        }
        /* /affichage du menu cat */

        if ($this->assignPage == 1) {
            $this->News->categories = CorrespondancesCategoriesClass::getCategoriesListeName((int) $this->News->id, (int) $this->context->cookie->id_lang, 1);
            $products_liaison = NewsClass::getProductLinkListe((int) $this->News->id, true);

            if (sizeof($products_liaison)) {
                $this->context->controller->addCSS(_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/css/skin.css', 'all');
                $this->context->controller->addJS(_MODULE_DIR_ . 'prestablog/js/jquery.jcarousel.min.js');

                foreach ($products_liaison As $ProductLink) {
                    $product = new Product((int) $ProductLink, false, (int) $this->context->cookie->id_lang);
                    $productCover = Image::getCover($product->id);
                    $image_product = new Image((int) $productCover["id_image"]);
                    $imageThumbPath = ImageManager::thumbnail(_PS_IMG_DIR_ . 'p/' . $image_product->getExistingImgPath() . '.jpg', 'product_mini_' . $product->id . '.jpg', 45, 'jpg');

                    $this->News->products_liaison[$ProductLink] = Array(
                        "name" => $product->name, "description_short" => $product->description_short, "thumb" => $imageThumbPath,
                        "link" => $product->getLink($this->context)
                    );
                }
            }

            if (file_exists(_PS_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/up-img/' . $this->News->id . '.jpg')) {
                $this->context->smarty->assign('News_Image', 'modules/prestablog/themes/' . Configuration::get('prestablog_theme') . '/up-img/' . $this->News->id . '.jpg');
            }
            $this->context->smarty->assign(array(
                'LinkReal'               => PrestaBlog::getBaseUrlFront() . '?fc=module&module=prestablog&controller=default', 'News' => $this->News,
                'Socials'                => Configuration::get('prestablog_socials_actif'), 'RssCategories' => Configuration::get('prestablog_uniqnews_rss'),
                'prestablog_current_url' => PrestaBlog::prestablog_url(array(
                    "id" => $this->News->id, "seo" => $this->News->link_rewrite, "titre" => $this->News->title
                ))
            ));

            // edit by sam // récupère les catégories liées, et si seule la catégorie "3 (évents)" est retournée, alors c'est un événemenent et on tag afin de pouvoir afficher un label pour la date
            $sql = '	select id_prestablog_categorie from ps_prestablog_categorie as pc
					inner join ps_prestablog_correspondancecategorie pcc on pc.id_prestablog_categorie = pcc.categorie
					inner join ps_prestablog_news pn on pn.id_prestablog_news = pcc.news	
					where pn.id_prestablog_news = ' . $this->News->id;

            $categorieAssociees = DB::getInstance(_PS_USE_SQL_SLAVE_)->query($sql)->fetchAll();
            $isEvent = false;
            if (sizeof($categorieAssociees) == 1 && $categorieAssociees[0]['id_prestablog_categorie'] == 3) {
                $isEvent = true;
            }
            $this->context->smarty->assign('isEvent', $isEvent);
            $this->context->smarty->assign(array(
                'tpl_unique' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/tpl/module_page-unique.tpl')
            ));

            if ($this->PrestaBlog->gestComment($this->News->id)) {
                $this->context->smarty->assign(array(
                    'Mail_Subscription' => Configuration::get('prestablog_comment_subscription'),
                    'Is_Subscribe'      => in_array($this->context->cookie->id_customer, CommentNewsClass::listeCommentAbo($this->News->id))
                ));

                $this->context->smarty->assign(array(
                    'tpl_comment' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/tpl/module_page-comment.tpl')
                ));
            }
        } elseif ($this->assignPage == 2) {
            if (Configuration::get('prestablog_pageslide_actif')
                && !Tools::getValue('c')
                && !Tools::getValue('y')
                && !Tools::getValue('m')
                && !Tools::getValue('p')
            ) {
                if ($this->PrestaBlog->slideNews()) {
                    $this->context->smarty->assign(array(
                        'tpl_slide' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/tpl/module_slide.tpl')
                    ));
                }
            }

            $this->Pagination = PrestaBlog::getPagination($this->NewsCountAll, null, (int) Configuration::get('prestablog_nb_liste_page'), (int) Tools::getValue('start'), (int) Tools::getValue('p'));
            $this->context->smarty->assign(array(
                'prestablog_theme' => Configuration::get('prestablog_theme'), 'Pagination' => $this->Pagination, 'News' => $this->News,
                'NbNews'           => $this->NewsCountAll
            ));

            $this->context->smarty->assign(array(
                'tpl_all' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/tpl/module_page-all.tpl')
            ));
        }

        $this->setTemplate('module_page.tpl');
    }

    private function VoirListeCatMenu()
    {
        $ListeCat = CategoriesClass::getListe((int) $this->context->cookie->id_lang, 1);

        if (sizeof($ListeCat)) {
            foreach ($ListeCat As $Key => $Value) {

                $sql = '
					SELECT COUNT(DISTINCT nl.`id_prestablog_news`) AS `value`
					FROM `' . _DB_PREFIX_ . 'prestablog_news_lang` As nl
					LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_correspondancecategorie` As co
						ON (co.news = nl.id_prestablog_news)
					LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_categorie` As c
						ON (co.categorie = c.id_prestablog_categorie)
					LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_news` As n
						ON (nl.id_prestablog_news = n.id_prestablog_news)
					WHERE	n.`actif` = 1
						AND nl.`id_lang` = ' . (int) $this->context->cookie->id_lang . '
						AND nl.`actif_langue` = 1 ';

                    // edit sam
                    if ((int) $Value["id_prestablog_categorie"] == 3) {
                        $sql .= "AND TIMESTAMP(n.`date`) >= '" . Date("Y/m/d H:i:s") . "'";
                    } else {
                        $sql .= "AND TIMESTAMP(n.`date`) <= '" . Date("Y/m/d H:i:s") . "'";
                    }
                    $sql .= 'AND	c.`actif` = 1
                            AND	c.id_prestablog_categorie = ' . (int) $Value["id_prestablog_categorie"];


                $nombre_news = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow($sql);

                if (!Configuration::get('prestablog_catnews_empty') && $nombre_news["value"] == 0) {
                    unset($ListeCat[$Key]);
                } else {
                    $ListeCat[$Key]["nombre_news"] = $nombre_news["value"];
                }
            }
        }

        $this->context->smarty->assign(array(
            'ListeCatNews'         => $ListeCat, 'RssMenuCat' => Configuration::get('prestablog_menu_cat_blog_rss'),
            'prestablog_theme_dir' => _MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/'
        ));

        $this->context->smarty->assign(array(
            //'tpl_menu_cat'			=> $this->context->smarty->fetch(_PS_MODULE_DIR_.'prestablog/themes/'.Configuration::get('prestablog_theme').'/tpl/module_page-menucat.tpl')
            'HOOK_LEFT_COLUMN_BLOG' => HOOK::exec('displayLeftColumnBlog'),
            'HOOK_END_PAGE_BLOG' => HOOK::exec('displayEndPageBlog')
        ));
    }
}
