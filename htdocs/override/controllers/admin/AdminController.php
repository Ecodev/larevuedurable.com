<?php

class AdminController extends AdminControllerCore
{

    public function processExport()
    {
        // clean buffer
        if (ob_get_level() && ob_get_length() > 0)
            ob_clean();
        $this->getList($this->context->language->id, null, null, 0, false);
        if (!count($this->_list))
            return;

        header('Content-type: text/csv');
        header('Content-Type: application/force-download; charset=UTF-8');
        header('Cache-Control: no-store, no-cache');
        header('Content-disposition: attachment; filename="'.$this->table.'_'.date('Y-m-d_His').'.csv"');

        $headers = array();
        foreach ($this->fields_list as $datas)
            $headers[] = Tools::htmlentitiesDecodeUTF8($datas['title']);

        $content = array();
        foreach ($this->_list as $i => $row)
        {
            $content[$i] = array();
            foreach ($this->fields_list as $key => $value)
                if (isset($row[$key]))
                    $content[$i][] = Tools::htmlentitiesDecodeUTF8($row[$key]);

        }

        $this->context->smarty->assign(array(
                'export_precontent' => "\xEF\xBB\xBF",
                'export_headers' => $headers,
                'export_content' => $content
            )
        );

        $this->layout = 'layout-export.tpl';
    }


}
