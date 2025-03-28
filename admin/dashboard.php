<?php
if (!defined('ABSPATH')) exit;

function wp_license_manager_dashboard() {
    global $wpdb;

    // Handle bulk actions
    if (isset($_POST['bulk_action']) && check_admin_referer('wp_license_manager_bulk_action')) {
        $selected_licenses = isset($_POST['license_ids']) ? array_map('sanitize_text_field', $_POST['license_ids']) : array();
        
        if (!empty($selected_licenses)) {
            foreach ($selected_licenses as $license_key) {
                switch ($_POST['bulk_action']) {
                    case 'revoke':
                        WP_License_Server::revoke_license($license_key);
                        break;
                    case 'renew':
                        WP_License_Server::renew_license($license_key);
                        break;
                }
            }
        }
    }

    // Get license statistics
    $stats = array(
        'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}license_manager_licenses"),
        'active' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}license_manager_licenses WHERE status = 'active'"),
        'expired' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}license_manager_licenses WHERE status = 'expired'"),
        'revoked' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}license_manager_licenses WHERE status = 'revoked'")
    );

    // Handle search and filters
    $where = array("1=1");
    if (!empty($_GET['s'])) {
        $search = sanitize_text_field($_GET['s']);
        $where[] = $wpdb->prepare("(license_key LIKE %s OR domain LIKE %s)", "%$search%", "%$search%");
    }
    if (!empty($_GET['status'])) {
        $status = sanitize_text_field($_GET['status']);
        $where[] = $wpdb->prepare("status = %s", $status);
    }

    // Get licenses with pagination
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    $licenses = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}license_manager_licenses 
        WHERE " . implode(' AND ', $where) . "
        ORDER BY created_at DESC LIMIT $offset, $per_page"
    );

    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}license_manager_licenses WHERE " . implode(' AND ', $where));
    $total_pages = ceil($total_items / $per_page);

    ?>
    <div class="wrap wp-license-manager-wrap">
        <h1 class="wp-heading-inline"><?php _e('License Manager Dashboard', 'wp-license-manager'); ?></h1>
        <a href="<?php echo admin_url('admin.php?page=wp-license-manager-settings'); ?>" class="page-title-action"><?php _e('Settings', 'wp-license-manager'); ?></a>

        <!-- Statistics Cards -->
        <div class="license-stats">
            <div class="stat-card">
                <h3><?php _e('Total Licenses', 'wp-license-manager'); ?></h3>
                <span class="stat-number"><?php echo esc_html($stats['total']); ?></span>
            </div>
            <div class="stat-card active">
                <h3><?php _e('Active', 'wp-license-manager'); ?></h3>
                <span class="stat-number"><?php echo esc_html($stats['active']); ?></span>
            </div>
            <div class="stat-card expired">
                <h3><?php _e('Expired', 'wp-license-manager'); ?></h3>
                <span class="stat-number"><?php echo esc_html($stats['expired']); ?></span>
            </div>
            <div class="stat-card revoked">
                <h3><?php _e('Revoked', 'wp-license-manager'); ?></h3>
                <span class="stat-number"><?php echo esc_html($stats['revoked']); ?></span>
            </div>
        </div>

        <!-- Search & Filters -->
        <form method="get">
            <input type="hidden" name="page" value="wp-license-manager">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="status">
                        <option value=""><?php _e('All Statuses', 'wp-license-manager'); ?></option>
                        <option value="active" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'active'); ?>><?php _e('Active', 'wp-license-manager'); ?></option>
                        <option value="expired" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'expired'); ?>><?php _e('Expired', 'wp-license-manager'); ?></option>
                        <option value="revoked" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'revoked'); ?>><?php _e('Revoked', 'wp-license-manager'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php _e('Filter', 'wp-license-manager'); ?>">
                </div>
                <div class="searchbox">
                    <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search licenses...', 'wp-license-manager'); ?>">
                </div>
            </div>
        </form>

        <form method="post">
            <?php wp_nonce_field('wp_license_manager_bulk_action'); ?>
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="bulk_action">
                        <option value=""><?php _e('Bulk Actions', 'wp-license-manager'); ?></option>
                        <option value="revoke"><?php _e('Revoke', 'wp-license-manager'); ?></option>
                        <option value="renew"><?php _e('Renew', 'wp-license-manager'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php _e('Apply', 'wp-license-manager'); ?>">
                    
                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=export_licenses'), 'export_licenses'); ?>" 
                       class="button"><?php _e('Export CSV', 'wp-license-manager'); ?></a>
                    <button type="button" class="button" onclick="jQuery('#import-modal').show();">
                        <?php _e('Import CSV', 'wp-license-manager'); ?>
                    </button>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('License Key', 'wp-license-manager'); ?></th>
                        <th><?php _e('Status', 'wp-license-manager'); ?></th>
                        <th><?php _e('Domain', 'wp-license-manager'); ?></th>
                        <th><?php _e('Expiry Date', 'wp-license-manager'); ?></th>
                        <th><?php _e('Actions', 'wp-license-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licenses as $license): ?>
                        <tr>
                            <td><?php echo esc_html($license->license_key); ?></td>
                            <td><?php echo esc_html($license->status); ?></td>
                            <td><?php echo esc_html($license->domain); ?></td>
                            <td><?php echo esc_html($license->expiry_date); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('wp_license_manager_action'); ?>
                                    <input type="hidden" name="license_key" value="<?php echo esc_attr($license->license_key); ?>">
                                    
                                    <?php if ($license->status === 'active'): ?>
                                        <button type="submit" name="action" value="revoke" class="button button-small">
                                            <?php _e('Revoke', 'wp-license-manager'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="renew" class="button button-small">
                                            <?php _e('Renew', 'wp-license-manager'); ?>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <!-- Import Modal -->
        <div id="import-modal" class="wp-license-modal" style="display:none;">
            <div class="wp-license-modal-content">
                <span class="close" onclick="jQuery('#import-modal').hide();">&times;</span>
                <h2><?php _e('Import Licenses', 'wp-license-manager'); ?></h2>
                <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('import_licenses'); ?>
                    <input type="hidden" name="action" value="import_licenses">
                    <input type="file" name="license_csv" accept=".csv" required>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php _e('Import', 'wp-license-manager'); ?>">
                    </p>
                </form>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
