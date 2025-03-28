<?php
if (!defined('ABSPATH')) exit;

class WP_License_EDD {
    public function __construct() {
        add_action('edd_payment_complete', array($this, 'generate_license_on_payment_complete'));
        add_filter('edd_download_metabox_fields_licensing', array($this, 'add_license_meta_fields'));
        add_action('edd_email_download_links', array($this, 'add_license_to_email'), 10, 2);
    }

    public function generate_license_on_payment_complete($payment_id) {
        $downloads = edd_get_payment_meta_cart_details($payment_id);
        $user_id = edd_get_payment_user_id($payment_id);

        foreach ($downloads as $download) {
            if ($this->download_needs_license($download['id'])) {
                $license_key = WP_License_Server::create_license($user_id, $download['id']);
                if ($license_key) {
                    edd_add_note(array(
                        'object_id' => $payment_id,
                        'content' => sprintf(__('License key generated: %s', 'wp-license-manager'), $license_key)
                    ));
                }
            }
        }
    }

    private function download_needs_license($download_id) {
        return get_post_meta($download_id, '_edd_requires_license', true) === 'yes';
    }
}
