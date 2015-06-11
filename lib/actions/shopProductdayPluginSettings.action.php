<?php

class shopProductdayPluginSettingsAction extends waViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopProductdayPlugin::$plugin_id);

        $domain_routes = wa()->getRouting()->getByApp('shop');
        $domains_settings = shopProductday::getDomainsSettings();

        $this->view->assign('domain_routes', $domain_routes);
        $this->view->assign('domain_settings', $domains_settings);
        $this->view->assign('_settings', $settings);
    }

}
