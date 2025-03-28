<?php
/**
 * Plugin Name: WP License Manager
 * Plugin URI: https://yourwebsite.com
 * Description: A WordPress plugin for managing and validating licenses with WooCommerce, CodeCanyon, and API integration
 * Version: 1.0.0
 * Author: Abu Shaikh
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) exit;

// Define plugin constants
define('WP_LICENSE_MANAGER_VERSION', '1.0.0');
define('WP_LICENSE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('WP_LICENSE_MANAGER_URL', plugin_dir_url(__FILE__));
define('WP_LICENSE_MANAGER_DEBUG', true); // Add this line

// Include required files
require_once WP_LICENSE_MANAGER_PATH . 'includes/setup.php';
require_once WP_LICENSE_MANAGER_PATH . 'includes/class-wp-license-manager.php';

// Initialize the plugin
function wp_license_manager_init() {
    if (WP_LICENSE_MANAGER_DEBUG) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    $plugin = new WP_License_Manager();
    $plugin->init();
}
add_action('plugins_loaded', 'wp_license_manager_init');

// Activation hook
register_activation_hook(__FILE__, 'wp_license_manager_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_license_manager_deactivate');
