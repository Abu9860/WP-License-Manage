<?php
if (!defined('ABSPATH')) exit;

function wp_license_manager_settings_page() {
    if (isset($_POST['save_settings']) && check_admin_referer('wp_license_manager_settings')) {
        update_option('wp_license_manager_envato_api_key', sanitize_text_field($_POST['envato_api_key']));
        echo '<div class="updated"><p>Settings saved successfully!</p></div>';
    }

    $envato_api_key = get_option('wp_license_manager_envato_api_key');
    ?>
    <div class="wrap">
        <h1><?php _e('License Manager Settings', 'wp-license-manager'); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('wp_license_manager_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="envato_api_key"><?php _e('Envato API Key', 'wp-license-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="envato_api_key" 
                               name="envato_api_key" 
                               value="<?php echo esc_attr($envato_api_key); ?>" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" 
                       name="save_settings" 
                       class="button button-primary" 
                       value="<?php _e('Save Settings', 'wp-license-manager'); ?>">
            </p>
        </form>
    </div>
    <?php
}
