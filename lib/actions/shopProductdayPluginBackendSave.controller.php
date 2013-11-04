<?php

class shopProductdayPluginBackendSaveController extends waJsonController {

    protected $tmp_path = 'plugins/productday/templates/Productday.html';
    public function execute()
    {
        try {
            $app_settings_model = new waAppSettingsModel();
            $shop_productday = waRequest::post('shop_productday');
            
            $app_settings_model->set(array('shop', 'productday'), 'mode', $shop_productday['mode']);
            $app_settings_model->set(array('shop', 'productday'), 'default_output', $shop_productday['default_output']);
            
            
            
            if($shop_productday['mode'] == 'manual') {
                if(!$shop_productday['product_url']) {
                    throw new waException('Введите ссылку на товар');
                }
                $app_settings_model->set(array('shop', 'productday'), 'product_url', $shop_productday['product_url']);

                $url = array_pop(explode('/',trim($shop_productday['product_url'], '/')));
                
                $product_model = new shopProductModel();
                $product = $product_model->getByField('url', $url);      
                if(!$product){
                    throw new waException('Товар не найден');
                }
                    
                $app_settings_model->set(array('shop', 'productday'), 'product_id', $product['id']);
            }
            else {
                $app_settings_model->set(array('shop', 'productday'), 'product_id', '');
            }
                
                
            

            if(isset($shop_productday['reset_tpl']) && $shop_productday['reset_tpl']) {
                $template_path =wa()->getDataPath($this->tmp_path, false, 'shop', true);
                @unlink($template_path);      
            }
            else {
                $post_template = waRequest::post('template');
                if(!$post_template){
                    throw new waException('Не определён шаблон');    
                } 
                
                $template_path = wa()->getDataPath($this->tmp_path, false, 'shop', true);
                if(!file_exists($template_path)){
                    $template_path = wa()->getAppPath($this->tmp_path,  'shop');
                }
                
                $template = file_get_contents($template_path);
                if($template!=$post_template){
                    $template_path = wa()->getDataPath($this->tmp_path, false, 'shop', true);
                    
                    $f = fopen($template_path, 'w');
                    if(!$f){
                        throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись '.$template_path);   
                    }
                    fwrite($f, $post_template);
                    fclose($f);
                } 
                
            }

            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}