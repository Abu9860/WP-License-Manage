<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

// Remove plugin tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}license_manager_licenses");

// Remove plugin options
delete_option('wp_license_manager_version');
delete_option('wp_license_manager_envato_api_key');
