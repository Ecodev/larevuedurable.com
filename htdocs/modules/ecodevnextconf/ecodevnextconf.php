<?php

class EcodevNextConf extends Module
{
    private $_html = '';
    private $_postErrors = array();
	private $default_hook = 'displayHomeCol1';

	public function __construct()
	{
		$this->name = 'ecodevnextconf';
		$this->tab = 'ecodev';
		$this->version = '1.0';
		
		$this->_errors = array();
		
		parent::__construct();

		$this->displayName = $this->l("Prochaine conférence");
		$this->description = $this->l("");
	}
	
	public function install()
	{
		Tools::createHookIfNoExist($this->default_hook, 'Accueil : première colonne');
		
		if ( !parent::install() OR !$this->registerHook($this->default_hook) ) 
		return false;	
	}
	
	
	public function uninstall()
	{
		parent::uninstall();
	}


	function hookDisplayHomeCol1($params)
	{	
		global $date_now,$smarty,$cookie;


		$editorialCategory = 23;
		$sql = "
			select p.id_product, p.date_parution 
			from ps_product as p 
			inner join ps_category_product as pc on p.id_product = pc.id_product 
			where date_parution < '".$date_now->format('Y-m-d')."'
			and pc.id_category = $editorialCategory
			and p.active=1 	
			order by p.date_parution desc";
		$nextEdito = Db::getInstance()->getRow($sql);
		$nextEdito = new Product($nextEdito['id_product'], true, intval($cookie->id_lang));
		$images = $nextEdito->getImages($cookie->id_lang);
		$editoImage = null;
		foreach($images as $row){
			if($row['cover']==1) $editoImage = $row;
		}




		$indicatorCategory = 24;
		$sql = "
			select p.id_product, p.date_parution 
			from ps_product as p 
			inner join ps_category_product as pc on p.id_product = pc.id_product 
			where date_parution < '".$date_now->format('Y-m-d')."'
			and pc.id_category = $indicatorCategory
			and p.active=1 	
			order by pc.position asc";
			//order by p.date_parution desc";
		$nextIndicator = Db::getInstance()->getRow($sql);

        $nextIndicator = new Product($nextIndicator['id_product'], true, intval($cookie->id_lang));
		$images = $nextIndicator->getImages($cookie->id_lang);
		$indicatorImage = null;
		if($images)
		foreach($images as $row){
			if($row['cover']==1) $indicatorImage = $row;
		}



		$smarty->assign('nextEditoProduct', $nextEdito);
		$smarty->assign('nextEditoImage', $nextEdito->id.'-'.$editoImage['id_image']);
		$smarty->assign('nextIndicatorProduct', $nextIndicator);
		$smarty->assign('nextIndicatorImage', $nextIndicator->id.'-'.$indicatorImage['id_image']);

		return $this->display(__FILE__, 'nextedito.tpl');
	}


	
	
}
?>