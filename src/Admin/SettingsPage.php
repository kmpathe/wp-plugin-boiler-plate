<?php

namespace WpPluginBoilerPlate\Admin;

use WpPluginBoilerPlate\Services\SettingsService;
use WpPluginBoilerPlate\Traits\SingletonTrait;

class SettingsPage
{
    use SingletonTrait;

    private function __construct()
    {
        add_action('admin_menu', [$this, 'register']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function register(): void
    {
        add_menu_page(
            __('WP Plugin Boiler Plate — Settings', 'wp-plugin-boiler-plate'),
            __('WP Plugin Boiler Plate', 'wp-plugin-boiler-plate'),
            'manage_options',
            WP_PLUGIN_BOILER_PLATE_SLUG,
            [$this, 'render'],
            'dashicons-admin-generic'
        );
    }

    public function registerSettings(): void
    {
        SettingsService::getInstance()->register();
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'wp-plugin-boiler-plate'));
        }

        $settings = SettingsService::getInstance()->getAll();

        include WP_PLUGIN_BOILER_PLATE_DIR . '/views/admin/SettingsPage.php';
    }
}
