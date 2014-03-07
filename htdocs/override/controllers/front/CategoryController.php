<?php

class CategoryController extends CategoryControllerCore
{

    public function initContent()
    {
        parent::initContent();

        if(isset($this->cat_products) && $this->cat_products) {

            foreach ($this->cat_products as $key => $p) {
                $tags = Tag::getProductTags($p['id_product']);
                $tagsObject = array();
                $lang_id = $this->context->language->id;
                if ($tags) {
                    foreach ($tags[$lang_id] as $tag) {
                        $tabObj = new Tag(null, $tag, $lang_id);
                        array_push($tagsObject, array('id'=>$tabObj->id, 'name'=>$tabObj->name));
                    }
                }
                $this->cat_products[$key]['tags'] = $tagsObject;
            }
            $this->context->smarty->assign('products', $this->cat_products);
        }
    }
}