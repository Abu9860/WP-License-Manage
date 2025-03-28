<?php
if (!defined('ABSPATH')) exit;

class WP_License_Checker {
    public static function check_license_validity($license_key, $domain) {
        global $wpdb;
        
        $license = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}license_manager_licenses 
            WHERE license_key = %s AND status = 'active'",
            $license_key
        ));

        if (!$license) {
            return false;
        }

        // Check domain if specified
        if ($domain && $license->domain && $license->domain !== $domain) {
            return false;
        }

        // Check expiration
        if ($license->expiry_date && strtotime($license->expiry_date) < time()) {
            return false;
        }

        return true;
    }

    public static function validate_domain($domain) {
        return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain);
    }
}
