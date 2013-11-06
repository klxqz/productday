<?php


class shopProductdayPlugin extends shopPlugin
{
    protected static $tmp_path = 'plugins/productday/templates/Productday.html';
    protected static $plugin;


    public function __construct($info)
    {
        parent::__construct($info);
        if(!self::$plugin) {
            self::$plugin = &$this;
        }
        
    }
    
    protected static function getThisPlugin()
    {
        if(self::$plugin) {
            return self::$plugin;
        } else {
            return wa()->getPlugin('productday'); 
        }       
    }
    
    public static function display()
    {
        $plugin = self::getThisPlugin();
        $plugin_path = $plugin->path;
        $product = $plugin->getProductday();

        $date = $plugin->getSettings('date');

        $time = strtotime($date)+3600*24-time();

        $view = wa()->getView();
        $view->assign('product', $product);
        $view->assign('plugin_path', $plugin_path);
        $view->assign('time', $time);
		
        $template_path = wa()->getDataPath(self::$tmp_path, false, 'shop', true);
        if(!file_exists($template_path)) {
            $template_path =wa()->getAppPath(self::$tmp_path,  'shop');
        }
        
		$html = $view->fetch($template_path);
        return $html;
    } 
       
    public function frontendNav()
    {
        if($this->getSettings('default_output')) {
            return self::display();
        }
        
    }
    
    public function getProductday()
    {
        $mode = $this->getSettings('mode');
        $date = $this->getSettings('date');
        $product_id = $this->getSettings('product_id');
        $p_model = new shopProductModel();
        
        
        if($mode == 'manual' && $product_id) {
            $this->saveSettings(array('date'=>date('Y-m-d')));
            $product = $p_model->getById($product_id);
            return $product;
        } elseif($mode == 'auto') {
            if($date == date('Y-m-d') && $product_id) {
                $product = $p_model->getById($product_id);
                return $product;
            } else {

                $product = $this->getProduct();

                $this->saveSettings(array('product_id'=>$product['id'],'date'=>date('Y-m-d')));
                return $product;
            }
        }

        return false;
 
    }
    
    public function getProduct()
    {
        $routing = wa()->getRouting();
        $domain = wa()->getConfig()->getDomain();
        $domain_routes = $routing->getByApp('shop');
        if(!isset($domain_routes[$domain])) {
            return false;
        }
        $routes = $domain_routes[$domain];
        $type_ids = array();
        foreach($routes as $route) {
            if($route['type_id'] && is_array($route['type_id'])) {
                $type_ids = array_merge($type_ids,$route['type_id']);
            }
        }
        
        $add_where = '';
        if($type_ids) {
            $add_where = " AND `sp`.`type_id` IN (".implode($type_ids).") ";
        }
        $db = new waModel();
        $sku = $db->query("SELECT * FROM `shop_product` as `sp` 
                    LEFT JOIN `shop_product_skus` as `sps` ON `sp`.`id` = `sps`.`product_id`
                    WHERE `sp`.`status` = 1 AND `sps`.`available` = 1 AND `sps`.`compare_price` > 0
                    AND `sp`.`compare_price`>0 AND (`sps`.`count` > 0 OR `sps`.`count` IS NULL)
                    AND `category_id`>0 ".$add_where."
                    ORDER BY RAND()
                    LIMIT 0, 1")->fetch();

        $p_model = new shopProductModel();
        $product = $p_model->getById($sku['product_id']);
        return $product;
    } 
}

