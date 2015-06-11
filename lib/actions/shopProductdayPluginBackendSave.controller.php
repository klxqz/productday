<?php

class shopProductdayPluginBackendSaveController extends waJsonController {

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $settings = waRequest::post('shop_productday', array());
            $domains_settings = waRequest::post('domains_settings', array());
            $reset = waRequest::post('reset');

            foreach ($settings as $name => $value) {
                $app_settings_model->set(shopProductdayPlugin::$plugin_id, $name, $value);
            }
            if ($reset) {
                $domains_settings = array();
            }
            shopProductday::saveDomainsSettings($domains_settings);

            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
