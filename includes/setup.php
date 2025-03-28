<?php
if (!defined('ABSPATH')) exit;

function wp_license_manager_activate() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = $wpdb->get_charset_collate();
    
    // Create licenses table
    $table_name = $wpdb->prefix . 'license_manager_licenses';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        license_key varchar(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        activation_date datetime DEFAULT CURRENT_TIMESTAMP,
        expiry_date datetime NULL,
        domain varchar(255) NULL,
        activation_limit int(11) DEFAULT 1,
        activations_count int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY license_key (license_key)
    ) $charset_collate;";

    dbDelta($sql);

    // Set default options
    add_option('wp_license_manager_version', WP_LICENSE_MANAGER_VERSION);
    add_option('wp_license_manager_envato_api_key', '');

    // Install demo data if it's a new installation
    if (get_option('wp_license_manager_version') === false) {
        wp_license_manager_install_demo_data();
    }
}

function wp_license_manager_deactivate() {
    // Cleanup tasks if needed
}

function wp_license_manager_verify_setup() {
    $required_files = array(
        'license_check.php',
        'license_server.php',
        'api.php',
        'envato_validate.php',
        'woo_integration.php'
    );

    $missing_files = array();
    foreach ($required_files as $file) {
        if (!file_exists(WP_LICENSE_MANAGER_PATH . 'includes/' . $file)) {
            $missing_files[] = $file;
        }
    }

    if (!empty($missing_files)) {
        add_action('admin_notices', function() use ($missing_files) {
            echo '<div class="error"><p>';
            echo '<strong>WP License Manager:</strong> The following required files are missing:<br>';
            echo '<code>' . implode('</code><br><code>', $missing_files) . '</code>';
            echo '</p></div>';
        });
        return false;
    }

    return true;
}

function wp_license_manager_install_demo_data() {
    global $wpdb;
    
    $demo_licenses = array(
        array(
            'license_key' => 'DEMO-1234-5678-9ABC',
            'user_id' => 1,
            'product_id' => 1,
            'status' => 'active',
            'domain' => 'example.com',
            'expiry_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
        ),
        array(
            'license_key' => 'DEMO-2345-6789-BCDE',
            'user_id' => 1,
            'product_id' => 2,
            'status' => 'expired',
            'domain' => 'test-site.com',
            'expiry_date' => date('Y-m-d H:i:s', strtotime('-1 month')),
        ),
        array(
            'license_key' => 'DEMO-3456-7890-CDEF',
            'user_id' => 1,
            'product_id' => 3,
            'status' => 'revoked',
            'domain' => 'demo.example.com',
            'expiry_date' => date('Y-m-d H:i:s', strtotime('+6 months')),
        )
    );

    foreach ($demo_licenses as $license) {
        $wpdb->insert($wpdb->prefix . 'license_manager_licenses', $license);
    }
}
