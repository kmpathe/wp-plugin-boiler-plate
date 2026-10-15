<?php

use WpPluginBoilerPlate\Config\Constants;

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('WP Plugin Boiler Plate — Settings', 'wp-plugin-boiler-plate'); ?></h1>
    <?php settings_errors(); ?>
    <form action="options.php" method="post">
        <?php settings_fields(Constants::SETTINGS_GROUP); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="wp-plugin-boiler-plate_demoText">
                        <?php echo esc_html__('Demo text', 'wp-plugin-boiler-plate'); ?>
                    </label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="wp-plugin-boiler-plate_demoText"
                        name="<?php echo esc_attr(Constants::SETTINGS_OPTION . '[demoText]'); ?>"
                        value="<?php echo esc_attr($settings['demoText']); ?>"
                    >
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="wp-plugin-boiler-plate_demoNumber">
                        <?php echo esc_html__('Demo number', 'wp-plugin-boiler-plate'); ?>
                    </label>
                </th>
                <td>
                    <input
                        type="number"
                        step="1"
                        class="regular-text"
                        id="wp-plugin-boiler-plate_demoNumber"
                        name="<?php echo esc_attr(Constants::SETTINGS_OPTION . '[demoNumber]'); ?>"
                        value="<?php echo esc_attr($settings['demoNumber']); ?>"
                    >
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
