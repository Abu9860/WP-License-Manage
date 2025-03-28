<?php
if (!defined('ABSPATH')) exit;

function wp_license_manager_documentation() {
    ?>
    <div class="wrap wp-license-manager-docs">
        <h1><?php _e('License Manager Documentation', 'wp-license-manager'); ?></h1>

        <div class="card">
            <h2><?php _e('Quick Start Guide', 'wp-license-manager'); ?></h2>
            <ol>
                <li><?php _e('Configure your Envato API key in Settings if you plan to use CodeCanyon integration', 'wp-license-manager'); ?></li>
                <li><?php _e('For WooCommerce products, edit the product and enable license generation in the License tab', 'wp-license-manager'); ?></li>
                <li><?php _e('Use the REST API endpoints to validate licenses in your software', 'wp-license-manager'); ?></li>
            </ol>
        </div>

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

        <div class="card">
            <h2><?php _e('Code Examples', 'wp-license-manager'); ?></h2>
            
            <h3><?php _e('PHP Example', 'wp-license-manager'); ?></h3>
            <pre>
$response = wp_remote_post('https://your-site.com/wp-json/license/v1/validate', array(
    'body' => json_encode(array(
        'license_key' => 'YOUR-LICENSE-KEY',
        'domain' => 'example.com'
    )),
    'headers' => array('Content-Type' => 'application/json')
));

if (!is_wp_error($response)) {
    $body = json_decode(wp_remote_retrieve_body($response));
    if ($body->valid) {
        // License is valid
    }
}
            </pre>

            <h3><?php _e('JavaScript Example', 'wp-license-manager'); ?></h3>
            <pre>
fetch('https://your-site.com/wp-json/license/v1/validate', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        license_key: 'YOUR-LICENSE-KEY',
        domain: 'example.com'
    })
})
.then(response => response.json())
.then(data => {
    if (data.valid) {
        // License is valid
    }
});
            </pre>
        </div>

        <div class="card">
            <h2><?php _e('Troubleshooting', 'wp-license-manager'); ?></h2>
            <h3><?php _e('Common Issues', 'wp-license-manager'); ?></h3>
            <ul>
                <li><strong><?php _e('Invalid License Key:', 'wp-license-manager'); ?></strong> <?php _e('Ensure the license key exists and is active', 'wp-license-manager'); ?></li>
                <li><strong><?php _e('Domain Mismatch:', 'wp-license-manager'); ?></strong> <?php _e('The license may be locked to a different domain', 'wp-license-manager'); ?></li>
                <li><strong><?php _e('Expired License:', 'wp-license-manager'); ?></strong> <?php _e('Check the expiration date in the dashboard', 'wp-license-manager'); ?></li>
            </ul>
        </div>
    </div>
    <?php
}
