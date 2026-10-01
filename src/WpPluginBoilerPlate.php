<?php

namespace WpPluginBoilerPlate;

use WpPluginBoilerPlate\Admin\SettingsPage;
use WpPluginBoilerPlate\Controllers\AssetsController;
use WpPluginBoilerPlate\Controllers\GutenbergController;
use WpPluginBoilerPlate\Traits\SingletonTrait;

class WpPluginBoilerPlate
{
    use SingletonTrait;

    private function __construct()
    {
        AssetsController::getInstance();
        GutenbergController::getInstance();

        if (is_admin()) {
            SettingsPage::getInstance();
        }
    }
}
