<?php
if (!defined('ABSPATH')) exit;

function wp_license_manager_documentation() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap wp-license-manager-docs">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="nav-tab-wrapper">
            <a href="#overview" class="nav-tab nav-tab-active"><?php _e('Overview', 'wp-license-manager'); ?></a>
            <a href="#getting-started" class="nav-tab"><?php _e('Getting Started', 'wp-license-manager'); ?></a>
            <a href="#woocommerce" class="nav-tab"><?php _e('WooCommerce', 'wp-license-manager'); ?></a>
            <a href="#api-reference" class="nav-tab"><?php _e('API Reference', 'wp-license-manager'); ?></a>
            <a href="#troubleshooting" class="nav-tab"><?php _e('Troubleshooting', 'wp-license-manager'); ?></a>
        </div>

        <div id="overview" class="tab-content active">
            <div class="card feature-section">
                <h2><span class="dashicons dashicons-admin-network"></span> <?php _e('What is WP License Manager?', 'wp-license-manager'); ?></h2>
                <p class="about-description">
                    <?php _e('WP License Manager is a comprehensive solution for managing software licenses, integrating with WooCommerce for automatic license generation, and providing secure API endpoints for license validation.', 'wp-license-manager'); ?>
                </p>

                <div class="feature-grid">
                    <div class="feature-item">
                        <span class="dashicons dashicons-lock"></span>
                        <h3><?php _e('License Management', 'wp-license-manager'); ?></h3>
                        <p><?php _e('Generate and manage software license keys with expiration dates and domain restrictions.', 'wp-license-manager'); ?></p>
                    </div>

                    <div class="feature-item">
                        <span class="dashicons dashicons-cart"></span>
                        <h3><?php _e('WooCommerce Integration', 'wp-license-manager'); ?></h3>
                        <p><?php _e('Automatically generate license keys when customers purchase your products.', 'wp-license-manager'); ?></p>
                    </div>

                    <div class="feature-item">
                        <span class="dashicons dashicons-rest-api"></span>
                        <h3><?php _e('REST API', 'wp-license-manager'); ?></h3>
                        <p><?php _e('Secure API endpoints for license validation and activation in your software.', 'wp-license-manager'); ?></p>
                    </div>

                    <div class="feature-item">
                        <span class="dashicons dashicons-envato"></span>
                        <h3><?php _e('Envato Integration', 'wp-license-manager'); ?></h3>
                        <p><?php _e('Validate CodeCanyon purchase codes and generate licenses automatically.', 'wp-license-manager'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div id="getting-started" class="tab-content">
            <div class="card">
                <h2><span class="dashicons dashicons-admin-tools"></span> <?php _e('Installation & Setup', 'wp-license-manager'); ?></h2>
                <div class="setup-steps">
                    <div class="step">
                        <span class="step-number">1</span>
                        <h4><?php _e('Plugin Installation', 'wp-license-manager'); ?></h4>
                        <p><?php _e('Upload the plugin files to wp-content/plugins/wp-license-manager directory.', 'wp-license-manager'); ?></p>
                    </div>

                    <div class="step">
                        <span class="step-number">2</span>
                        <h4><?php _e('Plugin Activation', 'wp-license-manager'); ?></h4>
                        <p><?php _e('Activate the plugin through the WordPress plugins screen.', 'wp-license-manager'); ?></p>
                    </div>

                    <div class="step">
                        <span class="step-number">3</span>
                        <h4><?php _e('Configure Settings', 'wp-license-manager'); ?></h4>
                        <p><?php _e('Go to License Manager → Settings to configure your API keys and preferences.', 'wp-license-manager'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div id="api-reference" class="tab-content">
            <div class="card">
                <h2><?php _e('API Reference', 'wp-license-manager'); ?></h2>
                
                <h3><?php _e('Validate License', 'wp-license-manager'); ?></h3>
                <pre>
# Request
POST /wp-json/license/v1/validate
Content-Type: application/json

{
    "license_key": "YOUR-LICENSE-KEY",
    "domain": "example.com"
}

# Response
{
    "valid": true,
    "message": "License is valid"
}
                </pre>

                <h3><?php _e('Activate License', 'wp-license-manager'); ?></h3>
                <pre>
# Request
POST /wp-json/license/v1/activate
Content-Type: application/json

{
    "license_key": "YOUR-LICENSE-KEY",
    "domain": "example.com"
}

# Response
{
    "success": true,
    "message": "License activated successfully"
}
                </pre>

                <h3><?php _e('Check License Expiry', 'wp-license-manager'); ?></h3>
                <pre>
# Request
GET /wp-json/license/v1/check-expiry?license_key=YOUR-LICENSE-KEY

# Response
{
    "status": "active",
    "expiry_date": "2024-12-31 23:59:59",
    "is_expired": false
}
                </pre>
            </div>
        </div>

        <div id="woocommerce" class="tab-content">
            <div class="card">
                <h2><span class="dashicons dashicons-cart"></span> <?php _e('WooCommerce Integration', 'wp-license-manager'); ?></h2>
                
                <div class="integration-steps">
                    <h3><?php _e('Setting Up Product Licensing', 'wp-license-manager'); ?></h3>
                    <ol>
                        <li>
                            <strong><?php _e('Edit Product', 'wp-license-manager'); ?></strong>
                            <p><?php _e('Go to Products → Edit Product in WooCommerce.', 'wp-license-manager'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('License Tab', 'wp-license-manager'); ?></strong>
                            <p><?php _e('Find the "License" tab in the Product Data section.', 'wp-license-manager'); ?></p>
                            <img src="<?php echo plugins_url('assets/images/license-tab.png', dirname(__FILE__)); ?>" alt="License Tab">
                        </li>
                        <li>
                            <strong><?php _e('Configure Options', 'wp-license-manager'); ?></strong>
                            <p><?php _e('Enable licensing and set the license duration.', 'wp-license-manager'); ?></p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div id="troubleshooting" class="tab-content">
            <div class="card">
                <h2><span class="dashicons dashicons-sos"></span> <?php _e('Troubleshooting Guide', 'wp-license-manager'); ?></h2>
                
                <div class="faq-section">
                    <div class="faq-item">
                        <h4><?php _e('License Keys Not Generating', 'wp-license-manager'); ?></h4>
                        <div class="solution">
                            <ol>
                                <li><?php _e('Verify that WooCommerce is activated', 'wp-license-manager'); ?></li>
                                <li><?php _e('Check if the product has licensing enabled', 'wp-license-manager'); ?></li>
                                <li><?php _e('Ensure the order status is "Completed"', 'wp-license-manager'); ?></li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h4><?php _e('API Validation Issues', 'wp-license-manager'); ?></h4>
                        <div class="solution">
                            <ul>
                                <li><?php _e('Confirm the API endpoint URL is correct', 'wp-license-manager'); ?></li>
                                <li><?php _e('Check if the license key exists and is active', 'wp-license-manager'); ?></li>
                                <li><?php _e('Verify the domain matches the activation', 'wp-license-manager'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="support-section">
                    <h3><?php _e('Need More Help?', 'wp-license-manager'); ?></h3>
                    <p><?php _e('Contact our support team or visit our documentation website for more detailed information.', 'wp-license-manager'); ?></p>
                    <a href="#" class="button button-primary"><?php _e('Contact Support', 'wp-license-manager'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php
}
