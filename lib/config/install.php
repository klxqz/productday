<?php
$plugin_id = array('shop', 'productday');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'mode', 'auto');
$app_settings_model->set($plugin_id, 'default_output', '1');
