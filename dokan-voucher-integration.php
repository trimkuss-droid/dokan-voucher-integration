<?php
/**
 * Plugin Name: Dokan Voucher Integration
 * Plugin URI: https://uzsakykmasaza.lt/
 * Description: Integruoja WooCommerce PDF Vouchers su Dokan - leidžia parduotuvėms patvirtinti dovanų kuponus
 * Version: 1.0.1
 * Author: Dokan Team
 * License: GPL v2 or later
 * Text Domain: dokan-voucher-integration
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * Dokan requires at least: 5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DVI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DVI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DVI_PLUGIN_VERSION', '1.0.1');

// Išjungiame, jei Dokan neįdiegtas
add_action('plugins_loaded', function() {
    if (!defined('DOKAN_PLUGIN_VERSION')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            esc_html_e('Dokan Voucher Integration reikalingas Dokan pluginas!', 'dokan-voucher-integration');
            echo '</p></div>';
        });
        return;
    }
});

// Įkeliu klases
require_once DVI_PLUGIN_PATH . 'includes/class-voucher-validator.php';
require_once DVI_PLUGIN_PATH . 'includes/class-dashboard-page.php';
require_once DVI_PLUGIN_PATH . 'includes/class-ajax-handler.php';
require_once DVI_PLUGIN_PATH . 'includes/class-order-filter.php';

// Inicijuoju pluginą
add_action('plugins_loaded', function() {
    new Dokan_Voucher_Integration\Voucher_Validator();
    new Dokan_Voucher_Integration\Dashboard_Page();
    new Dokan_Voucher_Integration\Ajax_Handler();
    new Dokan_Voucher_Integration\Order_Filter();
});

// Aktivacijos hook
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dokan_voucher_logs (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        order_id BIGINT(20) NOT NULL,
        vendor_id BIGINT(20) NOT NULL,
        voucher_code VARCHAR(255) NOT NULL,
        voucher_amount DECIMAL(10,2),
        status VARCHAR(50) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY order_id (order_id),
        KEY vendor_id (vendor_id),
        KEY voucher_code (voucher_code)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});
