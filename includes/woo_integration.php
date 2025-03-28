<?php
if (!defined('ABSPATH')) exit;

class WP_License_WooCommerce {
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'generate_license_on_order_complete'));
        add_action('woocommerce_email_order_meta', array($this, 'add_license_key_to_email'), 10, 3);
        
        // Product Data Tabs
        add_filter('woocommerce_product_data_tabs', array($this, 'add_license_product_tab'), 99);
        add_action('woocommerce_product_data_panels', array($this, 'add_license_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_license_product_fields'));
        
        // Variable Product Type Support
        add_action('woocommerce_variation_options', array($this, 'add_variation_license_option'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_license_fields'), 10, 2);
        
        // Add tab icon styling
        add_action('admin_head', array($this, 'add_tab_icon_style'));
        
        // Add license to order details
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_license_in_order'));
        add_action('woocommerce_email_after_order_table', array($this, 'display_license_in_email'));
        
        // My Account Integration
        add_action('init', array($this, 'add_licenses_endpoint'));
        add_filter('query_vars', array($this, 'add_licenses_query_var'), 0);
        add_filter('woocommerce_account_menu_items', array($this, 'add_licenses_account_menu_item'));
        add_action('woocommerce_account_licenses_endpoint', array($this, 'licenses_endpoint_content'));

        // Flush rewrite rules if needed
        if (get_option('wp_license_manager_flush_needed', 'yes') === 'yes') {
            add_action('init', array($this, 'flush_rewrite_rules'), 20);
        }
        
        // Add license info to product page
        add_action('woocommerce_single_product_summary', array($this, 'display_license_info'), 25);
        add_action('woocommerce_variation_details_price', array($this, 'display_variation_license_info'), 10, 2);
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
        $tabs['license_options'] = array(
            'label' => __('Licensing', 'wp-license-manager'),
            'target' => 'license_options_product_data',
            'class' => array('show_if_simple', 'show_if_variable'),
            'priority' => 21
        );
        return $tabs;
    }

    public function add_license_product_fields() {
        global $post;
        
        echo '<div id="license_options_product_data" class="panel woocommerce_options_panel">';
        
        woocommerce_wp_checkbox(array(
            'id' => '_requires_license',
            'label' => __('Enable Licensing', 'wp-license-manager'),
            'description' => __('Enable license key generation for this product', 'wp-license-manager')
        ));

        woocommerce_wp_select(array(
            'id' => '_license_type',
            'label' => __('License Type', 'wp-license-manager'),
            'options' => array(
                'standard' => __('Standard', 'wp-license-manager'),
                'professional' => __('Professional', 'wp-license-manager'),
                'enterprise' => __('Enterprise', 'wp-license-manager')
            ),
            'desc_tip' => true,
            'description' => __('Select the type of license for this product', 'wp-license-manager')
        ));

        woocommerce_wp_text_input(array(
            'id' => '_license_duration',
            'label' => __('License Duration (days)', 'wp-license-manager'),
            'type' => 'number',
            'desc_tip' => true,
            'description' => __('Number of days the license will be valid for. Leave empty for lifetime.', 'wp-license-manager'),
            'custom_attributes' => array(
                'min' => '1',
                'step' => '1'
            )
        ));

        woocommerce_wp_text_input(array(
            'id' => '_license_domain_limit',
            'label' => __('Domain Limit', 'wp-license-manager'),
            'type' => 'number',
            'desc_tip' => true,
            'description' => __('Maximum number of domains this license can be used on', 'wp-license-manager'),
            'custom_attributes' => array(
                'min' => '1',
                'step' => '1'
            )
        ));

        echo '</div>';
    }

    public function add_variation_license_option($loop, $variation_data, $variation) {
        woocommerce_wp_checkbox(array(
            'id' => "_variation_requires_license{$loop}",
            'name' => "_variation_requires_license[{$loop}]",
            'label' => __('Enable Licensing', 'wp-license-manager'),
            'value' => get_post_meta($variation->ID, '_variation_requires_license', true),
        ));

        woocommerce_wp_select(array(
            'id' => "_variation_license_type{$loop}",
            'name' => "_variation_license_type[{$loop}]",
            'label' => __('License Type', 'wp-license-manager'),
            'value' => get_post_meta($variation->ID, '_variation_license_type', true),
            'options' => array(
                'standard' => __('Standard', 'wp-license-manager'),
                'professional' => __('Professional', 'wp-license-manager'),
                'enterprise' => __('Enterprise', 'wp-license-manager')
            )
        ));
    }

    public function save_variation_license_fields($variation_id, $loop) {
        $requires_license = isset($_POST['_variation_requires_license'][$loop]) ? 'yes' : 'no';
        update_post_meta($variation_id, '_variation_requires_license', $requires_license);

        if (isset($_POST['_variation_license_type'][$loop])) {
            update_post_meta($variation_id, '_variation_license_type', 
                sanitize_text_field($_POST['_variation_license_type'][$loop]));
        }
    }

    public function add_tab_icon_style() {
        ?>
        <style type="text/css">
            #woocommerce-product-data ul.wc-tabs li.license_options_options a::before {
                content: "\f160";
                font-family: dashicons;
            }
        </style>
        <?php
    }

    public function save_license_product_fields($post_id) {
        $requires_license = isset($_POST['_requires_license']) ? 'yes' : 'no';
        update_post_meta($post_id, '_requires_license', $requires_license);

        if (isset($_POST['_license_duration'])) {
            update_post_meta($post_id, '_license_duration', absint($_POST['_license_duration']));
        }

        if (isset($_POST['_license_activation_limit'])) {
            update_post_meta($post_id, '_license_activation_limit', absint($_POST['_license_activation_limit']));
        }

        if (isset($_POST['_license_type'])) {
            update_post_meta($post_id, '_license_type', sanitize_text_field($_POST['_license_type']));
        }

        if (isset($_POST['_license_domain_limit'])) {
            update_post_meta($post_id, '_license_domain_limit', absint($_POST['_license_domain_limit']));
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

    public function add_licenses_endpoint() {
        add_rewrite_endpoint('licenses', EP_ROOT | EP_PAGES);
    }

    public function add_licenses_query_var($vars) {
        $vars[] = 'licenses';
        return $vars;
    }

    public function flush_rewrite_rules() {
        flush_rewrite_rules();
        update_option('wp_license_manager_flush_needed', 'no');
    }

    public function add_licenses_account_menu_item($items) {
        $items['licenses'] = __('My Licenses', 'wp-license-manager');
        return $items;
    }

    public function licenses_endpoint_content() {
        $user_id = get_current_user_id();
        $licenses = $this->get_user_licenses($user_id);
        
        wc_get_template(
            'myaccount/licenses.php',
            array('licenses' => $licenses),
            'wp-license-manager/',
            WP_LICENSE_MANAGER_PATH . 'templates/'
        );
    }

    private function get_user_licenses($user_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}license_manager_licenses 
            WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }

    public function display_license_info() {
        global $product;
        
        if ($this->product_needs_license($product->get_id())) {
            $duration = get_post_meta($product->get_id(), '_license_duration', true);
            $domain_limit = get_post_meta($product->get_id(), '_license_domain_limit', true);
            $license_type = get_post_meta($product->get_id(), '_license_type', true);
            
            echo '<div class="product-license-info">';
            echo '<h4>' . __('License Information', 'wp-license-manager') . '</h4>';
            
            if ($license_type) {
                echo '<span class="license-type"><i class="dashicons dashicons-awards"></i> ' 
                    . esc_html(ucfirst($license_type)) . ' License</span>';
            }
            
            echo '<ul class="license-details">';
            if ($duration) {
                echo '<li><i class="dashicons dashicons-calendar-alt"></i> ' 
                    . sprintf(__('Valid for %d days', 'wp-license-manager'), $duration) . '</li>';
            } else {
                echo '<li><i class="dashicons dashicons-infinity"></i> ' 
                    . __('Lifetime License', 'wp-license-manager') . '</li>';
            }
            
            if ($domain_limit) {
                echo '<li><i class="dashicons dashicons-admin-site"></i> ' 
                    . sprintf(__('Up to %d domains', 'wp-license-manager'), $domain_limit) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    public function display_variation_license_info($variation_id, $variable_product) {
        if (get_post_meta($variation_id, '_variation_requires_license', true) === 'yes') {
            $license_type = get_post_meta($variation_id, '_variation_license_type', true);
            $duration = get_post_meta($variation_id, '_license_duration', true);
            
            echo '<div class="variation-license-info" data-variation-id="' . esc_attr($variation_id) . '">';
            if ($license_type) {
                echo '<span class="license-type">' . esc_html(ucfirst($license_type)) . ' License</span>';
            }
            if ($duration) {
                echo '<span class="license-duration">' . sprintf(__('%d days', 'wp-license-manager'), $duration) . '</span>';
            }
            echo '</div>';
        }
    }
}

// Initialize WooCommerce integration
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        new WP_License_WooCommerce();
    }
});
