<?php

namespace WpPluginBoilerPlate\Services;

use WpPluginBoilerPlate\Config\Constants;
use WpPluginBoilerPlate\Traits\SingletonTrait;

class SettingsService
{
    use SingletonTrait;

    private array $defaults = [
        'demoText' => '',
        'demoNumber' => 0,
    ];

    private function __construct()
    {
    }

    public function register(): void
    {
        $this->migrateSettings();

        register_setting(Constants::SETTINGS_GROUP, Constants::SETTINGS_OPTION, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => $this->defaults,
            'show_in_rest' => false,
        ]);
    }

    public function get(string $key, mixed $default = ''): mixed
    {
        return $this->getAll()[$key] ?? $default;
    }

    public function getAll(): array
    {
        $settings = get_option(Constants::SETTINGS_OPTION, []);

        return array_replace($this->defaults, is_array($settings) ? $settings : []);
    }

    public function sanitize(mixed $settings): array
    {
        if (!is_array($settings)) {
            return [];
        }

        $settings = $this->sanitizeValues($settings);

        $demoNumber = $settings['demoNumber'] ?? $this->defaults['demoNumber'];
        $settings['demoNumber'] = filter_var($demoNumber, FILTER_VALIDATE_INT) !== false
            ? (int) $demoNumber
            : $this->defaults['demoNumber'];

        return array_replace($this->defaults, $settings);
    }

    private function sanitizeValues(array $settings): array
    {
        foreach ($settings as $key => $value) {
            $settings[$key] = is_array($value)
                ? $this->sanitizeValues($value)
                : sanitize_text_field(is_scalar($value) ? (string) $value : '');
        }

        return $settings;
    }
    private function migrateSettings(): void
    {
        if (get_option(Constants::SETTINGS_OPTION, false) !== false) {
            return;
        }

        $demoText = get_option('wp-plugin-boiler-plate_demo_text', false);

        if ($demoText !== false) {
            add_option(Constants::SETTINGS_OPTION, $this->sanitize(['demoText' => $demoText]));
        }
    }
}
