<?php
if (!defined('ABSPATH')) exit;

function wp_license_manager_settings_page() {
    if (isset($_POST['save_settings']) && check_admin_referer('wp_license_manager_settings')) {
        $settings = array(
            'envato_api_key' => sanitize_text_field($_POST['envato_api_key']),
            'license_prefix' => sanitize_text_field($_POST['license_prefix']),
            'license_length' => absint($_POST['license_length']),
            'default_duration' => absint($_POST['default_duration']),
            'enable_woocommerce' => isset($_POST['enable_woocommerce']),
            'enable_edd' => isset($_POST['enable_edd']),
            'email_template' => wp_kses_post($_POST['email_template'])
        );
        
        update_option('wp_license_manager_settings', $settings);
        echo '<div class="updated"><p>' . __('Settings saved successfully!', 'wp-license-manager') . '</p></div>';
    }

    $settings = get_option('wp_license_manager_settings', array(
        'envato_api_key' => '',
        'license_prefix' => 'LIC-',
        'license_length' => 16,
        'default_duration' => 365,
        'enable_woocommerce' => true,
        'enable_edd' => false,
        'email_template' => "Hello,\n\nYour license key: {license_key}\nExpiration: {expiry_date}"
    ));
    ?>
    <div class="wrap">
        <h1><?php _e('License Manager Settings', 'wp-license-manager'); ?></h1>
        
        <?php
        // Add test button
        if (isset($_GET['run_tests']) && current_user_can('manage_options')) {
            require_once WP_LICENSE_MANAGER_PATH . 'tests/test-setup.php';
            Test_Setup::run_tests();
        }
        ?>
        
        <a href="<?php echo add_query_arg('run_tests', '1'); ?>" class="page-title-action">
            <?php _e('Run Tests', 'wp-license-manager'); ?>
        </a>
        
        <form method="post" action="">
            <?php wp_nonce_field('wp_license_manager_settings'); ?>
            
            <div class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'wp-license-manager'); ?></a>
                <a href="#integrations" class="nav-tab"><?php _e('Integrations', 'wp-license-manager'); ?></a>
                <a href="#email" class="nav-tab"><?php _e('Email', 'wp-license-manager'); ?></a>
            </div>

            <div id="general" class="tab-content active">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="license_prefix"><?php _e('License Prefix', 'wp-license-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="license_prefix" name="license_prefix" 
                                value="<?php echo esc_attr($settings['license_prefix']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="license_length"><?php _e('License Length', 'wp-license-manager'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="license_length" name="license_length" 
                                value="<?php echo esc_attr($settings['license_length']); ?>" min="8" max="32">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_duration"><?php _e('Default Duration (days)', 'wp-license-manager'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_duration" name="default_duration" 
                                value="<?php echo esc_attr($settings['default_duration']); ?>" min="1">
                        </td>
                    </tr>
                </table>
            </div>

            <div id="integrations" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('WooCommerce Integration', 'wp-license-manager'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_woocommerce" value="1" 
                                    <?php checked($settings['enable_woocommerce']); ?>>
                                <?php _e('Enable WooCommerce Integration', 'wp-license-manager'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Easy Digital Downloads Integration', 'wp-license-manager'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_edd" value="1" 
                                    <?php checked($settings['enable_edd']); ?>>
                                <?php _e('Enable EDD Integration', 'wp-license-manager'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="envato_api_key"><?php _e('Envato API Key', 'wp-license-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="envato_api_key" name="envato_api_key" 
                                value="<?php echo esc_attr($settings['envato_api_key']); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <div id="email" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="email_template"><?php _e('Email Template', 'wp-license-manager'); ?></label>
                        </th>
                        <td>
                            <textarea id="email_template" name="email_template" rows="10" class="large-text"><?php 
                                echo esc_textarea($settings['email_template']); 
                            ?></textarea>
                            <p class="description">
                                <?php _e('Available variables: {license_key}, {expiry_date}, {product_name}, {customer_name}', 'wp-license-manager'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="submit">
                <input type="submit" name="save_settings" class="button button-primary" 
                    value="<?php _e('Save Settings', 'wp-license-manager'); ?>">
            </p>
        </form>
    </div>
    <?php
}
