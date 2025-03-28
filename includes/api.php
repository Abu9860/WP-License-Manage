<?php
if (!defined('ABSPATH')) exit;

class WP_License_API {
    public function register_routes() {
        register_rest_route('license/v1', '/validate', array(
            'methods' => 'POST',
            'callback' => array($this, 'validate_license'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('license/v1', '/activate', array(
            'methods' => 'POST',
            'callback' => array($this, 'activate_license'),
            'permission_callback' => array($this, 'check_api_auth'),
        ));

        register_rest_route('license/v1', '/check-expiry', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_expiry'),
            'permission_callback' => '__return_true',
        ));
    }

    public function validate_license($request) {
        $license_key = sanitize_text_field($request['license_key']);
        $domain = sanitize_text_field($request['domain']);

        if (!$license_key) {
            return new WP_Error('missing_license', 'License key is required', array('status' => 400));
        }

        $is_valid = WP_License_Checker::check_license_validity($license_key, $domain);
        
        return rest_ensure_response(array(
            'valid' => $is_valid,
            'message' => $is_valid ? 'License is valid' : 'License is invalid'
        ));
    }

    public function activate_license($request) {
        $license_key = sanitize_text_field($request['license_key']);
        $domain = sanitize_text_field($request['domain']);

        if (!$license_key || !$domain) {
            return new WP_Error('missing_params', 'License key and domain are required', array('status' => 400));
        }

        if (!WP_License_Checker::validate_domain($domain)) {
            return new WP_Error('invalid_domain', 'Invalid domain format', array('status' => 400));
        }

        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'license_manager_licenses',
            array('domain' => $domain),
            array('license_key' => $license_key),
            array('%s'),
            array('%s')
        );

        return rest_ensure_response(array(
            'success' => (bool)$result,
            'message' => $result ? 'License activated successfully' : 'Failed to activate license'
        ));
    }

    public function check_expiry($request) {
        $license_key = sanitize_text_field($request['license_key']);

        if (!$license_key) {
            return new WP_Error('missing_license', 'License key is required', array('status' => 400));
        }

        global $wpdb;
        $license = $wpdb->get_row($wpdb->prepare(
            "SELECT status, expiry_date FROM {$wpdb->prefix}license_manager_licenses WHERE license_key = %s",
            $license_key
        ));

        if (!$license) {
            return new WP_Error('invalid_license', 'License not found', array('status' => 404));
        }

        return rest_ensure_response(array(
            'status' => $license->status,
            'expiry_date' => $license->expiry_date,
            'is_expired' => strtotime($license->expiry_date) < time()
        ));
    }

    private function check_api_auth($request) {
        // Implement your authentication logic here
        return true;
    }
}
