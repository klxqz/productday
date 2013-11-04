<?php

class shopProductdayPluginSettingsAction extends waViewAction
{
    protected $tmp_path = 'plugins/productday/templates/Productday.html';
    public function execute()
    {
        $app_settings_model = new waAppSettingsModel();
        $mode = $app_settings_model->get(array('shop', 'productday'), 'mode');
        $default_output = $app_settings_model->get(array('shop', 'productday'), 'default_output');
        
        $product_url='';
        if($mode == 'manual'){
            $product_url = $app_settings_model->get(array('shop', 'productday'), 'product_url');    
        }            
            
        $change_tpl = false;

        $template_path = wa()->getDataPath($this->tmp_path, false, 'shop', true);
        if(file_exists($template_path)) {
            $change_tpl = true;
        }
        else {
            $template_path = wa()->getAppPath($this->tmp_path,  'shop');
        }

        $template = file_get_contents($template_path);
            
        $this->view->assign('mode', $mode);
        $this->view->assign('product_url', $product_url);
        $this->view->assign('default_output', $default_output);
        $this->view->assign('template', $template);
        $this->view->assign('change_tpl', $change_tpl);
    }
}
