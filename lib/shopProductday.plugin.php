<?php

class shopProductdayPlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'productday');
    public static $default_settings = array(
        'mode' => 'auto',
        'default_output' => '1',
        'set_id' => '',
        'title' => 'ТОВАР ДНЯ',
        'description' => 'Не упусти свою выгоду!',
        'interval' => '24',
        'width' => '200',
        'height' => '60',
        'style' => 'flip',
        'offset' => '0',
        'hideLabels' => 'false',
        'hideLine' => 'false',
        'second_text' => 'СЕКУНДЫ',
        'minute_text' => 'МИНУТЫ',
        'hour_text' => 'ЧАСЫ',
        'numbers_color' => '#000000',
        'numbers_bkgd' => '#FFFFFF',
        'labels_color' => '#000000',
        'labels_size' => '1',
        'time' => '',
        'templates' => array(
            'productday' => array(
                'name' => 'Основной шаблон',
                'tpl_path' => 'plugins/productday/templates/',
                'tpl_name' => 'Productday',
                'tpl_ext' => 'html',
                'public' => false
            )
        )
    );
    protected static $tmp_path = 'plugins/productday/templates/Productday.html';

    public function frontendNav() {
        $domain_settings = shopProductday::getDomainSettings();
        if ($domain_settings['default_output']) {
            return self::display();
        }
    }

    public static function display() {
        $app_settings_model = new waAppSettingsModel();
        if (!$app_settings_model->get(self::$plugin_id, 'status')) {
            return false;
        }

        if (!shopProductday::getDomainSetting('mode')) {
            return false;
        }
        $product = self::getProductday();
        if (!$product) {
            return false;
        }
        
        $domain_settings = shopProductday::getDomainSettings();
        $templates = $domain_settings['templates'];

        $time = $domain_settings['time'] - self::getTime();

        $view = wa()->getView();
        $view->assign('settings', $domain_settings);
        $view->assign('product_day', $product);
        $view->assign('time', $time);

        return $view->fetch($templates['productday']['template_path']);
    }

    private static function getTime() {
        return time() + (int) 3600 * shopProductday::getDomainSetting('offset');
    }

    public static function getEndTime($interval = 24) {
        $current_time = self::getTime();
        $start_day = mktime(0, 0, 0, date("m", $current_time), date("d", $current_time), date("Y", $current_time));
        $elapsed_time = ($current_time - $start_day) / ((int) 3600 * $interval);
        $interval_number = ceil($elapsed_time);
        $end_time = $start_day + $interval_number * (int) 3600 * $interval;
        return $end_time;
    }

    public static function getProductday() {
        $settings = shopProductday::getDomainSettings();
        $p_model = new shopProductModel();


        if ($settings['mode'] == 'manual' && !empty($settings['product_url'])) {
            $url = array_pop(explode('/', trim($settings['product_url'], '/')));
            $product_model = new shopProductModel();
            $product = $product_model->getByField('url', $url);
            if (!empty($product)) {
                shopProductday::saveDomainSetting('time', self::getEndTime($settings['interval']));
                return $product;
            }
        } elseif ($settings['mode'] == 'auto') {
            if (!empty($settings['time']) && $settings['time'] == self::getEndTime($settings['interval']) && !empty($settings['product_id'])) {
                $product = $p_model->getById($settings['product_id']);
                return $product;
            } else {
                $product = self::getProduct();
                if (!empty($product)) {
                    shopProductday::saveDomainSetting('product_id', $product['id']);
                    shopProductday::saveDomainSetting('time', self::getEndTime($settings['interval']));
                    return $product;
                }
            }
        } elseif ($settings['mode'] == 'list') {
            if ($settings['time'] == self::getEndTime($settings['interval']) && !empty($settings['product_id'])) {
                $product = $p_model->getById($settings['product_id']);
                return $product;
            } else {
                if (!isset($settings['list_index'])) {
                    $settings['list_index'] = 0;
                } else {
                    $settings['list_index'] ++;
                }
                $collection = new shopProductsCollection('set/' . $settings['set_id']);
                $offset = $settings['list_index'] % $collection->count();
                $products = $collection->getProducts('*', $offset, 1);
                if (!empty($products)) {
                    $product = array_pop($products);
                    shopProductday::saveDomainSetting('product_id', $product['id']);
                    shopProductday::saveDomainSetting('time', self::getEndTime($settings['interval']));
                    shopProductday::saveDomainSetting('list_index', $settings['list_index']);
                    return $product;
                }
            }
        }

        return false;
    }

    public static function getProduct() {
        $routing = wa()->getRouting();
        $domain = wa()->getConfig()->getDomain();
        $domain_routes = $routing->getByApp('shop');
        if (!isset($domain_routes[$domain])) {
            return false;
        }
        $routes = $domain_routes[$domain];
        $type_ids = array();
        foreach ($routes as $route) {
            if ($route['type_id'] && is_array($route['type_id'])) {
                $type_ids = array_merge($type_ids, $route['type_id']);
            }
        }

        $add_where = '';
        if ($type_ids) {
            $add_where = " AND `sp`.`type_id` IN (" . implode($type_ids) . ") ";
        }
        $db = new waModel();
        $sku = $db->query("SELECT * FROM `shop_product` as `sp` 
                    LEFT JOIN `shop_product_skus` as `sps` ON `sp`.`id` = `sps`.`product_id`
                    WHERE 
                    `sp`.`status` = 1 AND 
                    `sps`.`available` = 1 AND 
                    `sps`.`compare_price` > 0 AND 
                    `sp`.`compare_price`>0 AND 
                    (`sps`.`count` > 0 OR `sps`.`count` IS NULL) AND `category_id`>0 
                    " . $add_where . "
                    ORDER BY RAND()
                    LIMIT 0, 1")->fetch();

        $p_model = new shopProductModel();
        $product = $p_model->getById($sku['product_id']);
        return $product;
    }

}
