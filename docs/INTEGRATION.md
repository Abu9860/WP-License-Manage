# WP License Manager Integration Guide

## Required Files
The following files are critical for license validation:

1. `includes/license_check.php` - Core license validation
2. `includes/license_server.php` - License management
3. `includes/api.php` - API endpoints
4. `includes/envato_validate.php` - Envato integration
5. `includes/woo_integration.php` - WooCommerce integration

## Dependencies
- WordPress 5.0+
- PHP 7.2+
- WooCommerce 3.0+ (if using WooCommerce integration)
- MySQL 5.6+

## Kill Switch Implementation
The plugin includes a kill switch mechanism that prevents unauthorized use:

1. File Integrity Check
2. License Validation
3. Domain Verification
4. Dependency Verification

### Critical Components
If any of these files are removed/modified, the plugin will cease to function:
- license_check.php
- license_server.php
- api.php

## Integration Steps

### 1. Basic Integration
```php
// Check license validity
if (!check_my_license()) {
    return;
}
```

### 2. Advanced Integration
```php
// Initialize with kill switch
function initialize_my_plugin() {
    if (!kill_switch_check()) {
        deactivate_plugin_functionality();
        return;
    }
    start_plugin();
}
```

### 3. License Activation
```php
// Activate license
$activated = activate_my_license('YOUR-LICENSE-KEY');
if ($activated) {
    // Store license key
    update_option('my_project_license_key', 'YOUR-LICENSE-KEY');
}
```

## Security Measures

### Anti-Tampering
The plugin implements multiple security layers:
1. File integrity checks
2. Encrypted license storage
3. Domain validation
4. API request signing

### Recovery Procedures
If license validation fails:
1. Verify file integrity
2. Check domain settings
3. Validate license key
4. Contact support with your license key

## API Endpoints

### Validate License
```http
POST /wp-json/license/v1/validate
{
    "license_key": "YOUR-LICENSE-KEY",
    "domain": "example.com"
}
```

### Activate License
```http
POST /wp-json/license/v1/activate
{
    "license_key": "YOUR-LICENSE-KEY",
    "domain": "example.com"
}
```

## Troubleshooting

### Common Issues
1. Missing Files
   - Check all required files are present
   - Verify file permissions

2. Invalid License
   - Verify license key
   - Check domain registration
   - Validate expiration date

3. Integration Errors
   - Verify WordPress version
   - Check PHP version
   - Validate dependencies

### Debug Mode
Enable debug mode in wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support
- Documentation: /docs/
- Support Email: support@example.com
- API Documentation: /docs/api/
