<?php

namespace WpPluginBoilerPlate\Controllers\Blocks;

use WpPluginBoilerPlate\Traits\SingletonTrait;
use WP_Block;

class EditDemoText
{
    use SingletonTrait;

    private function __construct()
    {
    }

    public function render(array $attributes, string $content, WP_Block $block): string
    {
        $text = $attributes['text'] ?? '';
        $text = is_string($text) ? $text : '';

        return sprintf(
            '<p %s>%s</p>',
            get_block_wrapper_attributes(),
            esc_html($text)
        );
    }
}
