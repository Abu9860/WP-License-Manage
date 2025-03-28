<?php
if (!defined('ABSPATH')) exit;

class WP_License_Server {
    public static function generate_license_key($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    public static function create_license($user_id, $product_id, $expiry_days = null) {
        global $wpdb;
        
        // Get product-specific duration if not provided
        if ($expiry_days === null) {
            $expiry_days = (int) get_post_meta($product_id, '_license_duration', true);
            if (!$expiry_days) {
                $expiry_days = 365; // Default to 365 days
            }
        }

        $license_key = self::generate_license_key();
        $expiry_date = date('Y-m-d H:i:s', strtotime("+{$expiry_days} days"));

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'license_manager_licenses',
            array(
                'license_key' => $license_key,
                'user_id' => $user_id,
                'product_id' => $product_id,
                'expiry_date' => $expiry_date
            ),
            array('%s', '%d', '%d', '%s')
        );

        return $inserted ? $license_key : false;
    }

    public static function revoke_license($license_key) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . 'license_manager_licenses',
            array('status' => 'revoked'),
            array('license_key' => $license_key),
            array('%s'),
            array('%s')
        );
    }

    public static function renew_license($license_key, $days = 365) {
        global $wpdb;
        $new_expiry = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        
        return $wpdb->update(
            $wpdb->prefix . 'license_manager_licenses',
            array(
                'expiry_date' => $new_expiry,
                'status' => 'active'
            ),
            array('license_key' => $license_key),
            array('%s', '%s'),
            array('%s')
        );
    }
}
