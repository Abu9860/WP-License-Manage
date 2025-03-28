<?php
if (!defined('ABSPATH')) exit;

class WP_License_WooCommerce {
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'generate_license_on_order_complete'));
        add_action('woocommerce_email_order_meta', array($this, 'add_license_key_to_email'), 10, 3);
        
        // Add product license tab
        add_filter('woocommerce_product_data_tabs', array($this, 'add_license_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_license_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_license_product_fields'));
        
        // Add license to order details
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_license_in_order'));
        add_action('woocommerce_email_after_order_table', array($this, 'display_license_in_email'));
    }

    public function generate_license_on_order_complete($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            // Check if product needs a license
            if ($this->product_needs_license($product_id)) {
                $license_key = WP_License_Server::create_license($user_id, $product_id);
                
                if ($license_key) {
                    // Store license key as order item meta
                    $item->update_meta_data('_license_key', $license_key);
                    $item->save();
                    
                    // Add order note
                    $order->add_order_note(
                        sprintf(__('License key generated: %s', 'wp-license-manager'), $license_key)
                    );
                }
            }
        }
    }

    private function product_needs_license($product_id) {
        return get_post_meta($product_id, '_requires_license', true) === 'yes';
    }

    public function add_license_product_tab($tabs) {
        $tabs['license'] = array(
            'label' => __('License', 'wp-license-manager'),
            'target' => 'license_product_data',
            'class' => array('show_if_simple', 'show_if_variable'),
            'priority' => 80
        );
        return $tabs;
    }

    public function add_license_product_fields() {
        echo '<div id="license_product_data" class="panel woocommerce_options_panel">';
        woocommerce_wp_checkbox(array(
            'id' => '_requires_license',
            'label' => __('Requires License', 'wp-license-manager'),
            'description' => __('Enable if this product requires a license key', 'wp-license-manager')
        ));
        woocommerce_wp_text_input(array(
            'id' => '_license_duration',
            'label' => __('License Duration (days)', 'wp-license-manager'),
            'type' => 'number',
            'default' => '365'
        ));
        echo '</div>';
    }

    public function save_license_product_fields($post_id) {
        update_post_meta($post_id, '_requires_license', isset($_POST['_requires_license']) ? 'yes' : 'no');
        if (isset($_POST['_license_duration'])) {
            update_post_meta($post_id, '_license_duration', absint($_POST['_license_duration']));
        }
    }

    public function display_license_in_order($order) {
        $this->output_licenses($order, false);
    }

    public function display_license_in_email($order) {
        $this->output_licenses($order, true);
    }

    private function output_licenses($order, $is_email = false) {
        $has_licenses = false;
        foreach ($order->get_items() as $item) {
            $license_key = $item->get_meta('_license_key');
            if ($license_key) {
                if (!$has_licenses) {
                    echo '<h2>' . __('License Keys', 'wp-license-manager') . '</h2>';
                    $has_licenses = true;
                }
                echo '<p><strong>' . $item->get_name() . ':</strong> ' . $license_key . '</p>';
            }
        }
    }
}

// Initialize WooCommerce integration
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        new WP_License_WooCommerce();
    }
});
