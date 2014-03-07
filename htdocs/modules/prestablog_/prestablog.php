<?php
/*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestaBlog extends Module
{
    public $ModulePath = "";
    public $LPage = "";
    public $LSecteurAll = "";
    public $MoisLangue = array();
    public $RssLangue = array();

    protected $InPost = Array();
    protected $checkSlide;
    protected $checkActive;

    protected $checkCommentState = -2;

    protected $NormalImageSizeWidth = 1024;
    protected $NormalImageSizeHeight = 1024;

    protected $AdminCropImageSizeWidth = 400;
    protected $AdminCropImageSizeHeight = 400;

    protected $AdminThumbImageSizeWidth = 40;
    protected $AdminThumbImageSizeHeight = 40;

    protected $maxImageSize = 25510464;
    protected $default_theme = "default";
    protected $default_effect = "fade";

    protected $effets = Array(
        "blindX", "blindY", "blindZ", "cover", "curtainX", "curtainY", "fade", "fadeZoom", "growX", "growY", "none", "scrollUp", "scrollDown", "scrollLeft",
        "scrollRight", "scrollHorz", "scrollVert", "shuffle", "slideX", "slideY", "toss", "turnUp", "turnDown", "turnLeft", "turnRight", "uncover", "wipe",
        "zoom"
    );

    protected $PathModuleConf;

    protected $url_demo_slide = "http://malsup.com/jquery/cycle/browser.html";

    public function __construct()
    {
        $this->name = 'prestablog';
        $this->tab = 'front_office_features';
        $this->version = '2.051';
        $this->author = 'HDClic';
        $this->need_instance = 0;
        $this->module_key = '7aafe030447c17f08629e0319107b62b';

        parent::__construct();

        $this->displayName = $this->l('PrestaBlog');
        $this->description = $this->l('A module to add a blog on your web store.');

        $this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');

        $this->PathModuleConf = 'index.php?tab=AdminModules&configure=' . $this->name . '&token=' . Tools::getValue('token');

        $path = dirname(__FILE__);
        if (strpos(__FILE__, 'Module.php') !== false) {
            $path .= '/../modules/' . $this->name;
        }

        $this->MoisLangue = Array( // important pour les traductions langues
            1  => $this->l('January'), 2 => $this->l('February'), 3 => $this->l('March'), 4 => $this->l('April'), 5 => $this->l('May'), 6 => $this->l('June'),
            7  => $this->l('July'), 8 => $this->l('August'), 9 => $this->l('September'), 10 => $this->l('October'), 11 => $this->l('November'),
            12 => $this->l('December')
        );

        $this->LPage = $this->l('Page');
        $this->LSecteurAll = $this->l('All news');

        $this->ModulePath = $path;

        include_once($path . '/class/news.class.php');
        include_once($path . '/class/categories.class.php');
        include_once($path . '/class/correspondancescategories.class.php');
        include_once($path . '/class/commentnews.class.php');

        $this->MessageCallBack = array(
            "no_result_search" => $this->l('No results found'), "no_result_linked" => $this->l('No product linked')
        );
    }

    private function registerHookPosition($hook_name, $position)
    {
        if ($this->registerHook($hook_name)) {
            $this->updatePosition((int) Hook::getIdByName($hook_name), 0, (int) $position);
        } else {
            return false;
        }

        return true;
    }

    public function InitLangueModule($id_lang)
    {
        $this->RssLangue["id_lang"] = $id_lang;
        $this->RssLangue["channel_title"] = strval(Configuration::get('PS_SHOP_NAME')) . ' ' . $this->l('news feed');
    }

    public function install()
    {
        $News = new NewsClass();
        $Categories = new CategoriesClass();
        $CorrespondancesCategories = new CorrespondancesCategoriesClass();
        $CommentNews = new CommentNewsClass();

        @unlink(_PS_MODULE_DIR_ . $this->name . '/override/classes/Dispatcher.php');
        if (version_compare(_PS_VERSION_, '1.5.3.1', '<')) {
            if (copy(_PS_ROOT_DIR_ . '/override/classes/Dispatcher.php', _PS_MODULE_DIR_ . $this->name . '/backup_override/Dispatcher_' . md5(date("YmdHis")) . '.php')) {
                if (!copy(_PS_MODULE_DIR_ . $this->name . '/override_before_1531/Dispatcher.php', _PS_MODULE_DIR_ . $this->name . '/override/classes/Dispatcher.php')) {
                    return false;
                }
            } else {
                return false;
            }
        }

        $this->installQuickAccess();
        Tools::createHookIfNoExist('displayHomeCol2', 'Accueil : deuxième colonne');
        Tools::createHookIfNoExist('displayLeftColumnBlog', 'Barre de gauche dans les sections blog');

        if (!parent::install()
            // ACCROCHES TEMPLATE
            || !$this->registerHookPosition('displayHeader', 1)
            || !$this->registerHookPosition('displayHomeCol2', 1)
            //||	!$this->registerHookPosition('displayrightColumn', 1)
            || !$this->registerHook('displayLeftColumnBlog')
            || !$this->registerHook('ModuleRoutes')
            // CONFIGURATION & INTEGRATION BASE DE DONNEES
            || !Configuration::updateValue($this->name . '_theme', $this->default_theme)
            || !Configuration::updateValue($this->name . '_effect', $this->default_effect)
            || !Configuration::updateValue($this->name . '_thumb_picture_width', 129)
            || !Configuration::updateValue($this->name . '_thumb_picture_height', 129)
            || !Configuration::updateValue($this->name . '_slide_picture_width', 555)
            || !Configuration::updateValue($this->name . '_slide_picture_height', 246)
            || !Configuration::updateValue($this->name . '_title_length', 60)
            || !Configuration::updateValue($this->name . '_intro_length', 200)
            || !Configuration::updateValue($this->name . '_speed', 2500)
            || !Configuration::updateValue($this->name . '_timeout', 4500)

            || !Configuration::updateValue($this->name . '_nb_products_row', 3)

            || !Configuration::updateValue($this->name . '_lastnews_limit', 5)
            || !Configuration::updateValue($this->name . '_homenews_limit', 5)
            || !Configuration::updateValue($this->name . '_nb_liste_page', 5)

            || !Configuration::updateValue($this->name . '_datenews_order', "desc") // ou asc

            || !Configuration::updateValue($this->name . '_rss_col', "right") // ou left
            || !Configuration::updateValue($this->name . '_datenews_col', "right") // ou left
            || !Configuration::updateValue($this->name . '_lastnews_col', "right")
            || !Configuration::updateValue($this->name . '_catnews_col', "right")

            || !Configuration::updateValue($this->name . '_datenews_showall', false)
            || !Configuration::updateValue($this->name . '_lastnews_showall', true)
            || !Configuration::updateValue($this->name . '_catnews_showall', false)

            || !Configuration::updateValue($this->name . '_catnews_rss', true)

            || !Configuration::updateValue($this->name . '_datenews_actif', false)
            || !Configuration::updateValue($this->name . '_lastnews_actif', false)
            || !Configuration::updateValue($this->name . '_catnews_actif', false)
            || !Configuration::updateValue($this->name . '_catnews_empty', false)

            || !Configuration::updateValue($this->name . '_homenews_actif', false)
            || !Configuration::updateValue($this->name . '_pageslide_actif', true)
            || !Configuration::updateValue($this->name . '_rewrite_actif', false)

            || !Configuration::updateValue($this->name . '_socials_actif', true)
            || !Configuration::updateValue($this->name . '_allnews_rss', false)
            || !Configuration::updateValue($this->name . '_uniqnews_rss', true)

            || !Configuration::updateValue($this->name . '_menu_cat_blog_index', true)
            || !Configuration::updateValue($this->name . '_menu_cat_blog_list', true)
            || !Configuration::updateValue($this->name . '_menu_cat_blog_article', true)
            || !Configuration::updateValue($this->name . '_menu_cat_blog_rss', true)

            || !Configuration::updateValue($this->name . '_comment_actif', true)
            || !Configuration::updateValue($this->name . '_comment_only_login', false)
            || !Configuration::updateValue($this->name . '_comment_auto_actif', false)
            || !Configuration::updateValue($this->name . '_comment_nofollow', true)
            || !Configuration::updateValue($this->name . '_comment_alert_admin', true)
            || !Configuration::updateValue($this->name . '_comment_admin_mail', Configuration::get('PS_SHOP_EMAIL'))
            || !Configuration::updateValue($this->name . '_comment_subscription', true)

            || !$this->MetaTitlePageBlog("add")

            // STRUCTURE BASE DE DONNEES
            || !$News->registerTablesBdd()
            || !$Categories->registerTablesBdd()
            || !$CorrespondancesCategories->registerTablesBdd()
            || !$CommentNews->registerTablesBdd()
        ) {
            return false;
        } else {
            return true;
        }
    }

    public function uninstall()
    {
        $News = new NewsClass();
        $Categories = new CategoriesClass();
        $CorrespondancesCategories = new CorrespondancesCategoriesClass();
        $CommentNews = new CommentNewsClass();

        $this->uninstallQuickAccess();

        @unlink(_PS_MODULE_DIR_ . $this->name . '/override/classes/Dispatcher.php');

        if (!parent::uninstall()
            // CONFIGURATION & INTEGRATION BASE DE DONNEES
            || !Configuration::deleteByName($this->name . '_theme')
            || !Configuration::deleteByName($this->name . '_effect')
            || !Configuration::deleteByName($this->name . '_thumb_picture_width')
            || !Configuration::deleteByName($this->name . '_thumb_picture_height')
            || !Configuration::deleteByName($this->name . '_slide_picture_width')
            || !Configuration::deleteByName($this->name . '_slide_picture_height')
            || !Configuration::deleteByName($this->name . '_title_length')
            || !Configuration::deleteByName($this->name . '_intro_length')
            || !Configuration::deleteByName($this->name . '_speed')
            || !Configuration::deleteByName($this->name . '_timeout')

            || !Configuration::deleteByName($this->name . '_nb_products_row')

            || !Configuration::deleteByName($this->name . '_lastnews_limit')
            || !Configuration::deleteByName($this->name . '_homenews_limit')
            || !Configuration::deleteByName($this->name . '_nb_liste_page')

            || !Configuration::deleteByName($this->name . '_datenews_order')

            || !Configuration::deleteByName($this->name . '_rss_col')
            || !Configuration::deleteByName($this->name . '_datenews_col')
            || !Configuration::deleteByName($this->name . '_lastnews_col')
            || !Configuration::deleteByName($this->name . '_catnews_col')

            || !Configuration::deleteByName($this->name . '_datenews_showall')
            || !Configuration::deleteByName($this->name . '_lastnews_showall')
            || !Configuration::deleteByName($this->name . '_catnews_showall')

            || !Configuration::deleteByName($this->name . '_catnews_rss')

            || !Configuration::deleteByName($this->name . '_datenews_actif')
            || !Configuration::deleteByName($this->name . '_lastnews_actif')
            || !Configuration::deleteByName($this->name . '_catnews_actif')
            || !Configuration::deleteByName($this->name . '_catnews_empty')

            || !Configuration::deleteByName($this->name . '_homenews_actif')
            || !Configuration::deleteByName($this->name . '_pageslide_actif')
            || !Configuration::deleteByName($this->name . '_rewrite_actif')

            || !Configuration::deleteByName($this->name . '_socials_actif')
            || !Configuration::deleteByName($this->name . '_allnews_rss')
            || !Configuration::deleteByName($this->name . '_uniqnews_rss')

            || !Configuration::deleteByName($this->name . '_menu_cat_blog_index')
            || !Configuration::deleteByName($this->name . '_menu_cat_blog_list')
            || !Configuration::deleteByName($this->name . '_menu_cat_blog_article')
            || !Configuration::deleteByName($this->name . '_menu_cat_blog_rss')

            || !Configuration::deleteByName($this->name . '_comment_actif')
            || !Configuration::deleteByName($this->name . '_comment_only_login')
            || !Configuration::deleteByName($this->name . '_comment_auto_actif')
            || !Configuration::deleteByName($this->name . '_comment_nofollow')
            || !Configuration::deleteByName($this->name . '_comment_alert_admin')
            || !Configuration::deleteByName($this->name . '_comment_admin_mail')
            || !Configuration::deleteByName($this->name . '_comment_subscription')

            || !$this->MetaTitlePageBlog("del")

            // STRUCTURE BASE DE DONNEES
            || !$News->deleteTablesBdd()
            || !$Categories->deleteTablesBdd()
            || !$CorrespondancesCategories->deleteTablesBdd()
            || !$CommentNews->deleteTablesBdd()
        ) {
            return false;
        }

        return true;
    }

    public function installQuickAccess()
    {
        if (!Configuration::get($this->name . '_QuickAccess')) {
            $QA = new QuickAccess;
            foreach (Language::getLanguages(true) as $language) {
                $QA->name[(int) $language['id_lang']] = $this->displayName;
            }
            $QA->link = 'index.php?controller=AdminModules&configure=' . $this->name . '&module_name=' . $this->name;
            $QA->new_window = 0;
            $QA->Add();
            Configuration::updateValue($this->name . '_QuickAccess', $QA->id);
        }

        return true;
    }

    public function uninstallQuickAccess()
    {
        $QA = new QuickAccess((int) Configuration::get($this->name . '_QuickAccess'));
        $QA->delete();
        Configuration::deleteByName($this->name . '_QuickAccess');

        return true;
    }

    private function MetaTitlePageBlog($action)
    {
        switch ($action) {
        case "add" :
            $languages = Language::getLanguages(true);
            foreach ($languages as $language) {
                Configuration::updateValue($this->name . '_titlepageblog_' . $language['id_lang'], $this->displayName);
            }
            break;
        case "del" :
            $languages = Language::getLanguages();
            foreach ($languages as $language) {
                Configuration::deleteByName($this->name . '_titlepageblog_' . $language['id_lang']);
            }
            break;
        }
        return true;
    }

    private function _postForm()
    {
        $errors = array();
        $postEnCours = false;
        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));
        $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));
        $languages = Language::getLanguages();

        $this->checkSlide = 0;
        $this->checkActive = 0;

        if (Tools::getValue('submitFiltreNews')) {
            if (Tools::getValue('slide')) {
                $this->checkSlide = 1;
            } else {
                $this->checkSlide = 0;
            }
            if (Tools::getValue('activeNews')) {
                $this->checkActive = 1;
            } else {
                $this->checkActive = 0;
            }
        } else {
            if (Tools::getValue('slideget') == 1) {
                $this->checkSlide = 1;
            } else {
                $this->checkSlide = 0;
            }
            if (Tools::getValue('activeget') == 1) {
                $this->checkActive = 1;
            } else {
                $this->checkActive = 0;
            }
        }

        if (Tools::getValue('submitFiltreComment')) {
            $this->checkCommentState = Tools::getValue('activeComment');
        } else {
            if (Tools::getValue('activeCommentget')) {
                $this->checkCommentState = Tools::getValue('activeCommentget');
            } else {
                $this->checkCommentState = -2;
            }
        }

        $this->PathModuleConf .= '&activeget=' . $this->checkActive . '&slideget=' . $this->checkSlide . '&activeCommentget=' . $this->checkCommentState;

        if (Tools::isSubmit('deleteNews') && Tools::getValue('idN')) {
            $postEnCours = true;
            $News = new NewsClass((int) (Tools::getValue('idN')));
            //$News->copyFromPost();
            if (!$News->delete()) {
                $errors[] = Tools::displayError('An error occurred while delete object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                $this->deleteAllImagesThemes(Tools::getValue('idN'));
                Tools::redirectAdmin($this->PathModuleConf . '&newsListe');
            }
        } elseif (Tools::isSubmit('deleteCat') && Tools::getValue('idC')) {
            $postEnCours = true;
            $Categories = new CategoriesClass((int) (Tools::getValue('idC')));
            //$Categories->copyFromPost();
            if (!$Categories->delete()) {
                $errors[] = Tools::displayError('An error occurred while delete object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                Tools::redirectAdmin($this->PathModuleConf . '&catListe');
            }
        } elseif (Tools::isSubmit('etatNews') && Tools::getValue('idN')) {
            $postEnCours = true;
            $News = new NewsClass((int) (Tools::getValue('idN')));
            if (!$News->changeEtat('actif')) {
                $errors[] = Tools::displayError('An error occurred while change status object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                Tools::redirectAdmin($this->PathModuleConf . '&newsListe');
            }
        } elseif (Tools::isSubmit('slideNews') && Tools::getValue('idN')) {
            $postEnCours = true;
            $News = new NewsClass((int) (Tools::getValue('idN')));
            if (!$News->changeEtat('slide')) {
                $errors[] = Tools::displayError('An error occurred while change status object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                Tools::redirectAdmin($this->PathModuleConf . '&newsListe');
            }
        } elseif (Tools::isSubmit('etatCat') && Tools::getValue('idC')) {
            $postEnCours = true;
            $Categories = new CategoriesClass((int) (Tools::getValue('idC')));
            if (!$Categories->changeEtat('actif')) {
                $errors[] = Tools::displayError('An error occurred while change status object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                Tools::redirectAdmin($this->PathModuleConf . '&catListe');
            }
        } elseif (Tools::isSubmit('submitAddNews')) {
            $postEnCours = true;

            if (!sizeof(Tools::getValue('languesup'))) {
                $errors[] = Tools::displayError('You must activate at least one language');
            } else {
                foreach ($languages as $language) {
                    if (!Tools::getValue('title_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" alt="" title="" /> ' . Tools::displayError('The title must be specified');
                    }
                    if (!Tools::getValue('link_rewrite_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" alt="" title="" /> ' . Tools::displayError('The url rewrite must be specified');
                    }

                    $Summary = Tools::getValue('paragraph_' . $language['id_lang']);
                    $Content = Tools::getValue('content_' . $language['id_lang']);

                    if (!$Summary && !$Content && in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" alt="" title="" /> ' . Tools::displayError('The content or introduction must be specified');
                    }
                }
            }

            if (!sizeof($errors)) {
                $News = new NewsClass();
                $News->copyFromPost();
                $News->langues = serialize(Tools::getValue('languesup'));
                if (!$News->add()) {
                    $errors[] = Tools::displayError('An error occurred while add object.') . ' <b>' . mysql_error() . '</b>';
                }

                NewsClass::RemoveAllProductsLinkNews((int) $News->id);
                if (Tools::getValue('productsLink')) {
                    foreach (Tools::getValue('productsLink') As $productLink) {
                        NewsClass::updateProductLinkNews((int) $News->id, (int) $productLink);
                    }
                }

                $News->razEtatLangue((int) $News->id);
                foreach ($languages as $language) {
                    if (in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $News->changeActiveLangue((int) $News->id, (int) $language['id_lang']);
                    }
                }

                if ($_FILES['homepage_logo']["name"]) {
                    if (!$this->UploadImage($_FILES['homepage_logo'], $News->id, $this->NormalImageSizeWidth, $this->NormalImageSizeHeight)) {
                        $errors[] = Tools::displayError('An error occurred while upload image.');
                    } else {
                        foreach ($this->ScanDirectory(_PS_MODULE_DIR_ . $this->name . '/themes') As $KeyTheme => $ValueTheme) {
                            $ConfigTheme = $this->_getConfigXmlTheme($ValueTheme);
                            $this->ImageResize(dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/' . $News->id . '.jpg', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/admincrop_' . $News->id . '.jpg', $this->AdminCropImageSizeWidth, $this->AdminCropImageSizeHeight); // pour le crop

                            $this->AutoCropImage($News->id . '.jpg', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', $this->AdminThumbImageSizeWidth, $this->AdminThumbImageSizeHeight, "adminth_", null);

                            $ConfigThemeArray = objectToArray($ConfigTheme);
                            foreach ($ConfigThemeArray["images"] As $KeyThemeArray => $ValueThemeArray) {
                                $this->AutoCropImage($News->id . '.jpg', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', $ValueThemeArray["width"], $ValueThemeArray["height"], $KeyThemeArray . "_", null);
                            }
                        }
                    }
                }

                if (!sizeof($errors)) {
                    if (!Tools::getValue('categories')) {
                        CorrespondancesCategoriesClass::delAllCategoriesNews($News->id);
                    } else {
                        CorrespondancesCategoriesClass::delAllCategoriesNews($News->id);
                        CorrespondancesCategoriesClass::updateCategoriesNews(Tools::getValue('categories'), $News->id);
                    }
                    Tools::redirectAdmin($this->PathModuleConf . '&newsListe');
                }
            }
        } elseif (Tools::isSubmit('submitAddCat')) {
            $postEnCours = true;
            //~ if (!Tools::getValue('title_'.$defaultLanguage))
            //~ $errors[] = Tools::displayError('The title must be specified');

            if (!sizeof($errors)) {
                $Categories = new CategoriesClass();
                $Categories->copyFromPost();

                if (!$Categories->add()) {
                    $errors[] = Tools::displayError('An error occurred while add object.') . ' <b>' . mysql_error() . '</b>';
                } else {
                    Tools::redirectAdmin($this->PathModuleConf . '&catListe');
                }
            }
        } elseif (Tools::isSubmit('addProductLink') && Tools::getValue('idN') && Tools::getValue('idP')) {
            $postEnCours = true;

            NewsClass::updateProductLinkNews((int) Tools::getValue('idN'), (int) Tools::getValue('idP'));

            if (!sizeof($errors)) {
                Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN') . '#productLinkTable');
            }
        } elseif (Tools::isSubmit('removeProductLink') && Tools::getValue('idN') && Tools::getValue('idP')) {
            $postEnCours = true;

            NewsClass::removeProductLinkNews((int) Tools::getValue('idN'), (int) Tools::getValue('idP'));

            if (!sizeof($errors)) {
                Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN') . '#productLinkTable');
            }
        } elseif (Tools::isSubmit('submitUpdateNews') && Tools::getValue('idN')) {
            $postEnCours = true;

            if (!sizeof(Tools::getValue('languesup'))) {
                $errors[] = Tools::displayError('You must activate at least one language');
            } else {
                foreach ($languages as $language) {
                    if (!Tools::getValue('title_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" alt="" title="" /> ' . Tools::displayError('The title must be specified');
                    }
                    if (!Tools::getValue('link_rewrite_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" alt="" title="" /> ' . Tools::displayError('The url rewrite must be specified');
                    }

                    $Summary = Tools::getValue('paragraph_' . $language['id_lang']);
                    $Content = Tools::getValue('content_' . $language['id_lang']);

                    if (!$Summary && !$Content && in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" alt="" title="" /> ' . Tools::displayError('The content or introduction must be specified');
                    }
                }
            }

            if (!sizeof($errors)) {
                $News = new NewsClass((int) (Tools::getValue('idN')));
                $News->copyFromPost();
                $News->langues = serialize(Tools::getValue('languesup'));
                if (!$News->update()) {
                    $errors[] = Tools::displayError('An error occurred while update object.') . ' <b>' . mysql_error() . '</b>';
                }

                NewsClass::RemoveAllProductsLinkNews((int) $News->id);
                if (Tools::getValue('productsLink')) {
                    foreach (Tools::getValue('productsLink') As $productLink) {
                        NewsClass::updateProductLinkNews((int) $News->id, (int) $productLink);
                    }
                }

                $News->razEtatLangue((int) $News->id);
                foreach ($languages as $language) {
                    if (in_array($language['id_lang'], Tools::getValue('languesup'))) {
                        $News->changeActiveLangue((int) $News->id, (int) $language['id_lang']);
                    }
                }

                if ($_FILES['homepage_logo']["name"]) {
                    if (!$this->UploadImage($_FILES['homepage_logo'], Tools::getValue('idN'), $this->NormalImageSizeWidth, $this->NormalImageSizeHeight)) {
                        $errors[] = Tools::displayError('An error occurred while upload image.');
                    } else {
                        foreach ($this->ScanDirectory(_PS_MODULE_DIR_ . $this->name . '/themes') As $KeyTheme => $ValueTheme) {
                            $ConfigTheme = $this->_getConfigXmlTheme($ValueTheme);
                            $this->ImageResize(dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/' . Tools::getValue('idN') . '.jpg', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg', $this->AdminCropImageSizeWidth, $this->AdminCropImageSizeHeight); // pour le crop

                            $this->AutoCropImage(Tools::getValue('idN') . '.jpg', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', $this->AdminThumbImageSizeWidth, $this->AdminThumbImageSizeHeight, "adminth_", null);

                            $ConfigThemeArray = objectToArray($ConfigTheme);
                            foreach ($ConfigThemeArray["images"] As $KeyThemeArray => $ValueThemeArray) {
                                $this->AutoCropImage(Tools::getValue('idN') . '.jpg', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/', $ValueThemeArray["width"], $ValueThemeArray["height"], $KeyThemeArray . "_", null);
                            }
                        }
                    }
                }

                if (!sizeof($errors)) {
                    if (!Tools::getValue('categories')) {
                        CorrespondancesCategoriesClass::delAllCategoriesNews((int) Tools::getValue('idN'));
                    } else {
                        CorrespondancesCategoriesClass::delAllCategoriesNews((int) Tools::getValue('idN'));
                        CorrespondancesCategoriesClass::updateCategoriesNews(Tools::getValue('categories'), (int) Tools::getValue('idN'));
                    }
                }
            }
        } elseif (Tools::isSubmit('submitUpdateCat') && Tools::getValue('idC')) {
            $postEnCours = true;
            //~ if (!Tools::getValue('title_'.$defaultLanguage))
            //~ $errors[] = Tools::displayError('The title must be specified');

            if (!sizeof($errors)) {
                $Categories = new CategoriesClass((int) (Tools::getValue('idC')));
                $Categories->copyFromPost();

                if (!$Categories->update()) {
                    $errors[] = Tools::displayError('An error occurred while update object.') . ' <b>' . mysql_error() . '</b>';
                } else {
                    Tools::redirectAdmin($this->PathModuleConf . '&catListe');
                }
            }
        } elseif (Tools::isSubmit('submitUpdateComment') && Tools::getValue('idC')) {
            $postEnCours = true;
            if (!Tools::getValue('name')) {
                $errors[] = Tools::displayError('The name must be specified');
            }

            if (!sizeof($errors)) {
                $Comment = new CommentNewsClass((int) (Tools::getValue('idC')));
                $Comment->copyFromPost();

                if (!$Comment->update()) {
                    $errors[] = Tools::displayError('An error occurred while update object.') . ' <b>' . mysql_error() . '</b>';
                }
            }
        } elseif (Tools::isSubmit('deleteComment') && Tools::getValue('idC')) {
            $postEnCours = true;
            $CommentNews = new CommentNewsClass((int) (Tools::getValue('idC')));
            if (!$CommentNews->delete()) {
                $errors[] = Tools::displayError('An error occurred while delete object.') . ' <b>' . mysql_error() . '</b>';
            } else if (Tools::getValue('idN')) {
                Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN') . '&showComments');
            } else {
                Tools::redirectAdmin($this->PathModuleConf);
            }
        } elseif (Tools::isSubmit('enabledComment') && Tools::getValue('idC')) {
            $postEnCours = true;
            $CommentNews = new CommentNewsClass((int) (Tools::getValue('idC')));
            if (!$CommentNews->changeEtat('actif', 1)) {
                $errors[] = Tools::displayError('An error occurred while update object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                $NewsId = CommentNewsClass::getNewsFromComment($CommentNews->id);
                $ListeAbo = CommentNewsClass::listeCommentMailAbo($NewsId);

                if (Configuration::get($this->name . '_comment_subscription')
                    && sizeof($ListeAbo)
                ) {

                    $News = new NewsClass($NewsId, (int) Configuration::get('PS_LANG_DEFAULT'));
                    $content_form["title_news"] = $News->title;

                    foreach ($ListeAbo As $ValueAbo) {
                        Mail::Send((int) Configuration::get('PS_LANG_DEFAULT'), // langue
                            'feedback-subscribe', // template
                            $this->l('New comment') . ' / ' . $content_form["title_news"], // sujet
                            array( // templatevars
                                '{news}'              => $NewsId, '{title_news}' => $content_form["title_news"],
                                '{url_reponse}'       => Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . '?fc=module&module=prestablog&id=' . $content_form["news"],
                                '{url_desabonnement}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . '?fc=module&module=prestablog&d=' . $content_form["news"]
                            ), $ValueAbo, // destinataire mail
                            null, // destinataire nom
                            strval(Configuration::get('PS_SHOP_EMAIL')), // expéditeur
                            strval(Configuration::get('PS_SHOP_NAME')), // expéditeur nom
                            null, // fichier joint
                            null, // mode smtp
                            dirname(__FILE__) . '/mails/' // répertoire des mails templates
                        );
                    }
                }

                if (Tools::getValue('idN')) {
                    Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN') . '&showComments');
                } else {
                    Tools::redirectAdmin($this->PathModuleConf . (Tools::isSubmit('commentListe') ? '&commentListe' : ''));
                }
            }
        } elseif (Tools::isSubmit('pendingComment') && Tools::getValue('idC')) {
            $postEnCours = true;
            $CommentNews = new CommentNewsClass((int) (Tools::getValue('idC')));
            if (!$CommentNews->changeEtat('actif', -1)) {
                $errors[] = Tools::displayError('An error occurred while update object.') . ' <b>' . mysql_error() . '</b>';
            } else {
                Tools::redirectAdmin($this->PathModuleConf . (Tools::isSubmit('commentListe') ? '&commentListe' : ''));
            }
        } elseif (Tools::isSubmit('disabledComment') && Tools::getValue('idC')) {
            $postEnCours = true;
            $CommentNews = new CommentNewsClass((int) (Tools::getValue('idC')));
            if (!$CommentNews->changeEtat('actif', 0)) {
                $errors[] = Tools::displayError('An error occurred while update object.') . ' <b>' . mysql_error() . '</b>';
            } else if (Tools::getValue('idN')) {
                Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN') . '&showComments');
            } else {
                Tools::redirectAdmin($this->PathModuleConf . (Tools::isSubmit('commentListe') ? '&commentListe' : ''));
            }
        } elseif (Tools::isSubmit('deleteImage') && Tools::getValue('idN')) {
            $postEnCours = true;
            if (!file_exists(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/' . Tools::getValue('idN') . '.jpg')) {
                $errors[] = $this->displayError($this->l('This action cannot be taken.'));
            } else {
                $this->deleteAllImagesThemes(Tools::getValue('idN'));
            }
            if (!sizeof($errors)) {
                Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN'));
            }
        } elseif (Tools::isSubmit('submitCrop') && Tools::getValue('idN')) {
            $postEnCours = true;
            if (!file_exists(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg')) {
                $errors[] = $this->displayError($this->l('This action cannot be taken.'));
            } else {
                $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));
                $ConfigThemeArray = objectToArray($ConfigTheme);

                list($W_Image_Base, $H_Image_Base, $type, $attr) = getimagesize(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg');

                $this->CropImage(Tools::getValue('idN') . '.jpg', dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/', dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/', $W_Image_Base, // width de l'image sur lequel le crop a été selectionné
                    $H_Image_Base, // heigth de l'image sur lequel le crop a été selectionné
                    $ConfigThemeArray["images"][Tools::getValue('pfx')]["width"], // width de l'image sur lequel le crop a été selectionné
                    $ConfigThemeArray["images"][Tools::getValue('pfx')]["height"], // heigth de l'image sur lequel le crop a été selectionné
                    Tools::getValue('x'), // position horizontal du point de départ du crop selectionné
                    Tools::getValue('y'), // position vertical du point de départ du crop selectionné
                    Tools::getValue('w'), // width de la selection du crop
                    Tools::getValue('h'), // heigth de la selection du crop
                    Tools::getValue('pfx') . '_', null);
            }
            if (!sizeof($errors)) {
                Tools::redirectAdmin($this->PathModuleConf . '&editNews&idN=' . Tools::getValue('idN') . '&pfx=' . Tools::getValue('pfx'));
            }
        } elseif (Tools::isSubmit('submitTheme')) {
            Configuration::updateValue($this->name . '_theme', Tools::getValue('theme'));
            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitPageBlog')) {
            if (is_numeric(Tools::getValue($this->name . '_pageslide_actif'))) {
                Configuration::updateValue($this->name . '_pageslide_actif', (int) Tools::getValue($this->name . '_pageslide_actif'));
            }

            $languages = Language::getLanguages(true);
            foreach ($languages as $language) {
                Configuration::updateValue($this->name . '_titlepageblog_' . $language['id_lang'], Tools::getValue('meta_title_' . $language['id_lang']));
            }

            Tools::redirectAdmin($this->PathModuleConf . '&pageBlog');
        } elseif (Tools::isSubmit('submitConfSlideNews')) {
            if (is_numeric(Tools::getValue($this->name . '_homenews_limit'))) {
                Configuration::updateValue($this->name . '_homenews_limit', (int) Tools::getValue($this->name . '_homenews_limit'));
            }
            if (is_numeric(Tools::getValue($this->name . '_homenews_actif'))) {
                Configuration::updateValue($this->name . '_homenews_actif', (int) Tools::getValue($this->name . '_homenews_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_pageslide_actif'))) {
                Configuration::updateValue($this->name . '_pageslide_actif', (int) Tools::getValue($this->name . '_pageslide_actif'));
            }

            $xml = '<?xml version="1.0" encoding="UTF-8" ?>
<theme>
	<images>
		<thumb> <!--Image prévue pour les miniatures dans les listes -->
			<width>' . Tools::getValue('thumb_picture_width') . '</width>
			<height>' . Tools::getValue('thumb_picture_height') . '</height>
		</thumb>
		<slide> <!--Image prévue pour les slides -->
			<width>' . Tools::getValue('slide_picture_width') . '</width>
			<height>' . Tools::getValue('slide_picture_height') . '</height>
		</slide>
	</images>
	<title_length>' . Tools::getValue('title_length') . '</title_length>
	<intro_length>' . Tools::getValue('intro_length') . '</intro_length>
	<slide_speed>' . Tools::getValue('slide_speed') . '</slide_speed>
	<slide_timeout>' . Tools::getValue('slide_timeout') . '</slide_timeout>
	<slide_effect>' . Tools::getValue('slide_effect') . '</slide_effect>
</theme>';
            if (is_writable(_PS_MODULE_DIR_ . $this->name . '/themes/' . Configuration::get($this->name . '_theme') . '/')) {
                file_put_contents(_PS_MODULE_DIR_ . $this->name . '/themes/' . Configuration::get($this->name . '_theme') . '/config.xml', utf8_encode($xml));
                Tools::redirectAdmin($this->PathModuleConf . '&configModule');
            }
        } elseif (Tools::isSubmit('submitConfBlocRss')) {
            if (is_numeric(Tools::getValue($this->name . '_allnews_rss'))) {
                Configuration::updateValue($this->name . '_allnews_rss', (int) Tools::getValue($this->name . '_allnews_rss'));
            }
            Configuration::updateValue($this->name . '_rss_col', Tools::getValue($this->name . '_rss_col'));

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitConfBlocLastNews')) {
            if (is_numeric(Tools::getValue($this->name . '_lastnews_limit'))) {
                Configuration::updateValue($this->name . '_lastnews_limit', (int) Tools::getValue($this->name . '_lastnews_limit'));
            }
            if (is_numeric(Tools::getValue($this->name . '_lastnews_actif'))) {
                Configuration::updateValue($this->name . '_lastnews_actif', (int) Tools::getValue($this->name . '_lastnews_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_lastnews_showall'))) {
                Configuration::updateValue($this->name . '_lastnews_showall', (int) Tools::getValue($this->name . '_lastnews_showall'));
            }
            Configuration::updateValue($this->name . '_lastnews_col', Tools::getValue($this->name . '_lastnews_col'));

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitConfBlocDateNews')) {
            if (is_numeric(Tools::getValue($this->name . '_datenews_actif'))) {
                Configuration::updateValue($this->name . '_datenews_actif', (int) Tools::getValue($this->name . '_datenews_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_datenews_showall'))) {
                Configuration::updateValue($this->name . '_datenews_showall', (int) Tools::getValue($this->name . '_datenews_showall'));
            }
            Configuration::updateValue($this->name . '_datenews_col', Tools::getValue($this->name . '_datenews_col'));
            Configuration::updateValue($this->name . '_datenews_order', Tools::getValue($this->name . '_datenews_order'));

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitConfBlocCatNews')) {
            if (is_numeric(Tools::getValue($this->name . '_catnews_actif'))) {
                Configuration::updateValue($this->name . '_catnews_actif', (int) Tools::getValue($this->name . '_catnews_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_catnews_showall'))) {
                Configuration::updateValue($this->name . '_catnews_showall', (int) Tools::getValue($this->name . '_catnews_showall'));
            }
            if (is_numeric(Tools::getValue($this->name . '_catnews_empty'))) {
                Configuration::updateValue($this->name . '_catnews_empty', (int) Tools::getValue($this->name . '_catnews_empty'));
            }
            if (is_numeric(Tools::getValue($this->name . '_catnews_rss'))) {
                Configuration::updateValue($this->name . '_catnews_rss', (int) Tools::getValue($this->name . '_catnews_rss'));
            }

            Configuration::updateValue($this->name . '_catnews_col', Tools::getValue($this->name . '_catnews_col'));

            Tools::redirectAdmin($this->PathModuleConf . '' . '&configModule');
        } elseif (Tools::isSubmit('submitConfRewrite')) {
            if (is_numeric(Tools::getValue($this->name . '_rewrite_actif'))) {
                Configuration::updateValue($this->name . '_rewrite_actif', (int) Tools::getValue($this->name . '_rewrite_actif'));
            }

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitConfGobalFront')) {
            if (is_numeric(Tools::getValue($this->name . '_nb_liste_page'))) {
                Configuration::updateValue($this->name . '_nb_liste_page', (int) Tools::getValue($this->name . '_nb_liste_page'));
            }
            if (is_numeric(Tools::getValue($this->name . '_nb_products_row'))) {
                Configuration::updateValue($this->name . '_nb_products_row', (int) Tools::getValue($this->name . '_nb_products_row'));
            }
            if (is_numeric(Tools::getValue($this->name . '_socials_actif'))) {
                Configuration::updateValue($this->name . '_socials_actif', (int) Tools::getValue($this->name . '_socials_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_allnews_rss'))) {
                Configuration::updateValue($this->name . '_allnews_rss', (int) Tools::getValue($this->name . '_allnews_rss'));
            }
            if (is_numeric(Tools::getValue($this->name . '_uniqnews_rss'))) {
                Configuration::updateValue($this->name . '_uniqnews_rss', (int) Tools::getValue($this->name . '_uniqnews_rss'));
            }

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitConfMenuCatBlog')) {
            if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_index'))) {
                Configuration::updateValue($this->name . '_menu_cat_blog_index', (int) Tools::getValue($this->name . '_menu_cat_blog_index'));
            }
            if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_list'))) {
                Configuration::updateValue($this->name . '_menu_cat_blog_list', (int) Tools::getValue($this->name . '_menu_cat_blog_list'));
            }
            if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_article'))) {
                Configuration::updateValue($this->name . '_menu_cat_blog_article', (int) Tools::getValue($this->name . '_menu_cat_blog_article'));
            }
            if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_rss'))) {
                Configuration::updateValue($this->name . '_menu_cat_blog_rss', (int) Tools::getValue($this->name . '_menu_cat_blog_rss'));
            }

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        } elseif (Tools::isSubmit('submitConfComment')) {
            if (is_numeric(Tools::getValue($this->name . '_comment_actif'))) {
                Configuration::updateValue($this->name . '_comment_actif', (int) Tools::getValue($this->name . '_comment_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_comment_only_login'))) {
                Configuration::updateValue($this->name . '_comment_only_login', (int) Tools::getValue($this->name . '_comment_only_login'));
            }
            if (is_numeric(Tools::getValue($this->name . '_comment_auto_actif'))) {
                Configuration::updateValue($this->name . '_comment_auto_actif', (int) Tools::getValue($this->name . '_comment_auto_actif'));
            }
            if (is_numeric(Tools::getValue($this->name . '_comment_nofollow'))) {
                Configuration::updateValue($this->name . '_comment_nofollow', (int) Tools::getValue($this->name . '_comment_nofollow'));
            }
            if (is_numeric(Tools::getValue($this->name . '_comment_alert_admin'))) {
                Configuration::updateValue($this->name . '_comment_alert_admin', (int) Tools::getValue($this->name . '_comment_alert_admin'));
            }
            if (is_numeric(Tools::getValue($this->name . '_comment_subscription'))) {
                Configuration::updateValue($this->name . '_comment_subscription', (int) Tools::getValue($this->name . '_comment_subscription'));
            }

            Configuration::updateValue($this->name . '_comment_admin_mail', Tools::getValue($this->name . '_comment_admin_mail'));

            Tools::redirectAdmin($this->PathModuleConf . '&configModule');
        }

        if ($postEnCours) {
            if (sizeof($errors)) {
                $this->_html .= $this->displayError(implode('<br />', $errors));
            } else {
                $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully'));
            }
        }
    }

    public function displayError($error)
    {
        $output = '
		<div class="module_error error">
			<img src="' . _PS_IMG_ . 'admin/warning.gif" alt="" title="" /> ' . $error . '
		</div>';
        $this->error = true;

        return $output;
    }

    public function displayWarning($warn)
    {
        return '<div class="warn"><img src="../img/admin/warn2.png" />' . $warn . '</div>';
    }

    public function _getVerification()
    {
        $errors = $warnings = array();
        if (sizeof($this->CopyFiles)) {
            foreach ($this->CopyFiles As $Folder => $File) {
                if (!file_exists(_PS_ROOT_DIR_ . $Folder . $File)) {
                    $errors[] = $this->l('The file') . ' <span style="color:#FF0000;">' . _PS_ROOT_DIR_ . $Folder . $File . '</span> ' . $this->l('is not present');
                    $warnings[] = $this->l('Copy this source file :') . ' <span style="color:#0000FF;">' . _PS_MODULE_DIR_ . $this->name . '/cpy' . $Folder . $File . '</span></br>' . $this->l('to this destination :') . ' <span style="color:#009000;">' . _PS_ROOT_DIR_ . $Folder . $File . '</span>';
                }
            }
        }

        if (sizeof($errors)) {
            $MessageBefore = $this->l('Be carefull ! Those files are not present after install, due to permissions write.') . '<br /><br />';
            $this->_html .= $this->displayError($MessageBefore . implode('<br />', $errors));
            $this->_html .= $this->displayWarning($this->l('To solve this problem, you must copy/paste those file manually :') . '<br /><br />' . implode('<br /><br />', $warnings));
        }
    }

    private function ModuleDatepicker($class, $time)
    {
        $return = "";
        if ($time) {
            $return = '
			var dateObj = new Date();
			var hours = dateObj.getHours();
			var mins = dateObj.getMinutes();
			var secs = dateObj.getSeconds();
			if (hours < 10) { hours = "0" + hours; }
			if (mins < 10) { mins = "0" + mins; }
			if (secs < 10) { secs = "0" + secs; }
			var time = " "+hours+":"+mins+":"+secs;';
        }
        $return .= '
		$(function() {
			$(".' . Tools::htmlentitiesUTF8($class) . '").datepicker({
				prevText:"",
				nextText:"",
				dateFormat:"yy-mm-dd"' . ($time ? '+time' : '') . '});
		});';

        return '<script type="text/javascript">' . $return . '</script>';
    }

    public function getContent()
    {
        $this->_postForm();

        $this->context->controller->addJqueryUI('ui.datepicker');

        $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));
        $this->_html .= '<link type="text/css" rel="stylesheet" href="' . _MODULE_DIR_ . $this->name . '/css/admin.css" />' . "\n";
        $this->_html .= '<div id="hdclicconfiguration">';
        $this->_html .= '
			<table class="table" id="menu_config_prestablog" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center">
				<tr>
					<th>
						<img src="../modules/' . $this->name . '/img/home.png" alt="" />
						<a href="' . $this->PathModuleConf . '" title="' . $this->l('Home') . '">' . $this->l('Home') . '</a>
					</th>
					<th>
						<img src="../modules/' . $this->name . '/img/copy_files.gif" alt="" />
						<a href="' . $this->PathModuleConf . '&newsListe" title="' . $this->l('News') . '">' . $this->l('News') . '</a>
					</th>
					<th>
						<img src="../modules/' . $this->name . '/img/comments.gif" alt="" />
						<a href="' . $this->PathModuleConf . '&commentListe" title="' . $this->l('Comments') . '">' . $this->l('Comments') . '</a>
					</th>
					<th>
						<img src="../modules/' . $this->name . '/img/tab-categories.gif" alt="" />
						<a href="' . $this->PathModuleConf . '&catListe" title="' . $this->l('Categories') . '">' . $this->l('Categories') . '</a>
					</th>
					<th>
						<img src="../modules/' . $this->name . '/img/blog.png" alt="" />
						<a href="' . $this->PathModuleConf . '&pageBlog" title="' . $this->l('Blog page') . '">' . $this->l('Blog page') . '</a>
					</th>
					<th>
						<img src="../modules/' . $this->name . '/img/cog.gif" alt="" />
						<a href="' . $this->PathModuleConf . '&configModule" title="' . $this->l('General configuration') . '">' . $this->l('General configuration') . '</a>
					</th>
				</tr>
			</table>
			<br />';

        if (Tools::isSubmit('addNews')
            || Tools::isSubmit('editNews')
            || Tools::isSubmit('submitAddNews')
            || (Tools::isSubmit('submitUpdateNews') && Tools::getValue('idN'))
        ) {
            $this->_displayFormNews();
        } elseif (Tools::isSubmit('addCat')
            || Tools::isSubmit('editCat')
            || Tools::isSubmit('submitAddCat')
            || (Tools::isSubmit('submitUpdateCat') && Tools::getValue('idC'))
        ) {
            $this->_displayFormCategories();
        } elseif (Tools::isSubmit('editComment')
            || (Tools::isSubmit('submitUpdateComment') && Tools::getValue('idC'))
        ) {
            $this->_displayFormComments();
        } elseif (Tools::isSubmit('pageBlog')) {
            $this->_displayPageBlog();
        } elseif (Tools::isSubmit('configModule')) {
            $this->_displayConf();
        } elseif (Tools::isSubmit('catListe')) {
            $this->_displayListeCategories($ConfigTheme);
        } elseif (Tools::isSubmit('newsListe')) {
            $this->_displayListeNews($ConfigTheme);
        } elseif (Tools::isSubmit('commentListe')) {
            $this->_displayListeComments();
        } else {
            $this->_displayHome($ConfigTheme);
        }

        $this->_html .= '</div>';

        return $this->_html;
    }

    private function _displayHome($ConfigTheme)
    {
        $CommentsNonLu = CommentNewsClass::getListeNonLu();
        $this->_html .= '
		<div id="comments">' . "\n";
        $this->_html .= '
			<div class="blocs">
				<h3><img src="../modules/' . $this->name . '/img/question.gif" alt="' . $this->l('Pending') . '" />' . count($CommentsNonLu) . '&nbsp;' . $this->l('Comments pending') . '</h3>' . "\n";
        if (sizeof($CommentsNonLu)) {
            $this->_html .= '<div class="wrap">' . "\n";
            foreach ($CommentsNonLu As $KeyC => $ValueC) {
                $News = new NewsClass((int) ($ValueC["news"]), (int) ($this->context->language->id));
                $this->_html .= '<div>' . "\n";
                $this->_html .= '	<h2>
				<a href="' . $this->PathModuleConf . '&deleteComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /><span style="display:none;">' . $this->l('Delete') . '</span></a>
				<a href="' . $this->PathModuleConf . '&editComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment" style="float:right;"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /><span style="display:none;">' . $this->l('Edit') . '</span></a>
				<a href="' . $this->PathModuleConf . '&editNews&idN=' . $ValueC["news"] . '">' . $News->title . '</a></h2>' . "\n";
                $this->_html .= '	<h4>' . ToolsCore::displayDate($ValueC["date"], $this->context->language->id, true) . ', ' . $this->l('by') . ' <strong>' . $ValueC["name"] . '</strong></h4>' . "\n";
                if ($ValueC["url"] != "") {
                    $this->_html .= '	<h5><a href="' . $ValueC["url"] . '" target="_blank">' . $ValueC["url"] . '</a></h5>' . "\n";
                }
                $this->_html .= '	<p>' . $ValueC["comment"] . '</p>' . "\n";
                $this->_html .= '	
				<p class="center">
					<a href="' . $this->PathModuleConf . '&enabledComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/enabled.gif" alt="' . $this->l('Approuved') . '" /><span style="display:none;">' . $this->l('Approuved') . '</span></a>
					<a href="' . $this->PathModuleConf . '&disabledComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" /><span style="display:none;">' . $this->l('Disabled') . '</span></a>
				</p>' . "\n";
                $this->_html .= '</div>' . "\n";
            }
            $this->_html .= '</div>' . "\n";
        }
        $this->_html .= '
			</div>' . "\n";

        $ListeNews = NewsClass::getListe((int) ($this->context->language->id), 1, // actif only
            0, // slide
            $ConfigTheme, 0, // limit start
            (int) Configuration::get($this->name . '_lastnews_limit'), // limit stop
            'n.`date`', 'desc', null, // date début
            null, // date fin
            null, 0);

        $this->_html .= '
			<div class="blocs">
				<h3><img src="../modules/' . $this->name . '/img/lastnews.png" alt="' . $this->l('News') . '" />' . (int) Configuration::get($this->name . '_lastnews_limit') . ' ' . $this->l('latest news') . '</h3>' . "\n";
        if (sizeof($ListeNews)) {
            $this->_html .= '<div class="wrap">' . "\n";
            foreach ($ListeNews As $KeyN => $ValueN) {
                $this->_html .= '<div>' . "\n";
                if (file_exists(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_' . $ValueN['id_' . $this->name . '_news'] . '.jpg')) {
                    $this->_html .= '	<img src="' . $this->_path . 'themes/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_' . $ValueN['id_' . $this->name . '_news'] . '.jpg?' . md5(time()) . '" class="thumb"/>' . "\n";
                }
                $this->_html .= '	<h2><a href="' . $this->PathModuleConf . '&editNews&idN=' . $ValueN['id_' . $this->name . '_news'] . '" class="hrefComment" style="float:right;"><img src="../img/admin/edit.gif" alt="' . $this->l('Edit') . '" /><span style="display:none;">' . $this->l('Edit') . '</span></a>' . $ValueN["title"] . '</h2>' . "\n";
                $this->_html .= '	<h4>' . ToolsCore::displayDate($ValueN["date"], $this->context->language->id, true) . '</h4>' . "\n";
                $this->_html .= '	<p>' . "\n";
                $this->_html .= ($ValueN["paragraph_crop"] ? $ValueN["paragraph_crop"] : '<span style="color:red">' . $this->l('... empty content ...') . '</span>');
                $this->_html .= '	</p>' . "\n";
                $this->_html .= '	<div class="clear"></div>' . "\n";
                $this->_html .= '</div>' . "\n";
            }
            $this->_html .= '</div>' . "\n";
        }
        $this->_html .= '
			</div>' . "\n";

        $Stats = self::Statistiques();
        $Langue = LanguageCore::getLanguage((int) ($this->context->language->id));
        $this->_html .= '
			<div class="blocs">
				<h3><img src="../modules/' . $this->name . '/img/stats.png" alt="' . $this->l('Statistics') . '" />' . $this->l('Statistics') . '</h3>
				<h2>' . $this->l('Total news') . ' : <span style="color:#000;"><strong>' . $Stats["allnews"] . '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name . '/img/enabled.gif" width="10px;" />' . $Stats["allnewsactives"] . ' | <img src="../modules/' . $this->name . '/img/disabled.gif" width="10px;" />' . $Stats["allnewsinactives"] . ')</small></span></span></h2>
				<h2>' . $this->l('Total slides') . ' : <span style="color:#000;"><strong>' . $Stats["allslides"] . '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name . '/img/enabled.gif" width="10px;" />' . $Stats["allslidesactives"] . ' | <img src="../modules/' . $this->name . '/img/disabled.gif" width="10px;" />' . $Stats["allslidesinactives"] . ')</small></span></span></h2>
				<h2>' . $this->l('Total comments') . ' : <span style="color:#000;"><strong>' . $Stats["allcomments"] . '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name . '/img/enabled.gif" width="10px;" />' . $Stats["allcommentsactives"] . ' | <img src="../modules/' . $this->name . '/img/disabled.gif" width="10px;" />' . $Stats["allcommentsinactives"] . ' | <img src="../modules/' . $this->name . '/img/question.gif" width="10px;" />' . $Stats["allcommentspending"] . ')</small></span></span></h2>
				<h2>' . $this->l('Total categories') . ' : <span style="color:#000;"><strong>' . $Stats["allcategories"] . '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name . '/img/enabled.gif" width="10px;" />' . $Stats["allcategoriesactives"] . ' | <img src="../modules/' . $this->name . '/img/disabled.gif" width="10px;" />' . $Stats["allcategoriesinactives"] . ')</small></span></span></h2>
				<h2>' . $this->l('Total subscribe news') . ' : <span style="color:#000;"><strong>' . $Stats["allabonnements"] . '</strong> <span style="color:#7F7F7F;"><small>(' . $this->l('only registered user') . ')</small></span></span></h2>
			</div>' . "\n";

        $this->_html .= '
		</div>
		<div class="clear"></div>' . "\n";
        $this->_html .= '
			<script type="text/javascript">
				$(document).ready(function() { 
					$("a.hrefComment").mouseenter(function() { 
						$("span:first", this).show(\'slow\'); 
					}).mouseleave(function() { 
						$("span:first", this).hide(); 
					});
				});
			</script>' . "\n";
    }

    static public function Statistiques()
    {
        $Stats["allnews"] = NewsClass::getCountListeAllNoLang(0, 0, null, null, null);
        $Stats["allnewsactives"] = NewsClass::getCountListeAllNoLang(1, 0, null, null, null);
        $Stats["allnewsinactives"] = $Stats["allnews"] - $Stats["allnewsactives"];

        $Stats["allslides"] = NewsClass::getCountListeAllNoLang(0, 1, null, null, null);
        $Stats["allslidesactives"] = NewsClass::getCountListeAllNoLang(1, 1, null, null, null);
        $Stats["allslidesinactives"] = $Stats["allslides"] - $Stats["allslidesactives"];

        $Stats["allcomments"] = count(CommentNewsClass::getListe(-2, 0));
        $Stats["allcommentsactives"] = count(CommentNewsClass::getListe(1, 0));
        $Stats["allcommentspending"] = count(CommentNewsClass::getListe(-1, 0));
        $Stats["allcommentsinactives"] = count(CommentNewsClass::getListe(0, 0));

        $Stats["allcategories"] = count(CategoriesClass::getListeNoLang(0));
        $Stats["allcategoriesactives"] = count(CategoriesClass::getListeNoLang(1));
        $Stats["allcategoriesinactives"] = $Stats["allcategories"] - $Stats["allcategoriesactives"];

        $Stats["allabonnements"] = count(CommentNewsClass::listeCommentAbo());

        return $Stats;
    }

    private function _displayListeNews($ConfigTheme)
    {
        $NbParPage = 10;

        $tri_champ = 'n.`date`';
        $tri_ordre = 'desc';
        $languages = Language::getLanguages(true);

        if (Tools::getValue('c') && (int) Tools::getValue('c') > 0) {
            $Categorie = (int) Tools::getValue('c');
            $this->PathModuleConf .= $this->PathModuleConf . '&c=' . $Categorie;
        } else {
            $Categorie = null;
        }

        $CountListe = NewsClass::getCountListeAll(0, (int) $this->checkActive, // actif
            (int) $this->checkSlide, // slide
            null, // date début
            null, // date fin
            $Categorie, 0);

        $Liste = NewsClass::getListe(0, (int) $this->checkActive, // actif
            (int) $this->checkSlide, // slide
            $ConfigTheme, (int) Tools::getValue('start'), // limit start
            $NbParPage, // limit stop
            $tri_champ, $tri_ordre, null, // date début
            null, // date fin
            $Categorie, 0);

        $Pagination = self::getPagination($CountListe, null, $NbParPage, (int) Tools::getValue('start'), (int) Tools::getValue('p'));

        $Categories = CategoriesClass::getListe((int) ($this->context->language->id), 0);

        $this->_html .= '
			<form method="post" action="' . $this->PathModuleConf . '&newsListe" enctype="multipart/form-data">
			<input type="hidden" name="submitFiltreNews" value="1" />
			<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center;">
				<tr style="height:30px">
					<th>
						<img src="../modules/' . $this->name . '/img/add.gif" alt="" />
						<a href="' . $this->PathModuleConf . '&addNews" title="' . $this->l('Add a news') . '">' . $this->l('Add a news') . '</a>
					</th>
					<th style="border-left:3px solid #A0A0A0;">
						<img src="../modules/' . $this->name . '/img/filter.png" alt="" />
						' . $this->l('Filter list') . ' :
					</th>' . "\n";
        if (sizeof($Categories)) {
            $this->_html .= '
					<th style="border-left:1px solid #A0A0A0;">
						<select name="c" style="width:60px;" onchange="form.submit();">
							<option>' . $this->l('All') . '</option>' . "\n";
            foreach ($Categories As $Key => $Value) {
                $this->_html .= '<option value="' . $Value['id_' . $this->name . '_categorie'] . '" ' . ($Categorie == (int) $Value['id_' . $this->name . '_categorie'] ? 'selected' : '') . ' >' . $Value["title"] . '</option>' . "\n";
            }
            $this->_html .= '
						</select>
					</th>' . "\n";
        }
        $this->_html .= '
					<th style="border-left:1px solid #A0A0A0;">
						<input type="checkbox" name="activeNews" ' . ($this->checkActive == 1 ? 'checked' : '') . ' onchange="form.submit();"> ' . $this->l('Active') . '
					</th>' . "\n";
        $this->_html .= '
					<th style="border-left:1px solid #A0A0A0;">
						<input type="checkbox" name="slide" ' . ($this->checkSlide == 1 ? 'checked' : '') . ' onchange="form.submit();"> ' . $this->l('Slide') . '
					</th>' . "\n";

        $this->_html .= '
				</tr>
			</table>
			</form>
			<br/>';

        $this->_html .= '<fieldset style="width: 905px;">';
        $this->_html .= '<legend style="margin-bottom:10px;">' . $this->l('News') . ' : <span style="color: green;">' . $CountListe . ' currents items' . ($Categorie ? ' on ' . CategoriesClass::getCategoriesName((int) ($this->context->language->id), (int) $Categorie) : '') . '</span></legend>';
        $this->_html .= '<table class="table" cellpadding="0" cellspacing="0" style="margin-left:100px;width: 700px;">';
        $this->_html .= '	<thead class="center">';
        $this->_html .= '		<tr>';
        $this->_html .= '			<th></th>';
        $this->_html .= '			<th>' . $this->l('Date') . '</th>';
        $this->_html .= '			<th>' . $this->l('Image') . '</th>';
        $this->_html .= '			<th width="400px">' . $this->l('Title') . '</th>';
        $this->_html .= '			<th>' . $this->l('Comments') . '</th>';
        $this->_html .= '			<th>' . $this->l('Slide') . '</th>';
        $this->_html .= '			<th>' . $this->l('Activate') . '</th>';
        $this->_html .= '			<th>' . $this->l('Actions') . '</th>';
        $this->_html .= '		</tr>';
        $this->_html .= '	</thead>';
        if (sizeof($Liste)) {
            foreach ($Liste As $Key => $Value) {
                $this->_html .= '	<tr>';
                $this->_html .= '		<td class="center">' . ($Key + 1) . '</td>';
                $this->_html .= '		<td class="center" style="width:150px;font-size:80%;font-weight:bold;">' . (($dateC = new DateTime($Value["date"])) > ($now = new DateTime()) ? '<img src="../modules/' . $this->name . '/img/postdate.gif" alt="' . $this->l('Post Date') . '" />' : '') . $Value["date"] . '</td>';
                if (file_exists(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_' . $Value['id_' . $this->name . '_news'] . '.jpg')) {
                    $this->_html .= '		<td class="center"><img src="' . $this->_path . 'themes/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_' . $Value['id_' . $this->name . '_news'] . '.jpg?' . md5(time()) . '" /></td>';
                } else {
                    $this->_html .= '		<td class="center">-</td>';
                }

                $LangListeNews = unserialize($Value["langues"]);
                $this->_html .= '		<td>';
                foreach ($LangListeNews As $ValLangue) {
                    $this->_html .= (sizeof($languages) > 1 ? '<img src="../img/l/' . (int) ($ValLangue) . '.jpg" />' : '') . NewsClass::getTitleNews((int) $Value['id_' . $this->name . '_news'], $ValLangue) . '<br/>';
                }
                $this->_html .= '		</td>';

                $this->_html .= '		<td class="center">';
                $CommentsActif = CommentNewsClass::getListe(1, (int) $Value['id_' . $this->name . '_news']);
                $CommentsAll = CommentNewsClass::getListe(-2, (int) $Value['id_' . $this->name . '_news']);
                if (sizeof($CommentsAll)) {
                    $this->_html .= count($CommentsActif) . ' ' . $this->l('of') . ' ' . count($CommentsAll) . ' ' . $this->l('active');
                } else {
                    $this->_html .= '-';
                }
                $this->_html .= '		</td>';

                $this->_html .= '		<td class="center">
					<a href="' . $this->PathModuleConf . '&slideNews&idN=' . $Value['id_' . $this->name . '_news'] . '">
					' . ($Value["slide"] ? '<img src="../modules/' . $this->name . '/img/enabled.gif" alt="" />' : '<img src="../modules/' . $this->name . '/img/disabled.gif" alt="" />') . '
					</a>
				</td>';
                $this->_html .= '		<td class="center">
					<a href="' . $this->PathModuleConf . '&etatNews&idN=' . $Value['id_' . $this->name . '_news'] . '">
					' . ($Value["actif"] ? '<img src="../modules/' . $this->name . '/img/enabled.gif" alt="" />' : '<img src="../modules/' . $this->name . '/img/disabled.gif" alt="" />') . '
					</a>
				</td>';
                $this->_html .= '		<td class="center">
					<a href="' . $this->PathModuleConf . '&editNews&idN=' . $Value['id_' . $this->name . '_news'] . '" title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /></a>
					<a href="' . $this->PathModuleConf . '&deleteNews&idN=' . $Value['id_' . $this->name . '_news'] . '" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>
				</td>';
                $this->_html .= '	</tr>';
            }
            $PageType = "newsListe";
            if (intval($Pagination["NombreTotalPages"]) > 1) {
                $this->_html .= '<tfooter>';
                $this->_html .= '	<tr>';
                $this->_html .= '	<td colspan="6">';
                $this->_html .= '<div class="prestablog_pagination">' . "\n";
                if (intval($Pagination["PageCourante"] > 1)) {
                    $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $Pagination["StartPrecedent"] . '&p=' . $Pagination["PagePrecedente"] . '">&lt;&lt;</a>' . "\n";
                } else {
                    $this->_html .= '<span class="disabled">&lt;&lt;</span>' . "\n";
                }
                if ($Pagination["PremieresPages"]) {
                    foreach ($Pagination["PremieresPages"] As $key_page => $value_page) {
                        if ((intval(Tools::getValue('p')) == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1)) {
                            $this->_html .= '<span class="current">' . $key_page . '</span>' . "\n";
                        } else {
                            if ($key_page == 1) {
                                $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '">' . $key_page . '</a>' . "\n";
                            } else {
                                $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $value_page . '&p=' . $key_page . '">' . $key_page . '</a>' . "\n";
                            }
                        }
                    }
                }
                if (isset($Pagination["Pages"]) && $Pagination["Pages"]) {
                    $this->_html .= '<span class="more">...</span>' . "\n";

                    foreach ($Pagination["Pages"] As $key_page => $value_page) {
                        if (!in_array($value_page, $Pagination["PremieresPages"])) {
                            if ((intval(Tools::getValue('p')) == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1)) {
                                $this->_html .= '<span class="current">' . $key_page . '</span>' . "\n";
                            } else {
                                $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $value_page . '&p=' . $key_page . '">' . $key_page . '</a>' . "\n";
                            }
                        }
                    }
                }
                if ($Pagination["PageCourante"] < $Pagination["NombreTotalPages"]) {
                    $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $Pagination["StartSuivant"] . '&p=' . $Pagination["PageSuivante"] . '">&gt;&gt;</a>' . "\n";
                } else {
                    $this->_html .= '<span class="disabled">&gt;&gt;</span>' . "\n";
                }
                $this->_html .= '</div>' . "\n";
                $this->_html .= '	</td>';
                $this->_html .= '	</tr>';
                $this->_html .= '</tfooter>';
            }
        } else {
            $this->_html .= '<tr><td colspan="8" class="center">' . $this->l('No content registered') . '</td></tr>';
        }
        $this->_html .= '</table>';
        $this->_html .= '</fieldset>';

    }

    private function _displayListeComments()
    {
        $NbParPage = 10;

        $tri_champ = 'cn.`date`';
        $tri_ordre = 'desc';

        if (Tools::getValue('n') && (int) Tools::getValue('n') > 0) {
            $News = (int) Tools::getValue('n');
            $this->PathModuleConf .= $this->PathModuleConf . '&n=' . $News;
        } else {
            $News = null;
        }

        $CountListe = CommentNewsClass::getCountListeAll($this->checkCommentState, // active
            $News // only_news
        );

        $Liste = CommentNewsClass::getListe($this->checkCommentState, // active
            $News // only_news
        );

        $Pagination = self::getPagination($CountListe, null, $NbParPage, (int) Tools::getValue('start'), (int) Tools::getValue('p'));

        $this->_html .= '
			<form method="post" action="' . $this->PathModuleConf . '&commentListe" enctype="multipart/form-data">
			<input type="hidden" name="submitFiltreComment" value="1" />
			<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center;">
				<tr style="height:30px">
					<th style="border-left:3px solid #A0A0A0;">
						<img src="../modules/' . $this->name . '/img/filter.png" alt="" />
						' . $this->l('Filter list') . ' :
					</th>' . "\n";

        $this->_html .= '
					<th style="border-left:1px solid #A0A0A0;">
						<input type="radio" name="activeComment" ' . ($this->checkCommentState == -2 ? 'checked' : '') . ' onchange="form.submit();" value="-2" > <img src="../modules/' . $this->name . '/img/refresh.png" /> ' . $this->l('All') . '
					</th>
					<th style="border-left:1px solid #A0A0A0;">
						<input type="radio" name="activeComment" ' . ($this->checkCommentState == -1 ? 'checked' : '') . ' onchange="form.submit();" value="-1" > <img src="../modules/' . $this->name . '/img/question.gif" /> ' . $this->l('Pending') . '
					</th>
					<th style="border-left:1px solid #A0A0A0;">
						<input type="radio" name="activeComment" ' . ($this->checkCommentState == 1 ? 'checked' : '') . ' onchange="form.submit();" value="1"> <img src="../modules/' . $this->name . '/img/enabled.gif" /> ' . $this->l('Enabled') . '
					</th>
					<th style="border-left:1px solid #A0A0A0;">
						<input type="radio" name="activeComment" ' . (is_numeric($this->checkCommentState) && ($this->checkCommentState == 0) ? 'checked' : '') . ' onchange="form.submit();" value="0" > <img src="../modules/' . $this->name . '/img/disabled.gif" /> ' . $this->l('Disabled') . '
					</th>' . "\n";

        $this->_html .= '
				</tr>
			</table>
			</form>
			<br/>';

        $this->_html .= '<fieldset style="width: 905px;">';
        $this->_html .= '<legend style="margin-bottom:10px;">' . $this->l('Comments') . ' :</legend>';
        $this->_html .= '<table class="table" cellpadding="0" cellspacing="0" style="margin-left:10px;width: 890px;">';
        $this->_html .= '	<thead class="center">';
        $this->_html .= '		<tr>';
        $this->_html .= '			<th></th>';
        $this->_html .= '			<th>' . $this->l('Date') . '</th>';
        $this->_html .= '			<th>' . $this->l('News') . '</th>';
        $this->_html .= '			<th>' . $this->l('Name') . '</th>';
        $this->_html .= '			<th>' . $this->l('Url') . '</th>';
        $this->_html .= '			<th>' . $this->l('Comment') . '</th>';
        $this->_html .= '			<th>' . $this->l('Status') . '</th>';
        $this->_html .= '			<th>' . $this->l('Actions') . '</th>';
        $this->_html .= '		</tr>';
        $this->_html .= '	</thead>';
        if (sizeof($Liste)) {
            foreach ($Liste As $Key => $Value) {
                $this->_html .= '	<tr>';
                $this->_html .= '		<td class="center">' . ($Key + 1) . '</td>';
                $this->_html .= '		<td class="center" style="width:150px;font-size:80%;font-weight:bold;">' . $Value["date"] . '</td>';
                $TitleNews = NewsClass::getTitleNews((int) ($Value["news"]), (int) ($this->context->language->id));
                $this->_html .= '		<td><a href="' . $this->PathModuleConf . '&editNews&idN=' . $Value["news"] . '">' . (strlen($TitleNews) >= 20 ? substr($TitleNews, 0, 20) . '...' : $TitleNews) . '</a></td>';
                $this->_html .= '		<td>' . $Value["name"] . '</td>';
                $this->_html .= '		<td><a href="' . $Value["url"] . '" target="_blank">' . $Value["url"] . '</a></td>';
                $this->_html .= '		<td><small>' . (strlen($Value["comment"]) >= 100 ? substr($Value["comment"], 0, 100) . '...' : $Value["comment"]) . '</small></td>';
                $this->_html .= '		<td class="status">
					<a href="' . $this->PathModuleConf . '&enabledComment&commentListe&idC=' . $Value["id_prestablog_commentnews"] . '" ' . ((int) $Value["actif"] != 1 ? 'style="display:none;"' : 'rel="1"') . ' >
						<img src="../modules/' . $this->name . '/img/enabled.gif" title="' . $this->l('Approuved') . '" />
					</a>
					<a href="' . $this->PathModuleConf . '&disabledComment&commentListe&idC=' . $Value["id_prestablog_commentnews"] . '" ' . ((int) $Value["actif"] != 0 ? 'style="display:none;"' : 'rel="1"') . ' >
						<img src="../modules/' . $this->name . '/img/disabled.gif" title="' . $this->l('Disabled') . '" />
					</a>
					<a href="' . $this->PathModuleConf . '&pendingComment&commentListe&idC=' . $Value["id_prestablog_commentnews"] . '" ' . ((int) $Value["actif"] != -1 ? 'style="display:none;"' : 'rel="1"') . ' >
						<img src="../modules/' . $this->name . '/img/question.gif" title=""' . $this->l('Pending') . '" />
					</a>
				</td>
				<script language="javascript" type="text/javascript">
					$(document).ready(function() {
						$("td.status").mouseenter(function() { 
							$(this).find("a").fadeIn(); 
						}).mouseleave(function() { 
							$(this).find("a").hide(function() {
								if ($(this).attr(\'rel\') == 1) 
									$(this).fadeIn();
							}); 
						});
					});
				</script>
				';
                $this->_html .= '		<td class="center">
					<a href="' . $this->PathModuleConf . '&editComment&idC=' . $Value["id_prestablog_commentnews"] . '" title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /></a>
					<a href="' . $this->PathModuleConf . '&deleteComment&idC=' . $Value["id_prestablog_commentnews"] . '" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>
				</td>';
                $this->_html .= '	</tr>';
            }
            $PageType = "commentListe";
            if (intval($Pagination["NombreTotalPages"]) > 1) {
                $this->_html .= '<tfooter>';
                $this->_html .= '	<tr>';
                $this->_html .= '	<td colspan="6">';
                $this->_html .= '<div class="prestablog_pagination">' . "\n";
                if (intval($Pagination["PageCourante"] > 1)) {
                    $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $Pagination["StartPrecedent"] . '&p=' . $Pagination["PagePrecedente"] . '">&lt;&lt;</a>' . "\n";
                } else {
                    $this->_html .= '<span class="disabled">&lt;&lt;</span>' . "\n";
                }
                if ($Pagination["PremieresPages"]) {
                    foreach ($Pagination["PremieresPages"] As $key_page => $value_page) {
                        if ((intval(Tools::getValue('p')) == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1)) {
                            $this->_html .= '<span class="current">' . $key_page . '</span>' . "\n";
                        } else {
                            if ($key_page == 1) {
                                $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '">' . $key_page . '</a>' . "\n";
                            } else {
                                $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $value_page . '&p=' . $key_page . '">' . $key_page . '</a>' . "\n";
                            }
                        }
                    }
                }
                if (isset($Pagination["Pages"]) && $Pagination["Pages"]) {
                    $this->_html .= '<span class="more">...</span>' . "\n";

                    foreach ($Pagination["Pages"] As $key_page => $value_page) {
                        if (!in_array($value_page, $Pagination["PremieresPages"])) {
                            if ((intval(Tools::getValue('p')) == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1)) {
                                $this->_html .= '<span class="current">' . $key_page . '</span>' . "\n";
                            } else {
                                $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $value_page . '&p=' . $key_page . '">' . $key_page . '</a>' . "\n";
                            }
                        }
                    }
                }
                if ($Pagination["PageCourante"] < $Pagination["NombreTotalPages"]) {
                    $this->_html .= '<a href="' . $this->PathModuleConf . '&' . $PageType . '&start=' . $Pagination["StartSuivant"] . '&p=' . $Pagination["PageSuivante"] . '">&gt;&gt;</a>' . "\n";
                } else {
                    $this->_html .= '<span class="disabled">&gt;&gt;</span>' . "\n";
                }
                $this->_html .= '</div>' . "\n";
                $this->_html .= '	</td>';
                $this->_html .= '	</tr>';
                $this->_html .= '</tfooter>';
            }
        } else {
            $this->_html .= '<tr><td colspan="8" class="center">' . $this->l('No content registered') . '</td></tr>';
        }
        $this->_html .= '</table>';
        $this->_html .= '</fieldset>';
    }

    private function _displayListeCategories($ConfigTheme)
    {
        $Liste = CategoriesClass::getListe((int) ($this->context->language->id), 0);

        $this->_html .= '
			<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center">
				<tr>
					<th>
							<img src="../modules/' . $this->name . '/img/add.gif" alt="" />
							<a href="' . $this->PathModuleConf . '&addCat" title="' . $this->l('Add a category') . '">' . $this->l('Add a category') . '</a>
					</th>
				</tr>
			</table>';
        $this->_html .= '<fieldset style="width: 905px;">';
        $this->_html .= '<legend style="margin-bottom:10px;">' . $this->l('Categories') . '</legend>';
        $this->_html .= '<table class="table" cellpadding="0" cellspacing="0" style="margin-left:100px;width: 700px;">';
        $this->_html .= '	<thead class="center">';
        $this->_html .= '		<tr>';
        $this->_html .= '			<th></th>';
        $this->_html .= '			<th>' . $this->l('Title') . '</th>';
        $this->_html .= '			<th>' . $this->l('Title Meta') . '</th>';
        $this->_html .= '			<th>' . $this->l('Activate') . '</th>';
        $this->_html .= '			<th>' . $this->l('Actions') . '</th>';
        $this->_html .= '		</tr>';
        $this->_html .= '	</thead>';
        if (sizeof($Liste)) {
            foreach ($Liste As $Key => $Value) {
                $this->_html .= '	<tr>';
                $this->_html .= '		<td class="center">' . ($Key + 1) . '</td>';
                $this->_html .= '		<td>' . $Value["title"] . '</td>';
                $this->_html .= ($Value["meta_title"] ? '<td style="font-size:90%;">' . $Value["meta_title"] . '</td>' : '<td style="text-align:center;">-</td>') . '</td>';
                $this->_html .= '		<td class="center">
					<a href="' . $this->PathModuleConf . '&etatCat&idC=' . $Value['id_' . $this->name . '_categorie'] . '">
					' . ($Value["actif"] ? '<img src="../modules/' . $this->name . '/img/enabled.gif" alt="" />' : '<img src="../modules/' . $this->name . '/img/disabled.gif" alt="" />') . '
					</a>
				</td>';
                $this->_html .= '		<td class="center">
					<a href="' . $this->PathModuleConf . '&editCat&idC=' . $Value['id_' . $this->name . '_categorie'] . '" title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /></a>';
                if ($Value['id_' . $this->name . '_categorie'] > 1) {
                    $this->_html .= '		<a href="' . $this->PathModuleConf . '&deleteCat&idC=' . $Value['id_' . $this->name . '_categorie'] . '" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>';
                }
                $this->_html .= '		</td>';
                $this->_html .= '	</tr>';
            }
        } else {
            $this->_html .= '<tr><td colspan="5" class="center">' . $this->l('No content registered') . '</td></tr>';
        }
        $this->_html .= '</table>';
        $this->_html .= '</fieldset>';
    }

    private function _displayPageBlog()
    {
        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(true);
        $iso = Language::getIsoById((int) ($this->context->language->id));
        $divLangName = 'meta_title';

        $this->_html .= '
			<script type="text/javascript">id_language = Number(' . $defaultLanguage . ');</script>
			<fieldset style="margin-bottom:20px;">
				<legend style="margin-bottom:10px;">' . $this->l('Blog page configuration') . '</legend>
				<div class="warn">
					<p>' . $this->l('If you have a custom menu, or if you want to make an acces to your blog page, you can use this link :') . '</p>
					<ul>';
        $multilang = (Language::countActiveLanguages() > 1);

        if ($multilang) {
            $languages = Language::getLanguages(true);
            foreach ($languages as $language) {
                if (intval(Configuration::get('prestablog_rewrite_actif'))) {
                    if (intval(Configuration::get('PS_REWRITING_SETTINGS'))) {
                        $url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__ . Language::getIsoById((int) $language['id_lang']) . '/module/prestablog/default';
                    } else {
                        $url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__ . '?fc=module&module=prestablog&id_lang=' . (int) $language['id_lang'];
                    }
                } else if (intval(Configuration::get('PS_REWRITING_SETTINGS'))) {
                    $url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__ . Language::getIsoById((int) $language['id_lang']) . '/?fc=module&module=prestablog';
                } else {
                    $url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__ . '?fc=module&module=prestablog&id_lang=' . (int) $language['id_lang'];
                }

                $this->_html .= '<li><img src="../../img/l/' . $language['id_lang'] . '.jpg" style="vertical-align:middle;" />
								<a href="' . $url_page_blog . '" target="_blank">' . $url_page_blog . '</a>
							</li>';
            }
        } else {
            if (intval(Configuration::get('PS_REWRITING_SETTINGS')) && intval(Configuration::get('prestablog_rewrite_actif'))) {
                $url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__ . 'module/prestablog/default';
            } else {
                $url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__ . '?fc=module&module=prestablog';
            }

            $this->_html .= '<li>
								<a href="' . $url_page_blog . '" target="_blank">' . $url_page_blog . '</a>
							</li>';
        }

        $this->_html .= '
					</ul>
				</div>
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					' . $this->_displayFormEnableItem($this->l('Slide on blogpage'), $this->name . '_pageslide_actif') . '
					
					<label>' . $this->l('Title Meta') . '</label>
					<div class="margin-form">';
        foreach ($languages as $language) {
            $this->_html .= '
						<div id="meta_title_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
							<input type="text" name="meta_title_' . $language['id_lang'] . '" id="meta_title_' . $language['id_lang'] . '" style="width:500px" value="' . Configuration::get($this->name . '_titlepageblog_' . $language['id_lang']) . '" />
						</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_title', true);

        $this->_html .= '
						<div class="clear"></div>
					</div>
					
					<div class="margin-form clear">
						<input type="submit" name="submitPageBlog" value="' . $this->l('Update the configuration') . '" class="button" />
					</div>
				</form>
			</fieldset>';
    }

    private function _displayConf()
    {
        $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));

        $this->_html .= '
			<fieldset style="width: 950px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Theme') . '</legend>
					<label>' . $this->l('Choose your module theme :') . '</label>
					<div class="margin-form">
						<select name="theme" id="theme">' . "\n";
        foreach ($this->ScanDirectory(_PS_MODULE_DIR_ . $this->name . '/themes') As $KeyTheme => $ValueTheme) {
            $this->_html .= '	<option value="' . $ValueTheme . '" ' . (Configuration::get($this->name . '_theme') == $ValueTheme ? "selected" : "") . '>' . $ValueTheme . '</option>' . "\n";
        }
        $this->_html .= '</select>
						<div class="clear"></div>
					</div>
					<script language="javascript" type="text/javascript">
						$(document).ready(function() {
							$("#theme").change(function() {
								var src = $(this).val();
								$("#imagePreview").hide();
								$("#imagePreview").html(src ? "<img src=\'../modules/' . $this->name . '/themes/" + src + "/preview.jpg\'>" : "");
								$("#imagePreview").fadeIn();
							});
						});
					</script>
					<div class="clear"></div> 
					<label>' . $this->l('Preview :') . '</label>
					<div class="margin-form">
						<div id="imagePreview" style="border: 1px #ccc solid;text-align:center;padding:10px;">
							<img src="../modules/' . $this->name . '/themes/' . Configuration::get($this->name . '_theme') . '/preview.jpg" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div> 
					<div class="margin-form clear">
						<input type="submit" name="submitTheme" value="' . $this->l('Update theme') . '" class="button" />
					</div>
				</form>
			</fieldset>
			<br />';

        $this->_html .= '
			<div style="width: 480px;margin-right:20px;float:left;">
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Slideshow') . ' :</legend>
					' . $this->_displayFormEnableItem($this->l('Slide on homepage'), $this->name . '_homenews_actif') . '
					' . $this->_displayFormEnableItem($this->l('Slide on blogpage'), $this->name . '_pageslide_actif') . '
					<label>' . $this->l('Number of slide to display') . '</label>
					<div class="margin-form">
						<input type="text" name="' . $this->name . '_homenews_limit" style="width:50px" value="' . Configuration::get($this->name . '_homenews_limit') . '" />
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Slide transition effect') . '</label>
					<div class="margin-form">
						<select name="slide_effect" style="width:100px">';
        foreach ($this->effets As $Key => $Value) {
            $this->_html .= '<option value="' . $Value . '" ' . ($ConfigTheme->slide_effect == $Value ? ' selected' : '') . '>' . $Value . '</option>';
        }
        $this->_html .= '
						</select>
						<p><a href="' . $this->url_demo_slide . '" target="_blank">' . $this->l('See demo effect') . '</a></p>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Thumb picture width') . '</label>
					<div class="margin-form">
						<input type="text" name="thumb_picture_width" style="width:50px" value="' . $ConfigTheme->images->thumb->width . '" />&nbsp;<strong>px</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Thumb picture height') . '</label>
					<div class="margin-form">
						<input type="text" name="thumb_picture_height" style="width:50px" value="' . $ConfigTheme->images->thumb->height . '" />&nbsp;<strong>px</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Slide picture width') . '</label>
					<div class="margin-form">
						<input type="text" name="slide_picture_width" style="width:50px" value="' . $ConfigTheme->images->slide->width . '" />&nbsp;<strong>px</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Slide picture height') . '</label>
					<div class="margin-form">
						<input type="text" name="slide_picture_height" style="width:50px" value="' . $ConfigTheme->images->slide->height . '" />&nbsp;<strong>px</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Title length') . '</label>
					<div class="margin-form">
						<input type="text" name="title_length" style="width:50px" value="' . $ConfigTheme->title_length . '" />&nbsp;<strong>' . $this->l('caracters') . '</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Introduction length') . '</label>
					<div class="margin-form">
						<input type="text" name="intro_length" style="width:50px" value="' . $ConfigTheme->intro_length . '" />&nbsp;<strong>' . $this->l('caracters') . '</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Slide speed') . '</label>
					<div class="margin-form">
						<input type="text" name="slide_speed" style="width:50px" value="' . $ConfigTheme->slide_speed . '" />&nbsp;<strong>' . $this->l('ms') . '</strong>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Slide timeout') . '</label>
					<div class="margin-form">
						<input type="text" name="slide_timeout" style="width:50px" value="' . $ConfigTheme->slide_timeout . '" />&nbsp;<strong>' . $this->l('ms') . '</strong>
					</div>
					<div class="clear"></div> 
					<div class="margin-form clear">
						<input type="submit" name="submitConfSlideNews" value="' . $this->l('Update the slideshow') . '" class="button" />
					</div>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Rewrite configuration') . ' : ' . (Configuration::get($this->name . '_rewrite_actif') ? '<span style="font-weight: bold; color: green;"><img src="../modules/' . $this->name . '/img/ok.gif" alt="" />' . $this->l('Activated') . '</span>' : '<span style="font-weight: bold; color: red;"><img src="../modules/' . $this->name . '/img/ko.gif" alt="" />' . $this->l('Disabled') . '</span>') . '</legend>
					' . $this->_displayFormEnableItem($this->l('Enable rewrite'), $this->name . '_rewrite_actif') . '
					<div class="margin-form clear">
						<input type="submit" name="submitConfRewrite" value="' . $this->l('Update the rewrite state') . '" class="button" />
					</div>
					<p class="warn small">
						' . $this->l('Enable only if your server allows URL rewriting (recommended)') . '
					</p>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Global front configuration') . ' :</legend>
					<label>' . $this->l('Number of news per page') . '</label>
					<div class="margin-form">
						<input type="text" name="' . $this->name . '_nb_liste_page" style="width:50px" value="' . Configuration::get($this->name . '_nb_liste_page') . '" />
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Default number of columns in the linked product slideshow') . '</label>
					<div class="margin-form">
						<input type="text" name="' . $this->name . '_nb_products_row" style="width:50px" value="' . Configuration::get($this->name . '_nb_products_row') . '" />
					</div>
					<div class="clear"></div> 
					' . $this->_displayFormEnableItem($this->l('Socials buttons share'), $this->name . '_socials_actif') . '
					' . $this->_displayFormEnableItem('<img src="../modules/' . $this->name . '/img/rss.png" alt="" align="absmiddle" /> ' . $this->l('Rss link for categories news'), $this->name . '_uniqnews_rss', $this->l('Rss link for categories in the news page.')) . '
					<div class="margin-form clear">
						<input type="submit" name="submitConfGobalFront" value="' . $this->l('Update the gobal config') . '" class="button" />
					</div>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Category menu in blog page') . ' :</legend>
					' . $this->_displayFormEnableItem($this->l('Activate menu on blog index'), $this->name . '_menu_cat_blog_index') . '
					' . $this->_displayFormEnableItem($this->l('Activate menu on blog list'), $this->name . '_menu_cat_blog_list') . '
					' . $this->_displayFormEnableItem($this->l('Activate menu on article'), $this->name . '_menu_cat_blog_article') . '
					' . $this->_displayFormEnableItem('<img src="../modules/' . $this->name . '/img/rss.png" alt="" align="absmiddle" /> ' . $this->l('Rss link on categories'), $this->name . '_menu_cat_blog_rss', $this->l('Rss link for the categories blog menu.')) . '
					<div class="margin-form clear">
						<input type="submit" name="submitConfMenuCatBlog" value="' . $this->l('Update menu of categories') . '" class="button" />
					</div>
				</form>
			</fieldset>
			</div>';

        $this->_html .= '
			<div style="width: 480px;float:left;">
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/rss.png" alt="" /> ' . $this->l('Block Rss all news') . ' : ' . (Configuration::get($this->name . '_allnews_rss') ? '<span style="font-weight: bold; color: green;"><img src="../modules/' . $this->name . '/img/ok.gif" alt="" />' . $this->l('Activated') . '</span>' : '<span style="font-weight: bold; color: red;"><img src="../modules/' . $this->name . '/img/ko.gif" alt="" />' . $this->l('Disabled') . '</span>') . '</legend>
					' . $this->_displayFormEnableItem($this->l('Activate'), $this->name . '_allnews_rss') . '
					<label>' . $this->l('Column') . '</label>
					<div class="margin-form">
						<select name="' . $this->name . '_rss_col" style="width:100px">
							<option value="left" ' . (Configuration::get($this->name . '_rss_col') == "left" ? ' selected' : '') . '>' . $this->l('Left') . '</option>
							<option value="right" ' . (Configuration::get($this->name . '_rss_col') == "right" ? ' selected' : '') . '>' . $this->l('Right') . '</option>
						</select>
					</div>
					<div class="clear"></div> 
					<div class="margin-form clear">
						<input type="submit" name="submitConfBlocRss" value="' . $this->l('Update the block rss') . '" class="button" />
					</div>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Block last news') . ' : ' . (Configuration::get($this->name . '_lastnews_actif') ? '<span style="font-weight: bold; color: green;"><img src="../modules/' . $this->name . '/img/ok.gif" alt="" />' . $this->l('Activated') . '</span>' : '<span style="font-weight: bold; color: red;"><img src="../modules/' . $this->name . '/img/ko.gif" alt="" />' . $this->l('Disabled') . '</span>') . '</legend>
					' . $this->_displayFormEnableItem($this->l('Activate'), $this->name . '_lastnews_actif') . '
					<label>' . $this->l('Column') . '</label>
					<div class="margin-form">
						<select name="' . $this->name . '_lastnews_col" style="width:100px">
							<option value="left" ' . (Configuration::get($this->name . '_lastnews_col') == "left" ? ' selected' : '') . '>' . $this->l('Left') . '</option>
							<option value="right" ' . (Configuration::get($this->name . '_lastnews_col') == "right" ? ' selected' : '') . '>' . $this->l('Right') . '</option>
						</select>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Number of news to display') . '</label>
					<div class="margin-form">
						<input type="text" name="' . $this->name . '_lastnews_limit" style="width:50px" value="' . Configuration::get($this->name . '_lastnews_limit') . '" />
					</div>
					<div class="clear"></div> 
					' . $this->_displayFormEnableItem($this->l('Link "show all"'), $this->name . '_lastnews_showall') . '
					<div class="margin-form clear">
						<input type="submit" name="submitConfBlocLastNews" value="' . $this->l('Update the block last news') . '" class="button" />
					</div>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Block date news') . ' : ' . (Configuration::get($this->name . '_datenews_actif') ? '<span style="font-weight: bold; color: green;"><img src="../modules/' . $this->name . '/img/ok.gif" alt="" />' . $this->l('Activated') . '</span>' : '<span style="font-weight: bold; color: red;"><img src="../modules/' . $this->name . '/img/ko.gif" alt="" />' . $this->l('Disabled') . '</span>') . '</legend>
					' . $this->_displayFormEnableItem($this->l('Activate'), $this->name . '_datenews_actif') . '
					<label>' . $this->l('Column') . '</label>
					<div class="margin-form">
						<select name="' . $this->name . '_datenews_col" style="width:100px">
							<option value="left" ' . (Configuration::get($this->name . '_datenews_col') == "left" ? ' selected' : '') . '>' . $this->l('Left') . '</option>
							<option value="right" ' . (Configuration::get($this->name . '_datenews_col') == "right" ? ' selected' : '') . '>' . $this->l('Right') . '</option>
						</select>
					</div>
					<div class="clear"></div> 
					<label>' . $this->l('Order news') . '</label>
					<div class="margin-form">
						<select name="' . $this->name . '_datenews_order" style="width:100px">
							<option value="desc" ' . (Configuration::get($this->name . '_datenews_order') == "desc" ? ' selected' : '') . '>' . $this->l('Desc') . '</option>
							<option value="asc" ' . (Configuration::get($this->name . '_datenews_order') == "asc" ? ' selected' : '') . '>' . $this->l('Asc') . '</option>
						</select>
					</div>
					<div class="clear"></div> 
					' . $this->_displayFormEnableItem($this->l('Link "show all"'), $this->name . '_datenews_showall') . '
					<div class="margin-form clear">
						<input type="submit" name="submitConfBlocDateNews" value="' . $this->l('Update the block date news') . '" class="button" />
					</div>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/cog.gif" alt="" /> ' . $this->l('Block categories news') . ' : ' . (Configuration::get($this->name . '_catnews_actif') ? '<span style="font-weight: bold; color: green;"><img src="../modules/' . $this->name . '/img/ok.gif" alt="" />' . $this->l('Activated') . '</span>' : '<span style="font-weight: bold; color: red;"><img src="../modules/' . $this->name . '/img/ko.gif" alt="" />' . $this->l('Disabled') . '</span>') . '</legend>
					' . $this->_displayFormEnableItem($this->l('Activate'), $this->name . '_catnews_actif') . '
					<label>' . $this->l('Column') . '</label>
					<div class="margin-form">
						<select name="' . $this->name . '_catnews_col" style="width:100px">
							<option value="left" ' . (Configuration::get($this->name . '_catnews_col') == "left" ? ' selected' : '') . '>' . $this->l('Left') . '</option>
							<option value="right" ' . (Configuration::get($this->name . '_catnews_col') == "right" ? ' selected' : '') . '>' . $this->l('Right') . '</option>
						</select>
					</div>
					<div class="clear"></div> 
					' . $this->_displayFormEnableItem($this->l('View empty categories'), $this->name . '_catnews_empty') . '
					' . $this->_displayFormEnableItem($this->l('Link "show all"'), $this->name . '_catnews_showall') . '
					' . $this->_displayFormEnableItem('<img src="../modules/' . $this->name . '/img/rss.png" alt="" align="absmiddle" /> ' . $this->l('Rss feed'), $this->name . '_catnews_rss', $this->l('List only for selected category')) . '
					<div class="margin-form clear">
						<input type="submit" name="submitConfBlocCatNews" value="' . $this->l('Update the block categories') . '" class="button" />
					</div>
				</form>
			</fieldset>';

        $this->_html .= '
			<fieldset style="margin-bottom:20px;">
				<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
					<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/img/comments.gif" alt="" /> ' . $this->l('Comments') . ' : ' . (Configuration::get($this->name . '_comment_actif') ? '<span style="font-weight: bold; color: green;"><img src="../modules/' . $this->name . '/img/ok.gif" alt="" />' . $this->l('Activated') . '</span>' : '<span style="font-weight: bold; color: red;"><img src="../modules/' . $this->name . '/img/ko.gif" alt="" />' . $this->l('Disabled') . '</span>') . '</legend>
					' . $this->_displayFormEnableItem($this->l('Activate'), $this->name . '_comment_actif') . '
					' . $this->_displayFormEnableItem($this->l('Only registered can publish'), $this->name . '_comment_only_login') . '
					' . $this->_displayFormEnableItem($this->l('Auto approve comments'), $this->name . '_comment_auto_actif') . '
					' . $this->_displayFormEnableItem($this->l('Link href nofollow'), $this->name . '_comment_nofollow') . '
					' . $this->_displayFormEnableItem($this->l('Mail to admin on new comment'), $this->name . '_comment_alert_admin') . '
					<label id="AdminMailLabel" ' . (!Configuration::get($this->name . '_comment_alert_admin') ? 'style="display:none;")' : '') . '>' . $this->l('Admin Mail') . '</label>
					<div id="AdminMailDiv" class="margin-form" ' . (!Configuration::get($this->name . '_comment_alert_admin') ? 'style="display:none;")' : '') . '>
						<input type="text" name="' . $this->name . '_comment_admin_mail" style="width:150px" value="' . Configuration::get($this->name . '_comment_admin_mail') . '" />
					</div>
					<div class="clear"></div> 
					' . $this->_displayFormEnableItem($this->l('Mail user subscription'), $this->name . '_comment_subscription', $this->l('Only registered can subscribe')) . '
					<script language="javascript" type="text/javascript">
						$(document).ready(function() {
							$("input:radio[name=' . $this->name . '_comment_alert_admin]").change(function() {
								if($(this).val() == 1) {
									$("#AdminMailLabel").slideDown();
									$("#AdminMailDiv").slideDown();
								}
								else {
									$("#AdminMailLabel").slideUp();
									$("#AdminMailDiv").slideUp();
								}
							});
						});
					</script>
					<div class="margin-form clear">
						<input type="submit" name="submitConfComment" value="' . $this->l('Update comments') . '" class="button" />
					</div>
				</form>
			</fieldset>
		</div>';
    }

    private function _displayFormEnableItem($labelText, $nameItem, $infoSpan = '')
    {
        return '<label>' . $labelText . '</label>
					<div class="margin-form">
						<input type="radio" name="' . $nameItem . '" value="1" ' . (Tools::getValue($nameItem, Configuration::get($nameItem)) ? 'checked="checked" ' : '') . '/>
						<label class="t" > <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" /></label>
						<input type="radio" name="' . $nameItem . '" value="0" ' . (!Tools::getValue($nameItem, Configuration::get($nameItem)) ? 'checked="checked" ' : '') . '/>
						<label class="t" > <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" /></label>
						' . ($infoSpan ? '<br /><span>' . $infoSpan . '</span>' : '') . '
					</div>
					<div class="clear"></div>';
    }

    private function _displayFormNews()
    {
        $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));

        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(true);
        $iso = Language::getIsoById((int) ($this->context->language->id));
        $divLangName = 'title¤link_rewrite¤meta_title¤meta_description¤meta_keywords¤cpara1¤cpara2';

        if (Tools::getValue('idN')) {
            $News = new NewsClass((int) (Tools::getValue('idN')));
            $LangListeNews = unserialize($News->langues);
        } else {
            $News = new NewsClass();
            $defaultCat = 1;
            $defaultLang = $defaultLanguage;
            $News->nb_products_row = (int) (Configuration::get($this->name . '_nb_products_row'));
        }

        if ($_POST) {
            $News->copyFromPost();
        }

        // TinyMCE

        //$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
        $isoTinyMCE = (file_exists(_PS_ROOT_DIR_ . '/js/tinymce/jscripts/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en');
        $ad = dirname($_SERVER["PHP_SELF"]);
        $this->_html .= '
			<script type="text/javascript">
			' . (Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL') ? 'var PS_ALLOW_ACCENTED_CHARS_URL = 1;' : 'var PS_ALLOW_ACCENTED_CHARS_URL = 0;') . '
			var iso = \'' . $isoTinyMCE . '\' ;
			var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
			var ad = \'' . $ad . '\' ;
			</script>
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/js/tinymce.inc.js"></script>';

        $this->_html .= '
			<script type="text/javascript">
				id_language = Number(' . $defaultLanguage . ');
				
				function copy2friendlyURLPrestaBlog() {
					if(!$(\'#slink_rewrite_\'+id_language).attr(\'disabled\')) {
						$(\'#slink_rewrite_\'+id_language).val(str2url($(\'input#title_\'+id_language).val().replace(/^[0-9]+\./, \'\'), \'UTF-8\')); 
					}
				}
				function updateFriendlyURLPrestaBlog() { 
					$(\'#slink_rewrite_\'+id_language).val(str2url($(\'#slink_rewrite_\'+id_language).val().replace(/^[0-9]+\./, \'\'), \'UTF-8\')); 
				}
				
				function RetourLangueCheckUp(ArrayCheckedLang, idLangEnCheck, idLangDefaut) {
					if(ArrayCheckedLang.length > 0)
						return ArrayCheckedLang[0];
					else
						return idLangDefaut;
				}
				
				$(document).ready(function() {
					$("input[name=\'languesup[]\']").click(function() {
						if(this.checked)
							changeLanguage(\'title\', \'' . $divLangName . '\', this.value, \'\');
						else {
							selectedL = new Array();
							$("input[name=\'languesup[]\']:checked").each(function() {selectedL.push($(this).val());});
							changeLanguage(\'title\', \'' . $divLangName . '\', RetourLangueCheckUp(selectedL, this.value, ' . $defaultLanguage . '), \'\');
						}
					});
					
					$("#submitForm").click(function() {';
        foreach ($languages as $language) {
            $this->_html .= '$(\'#slink_rewrite_' . $language['id_lang'] . '\').removeAttr("disabled");';
        }
        $this->_html .= '
						selectedLangues = new Array();
						$("input[name=\'languesup[]\']:checked").each(function() {selectedLangues.push($(this).val());});
						
						if (selectedLangues.length == 0) {
							alert("' . $this->l('You must choose at least one language !') . '");
							$("html, body").animate({scrollTop: $("#menu_config_prestablog").offset().top}, 300);
							$("#check_lang_prestablog").css("background-color", "#FFA300");
							return false;
						}
						else return true;
					});
					
					$("#control").toggle( 
						function () { 
							$(\'#slink_rewrite_\'+id_language).removeAttr("disabled");
							$(\'#slink_rewrite_\'+id_language).css("background-color", "#fff");
							$(\'#slink_rewrite_\'+id_language).css("color", "#000");
							$(this).html("' . $this->l('Disable this rewrite') . '");
						},
						function () { 
							$(\'#slink_rewrite_\'+id_language).attr("disabled", true);
							$(\'#slink_rewrite_\'+id_language).css("background-color", "#e0e0e0");
							$(\'#slink_rewrite_\'+id_language).css("color", "#7F7F7F");
							$(this).html("' . $this->l('Enable this rewrite') . '");
						} 
					);
					';

        foreach ($languages as $language) {
            $this->_html .= '
					if ($("#slink_rewrite_' . $language['id_lang'] . '").val() == \'\') { 
						$("#slink_rewrite_' . $language['id_lang'] . '").removeAttr("disabled");
						$("#slink_rewrite_' . $language['id_lang'] . '").css("background-color", "#fff");
						$("#slink_rewrite_' . $language['id_lang'] . '").css("color", "#000");
						$("#control").html("' . $this->l('Disable this rewrite') . '");
					}
					
					$("#paragraph_' . $language['id_lang'] . '").keyup(function(){  
						var limit = parseInt($(this).attr("maxlength"));
						var text = $(this).val();
						var chars = text.length;
						if(chars > limit){
							var new_text = text.substr(0, limit);
							$(this).val(new_text);
						}
						$("#compteur-texte-' . $language['id_lang'] . '").html(chars+" / "+limit);
					});';
        }

        $this->_html .= '
					$("#productLinkSearch").bind("keyup click focusin", function() {
						ReloadLinkedSearchProducts();
					});
					
					ReloadLinkedProducts();
				});
				
				function ReloadLinkedSearchProducts() {
					var listLinkedProducts="";
					$("input[name^=productsLink]").each(function() {
						listLinkedProducts += $(this).val() + ";";
					});
					
					if($("#productLinkSearch").val() != \'\' && $("#productLinkSearch").val().length >= 3) {
						var req=$("#productLinkSearch").attr("value"); 
						$.ajax({
							type: "GET",
							url: "../modules/' . $this->name . '/prestablog-ajax.php?do=searchProducts&token=' . Tools::getValue('token') . '&idE=' . $this->context->employee->id . '&idN=' . Tools::getValue('idN') . '&listLinkedProducts="+listLinkedProducts+"&req="+req,
							dataType : "html",
							error:function(msg, string){ alert( "Error !: " + string ); },
							success:function(data){
									$("#productLinkResult").empty();
									$("#productLinkResult").append(data);
								}
						});
					}
					else {
						$("#productLinkResult").empty();
						$("#productLinkResult").append(\'<tr><td colspan="4" class="center">' . $this->l('You must search before (3 cararc. minimum)') . '</td></tr>\');
					}
				}
				
				function ReloadLinkedProducts() {
					var req="";
					$("input[name^=productsLink]").each(function() {
						req += $(this).val() + ";";
					});
					$.ajax({
						type: "GET",
						url: "../modules/' . $this->name . '/prestablog-ajax.php?do=loadProductsLink&token=' . Tools::getValue('token') . '&idE=' . $this->context->employee->id . '&req="+req,
						dataType : "html",
						error:function(msg, string){ alert( "Error !: " + string ); },
						success:function(data){
								$("#productLinked").empty();
								$("#productLinked").append(data);
							}
					});
				}
			</script>
			
			<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
				' . (Tools::getValue('idN') ? '<input type="hidden" name="idN" value="' . Tools::getValue('idN') . '" />' : '') . '
				<fieldset style="width: 950px;">
					<legend style="margin-bottom:10px;">
						<img src="' . $this->_path . 'logo.gif" alt="" title="" />' . "\n";
        if (Tools::getValue('idN')) {
            $this->_html .= $this->l('Edit news') . ' #' . $News->id;
        } else {
            $this->_html .= $this->l('Add news');
        }
        $this->_html .= '</legend>';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Language') . '</label>
					<div class="margin-form" >
						<span id="check_lang_prestablog">
							' . (sizeof($languages) == 1 ? '' : '<input type="checkbox" name="checkmelang" class="noborder" onclick="checkDelBoxes(this.form, \'languesup[]\', this.checked)" /> ' . $this->l('All') . ' | ');
        foreach ($languages as $language) {
            $this->_html .= '<input type="checkbox" name="languesup[]" value="' . $language['id_lang'] . '" ' . ((Tools::getValue('idN') && in_array((int) $language['id_lang'], $LangListeNews))
                || (Tools::getValue('languesup') && in_array((int) $language['id_lang'], Tools::getValue('languesup')))
                || ((isset($defaultLang) && !Tools::getValue('languesup')) && ((int) $language['id_lang'] == (int) $defaultLang))
                || (sizeof($languages) == 1) ? 'checked=checked' : '') . ' ' . (sizeof($languages) == 1 ? 'style="display:none;"' : '') . ' />
							<img src="../img/l/' . (int) ($language['id_lang']) . '.jpg" class="pointer" alt="' . $language['name'] . '" title="' . $language['name'] . '" onclick="changeLanguage(\'title\', \'' . $divLangName . '\', ' . $language['id_lang'] . ', \'' . $language['iso_code'] . '\');"  />
							
						';
        }

        $this->_html .= '</span>
					<div class="clear"></div>
				</div>';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Main title') . '</label>
					<div class="margin-form">';

        foreach ($languages as $language) {
            $this->_html .= '
					<div id="title_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<input type="text" name="title_' . $language['id_lang'] . '" id="title_' . $language['id_lang'] . '" maxlength="' . $ConfigTheme->title_length . '" style="width:500px" value="' . (isset($News->title[$language['id_lang']]) ? $News->title[$language['id_lang']] : '') . '" 
						onkeyup="if (isArrowKey(event)) return; copy2friendlyURLPrestaBlog();" onchange="copy2friendlyURLPrestaBlog();"
						/><sup> *</sup>
					</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'title', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Activate') . '</label>
				<div class="margin-form">
					<input type="radio" name="actif" id="actif" value="1" ' . ($News->actif ? 'checked="checked" ' : '') . '/>
					<label class="t">
						<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
					</label>
					
					<input type="radio" name="actif" id="actif" value="0" ' . (!$News->actif ? 'checked="checked" ' : '') . '/>
					<label class="t">
						<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
					</label>
					<div class="clear"></div>
				</div>' . "\n";
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Slide') . '</label>
				<div class="margin-form">
					<input type="radio" name="slide" id="slide" value="1" ' . ($News->slide ? 'checked="checked" ' : '') . '/>
					<label class="t">
						<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
					</label>
					
					<input type="radio" name="slide" id="slide" value="0" ' . (!$News->slide ? 'checked="checked" ' : '') . '/>
					<label class="t">
						<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
					</label>
					<div class="clear"></div>
				</div>' . "\n";
        /***********************************************************/
        if (Tools::getValue('idN')) {
            $CommentsActif = CommentNewsClass::getListe(1, $News->id);
            $CommentsAll = CommentNewsClass::getListe(-2, $News->id);
            $CommentsNonLu = CommentNewsClass::getListe(-1, $News->id);
            $CommentsDisabled = CommentNewsClass::getListe(0, $News->id);
            $this->_html .= '
				<hr />
				<label id="labelComments">' . $this->l('Comments') . '</label>
				<div class="margin-form">
					' . ((sizeof($CommentsAll)) ? '<strong>' . count($CommentsActif) . '</strong> ' . $this->l('approuved') . ' ' . $this->l('of') . ' <strong>' . count($CommentsAll) . '</strong>' : $this->l('No comment')) . ((sizeof($CommentsNonLu)) ? '&nbsp;&mdash;-&nbsp;<span style="color:green;font-weight:bold;">' . count($CommentsNonLu) . ' pending</span>' : '') . '<br />
					' . ((sizeof($CommentsAll)) ? '<span onclick="$(\'#comments\').slideToggle();" style="cursor: pointer" class="link"><img src="../img/admin/cog.gif" alt="' . $this->l('Comments') . '" title="' . $this->l('Comments') . '" style="float:left; margin-right:5px;"/>' . $this->l('Click here to manage comments') . '</span>' : '') . '
					<div class="clear"></div>
				</div>' . "\n";
            if (sizeof($CommentsAll)) {
                if (Tools::isSubmit('showComments')) {
                    $this->_html .= '<div id="comments">' . "\n";
                    $this->_html .= '<script type="text/javascript">$(document).ready(function() { $("html, body").animate({scrollTop: $("#labelComments").offset().top}, 750); });</script>' . "\n";
                } else {
                    $this->_html .= '<div id="comments" style="display: none;">' . "\n";
                }

                $this->_html .= '
					<div class="blocs">
						<h3><img src="../modules/' . $this->name . '/img/question.gif" alt="' . $this->l('Pending') . '" title="' . $this->l('Pending') . '" />' . count($CommentsNonLu) . '&nbsp;' . $this->l('Comments pending') . '</h3>' . "\n";
                if (sizeof($CommentsNonLu)) {
                    $this->_html .= '<div class="wrap">' . "\n";
                    foreach ($CommentsNonLu As $KeyC => $ValueC) {
                        $this->_html .= '<div>' . "\n";
                        $this->_html .= '
							<h4>
								<a href="' . $this->PathModuleConf . '&deleteComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /><span style="display:none;">' . $this->l('Delete') . '</span></a>
								' . ToolsCore::displayDate($ValueC["date"], $this->context->language->id, true) . '<br />' . $this->l('by') . ' <strong>' . $ValueC["name"] . '</strong>
							</h4>' . "\n";
                        if ($ValueC["url"] != "") {
                            $this->_html .= '	<h5><a href="' . $ValueC["url"] . '" target="_blank">' . $ValueC["url"] . '</a></h5>' . "\n";
                        }
                        $this->_html .= '	<p>' . $ValueC["comment"] . '</p>' . "\n";
                        $this->_html .= '	
						<p class="center">
							<a href="' . $this->PathModuleConf . '&enabledComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/enabled.gif" alt="' . $this->l('Approuved') . '" /><span style="display:none;">' . $this->l('Approuved') . '</span></a>
							<a href="' . $this->PathModuleConf . '&editComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /><span style="display:none;">' . $this->l('Edit') . '</span></a>
							<a href="' . $this->PathModuleConf . '&disabledComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" /><span style="display:none;">' . $this->l('Disabled') . '</span></a>
						</p>' . "\n";
                        $this->_html .= '</div>' . "\n";
                    }
                    $this->_html .= '</div>' . "\n";
                }
                $this->_html .= '
					</div>' . "\n";

                $this->_html .= '
					<div class="blocs">
						<h3><img src="../img/admin/enabled.gif" alt="' . $this->l('Approuved') . '" title="' . $this->l('Approuved') . '" />' . count($CommentsActif) . '&nbsp;' . $this->l('Comments approuved') . '</h3>' . "\n";
                if (sizeof($CommentsActif)) {
                    $this->_html .= '<div class="wrap">' . "\n";
                    foreach ($CommentsActif As $KeyC => $ValueC) {
                        $this->_html .= '<div>' . "\n";
                        $this->_html .= '
							<h4>
								<a href="' . $this->PathModuleConf . '&deleteComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '"/><span style="display:none;">' . $this->l('Delete') . '</span></a>
								' . ToolsCore::displayDate($ValueC["date"], $this->context->language->id, true) . '<br />' . $this->l('by') . ' <strong>' . $ValueC["name"] . '</strong>
							</h4>' . "\n";
                        if ($ValueC["url"] != "") {
                            $this->_html .= '	<h5><a href="' . $ValueC["url"] . '" target="_blank">' . $ValueC["url"] . '</a></h5>' . "\n";
                        }
                        $this->_html .= '	<p>' . $ValueC["comment"] . '</p>' . "\n";
                        $this->_html .= '	
						<p class="center">
							<a href="' . $this->PathModuleConf . '&editComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /><span style="display:none;">' . $this->l('Edit') . '</span></a>
							<a href="' . $this->PathModuleConf . '&disabledComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/disabled.gif" alt="' . $this->l('Deleted') . '" /><span style="display:none;">' . $this->l('Disabled') . '</span></a>
						</p>' . "\n";
                        $this->_html .= '</div>' . "\n";
                    }
                    $this->_html .= '</div>' . "\n";
                }
                $this->_html .= '
					</div>' . "\n";

                $this->_html .= '
					<div class="blocs">
						<h3><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />' . count($CommentsDisabled) . '&nbsp;' . $this->l('Comments disabled') . '</h3>' . "\n";
                if (sizeof($CommentsDisabled)) {
                    $this->_html .= '<div class="wrap">' . "\n";
                    foreach ($CommentsDisabled As $KeyC => $ValueC) {
                        $this->_html .= '<div>' . "\n";
                        $this->_html .= '
							<h4>
								<a href="' . $this->PathModuleConf . '&deleteComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;"><img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '"/><span style="display:none;">' . $this->l('Delete') . '</span></a>
								' . ToolsCore::displayDate($ValueC["date"], $this->context->language->id, true) . '<br />' . $this->l('by') . ' <strong>' . $ValueC["name"] . '</strong>
							</h4>' . "\n";
                        if ($ValueC["url"] != "") {
                            $this->_html .= '	<h5><a href="' . $ValueC["url"] . '" target="_blank">' . $ValueC["url"] . '</a></h5>' . "\n";
                        }
                        $this->_html .= '	<p>' . $ValueC["comment"] . '</p>' . "\n";
                        $this->_html .= '	
						<p class="center">
							<a href="' . $this->PathModuleConf . '&editComment&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../modules/' . $this->name . '/img/edit.gif" alt="" /><span style="display:none;">' . $this->l('Edit') . '</span></a>
							<a href="' . $this->PathModuleConf . '&enabledComment&idN=' . Tools::getValue('idN') . '&idC=' . $ValueC['id_' . $this->name . '_commentnews'] . '" class="hrefComment" ><img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" /><span style="display:none;">' . $this->l('Approuved') . '</span></a>
						</p>' . "\n";
                        $this->_html .= '</div>' . "\n";
                    }
                    $this->_html .= '</div>' . "\n";
                }
                $this->_html .= '
					</div>' . "\n";

                $this->_html .= '
					</div>
					<div class="clear"></div>
					' . "\n";
                $this->_html .= '
					<script type="text/javascript">
						$(document).ready(function() { 
							$("a.hrefComment").mouseenter(function() { 
								$("span:first", this).show(\'slow\'); 
							}).mouseleave(function() { 
								$("span:first", this).hide(); 
							});
						});
					</script>' . "\n";
            }
        }
        /***********************************************************/
        $this->_html .= '
			<hr />
			<label>' . $this->l('SEO') . '</label>
			<div class="margin-form">
				<span onclick="$(\'#seo\').slideToggle();" style="cursor: pointer" class="link"><img src="../img/admin/cog.gif" alt="' . $this->l('SEO') . '" title="' . $this->l('SEO') . '" style="float:left; margin-right:5px;"/>' . $this->l('Click here to improve SEO') . '</span><br />
				<div class="clear"></div>
			</div>
			<div id="seo" style="display: none; padding-top: 15px;">';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Url Rewrite') . '<br/><a href="#" id="control" />' . (isset($News->id) ? $this->l('Enable this rewrite') : $this->l('Disable this rewrite')) . '</a></label>
					<div class="margin-form">';
        foreach ($languages as $language) {
            $this->_html .= '
					
					<div id="link_rewrite_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<input type="text" name="link_rewrite_' . $language['id_lang'] . '" id="slink_rewrite_' . $language['id_lang'] . '" value="' . (isset($News->link_rewrite[$language['id_lang']]) ? $News->link_rewrite[$language['id_lang']] : '') . '" 
						onkeyup="if (isArrowKey(event)) return ;updateFriendlyURLPrestaBlog();" onchange="updateFriendlyURLPrestaBlog();" 
						' . (isset($News->id) ? ' style="width:500px;color:#7F7F7F;background-color:#e0e0e0;" disabled="true"' : ' style="width:500px"') . '
						/><sup> *</sup>
					</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'link_rewrite', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Meta Title') . '</label>
					<div class="margin-form">';

        foreach ($languages as $language) {
            $this->_html .= '
					<div id="meta_title_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<input type="text" name="meta_title_' . $language['id_lang'] . '" id="meta_title_' . $language['id_lang'] . '" style="width:500px" value="' . (isset($News->meta_title[$language['id_lang']]) ? $News->meta_title[$language['id_lang']] : '') . '" />
					</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_title', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Meta Description') . '</label>
					<div class="margin-form">';

        foreach ($languages as $language) {
            $this->_html .= '
					<div id="meta_description_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<input type="text" name="meta_description_' . $language['id_lang'] . '" id="meta_description_' . $language['id_lang'] . '" style="width:500px" value="' . (isset($News->meta_description[$language['id_lang']]) ? $News->meta_description[$language['id_lang']] : '') . '" />
					</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_description', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>';
        /***********************************************************/
        $this->_html .= '<label>' . $this->l('Meta Keywords') . '</label>
					<div class="margin-form">';

        foreach ($languages as $language) {
            $this->_html .= '
					<div id="meta_keywords_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<input type="text" name="meta_keywords_' . $language['id_lang'] . '" id="meta_keywords_' . $language['id_lang'] . '" style="width:500px" value="' . (isset($News->meta_keywords[$language['id_lang']]) ? $News->meta_keywords[$language['id_lang']] : '') . '" />
					</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_keywords', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>';
        /***********************************************************/
        $this->_html .= '
				</div>
				<hr />';

        $this->_html .= '<label id="labelPicture">' . $this->l('Picture') . ' </label>
				<div class="margin-form">';
        if (Tools::getValue('idN')
            && file_exists(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg')
        ) {
            $ConfigThemeArray = objectToArray($ConfigTheme);
            if (Tools::getValue('pfx')) {
                $this->_html .= '<script type="text/javascript">$(document).ready(function() { $("html, body").animate({scrollTop: $("#labelPicture").offset().top}, 750); });</script>' . "\n";
            }
            $this->_html .= '
						<script src="' . $this->_path . 'js/Jcrop/jquery.Jcrop.prestablog.js"></script>
						<link rel="stylesheet" href="' . $this->_path . 'js/Jcrop/css/jquery.Jcrop.css" type="text/css" />
						<script language="Javascript">' . "\n";

            $this->_html .= '							var ratioValue = new Array();' . "\n";
            foreach ($ConfigThemeArray["images"] As $KeyThemeArray => $ValueThemeArray) {
                $this->_html .= '							ratioValue[\'' . $KeyThemeArray . '\'] = ' . $ValueThemeArray["width"] / $ValueThemeArray["height"] . ';' . "\n";
            }

            $this->_html .= '
							var monRatio;
							var monImage;
							
							$(function(){
								$("div.togglePreview").hide();' . "\n";
            if (Tools::getValue('pfx')) {
                $this->_html .= '
									$(\'input[name$="imageChoix"]\').filter(\'[value="' . Tools::getValue('pfx') . '"]\').attr(\'checked\', true);
									$(\'input[name$="imageChoix"]\').filter(\'[value="' . Tools::getValue('pfx') . '"]\').parent().next(1).slideDown();
									$("#pfx").val(\'' . Tools::getValue('pfx') . '\');
									$("#ratio").val(ratioValue[\'' . Tools::getValue('pfx') . '\']);
									monRatio = ratioValue[\'' . Tools::getValue('pfx') . '\'];
									$(\'#cropbox\').Jcrop({
										\'aspectRatio\' : monRatio,
										\'onSelect\' : updateCoords
									});
									nomImage = \'' . $this->l('Resize') . ' ' . Tools::getValue('pfx') . '\';
									$("#resizeBouton").val(nomImage);
									' . "\n";
            }
            $this->_html .= '
								$(\'input[name$="imageChoix"]\').change(function () {
									$("div.togglePreview").slideUp();
									$(this).parent().next().slideDown();
									$("#pfx").val($(this).val());
									$("#ratio").val(ratioValue[$(this).val()]);
									monRatio = ratioValue[$(this).val()];
									$(\'#cropbox\').Jcrop({
										\'aspectRatio\' : monRatio,
										\'onSelect\' : updateCoords
									});
									nomImage = \'' . $this->l('Resize') . ' \'+$("#pfx").val();
									$("#resizeBouton").val(nomImage);
								});
							});
							
							function updateCoords(c)
							{
								$(\'#x\').val(c.x);
								$(\'#y\').val(c.y);
								$(\'#w\').val(c.w);
								$(\'#h\').val(c.h);
							};
							function checkCoords()
							{
								if (!$(\'input[@name="imageChoix"]:checked\').val()) {
									alert(\'' . $this->l('Please select a picture to crop.') . '\');
									return false;
								}
								else {
									if (parseInt($(\'#w\').val())) 
										return true;
									alert(\'' . $this->l('Please select a crop region then press submit.') . '\');
									return false;
								}
							};
						</script>
						<div id="image" style="float:left;width:410px;" >
							<img style="float:left;" id="cropbox" src="' . $this->_path . 'themes/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg?' . md5(time()) . '" />
							<p align="center">' . $this->l('Filesize') . ' ' . (filesize(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/' . Tools::getValue('idN') . '.jpg') / 1000) . 'kb</p>
							<p>
								<a href="' . $this->PathModuleConf . '&deleteImage&idN=' . Tools::getValue('idN') . '" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
									<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /> ' . $this->l('Delete') . '
								</a>
							</p>
							<p><input type="file" name="homepage_logo" /></p>
						</div>' . "\n";

            foreach ($ConfigThemeArray["images"] As $KeyThemeArray => $ValueThemeArray) {
                $widthForce = '';
                if (file_exists(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/' . $KeyThemeArray . '_' . Tools::getValue('idN') . '.jpg')) {
                    $attribImage = getimagesize(dirname(__FILE__) . '/themes/' . Configuration::get($this->name . '_theme') . '/up-img/' . $KeyThemeArray . '_' . Tools::getValue('idN') . '.jpg');
                    if ((int) $attribImage[0] > 200) {
                        $widthForce = 'width="200"';
                    }
                }
                $this->_html .= '
								<div style="float:right;width:250px;border:1px solid #ccc;background-color:#fff;padding:5px;margin-bottom:10px;">
									<h3><input type="radio" name="imageChoix" value="' . $KeyThemeArray . '" />&nbsp;' . $KeyThemeArray . ' <span style="font-size: 80%;">(' . ($widthForce ? $this->l('Real size : ') : '') . $attribImage[0] . ' * ' . $attribImage[1] . ')</span></h3>
									<div class="togglePreview" style="text-align:center;">
										<hr />
										<img style="border:1px solid #4D4D4D;padding:0px;" src="' . $this->_path . 'themes/' . Configuration::get($this->name . '_theme') . '/up-img/' . $KeyThemeArray . '_' . Tools::getValue('idN') . '.jpg?' . md5(time()) . '" ' . $widthForce . ' />
									</div>
								</div>' . "\n";
            }
            $this->_html .= '
							<div style="text-align:center;float:right;width:250px;border:1px solid #ccc;background-color:#fff;padding:5px;margin-bottom:10px;">
								<input type="button" value="' . $this->l('Resize') . '" id="resizeBouton" class="button" onclick="if (checkCoords()) {formCrop.submit();}" />
							</div>' . "\n";

        } else {
            $this->_html .= '<p><input type="file" name="homepage_logo" /></p>';
        }
        $this->_html .= '
					<div class="clear"></div>
				</div>
				<label>' . $this->l('Introduction') . '</label>
				<div class="margin-form">';

        foreach ($languages as $language) {
            $this->_html .= '
					<div id="cpara1_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<textarea maxlength="' . $ConfigTheme->intro_length . '" style="width:500px;height:100px;" id="paragraph_' . $language['id_lang'] . '" name="paragraph_' . $language['id_lang'] . '">' . (isset($News->paragraph[$language['id_lang']]) ? $News->paragraph[$language['id_lang']] : '') . '</textarea>
						<p>' . $this->l('Caracters remaining') . ' : <span id="compteur-texte-' . $language['id_lang'] . '" style="color:red;">' . strlen($News->paragraph[$language['id_lang']]) . ' / ' . $ConfigTheme->intro_length . '</span>
						<br/>' . $this->l('You can configure the max lenght in the general configuration of the module theme.') . '</p>
					</div>';
        }

        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'cpara1', true);

        $this->_html .= '
					<div class="clear"></div>
				</div>
				
				<label>' . $this->l('Content') . '</label>
				<div class="margin-form">';

        foreach ($languages as $language) {
            $this->_html .= '
					<div id="cpara2_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
						<textarea class="rte" style="width:500px;height:100px;" id="content_' . $language['id_lang'] . '" name="content_' . $language['id_lang'] . '">' . (isset($News->content[$language['id_lang']]) ? $News->content[$language['id_lang']] : '') . '</textarea>
					</div>';
        }

        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'cpara2', true);

        $this->_html .= '
					<div class="clear"></div>
				</div>
				
				<label>' . $this->l('Categories') . ' </label>
				<div class="margin-form">
					<table cellspacing="0" cellpadding="0" class="table" style="font-size:110%;">
						<tr>
							<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categories[]\', this.checked)" /></th>
							<th>' . $this->l('ID') . '</th>
							<th style="width: 300px">' . $this->l('Name') . '</th>
						</tr>';
        $ListeCat = CategoriesClass::getListe((int) ($this->context->language->id), 0);
        static $irow;
        foreach ($ListeCat As $Key => $Value) {
            $this->_html .= '
					<tr class="' . ($irow++ % 2 ? 'alt_row' : '') . '">
						<td>
							<input type="checkbox" name="categories[]" value="' . (int) $Value["id_prestablog_categorie"] . '" ' . ((Tools::getValue('idN') && in_array((int) $Value["id_prestablog_categorie"], CorrespondancesCategoriesClass::getCategoriesListe((int) Tools::getValue('idN'))))
                || (Tools::getValue('categories') && in_array((int) $Value["id_prestablog_categorie"], Tools::getValue('categories')))
                || ((isset($defaultCat) && !Tools::getValue('categories')) && ((int) $Value["id_prestablog_categorie"] == (int) $defaultCat)) ? 'checked=checked' : '') . ' />
						</td>
						<td>' . $Value["id_prestablog_categorie"] . '</td>
						<td>' . $Value["title"] . '</td>
					</tr>';
        }

        $this->_html .= '
					</table>
				</div>
				
				<label>' . $this->l('Number of columns in the linked product slideshow') . ' </label>
				<div class="margin-form">
					<input type="text" name="nb_products_row" id="nb_products_row" value="' . $News->nb_products_row . '" />
					<p class="clear">' . $this->l('Default config value') . ' : ' . Configuration::get($this->name . '_nb_products_row') . '</p>
				</div>
				
				<label>' . $this->l('Products links') . ' </label>
				<div class="margin-form">
					<div id="currentProductLink" style="display:none;">' . "\n";

        if (Tools::getValue('idN')) {
            $ProductsLink = NewsClass::getProductLinkListe((int) Tools::getValue('idN'));

            if (sizeof($ProductsLink)) {
                foreach ($ProductsLink As $ProductLink) {
                    $this->_html .= '<input type="text" name="productsLink[]" value="' . (int) $ProductLink . '" class="linked_' . (int) $ProductLink . '" />' . "\n";
                }
            }
        }
        if (Tools::getValue('productsLink')
            && !Tools::getValue('idN')
        ) {
            foreach (Tools::getValue('productsLink') As $ProductLink) {
                $this->_html .= '<input type="text" name="productsLink[]" value="' . (int) $ProductLink["id_product"] . '" class="linked_' . (int) $ProductLink["id_product"] . '" />' . "\n";
            }
        }

        $this->_html .= '</div>
					<table style="width:100%" cellspacing="0" cellpadding="0" id="productLinkTable">
						<tr>
							<td style="padding-right:3px;width:50%;vertical-align:top;">
								<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
									<thead>
										<tr>
											<th class="center" style="width:30px;">' . $this->l('ID') . '</th>
											<th class="center" style="width:50px;">' . $this->l('Image') . '</th>
											<th class="center">' . $this->l('Name') . '</th>
											<th class="center" style="width:40px;">' . $this->l('Delink') . '</th>
										</tr>
									</thead>
									<tbody id="productLinked">
										<tr>
											<td colspan="4" class="center">' . $this->l('No product linked') . '</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td style="padding-left:3px;width:50%;vertical-align:top;">
								<p class="center">' . $this->l('Search') . ' : <input type="text" size="20" id="productLinkSearch" name="productLinkSearch" /></p>
								<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
									<thead>
										<tr>
											<th class="center" style="width:40px;">' . $this->l('Link') . '</th>
											<th class="center" style="width:30px;">' . $this->l('ID') . '</th>
											<th class="center" style="width:50px;">' . $this->l('Image') . '</th>
											<th class="center">' . $this->l('Name') . '</th>
										</tr>
									</thead>
									<tbody id="productLinkResult">
										<tr>
											<td colspan="4" class="center">' . $this->l('You must search before (3 cararc. minimum)') . '</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
					<p class="clear"></p>
				</div>';

        if ($News->date) {
            $Date = $News->date;
        } else {
            $Date = date("Y-m-d H:i:s");
        }
        $this->_html .= '<label>' . $this->l('Date') . ' </label>
				<div class="margin-form">
					<input type="text" size="20" id="date" class="date" name="date" value="' . htmlentities($Date, ENT_COMPAT, 'UTF-8') . '" />
					' . (($dateC = new DateTime($Date)) > ($now = new DateTime()) ? '<img src="../modules/' . $this->name . '/img/postdate.gif" alt="' . $this->l('Post Date') . '" />' : '') . '
					<p class="clear">' . $this->l('Format: YYYY-MM-DD HH:MM:SS') . '</p>
				</div>
				<div class="clear pspace"></div>
				<div class="margin-form clear">';

        $this->_html .= $this->ModuleDatepicker("date", true);

        if (Tools::getValue('idN')) {
            $this->_html .= '<input type="submit" id="submitForm" name="submitUpdateNews" value="' . $this->l('Update the content news') . '" class="button" />';
        } else {
            $this->_html .= '<input type="submit" id="submitForm" name="submitAddNews" value="' . $this->l('Add content') . '" class="button" />';
        }

        $this->_html .= '</div>
			</fieldset>
		</form>
		<form name="formCrop" id="formCrop" action="' . $this->PathModuleConf . '" method="post" onsubmit="return checkCoords();">
			<input type="hidden" name="idN" value="' . Tools::getValue('idN') . '" />
			<input type="hidden" id="pfx" name="pfx" value="' . Tools::getValue('pfx') . '" />
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" id="ratio" name="ratio" />
			<input type="hidden" name="submitCrop" value="submitCrop" />
		</form>
		<br />';
    }

    private function _displayFormCategories()
    {
        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(true);
        $iso = Language::getIsoById((int) ($this->context->language->id));
        $divLangName = 'title¤meta_title';

        if (Tools::getValue('idC')) {
            $Categories = new CategoriesClass((int) (Tools::getValue('idC')));
        } else {
            $Categories = new CategoriesClass();
            $Categories->copyFromPost();
        }
        // TinyMCE
        $this->_html .= '
			<script type="text/javascript">id_language = Number(' . $defaultLanguage . ');</script>
			
			<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
				' . (Tools::getValue('idC') ? '<input type="hidden" name="idC" value="' . Tools::getValue('idC') . '" />' : '') . '
				<fieldset style="width: 905px;">
					<legend style="margin-bottom:10px;">
						<img src="' . $this->_path . 'logo.gif" alt="" title="" />' . "\n";
        if (Tools::getValue('idC')) {
            $this->_html .= $this->l('Update the category');
        } else {
            $this->_html .= $this->l('Add a category');
        }
        $this->_html .= '</legend>
					
					<label>' . $this->l('Title') . '</label>
					<div class="margin-form">';
        foreach ($languages as $language) {
            $this->_html .= '
						<div id="title_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
							<input type="text" name="title_' . $language['id_lang'] . '" id="title_' . $language['id_lang'] . '" style="width:500px" value="' . (isset($Categories->title[$language['id_lang']]) ? $Categories->title[$language['id_lang']] : '') . '" /><sup> *</sup>
						</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'title', true);

        $this->_html .= '
						<div class="clear"></div>
					</div>
					
					<label>' . $this->l('Title Meta') . '</label>
					<div class="margin-form">';
        foreach ($languages as $language) {
            $this->_html .= '
						<div id="meta_title_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';float: left;">
							<input type="text" name="meta_title_' . $language['id_lang'] . '" id="meta_title_' . $language['id_lang'] . '" style="width:500px" value="' . (isset($Categories->meta_title[$language['id_lang']]) ? $Categories->meta_title[$language['id_lang']] : '') . '" />
						</div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_title', true);

        $this->_html .= '
						<div class="clear"></div>
					</div>
					
					<label>' . $this->l('Activate') . '</label>
					<div class="margin-form">
						<input type="radio" name="actif" id="actif" value="1" ' . ($Categories->actif ? 'checked="checked" ' : '') . '/>
						<label class="t">
							<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
						</label>
						
						<input type="radio" name="actif" id="actif" value="0" ' . (!$Categories->actif ? 'checked="checked" ' : '') . '/>
						<label class="t">
							<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
						</label>
						<div class="clear"></div>
					</div>
					<div class="clear pspace"></div>
					<div class="margin-form clear">';
        if (Tools::getValue('idC')) {
            $this->_html .= '<input type="submit" name="submitUpdateCat" value="' . $this->l('Update the category') . '" class="button" />';
        } else {
            $this->_html .= '<input type="submit" name="submitAddCat" value="' . $this->l('Add the category') . '" class="button" />';
        }

        $this->_html .= '</div>
			</fieldset>
		</form>
		<br />';
    }

    private function _displayFormComments()
    {
        if (Tools::getValue('idC')) {
            $Comment = new CommentNewsClass((int) (Tools::getValue('idC')));
        } else {
            $Comment = new CommentNewsClass();
            $Comment->copyFromPost();
        }
        // TinyMCE
        $this->_html .= '
			<form method="post" action="' . $this->PathModuleConf . '" enctype="multipart/form-data">
				' . (Tools::getValue('idC') ? '<input type="hidden" name="idC" value="' . Tools::getValue('idC') . '" />' : '') . '
				<fieldset style="width: 905px;">
					<legend style="margin-bottom:10px;">
						<img src="' . $this->_path . '/img/comments.gif" alt="" title="" />' . "\n";
        if (Tools::getValue('idC')) {
            $this->_html .= $this->l('Update the comment');
        } else {
            $this->_html .= $this->l('Add a comment');
        }
        $this->_html .= '</legend>' . "\n";

        $TitleNews = NewsClass::getTitleNews((int) ($Comment->news), (int) ($this->context->language->id));
        $this->_html .= '<label>' . $this->l('Parent news') . ' </label>
				<div class="margin-form">
					<a href="' . $this->PathModuleConf . '&editNews&idN=' . $Comment->news . '" style="font-size:110%;font-weight:bold;" onclick="return confirm(\'' . $this->l('You will leave this page. Are you sure ?') . '\');" >' . $TitleNews . '</a>
				</div>
				<div class="clear pspace"></div>' . "\n";

        $this->_html .= '<label>' . $this->l('Name') . ' </label>
				<div class="margin-form">
					<input type="text" id="name" name="name" value="' . $Comment->name . '" />
				</div>
				<div class="clear pspace"></div>' . "\n";

        $this->_html .= '<label>' . $this->l('Url') . ' </label>
				<div class="margin-form">
					<input type="text" id="url" name="url" value="' . $Comment->url . '" />
				</div>
				<div class="clear pspace"></div>' . "\n";

        $iso = Language::getIsoById((int) ($this->context->language->id));
        //$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
        $isoTinyMCE = (file_exists(_PS_ROOT_DIR_ . '/js/tinymce/jscripts/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en');
        $ad = dirname($_SERVER["PHP_SELF"]);
        $this->_html .= '
				<script type="text/javascript">	
				var iso = \'' . $isoTinyMCE . '\' ;
				var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
				var ad = \'' . $ad . '\' ;
				</script>
				<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/js/tinymce.inc.js"></script>';

        $this->_html .= '<label>' . $this->l('Comment') . ' </label>
				<div class="margin-form">
					<textarea class="rte" id="comment" name="comment">' . $Comment->comment . '</textarea>
				</div>
				<div class="clear pspace"></div>' . "\n";

        if ($Comment->date) {
            $Date = $Comment->date;
        } else {
            $Date = date("Y-m-d H:i:s");
        }
        $this->_html .= '<label>' . $this->l('Date') . ' </label>
				<div class="margin-form">
					<input type="text" size="20" id="date" class="date" name="date" value="' . htmlentities($Date, ENT_COMPAT, 'UTF-8') . '" />
					<p class="clear">' . $this->l('Format: YYYY-MM-DD HH:MM:SS') . '</p>
				</div>
				<div class="clear pspace"></div>' . "\n";

        $this->_html .= $this->ModuleDatepicker("date", true);

        $this->_html .= '<label>' . $this->l('Status') . '</label>
				<div class="margin-form">
					<select name="actif" id="actif">
						<option value="-1" ' . ((int) $Comment->actif == -1 ? ' selected' : '') . '>' . $this->l('Pending') . '</option>
						<option value="1" ' . ((int) $Comment->actif == 1 ? ' selected' : '') . '>' . $this->l('Enabled') . '</option>
						<option value="0" ' . ((int) $Comment->actif == 0 ? ' selected' : '') . '>' . $this->l('Disabled') . '</option>
					</select>
				</div>
				<div class="clear pspace"></div>' . "\n";

        $this->_html .= '<div class="margin-form clear">';
        if (Tools::getValue('idC')) {
            $this->_html .= '<input type="submit" name="submitUpdateComment" value="' . $this->l('Update the comment') . '" class="button" />';
            $this->_html .= '<a href="' . $this->PathModuleConf . '&deleteComment&idC=' . $Comment->id . '" title="' . $this->l('Delete the comment') . '" class="button" style="margin-left:10px;padding:4px;" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" />' . $this->l('Delete the comment') . '</a>';
        } else {
            $this->_html .= '<input type="submit" name="submitAddComment" value="' . $this->l('Add the comment') . '" class="button" />';
        }

        $this->_html .= '</div>
			</fieldset>
		</form>
		<br />';
    }

    private function deleteAllImagesThemes($id)
    {
        foreach ($this->ScanDirectory(_PS_MODULE_DIR_ . $this->name . '/themes') As $KeyTheme => $ValueTheme) {
            $ConfigTheme = $this->_getConfigXmlTheme($ValueTheme);
            $ConfigThemeArray = objectToArray($ConfigTheme);
            foreach ($ConfigThemeArray["images"] As $KeyThemeArray => $ValueThemeArray) {
                @unlink(_PS_MODULE_DIR_ . $this->name . '/themes/' . $ValueTheme . '/up-img/' . $KeyThemeArray . '_' . $id . '.jpg');
            }
            @unlink(_PS_MODULE_DIR_ . $this->name . '/themes/' . $ValueTheme . '/up-img/' . $id . '.jpg');
            @unlink(_PS_MODULE_DIR_ . $this->name . '/themes/' . $ValueTheme . '/up-img/admincrop_' . $id . '.jpg');
            @unlink(_PS_MODULE_DIR_ . $this->name . '/themes/' . $ValueTheme . '/up-img/adminth_' . $id . '.jpg');
        }

        return true;
    }

    private function deleteAllThemesConfig()
    {
        foreach ($this->ScanDirectory(_PS_MODULE_DIR_ . $this->name . '/themes') As $KeyTheme => $ValueTheme) {
            self::effacementDossier(_PS_MODULE_DIR_ . $this->name . '/themes/' . $ValueTheme . '/up-img/');
            //@unlink(_PS_MODULE_DIR_.$this->name.'/themes/'.$ValueTheme.'/config.xml');
        }

        return true;
    }

    private function effacementDossier($dossier)
    {
        $ouverture = @opendir($dossier);
        if (!$ouverture) {
            return;
        }
        while ($fichier = readdir($ouverture)) {
            if ($fichier == '.' || $fichier == '..') {
                continue;
            }
            if (is_dir($dossier . "/" . $fichier)) {
                $r = clearDir($dossier . "/" . $fichier);
                if (!$r) {
                    return false;
                }
            } else {
                $r = @unlink($dossier . "/" . $fichier);
                if (!$r) {
                    return false;
                }
            }
        }
        closedir($ouverture);
        if (!$r) {
            return false;
        }

        return true;
    }

    private function UploadImage($file_image, $id, $w, $h)
    {
        if (isset($file_image) AND isset($file_image['tmp_name']) AND !empty($file_image['tmp_name'])) {
            Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
            if ($error = ImageManager::validateUpload($file_image, $this->maxImageSize)) {
                return false;
            } elseif (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($file_image['tmp_name'], $tmpName)) {
                return false;
            } else {
                foreach ($this->ScanDirectory(_PS_MODULE_DIR_ . $this->name . '/themes') As $KeyTheme => $ValueTheme) {
                    $ConfigTheme = $this->_getConfigXmlTheme($ValueTheme);
                    if (!$this->ImageResize($tmpName, dirname(__FILE__) . '/themes/' . $ValueTheme . '/up-img/' . $id . '.jpg', $w, $h)
                    ) {
                        return false;
                    }
                }
            }
            if (isset($tmpName)) {
                unlink($tmpName);
            }
        }

        return true;
    }

    private function ImageResize($fichier_avant, $fichier_apres, $dest_width, $dest_height)
    {
        list($image_width, $image_height, $type, $attr) = getimagesize($fichier_avant);
        $sourceImage = ImageManager::create($type, $fichier_avant);

        if ($image_width > $dest_width || $image_height > $dest_height) {
            $proportion = $dest_width / $image_width;
            $dest_height = $image_height * $proportion;
            $dest_width = $dest_width;
        } else {
            $dest_height = $image_height;
            $dest_width = $image_width;
        }
        $destImage = imagecreatetruecolor($dest_width, $dest_height);
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $dest_width + 1, $dest_height + 1, $image_width, $image_height);

        return (ImageManager::write('jpg', $destImage, $fichier_apres));
    }

    private function ScanDirectory($Directory)
    {
        $output = array();
        $MyDirectory = opendir($Directory); // or die('Erreur');

        while ($Entry = @readdir($MyDirectory)) {
            if ($Entry != '.' && $Entry != '..') {
                if (is_dir($Directory . '/' . $Entry)) {
                    $output[] = $Entry;
                }
            }
        }
        closedir($MyDirectory);

        return $output;
    }

    static public function _getConfigXmlTheme($theme)
    {
        $configFile = _PS_MODULE_DIR_ . 'prestablog/themes/' . $theme . '/config.xml';
        $xml_exist = file_exists($configFile);

        if ($xml_exist) {
            return simplexml_load_file($configFile);
        } else {
            self::_generateConfigXmlTheme($theme);

            return self::_getConfigXmlTheme($theme);
        }
    }

    private function RetourneTexteBalise($text, $debut, $fin)
    {
        $debut = strpos($text, $debut) + strlen($debut);
        $fin = strpos($text, $fin);

        return substr($text, $debut, $fin - $debut);
    }

    protected function _generateConfigXmlTheme($theme)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
<theme>
	<images>
		<thumb> <!--Image prévue pour les miniatures dans les listes -->
			<width>' . Configuration::get($this->name . '_thumb_picture_width') . '</width>
			<height>' . Configuration::get($this->name . '_thumb_picture_height') . '</height>
		</thumb>
		<slide> <!--Image prévue pour les slides -->
			<width>' . Configuration::get($this->name . '_slide_picture_width') . '</width>
			<height>' . Configuration::get($this->name . '_slide_picture_height') . '</height>
		</slide>
	</images>
	<title_length>' . Configuration::get($this->name . '_title_length') . '</title_length>
	<intro_length>' . Configuration::get($this->name . '_intro_length') . '</intro_length>
	<slide_speed>' . Configuration::get($this->name . '_speed') . '</slide_speed>
	<slide_timeout>' . Configuration::get($this->name . '_timeout') . '</slide_timeout>
	<slide_effect>' . Configuration::get($this->name . '_effect') . '</slide_effect>
</theme>';
        if (is_writable(_PS_MODULE_DIR_ . $this->name . '/themes/' . $theme . '/')) {
            file_put_contents(_PS_MODULE_DIR_ . $this->name . '/themes/' . $theme . '/config.xml', utf8_encode($xml));
        }
    }

    private function _cleanMetaKeywords($keywords)
    {
        if (!empty($keywords) && $keywords != '') {
            $out = array();
            $words = explode(',', $keywords);
            foreach ($words as $word_item) {
                $word_item = trim($word_item);
                if (!empty($word_item) && $word_item != '') {
                    $out[] = $word_item;
                }
            }

            return ((count($out) > 0) ? implode(',', $out) : '');
        } else {
            return '';
        }
    }

    static public function prestablog_ajax_search_url($params)
    {
        $base = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));

        $base .= __PS_BASE_URI__ . 'modules/prestablog/prestablog-ajax.php';

        return $base;
    }

    static public function prestablog_url($params)
    {
        $param = null;
        $ok_rewrite = '';
        $ok_rewrite_id = '';
        $ok_rewrite_do = '';
        $ok_rewrite_cat = '';
        $ok_rewrite_categorie = '';
        $ok_rewrite_page = '';
        $ok_rewrite_titre = '';
        $ok_rewrite_seo = '';
        $ok_rewrite_year = '';
        $ok_rewrite_month = '';

        $ko_rewrite = '';
        $ko_rewrite_id = '';
        $ko_rewrite_do = '';
        $ko_rewrite_cat = '';
        $ko_rewrite_page = '';
        $ko_rewrite_year = '';
        $ko_rewrite_month = '';

        if (isset($params["do"]) && $params["do"] != "") {
            $ko_rewrite_do = 'do=' . $params["do"];
            $ok_rewrite_do = $params["do"];
            $param += 1;
        }
        if (isset($params["id"]) && $params["id"] != "") {
            $ko_rewrite_id = '&id=' . $params["id"];
            $ok_rewrite_id = '-n' . $params["id"];
            $param += 1;
        }
        if (isset($params["c"]) && $params["c"] != "") {
            $ko_rewrite_cat = '&c=' . $params["c"];
            $ok_rewrite_cat = '-c' . $params["c"];
            $param += 1;
        }
        if (isset($params["start"]) && isset($params["p"]) && $params["start"] != "" && $params["p"] != "") {
            $ko_rewrite_page = '&start=' . $params["start"] . '&p=' . $params["p"];
            $ok_rewrite_page = $params["start"] . 'p' . $params["p"];
            $param += 1;
        }
        if (isset($params["titre"]) && $params["titre"] != "") {
            $ok_rewrite_titre = PrestaBlog::prestablog_filter(Tools::link_rewrite($params["titre"]));
            $param += 1;
        }
        if (isset($params["categorie"]) && $params["categorie"] != "") {
            $ok_rewrite_categorie = PrestaBlog::prestablog_filter(Tools::link_rewrite($params["categorie"])) . (isset($params["start"]) && isset($params["p"]) && $params["start"] != "" && $params["p"] != "" ? '-' : '');
            $param += 1;
        }
        if (isset($params["seo"]) && $params["seo"] != "") {
            $ok_rewrite_titre = PrestaBlog::prestablog_filter(Tools::link_rewrite($params["seo"]));
            $param += 1;
        }
        if (isset($params["y"]) && $params["y"] != "") {
            $ko_rewrite_year = '&y=' . $params["y"];
            $ok_rewrite_year = 'y' . $params["y"];
            $param += 1;
        }
        if (isset($params["m"]) && $params["m"] != "") {
            $ko_rewrite_month = '&m=' . $params["m"];
            $ok_rewrite_month = '-m' . $params["m"];
            $param += 1;
        }
        if (isset($params["seo"]) && $params["seo"] != "") {
            $ok_rewrite_seo = $params["seo"];
            $ok_rewrite_titre = "";
            $param += 1;
        }

        if (sizeof($param) && !isset($params["rss"])) {
            $ok_rewrite = 'prestablog-' . $ok_rewrite_do . $ok_rewrite_categorie . $ok_rewrite_page . $ok_rewrite_year . $ok_rewrite_month . $ok_rewrite_titre . $ok_rewrite_seo . $ok_rewrite_cat . $ok_rewrite_id . '/default';
            $ko_rewrite = '?fc=module&module=prestablog&' . ltrim($ko_rewrite_do . $ko_rewrite_id . $ko_rewrite_cat . $ko_rewrite_page . $ko_rewrite_year . $ko_rewrite_month, "&");
        } elseif (isset($params["rss"])) {
            $ok_rewrite = 'prestablog-rss-' . $params["rss"] . '/default';
            $ko_rewrite = '?fc=module&module=prestablog&rss=' . $params["rss"];
        } else {
            $ok_rewrite = 'module/prestablog/default';
            $ko_rewrite = '?fc=module&module=prestablog';
        }

        if (intval(Configuration::get('PS_REWRITING_SETTINGS')) && intval(Configuration::get('prestablog_rewrite_actif'))) {
            return self::getBaseUrlFront() . $ok_rewrite;
        } else {
            return self::getBaseUrlFront() . $ko_rewrite;
        }
    }

    static public function getBaseUrlFront()
    {
        $base = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));

        $base .= __PS_BASE_URI__ . self::getLangLink();

        return $base;
    }

    static public function getLangLink($id_lang = null)
    {
        $context = Context::getContext();

        if (!Configuration::get('PS_REWRITING_SETTINGS')) {
            return '';
        }
        if (Language::countActiveLanguages() <= 1) {
            return '';
        }
        if (!$id_lang) {
            $id_lang = $context->language->id;
        }

        return Language::getIsoById((int) $id_lang) . '/';
    }

    static public function prestablog_filter($in)
    {
        $search = array(
            '/--+/'
        );
        $replace = array(
            '-'
        );

        $retourne = strtolower(preg_replace($search, $replace, $in));

        return $retourne;
    }

    static public function getPrestaBlogMetaTagsNewsOnly($id_lang, $id = null)
    {
        if ($id) {
            $row = array();

            $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `title`, `meta_title`, `meta_description`, `meta_keywords`
			FROM `' . _DB_PREFIX_ . 'prestablog_news_lang`
			WHERE id_lang = ' . (int) ($id_lang) . ' AND id_prestablog_news = ' . (int) ($id));
        }
        if ($row) {
            return self::completeMetaTags($row, $row['title']);
        }
    }

    static public function getPrestaBlogMetaTagsNewsCat($id_lang, $id = null)
    {
        if ($id) {
            $row = array();

            $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `title`, `meta_title`
			FROM `' . _DB_PREFIX_ . 'prestablog_categorie_lang`
			WHERE id_lang = ' . (int) ($id_lang) . ' AND id_prestablog_categorie = ' . (int) ($id));
        }
        if ($row) {
            return self::completeMetaTags($row, $row['title']);
        }
    }

    static public function getPrestaBlogMetaTagsPage($id_lang)
    {
        return self::completeMetaTags(null, Configuration::get('prestablog_titlepageblog_' . $id_lang));
    }

    static public function getPrestaBlogMetaTagsNewsDate()
    {
        return self::completeMetaTags(null, null);
    }

    public static function completeMetaTags($metaTags, $defaultValue)
    {
        $context = Context::getContext();

        $PrestaBlog = new PrestaBlog();

        if (empty($metaTags['meta_title'])) {
            $metaTags['meta_title'] = ($defaultValue ? $defaultValue . ' - ' : '') . Configuration::get('PS_SHOP_NAME');
        }
        if (empty($metaTags['meta_description'])) {
            $metaTags['meta_description'] = Configuration::get('PS_META_DESCRIPTION', (int) ($context->language->id)) ? Configuration::get('PS_META_DESCRIPTION', (int) ($context->language->id)) : '';
        }
        if (empty($metaTags['meta_keywords'])) {
            $metaTags['meta_keywords'] = Configuration::get('PS_META_KEYWORDS', (int) ($context->language->id)) ? Configuration::get('PS_META_KEYWORDS', (int) ($context->language->id)) : '';
        }

        $metaTags['meta_title'] .= (Tools::getValue('p') ? ' - ' . $PrestaBlog->l('page') . ' ' . Tools::getValue('p') : '');
        $metaTags['meta_title'] .= (Tools::getValue('y') ? ' - ' . Tools::getValue('y') : '');
        $metaTags['meta_title'] .= (Tools::getValue('m') ? ' - ' . $PrestaBlog->MoisLangue[Tools::getValue('m')] : '');

        return $metaTags;
    }

    static public function getPagination($CountListe, // nombre total d'entités
        $EntitesEnMoins = 0, // enlever les premières entités
        $End = 10, $Start = 0, $p = 1)
    {
        $Pagination = array();

        $Pagination["NombreTotalEntites"] = ($CountListe - $EntitesEnMoins);

        $Pagination["NombreTotalPages"] = ceil(intval($Pagination["NombreTotalEntites"]) / intval($End));

        if ($Pagination["NombreTotalEntites"] > 0) {
            if ($p) {
                $Pagination["PageCourante"] = intval($p);
                $Pagination["PagePrecedente"] = intval($p) - 1;
                $Pagination["PageSuivante"] = intval($p) + 1;
            } else {
                $Pagination["PageCourante"] = 1;
                $Pagination["PagePrecedente"] = 0;
                $Pagination["PageSuivante"] = 2;
            }

            if ($Start) {
                $Pagination["StartCourant"] = intval($Start);
                $Pagination["StartPrecedent"] = intval($Start) - intval($End);
                $Pagination["StartSuivant"] = intval($Start) + intval($End);
            } else {
                $Pagination["StartCourant"] = 0;
                $Pagination["StartPrecedent"] = 0;
                $Pagination["StartSuivant"] = intval($End);
            }
            for ($icount = 1; $icount <= intval($Pagination["NombreTotalPages"]); $icount++) {
                $Pagination["Pages"][$icount] = ($icount - 1) * intval($End);
            }

            if (count($Pagination["Pages"]) <= 5) {
                $Pagination["PremieresPages"] = array_slice($Pagination["Pages"], 0, 5, true);
                unset($Pagination["Pages"]);
            } else {
                $Pagination["PremieresPages"] = array_slice($Pagination["Pages"], 0, 1, true);
                if ($Pagination["PageCourante"] == 1) {
                    $Pagination["Pages"] = array_slice($Pagination["Pages"], $Pagination["PageCourante"] - 1, 6, true);
                } else {
                    if ($Pagination["PageCourante"] + 4 >= $Pagination["NombreTotalPages"]) {
                        $Pagination["Pages"] = array_slice($Pagination["Pages"], ($Pagination["NombreTotalPages"] - 5), 5, true);
                    } else {
                        $Pagination["Pages"] = array_slice($Pagination["Pages"], $Pagination["PageCourante"] - 1, 5, true);
                    }
                }
            }
        }

        return $Pagination;
    }

    public function AutoCropImage($ImageSource, $RepSource, $RepDest, $tl, // width
        $th, // height
        $Prefixe, $ChangeNom)
    {
        $tl = intval($tl);
        $th = intval($th);
        $tr = $tl / $th;

        $full_path = $RepSource . $ImageSource;
        $basefilename = preg_replace("/(.*)\.([^.]+)$/", "\\1", $ImageSource);
        $extensionsource = preg_replace("/.*\.([^.]+)$/", "\\1", $ImageSource);

        switch ($extensionsource) {
        case 'png':
            $imagesource = imagecreatefrompng($full_path);
            break;

        case 'jpg':
            $imagesource = imagecreatefromjpeg($full_path);
            break;

        case 'jpeg':
            $imagesource = imagecreatefromjpeg($full_path);
            break;

        default:
            //$this->message_err('ATTENTION ! La librairie GD ne supporte pas cette extension => '.$ext, '', '', '');
            break;
        }

        $sl = imagesx($imagesource);
        $sh = imagesy($imagesource);

        $sr = $sl / $sh;

        if ($sr > $tr) {
            $nh = $th;
            $nl = intval((($nh * $sl) / $sh));
        } elseif ($sr < $tr) {
            $nl = $tl;
            $nh = intval((($nl * $sh) / $sl));
        } elseif ($sr == $tr) {
            $nh = $th;
            $nl = $tl;
        }

        if ($tr > 1) {
            $nx = 0;
            $ny = intval((($nh - $th) / 2));
        } elseif ($tr < 1) {
            $ny = 0;
            $nx = intval((($nl - $tl) / 2));
        } elseif ($tr == 1) {
            if ($sr > 1) {
                $ny = 0;
                $nx = intval((($nl - $tl) / 2));
            } elseif ($sr < 1) {
                $nx = 0;
                $ny = intval((($nh - $th) / 2));
            } elseif ($sr == 1) {
                $nx = 0;
                $ny = 0;
            }
        }

        $image_avant_crop = imagecreatetruecolor($nl, $nh);

        imagecopyresampled($image_avant_crop, $imagesource, 0, 0, 0, 0, $nl, $nh, $sl, $sh);

        $dest_crop = imagecreatetruecolor($tl, $th);

        imagecopyresampled($dest_crop, $image_avant_crop, 0, 0, $nx, $ny, $tl, $th, $tl, $th);

        if ($ChangeNom) {
            $ImageSource = $ChangeNom . '.jpg';
        }

        switch ($extensionsource) {
        case 'png':
            imagepng($dest_crop, $RepDest . $Prefixe . $ImageSource);
            break;
        case 'jpg':
            imagejpeg($dest_crop, $RepDest . $Prefixe . $ImageSource);
            break;
        case 'jpeg':
            imagejpeg($dest_crop, $RepDest . $Prefixe . $ImageSource);
            break;
        }
        imagedestroy($image_avant_crop);
        imagedestroy($dest_crop);
    }

    public function CropImage($ImageSource, $RepSource, $RepDest, $W_Image_Base, // width de l'image sur lequel le crop a été selectionné
        $H_Image_Base, // heigth de l'image sur lequel le crop a été selectionné
        $W_Image_Dest, // width du crop final
        $H_Image_Dest, // heigth du crop final
        $X_Crop_Base, // position horizontal du point de départ du crop selectionné
        $Y_Crop_Base, // position vertical du point de départ du crop selectionné
        $W_Crop_Base, // width de la selection du crop
        $H_Crop_Base, // heigth de la selection du crop
        $Prefixe, $ChangeNom)
    {
        $full_path = $RepSource . $ImageSource;
        $ext = preg_replace("/.*\.([^.]+)$/", "\\1", $ImageSource);
        $dst_r = ImageCreateTrueColor($W_Image_Dest, $H_Image_Dest);

        list($W_Image_Source, $H_Image_Source, $type, $attr) = getimagesize($full_path);

        $W_Ratio = $W_Image_Source / $W_Image_Base;
        $H_Ratio = $H_Image_Source / $H_Image_Base;

        $X_Crop_Base = intval($W_Ratio * $X_Crop_Base);
        $Y_Crop_Base = intval($H_Ratio * $Y_Crop_Base);
        $W_Crop_Base = intval($W_Ratio * $W_Crop_Base);
        $H_Crop_Base = intval($H_Ratio * $H_Crop_Base);

        switch ($ext) {
        case 'png':
            $image = imagecreatefrompng($full_path);
            break;
        case 'jpg':
            $image = imagecreatefromjpeg($full_path);
            break;
        case 'jpeg':
            $image = imagecreatefromjpeg($full_path);
            break;
        default:
            break;
        }
        imagecopyresampled($dst_r, $image, 0, 0, $X_Crop_Base, $Y_Crop_Base, $W_Image_Dest, $H_Image_Dest, $W_Crop_Base, $H_Crop_Base);

        if ($ChangeNom) {
            $ImageSource = $ChangeNom . '.jpg';
        }

        switch ($ext) {
        case 'png':
            imagepng($dst_r, $RepDest . $Prefixe . $ImageSource);
            break;
        case 'jpg':
            imagejpeg($dst_r, $RepDest . $Prefixe . $ImageSource);
            break;
        case 'jpeg':
            imagejpeg($dst_r, $RepDest . $Prefixe . $ImageSource);
            break;
        }
        imagedestroy($dst_r);
    }

    public function gestComment($news)
    {
        if (!Configuration::get($this->name . '_comment_actif')) {
            return false;
        }

        $errors = array();
        $isSubmit = true;
        $content_form = Array(
            "news"    => (int) $news, "name" => trim(Tools::getValue('name')), "url" => trim(Tools::getValue('url')),
            "comment" => trim(Tools::getValue('comment')), "date" => Date("Y/m/d H:i:s"),
            "actif"   => (Configuration::get($this->name . '_comment_auto_actif') ? 1 : -1)
        );

        if (Tools::getValue('submitComment')) {
            $EregUrl = "#^\b(((http|https)\:\/\/)[^\s()]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))$#";
            if (strlen($content_form["name"]) < 3) {
                $errors["name"] = $this->l('Your name cannot be empty or inferior at 3 characters.');
            }
            if (strlen($content_form["comment"]) < 5) {
                $errors["comment"] = $this->l('Your comment cannot be empty or inferior at 5 characters.');
            }
            if (strlen($content_form["url"]) != "" && !preg_match($EregUrl, $content_form["url"])) {
                $errors["url"] = $this->l('Make sure the url is correct.');
            }

            if (sizeof($errors)) {
                $isSubmit = false;
            } else {
                CommentNewsClass::insertComment($content_form["news"], $content_form["date"], $content_form["name"], $content_form["url"], $content_form["comment"], $content_form["actif"]);

                if (Configuration::get($this->name . '_comment_alert_admin')) {
                    $News = new NewsClass($content_form["news"], (int) Configuration::get('PS_LANG_DEFAULT'));
                    $content_form["title_news"] = $News->title;

                    Mail::Send((int) Configuration::get('PS_LANG_DEFAULT'), // langue
                        'feedback-admin', // template
                        $this->l('New comment') . ' / ' . $content_form["title_news"], // sujet
                        array( // templatevars
                            '{news}'     => $content_form["news"], '{title_news}' => $content_form["title_news"], '{date}' => $content_form["date"],
                            '{name}'     => $content_form["name"], '{url}' => $content_form["url"], '{comment}' => $content_form["comment"],
                            '{url_news}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . '?fc=module&module=prestablog&id=' . $content_form["news"],
                            '{actif}'    => $content_form["actif"]
                        ), Configuration::get($this->name . '_comment_admin_mail'), // destinataire mail
                        null, // destinataire nom
                        strval(Configuration::get('PS_SHOP_EMAIL')), // expéditeur
                        strval(Configuration::get('PS_SHOP_NAME')), // expéditeur nom
                        null, // fichier joint
                        null, // mode smtp
                        dirname(__FILE__) . '/mails/' // répertoire des mails templates
                    );
                }

                $ListeAbo = CommentNewsClass::listeCommentMailAbo($content_form["news"]);

                if (Configuration::get($this->name . '_comment_subscription')
                    && sizeof($ListeAbo)
                    && Configuration::get($this->name . '_comment_auto_actif')
                ) {

                    $News = new NewsClass($content_form["news"], (int) Configuration::get('PS_LANG_DEFAULT'));
                    $content_form["title_news"] = $News->title;

                    foreach ($ListeAbo As $ValueAbo) {
                        Mail::Send((int) Configuration::get('PS_LANG_DEFAULT'), // langue
                            'feedback-subscribe', // template
                            $this->l('New comment') . ' / ' . $content_form["title_news"], // sujet
                            array( // templatevars
                                '{news}'              => $content_form["news"], '{title_news}' => $content_form["title_news"],
                                '{url_news}'          => Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . '?fc=module&module=prestablog&id=' . $content_form["news"],
                                '{url_desabonnement}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . '?fc=module&module=prestablog&d=' . $content_form["news"]
                            ), $ValueAbo, // destinataire mail
                            null, // destinataire nom
                            strval(Configuration::get('PS_SHOP_EMAIL')), // expéditeur
                            strval(Configuration::get('PS_SHOP_NAME')), // expéditeur nom
                            null, // fichier joint
                            null, // mode smtp
                            dirname(__FILE__) . '/mails/' // répertoire des mails templates
                        );
                    }
                }

                $isSubmit = true;
            }
        } else {
            $isSubmit = false;
        }

        $this->context->smarty->assign(array(
                'isSubmit'           => $isSubmit, 'errors' => $errors, 'content_form' => $content_form, 'comments' => CommentNewsClass::getListe(1, $news),
                'comment_only_login' => Configuration::get($this->name . '_comment_only_login'),
                'comment_auto'       => Configuration::get($this->name . '_comment_auto_actif'),
                'link_nofollow'      => Configuration::get($this->name . '_comment_nofollow')
            ));

        return true;
    }

    public function blocDateListe()
    {
        if (Configuration::get($this->name . '_datenews_actif')) {
            $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));

            $ResultDateListe = Array();

            $FinReel = 'TIMESTAMP(n.`date`) <= \'' . Date("Y/m/d H:i:s") . '\'';

            $ResultAnnee = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
				SELECT	DISTINCT YEAR(n.`date`) AS `annee`
				FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` As nl
				LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` As n
					ON (n.id_prestablog_news = nl.id_prestablog_news)
				WHERE n.`actif` = 1
				AND nl.`id_lang` = ' . (int) $this->context->language->id . '
				AND nl.`actif_langue` = 1
				AND ' . $FinReel . '
				ORDER BY annee ' . Configuration::get($this->name . '_datenews_order'));

            if (sizeof($ResultAnnee)) {
                foreach ($ResultAnnee As $ValueAnnee) {
                    $ResultCountAnnee = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
						SELECT COUNT(DISTINCT nl.`id_prestablog_news`) AS `value`
						FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` As nl
						LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` As n
							ON (n.id_prestablog_news = nl.id_prestablog_news)
						WHERE n.`actif` = 1
						AND nl.`id_lang` = ' . (int) $this->context->language->id . '
						AND nl.`actif_langue` = 1
						AND ' . $FinReel . '
						AND YEAR(n.`date`) = \'' . $ValueAnnee["annee"] . '\'');

                    $ResultDateListe[$ValueAnnee["annee"]]["nombre_news"] = $ResultCountAnnee["value"];

                    $ResultMois = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
						SELECT	DISTINCT MONTH(n.`date`) AS `mois`
						FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` As nl
						LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` As n
							ON (n.id_prestablog_news = nl.id_prestablog_news)
						WHERE n.`actif` = 1
						AND nl.`id_lang` = ' . (int) $this->context->language->id . '
						AND nl.`actif_langue` = 1
						AND YEAR(n.`date`) = ' . $ValueAnnee["annee"] . '
						AND ' . $FinReel . '
						ORDER BY mois ' . Configuration::get($this->name . '_datenews_order'));

                    if (sizeof($ResultMois)) {
                        foreach ($ResultMois As $ValueMois) {
                            $ResultCountMois = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
								SELECT COUNT(DISTINCT n.`id_prestablog_news`) AS `value`
								FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` As nl
								LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` As n
									ON (n.id_prestablog_news = nl.id_prestablog_news)
								WHERE n.`actif` = 1
								AND nl.`id_lang` = ' . (int) $this->context->language->id . '
								AND nl.`actif_langue` = 1
								AND ' . $FinReel . '
								AND YEAR(n.`date`) = ' . $ValueAnnee["annee"] . ' AND MONTH(n.`date`) = ' . $ValueMois["mois"]);

                            $ResultDateListe[$ValueAnnee["annee"]]["mois"][$ValueMois["mois"]]["nombre_news"] = $ResultCountMois["value"];
                            $ResultDateListe[$ValueAnnee["annee"]]["mois"][$ValueMois["mois"]]["mois_value"] = $this->MoisLangue[$ValueMois["mois"]];
                        }
                    }
                }
            }

            $this->context->smarty->assign(array(
                    'md5pic'                => md5(time()), 'ResultDateListe' => $ResultDateListe, 'prestablog_annee' => Tools::getValue("prestablog_annee"),
                    'link_datenews_showall' => Configuration::get($this->name . '_datenews_showall')
                ));

            return $this->display(__FILE__, 'themes/' . Configuration::get($this->name . '_theme') . '/tpl/module_bloc-dateliste.tpl');
        }
    }

    public function blocLastListe()
    {
        if (Configuration::get($this->name . '_lastnews_actif')) {
            $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));

            $tri_champ = 'n.`date`';
            $tri_ordre = 'desc';
            $Liste = NewsClass::getListe((int) ($this->context->language->id), 1, // actif only
                0, // slide
                $ConfigTheme, 0, // limit start
                (int) Configuration::get($this->name . '_lastnews_limit'), // limit stop
                $tri_champ, $tri_ordre, null, // date début
                Date("Y/m/d H:i:s"), // date fin
                null, 1);

            $this->context->smarty->assign(array(
                    'md5pic'                => md5(time()), 'ListeBlocLastNews' => $Liste,
                    'link_lastnews_showall' => Configuration::get($this->name . '_lastnews_showall'),
                    'prestablog_theme_dir'  => _MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/'
                ));

            return $this->display(__FILE__, 'themes/' . Configuration::get($this->name . '_theme') . '/tpl/module_bloc-lastliste.tpl');
        }
    }

    public function blocCatListe()
    {
        if (Configuration::get($this->name . '_catnews_actif')) {
            $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));

            $Liste = CategoriesClass::getListe((int) ($this->context->language->id), 1);

            if (sizeof($Liste)) {
                foreach ($Liste As $Key => $Value) {

                    $sql = 'SELECT COUNT(DISTINCT nl.`id_prestablog_news`) AS `value`
						FROM `' . _DB_PREFIX_ . 'prestablog_news_lang` As nl
						LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_correspondancecategorie` As co
							ON (co.news = nl.id_prestablog_news)
						LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_categorie` As c
							ON (co.categorie = c.id_prestablog_categorie)
						LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_news` As n
							ON (nl.id_prestablog_news = n.id_prestablog_news)
						WHERE	n.`actif` = 1
							AND nl.`id_lang` = ' . (int) $this->context->language->id . '
							AND nl.`actif_langue` = 1 ';

                    // edit sam
                    if ((int) $Value["id_prestablog_categorie"] == 3) {
                        $sql .= "AND TIMESTAMP(n.`date`) >= '" . Date("Y/m/d H:i:s") . "'";
                    } else {
                        $sql .= "AND TIMESTAMP(n.`date`) <= '" . Date("Y/m/d H:i:s") . "'";
                    }

                    $sql .= 'AND c.`actif` = 1
							 AND c.id_prestablog_categorie = ' . (int) $Value["id_prestablog_categorie"];

                    $nombre_news = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow($sql);

                    if (!Configuration::get($this->name . '_catnews_empty') && $nombre_news["value"] == 0)
                    {
                        unset($Liste[$Key]);
                    }
                    else{
                        $Liste[$Key]["nombre_news"] = $nombre_news["value"];
                    }
                }
            }

            $this->context->smarty->assign(array(
                'md5pic'               => md5(time()), 'ListeBlocCatNews' => $Liste,
                'link_catnews_showall' => Configuration::get($this->name . '_catnews_showall'),
                'RssCatNews'           => Configuration::get($this->name . '_catnews_rss'),
                'prestablog_theme_dir' => _MODULE_DIR_ . 'prestablog/themes/' . Configuration::get('prestablog_theme') . '/'
            ));

        return $this->display(__FILE__, 'themes/' . Configuration::get($this->name . '_theme') . '/tpl/module_page-menucat.tpl');
        }
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/themes/' . Configuration::get($this->name . '_theme') . '/css/module.css', 'all');
        //if(Configuration::get($this->name.'_homenews_actif') || Configuration::get($this->name.'_pageslide_actif'))
        //$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/module-slide.js');

        smartyRegisterFunction($this->context->smarty, 'function', 'PrestaBlogUrl', array('PrestaBlog', 'prestablog_url'));
        smartyRegisterFunction($this->context->smarty, 'function', 'PrestaBlogAjaxSearchUrl', array('PrestaBlog', 'prestablog_ajax_search_url'));

        ////// A FINIR : liaison fonction recherche
        /*
            if(isset($this->context->controller->php_self) && $this->context->controller->php_self == 'search')
                return $this->display(__FILE__, 'themes/'.Configuration::get($this->name.'_theme').'/tpl/module_header-search.tpl');
            */
    }

    //public function hookHome($params)
    public
    function hookDisplayHomeCol2($params)
    {

        if (Configuration::get($this->name . '_homenews_actif')) {
            if ($this->slideNews()) {
                return $this->display(__FILE__, 'themes/' . Configuration::get($this->name . '_theme') . '/tpl/module_home.tpl');
            }
        }
    }

    public
    function slideNews()
    {
        $Liste = array();
        $ConfigTheme = $this->_getConfigXmlTheme(Configuration::get($this->name . '_theme'));

        // edit sam -> reporter id des actus et de l'agenda
        $dernieresActus = NewsClass::getListe((int) ($this->context->language->id), 1, // actif only
            1, // slide
            $ConfigTheme, null, // limit start
            2, // limit stop
            'n.`date`', 'desc', null, // date début
            Date("Y/m/d H:i:s"), // date fin
            2, 1);

        $prochainsEvents = NewsClass::getListe((int) ($this->context->language->id), 1, // actif only
            1, // slide
            $ConfigTheme, null, // limit start
            2, // limit stop
            'n.`date`', 'asc', Date("Y/m/d H:i:s"), // date debut
            null, // date fin
            3, 1);

        // echo '<pre>';
        // print_r( $derniereActu );
        // echo '</pre>';
        //
        // echo '<pre style="color:red">/////////////////////////////////////';
        // print_r( $prochainEvent );
        // echo '</pre>';

        $Liste = array(
            'actus' => $dernieresActus, 'events' => $prochainsEvents
        );

        if (sizeof($Liste)) {
            $this->context->smarty->assign(array(
                    'md5pic'                   => md5(time()), $this->name . '_config' => objectToArray($ConfigTheme),
                    $this->name . '_theme_dir' => _MODULE_DIR_ . $this->name . '/themes/' . Configuration::get($this->name . '_theme') . '/',
                    'ListeBlogNews'            => $Liste
                ));

            return true;
        } else {
            return false;
        }

    }

    public
    function blocRss()
    {
        if (Configuration::get('prestablog_allnews_rss')) {
            $this->context->smarty->assign(array(
                    $this->name . '_theme_dir' => _MODULE_DIR_ . $this->name . '/themes/' . Configuration::get($this->name . '_theme') . '/'
                ));

            return $this->display(__FILE__, 'themes/' . Configuration::get($this->name . '_theme') . '/tpl/module_bloc-rss.tpl');
        }
    }

    function hookDisplayLeftColumnBlog($params)
    {

        $result = null;

        if (Configuration::get($this->name . '_rss_col') == "left") {
            $result .= $this->blocRss();
        }
        if (Configuration::get($this->name . '_datenews_col') == "left") {
            $result .= $this->blocDateListe();
        }
        if (Configuration::get($this->name . '_lastnews_col') == "left") {
            $result .= $this->blocLastListe();
        }
        if (Configuration::get($this->name . '_catnews_col') == "left") {
            $result .= $this->blocCatListe();
        }

        return $result;
    }

    function hookDisplayRightColumn($params)
    {

        $result = null;

        if (Configuration::get($this->name . '_rss_col') == "right") {
            $result .= $this->blocRss();
        }
        if (Configuration::get($this->name . '_datenews_col') == "right") {
            $result .= $this->blocDateListe();
        }
        if (Configuration::get($this->name . '_lastnews_col') == "right") {
            $result .= $this->blocLastListe();
        }
        if (Configuration::get($this->name . '_catnews_col') == "right") {
            $result .= $this->blocCatListe();
        }

        return $result;
    }

    public
    function hookModuleRoutes()
    {
        return self::$ModuleRoutes;
    }

    public
    function getStaticModuleRoutes($ModuleRoutes)
    {
        return self::$ModuleRoutes;
    }

    public
    static $ModuleRoutes = array(
        'prestablog-news'                => array(
            'controller' => null, 'rule' => '{module}-{urlnews}-n{n}{/:controller}', 'keywords' => array(
                'urlnews' => array('regexp' => '[_a-zA-Z0-9-\pL]*'), 'n' => array('regexp' => '[0-9]+', 'param' => 'id'),
                'module'  => array('regexp' => 'prestablog', 'param' => 'module'), 'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        ), 'prestablog-date'             => array(
            'controller' => null, 'rule' => '{module}-y{y}-m{m}{/:controller}', 'keywords' => array(
                'y'      => array('regexp' => '[0-9]{4}', 'param' => 'y'), 'm' => array('regexp' => '[0-9]+', 'param' => 'm'),
                'module' => array('regexp' => 'prestablog', 'param' => 'module'), 'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        ), 'prestablog-date-pagignation' => array(
            'controller' => null, 'rule' => '{module}-{start}p{p}y{y}-m{m}{/:controller}', 'keywords' => array(
                'y'      => array('regexp' => '[0-9]{4}', 'param' => 'y'), 'm' => array('regexp' => '[0-9]+', 'param' => 'm'),
                'start'  => array('regexp' => '[0-9]+', 'param' => 'start'), 'p' => array('regexp' => '[0-9]+', 'param' => 'p'),
                'module' => array('regexp' => 'prestablog', 'param' => 'module'), 'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        ), 'prestablog-pagignation'      => array(
            'controller' => null, 'rule' => '{module}-{start}p{p}{/:controller}', 'keywords' => array(
                'start'  => array('regexp' => '[0-9]+', 'param' => 'start'), 'p' => array('regexp' => '[0-9]+', 'param' => 'p'),
                'module' => array('regexp' => 'prestablog', 'param' => 'module'), 'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        ), 'prestablog-catpagination'    => array(
            'controller' => null, 'rule' => '{module}-{urlcat}-{start}p{p}-c{c}{/:controller}', 'keywords' => array(
                'c'      => array('regexp' => '[0-9]+', 'param' => 'c'), 'urlcat' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'start'  => array('regexp' => '[0-9]+', 'param' => 'start'), 'p' => array('regexp' => '[0-9]+', 'param' => 'p'),
                'module' => array('regexp' => 'prestablog', 'param' => 'module'), 'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        ), 'prestablog-cat'              => array(
            'controller' => null, 'rule' => '{module}-{urlcat}-c{c}{/:controller}', 'keywords' => array(
                'c'      => array('regexp' => '[0-9]+', 'param' => 'c'), 'urlcat' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'module' => array('regexp' => 'prestablog', 'param' => 'module'), 'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        ), 'prestablog-rss'              => array(
            'controller' => null, 'rule' => '{module}-rss-{rss}{/:controller}', 'keywords' => array(
                'rss'        => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'rss'), 'module' => array('regexp' => 'prestablog', 'param' => 'module'),
                'controller' => array('regexp' => 'default', 'param' => 'controller')
            ), 'params'  => array(
                'fc' => 'module'
            )
        )
    );
    }

    function objectToArray($object)
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        if (is_object($object)) {
            $object = get_object_vars($object);
        }

        return array_map('objectToArray', $object);
    }

    /*
        echo '<pre style="font-size:11px;text-align:left">';
            print_r();
        echo '</pre>';
    */

?>
