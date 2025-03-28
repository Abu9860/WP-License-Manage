<?php
// Test API endpoints
$test_license = 'DEMO-1234-5678-9ABC';
$test_domain = 'example.com';

// Test validate endpoint
$validate_response = wp_remote_post(rest_url('license/v1/validate'), array(
    'body' => json_encode(array(
        'license_key' => $test_license,
        'domain' => $test_domain
    )),
    'headers' => array('Content-Type' => 'application/json')
));

// Test activate endpoint
$activate_response = wp_remote_post(rest_url('license/v1/activate'), array(
    'body' => json_encode(array(
        'license_key' => $test_license,
        'domain' => $test_domain
    )),
    'headers' => array('Content-Type' => 'application/json')
));

// Test check-expiry endpoint
$expiry_response = wp_remote_get(rest_url('license/v1/check-expiry') . '?license_key=' . $test_license);
