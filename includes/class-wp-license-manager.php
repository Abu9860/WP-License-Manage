<?php
class WP_License_Manager {
    private $version;
    private $plugin_path;
    private $plugin_url;

    public function __construct() {
        $this->version = WP_LICENSE_MANAGER_VERSION;
        $this->plugin_path = WP_LICENSE_MANAGER_PATH;
        $this->plugin_url = WP_LICENSE_MANAGER_URL;
    }

    public function init() {
        // Load dependencies
        $this->load_dependencies();

        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }

        // Initialize API
        $this->init_api();

        add_action('admin_post_export_licenses', array($this, 'handle_export'));
        add_action('admin_post_import_licenses', array($this, 'handle_import'));
    }

    private function load_dependencies() {
        require_once $this->plugin_path . 'includes/license_check.php';
        require_once $this->plugin_path . 'includes/license_server.php';
        require_once $this->plugin_path . 'includes/api.php';
        require_once $this->plugin_path . 'includes/envato_validate.php';
        require_once $this->plugin_path . 'includes/woo_integration.php';
    }

    private function init_admin() {
        require_once $this->plugin_path . 'admin/dashboard.php';
        require_once $this->plugin_path . 'admin/settings.php';
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    private function init_api() {
        $api = new WP_License_API();
        add_action('rest_api_init', array($api, 'register_routes'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'License Manager',
            'License Manager',
            'manage_options',
            'wp-license-manager',
            'wp_license_manager_dashboard',
            'dashicons-lock',
            55
        );

        add_submenu_page(
            'wp-license-manager',
            'Documentation',
            'Documentation',
            'manage_options',
            'wp-license-manager-docs',
            array($this, 'display_documentation')
        );

        add_submenu_page(
            'wp-license-manager',
            'Settings',
            'Settings',
            'manage_options',
            'wp-license-manager-settings',
            'wp_license_manager_settings_page'
        );
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'wp-license-manager-admin',
            $this->plugin_url . 'assets/style.css',
            array(),
            $this->version
        );
    }

    public function display_documentation() {
        require_once $this->plugin_path . 'admin/documentation.php';
    }

    public function handle_export() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'export_licenses')) {
            wp_die('Unauthorized access');
        }

        global $wpdb;
        $licenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}license_manager_licenses");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="licenses-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('License Key', 'Status', 'Domain', 'Expiry Date'));

        foreach ($licenses as $license) {
            fputcsv($output, array(
                $license->license_key,
                $license->status,
                $license->domain,
                $license->expiry_date
            ));
        }

        fclose($output);
        exit;
    }

    public function handle_import() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'import_licenses')) {
            wp_die('Unauthorized access');
        }

        if (!isset($_FILES['license_csv'])) {
            wp_die('No file uploaded');
        }

        $file = fopen($_FILES['license_csv']['tmp_name'], 'r');
        $header = fgetcsv($file); // Skip header row

        global $wpdb;
        $imported = 0;

        while ($row = fgetcsv($file)) {
            $data = array_combine($header, $row);
            $wpdb->insert(
                $wpdb->prefix . 'license_manager_licenses',
                array(
                    'license_key' => $data['License Key'],
                    'status' => $data['Status'],
                    'domain' => $data['Domain'],
                    'expiry_date' => $data['Expiry Date']
                )
            );
            $imported++;
        }

        fclose($file);
        wp_redirect(add_query_arg(
            array('imported' => $imported),
            admin_url('admin.php?page=wp-license-manager')
        ));
        exit;
    }
}
