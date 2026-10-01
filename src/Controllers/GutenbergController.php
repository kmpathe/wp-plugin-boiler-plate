<?php

namespace WpPluginBoilerPlate\Controllers;

use WpPluginBoilerPlate\Controllers\Blocks\EditDemoText;
use WpPluginBoilerPlate\Traits\SingletonTrait;

class GutenbergController
{
    use SingletonTrait;

    private function __construct()
    {
        add_action('init', [$this, 'registerBlocks']);
    }

    public function registerBlocks(): void
    {
        $scriptPath = WP_PLUGIN_BOILER_PLATE_DIR . '/blocks/build/index.js';
        $assetPath = WP_PLUGIN_BOILER_PLATE_DIR . '/blocks/build/index.asset.php';

        if (is_file($scriptPath) && is_file($assetPath)) {
            $asset = require $assetPath;

            wp_register_script(
                WP_PLUGIN_BOILER_PLATE_SLUG . '-blocks',
                WP_PLUGIN_BOILER_PLATE_URL . 'blocks/build/index.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );
            wp_set_script_translations(
                WP_PLUGIN_BOILER_PLATE_SLUG . '-blocks',
                WP_PLUGIN_BOILER_PLATE_SLUG,
                WP_PLUGIN_BOILER_PLATE_DIR . '/languages'
            );
        }

        register_block_type(
            WP_PLUGIN_BOILER_PLATE_DIR . '/blocks/src/components/demo-text',
            ['render_callback' => [EditDemoText::getInstance(), 'render']]
        );
    }
}
