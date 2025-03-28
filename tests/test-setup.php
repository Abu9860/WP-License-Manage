<?php
// Add this test file to verify basic functionality
class Test_Setup {
    public static function run_tests() {
        self::test_database_tables();
        self::test_demo_data();
        self::test_settings();
    }

    private static function test_database_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'license_manager_licenses';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        echo "<div class='notice notice-info'>";
        echo "<p>Testing Database Setup:</p>";
        echo "<ul>";
        echo "<li>License table exists: " . ($table_exists ? '✅' : '❌') . "</li>";
        echo "</ul>";
        echo "</div>";
    }

    private static function test_demo_data() {
        global $wpdb;
        $demo_licenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}license_manager_licenses WHERE license_key LIKE 'DEMO%'");
        
        echo "<div class='notice notice-info'>";
        echo "<p>Testing Demo Data:</p>";
        echo "<ul>";
        echo "<li>Demo licenses found: " . count($demo_licenses) . "</li>";
        echo "</ul>";
        echo "</div>";
    }

    private static function test_settings() {
        $settings = get_option('wp_license_manager_settings');
        
        echo "<div class='notice notice-info'>";
        echo "<p>Testing Settings:</p>";
        echo "<ul>";
        echo "<li>Settings exist: " . ($settings ? '✅' : '❌') . "</li>";
        echo "</ul>";
        echo "</div>";
    }
}
