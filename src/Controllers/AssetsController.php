<?php

namespace WpPluginBoilerPlate\Controllers;

use WpPluginBoilerPlate\Traits\SingletonTrait;

class AssetsController
{
    use SingletonTrait;

    private function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);
    }

    public function enqueueAdminAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'toplevel_page_' . WP_PLUGIN_BOILER_PLATE_SLUG) {
            return;
        }

        $this->enqueueAssets('admin');
    }

    public function enqueuePublicAssets(): void
    {
        $this->enqueueAssets('public');
    }

    private function enqueueAssets(string $context): void
    {
        $basePath = 'assets/' . $context . '/dist/';
        $handle = WP_PLUGIN_BOILER_PLATE_SLUG . '-' . $context;
        $cssPath = $basePath . 'css/' . $context . '.min.css';
        $jsPath = $basePath . 'js/' . $context . '.min.js';

        if (is_file(WP_PLUGIN_BOILER_PLATE_DIR . '/' . $cssPath)) {
            wp_enqueue_style(
                $handle,
                WP_PLUGIN_BOILER_PLATE_URL . $cssPath,
                [],
                (string) filemtime(WP_PLUGIN_BOILER_PLATE_DIR . '/' . $cssPath)
            );
        }

        if (is_file(WP_PLUGIN_BOILER_PLATE_DIR . '/' . $jsPath)) {
            wp_enqueue_script(
                $handle,
                WP_PLUGIN_BOILER_PLATE_URL . $jsPath,
                [],
                (string) filemtime(WP_PLUGIN_BOILER_PLATE_DIR . '/' . $jsPath),
                true
            );

            wp_localize_script(
                $handle,
                $context === 'admin' ? 'wpPluginBoilerPlateAdmin' : 'wpPluginBoilerPlatePublic',
                [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce(WP_PLUGIN_BOILER_PLATE_SLUG . '-' . $context),
                    'pluginUrl' => WP_PLUGIN_BOILER_PLATE_URL,
                    'strings' => [
                        'loading' => __('Loading...', 'wp-plugin-boiler-plate'),
                        'error' => __('An error occurred.', 'wp-plugin-boiler-plate'),
                    ],
                ]
            );
        }
    }
}
