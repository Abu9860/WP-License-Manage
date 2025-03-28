<?php
/**
 * Example Integration File
 * 
 * This file demonstrates how to integrate the license system into your project.
 */

// 1. Basic License Check
function check_my_license() {
    $license_key = get_option('my_project_license_key');
    $domain = $_SERVER['HTTP_HOST'];

    $response = wp_remote_post('https://your-site.com/wp-json/license/v1/validate', array(
        'body' => json_encode(array(
            'license_key' => $license_key,
            'domain' => $domain
        )),
        'headers' => array('Content-Type' => 'application/json')
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $result = json_decode(wp_remote_retrieve_body($response));
    return $result->valid;
}

// 2. License Activation
function activate_my_license($license_key) {
    $domain = $_SERVER['HTTP_HOST'];

    $response = wp_remote_post('https://your-site.com/wp-json/license/v1/activate', array(
        'body' => json_encode(array(
            'license_key' => $license_key,
            'domain' => $domain
        )),
        'headers' => array('Content-Type' => 'application/json')
    ));

    return !is_wp_error($response);
}

// 3. Dependency Check
function verify_license_dependencies() {
    $required_files = array(
        'wp-content/plugins/WP License Manage/includes/license_check.php',
        'wp-content/plugins/WP License Manage/includes/license_server.php',
        'wp-content/plugins/WP License Manage/includes/api.php'
    );

    foreach ($required_files as $file) {
        if (!file_exists(ABSPATH . $file)) {
            return false;
        }
    }
    return true;
}

// 4. Kill Switch Implementation
function kill_switch_check() {
    if (!verify_license_dependencies() || !check_my_license()) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>';
            echo 'Critical licensing components are missing or license is invalid. ';
            echo 'The plugin will not function until this is resolved.';
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

// 5. Integration Example
function my_plugin_init() {
    if (!kill_switch_check()) {
        // Disable plugin functionality
        return;
    }

    // Your plugin code here
    // ...
}
add_action('init', 'my_plugin_init');
