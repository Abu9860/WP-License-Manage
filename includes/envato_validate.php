<?php
if (!defined('ABSPATH')) exit;

class WP_License_Envato {
    private $api_key;
    
    public function __construct() {
        $this->api_key = get_option('wp_license_manager_envato_api_key');
    }
    
    public function validate_purchase($purchase_code) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Envato API key not configured');
        }

        $response = wp_remote_get(
            'https://api.envato.com/v3/market/author/sale?code=' . $purchase_code,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'User-Agent' => 'WordPress License Manager'
                )
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('invalid_purchase', $body['description']);
        }

        return array(
            'valid' => true,
            'buyer' => $body['buyer'],
            'purchase_date' => $body['sold_at']
        );
    }
}
