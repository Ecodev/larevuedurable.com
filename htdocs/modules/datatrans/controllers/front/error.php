<?php


	/*
	 * Creator   : WDXperience SARL : YM (121008)
	 * Copyright : All Right Reserved - Licence available for 1 shop
	 * Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
	 * Compat    : Prestashop v1.5
	 */


	class DatatransErrorModuleFrontController extends ModuleFrontController {

		public $display_column_left = false;
		public $ssl = true;

		/**
		 * @see FrontController::initContent()
		 */
		public function initContent() {

			parent::initContent();

			$this->context->smarty->assign("title", $this->module->displayName);
			$this->setTemplate('payment_error.tpl');
		}
	}
