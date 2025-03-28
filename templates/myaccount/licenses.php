<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wc-licenses">
    <?php if (!empty($licenses)) : ?>
        <table class="woocommerce-orders-table shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th><?php _e('License Key', 'wp-license-manager'); ?></th>
                    <th><?php _e('Product', 'wp-license-manager'); ?></th>
                    <th><?php _e('Status', 'wp-license-manager'); ?></th>
                    <th><?php _e('Expiry Date', 'wp-license-manager'); ?></th>
                    <th><?php _e('Domains', 'wp-license-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licenses as $license) : 
                    $product = wc_get_product($license->product_id);
                    if (!$product) continue;
                    ?>
                    <tr>
                        <td>
                            <div class="license-key-wrapper">
                                <span class="license-key-hidden">
                                    <?php echo substr($license->license_key, 0, 6) . '****' . substr($license->license_key, -4); ?>
                                </span>
                                <span class="license-key-full" style="display: none;">
                                    <?php echo esc_html($license->license_key); ?>
                                </span>
                                <button type="button" class="toggle-license-key button button-small" data-show="<?php _e('Show', 'wp-license-manager'); ?>" data-hide="<?php _e('Hide', 'wp-license-manager'); ?>">
                                    <?php _e('Show', 'wp-license-manager'); ?>
                                </button>
                                <button type="button" class="copy-license-key button button-small" data-license="<?php echo esc_attr($license->license_key); ?>" data-copied="<?php _e('Copied!', 'wp-license-manager'); ?>">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                        </td>
                        <td><?php echo esc_html($product->get_name()); ?></td>
                        <td>
                            <span class="license-status status-<?php echo esc_attr($license->status); ?>">
                                <?php echo esc_html(ucfirst($license->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($license->expiry_date); ?></td>
                        <td>
                            <?php echo esc_html($license->domain ?: '-'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script>
        jQuery(document).ready(function($) {
            $('.toggle-license-key').click(function() {
                var wrapper = $(this).closest('.license-key-wrapper');
                var hidden = wrapper.find('.license-key-hidden');
                var full = wrapper.find('.license-key-full');
                
                hidden.toggle();
                full.toggle();
                
                $(this).text(full.is(':visible') ? $(this).data('hide') : $(this).data('show'));
            });

            $('.copy-license-key').click(function() {
                var button = $(this);
                var license = button.data('license');
                
                // Copy to clipboard
                navigator.clipboard.writeText(license).then(function() {
                    var originalText = button.html();
                    button.text(button.data('copied'));
                    
                    setTimeout(function() {
                        button.html(originalText);
                    }, 1000);
                });
            });
        });
        </script>
    <?php else : ?>
        <div class="woocommerce-message woocommerce-message--info">
            <?php _e('No licenses found.', 'wp-license-manager'); ?>
        </div>
    <?php endif; ?>
</div>
