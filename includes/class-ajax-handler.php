<?php
/**
 * AJAX Handler Class
 */

namespace Dokan_Voucher_Integration;

if (!defined('ABSPATH')) {
    exit;
}

class Ajax_Handler {
    
    public function __construct() {
        add_action('wp_ajax_dvi_validate_voucher', [$this, 'validate_voucher']);
    }

    /**
     * AJAX handler kupono validavimui
     */
    public function validate_voucher() {
        // Tikrinama nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dvi_voucher_nonce')) {
            wp_send_json_error([
                'message' => __('Saugumo patikra nepavyko', 'dokan-voucher-integration')
            ]);
        }

        // Tikrinama ar user yra vendor
        if (!is_user_logged_in() || !dokan_is_seller()) {
            wp_send_json_error([
                'message' => __('Neturite teisės atlikti šią operaciją', 'dokan-voucher-integration')
            ]);
        }

        // Gaunami POST parametrai
        $voucher_code = isset($_POST['voucher_code']) ? sanitize_text_field($_POST['voucher_code']) : '';
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

        // Validacija
        if (empty($voucher_code) || empty($order_id)) {
            wp_send_json_error([
                'message' => __('Prašome užpildyti visus laukus', 'dokan-voucher-integration')
            ]);
        }

        // Tikrinama ar vendor gali matyti šį orderį
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error([
                'message' => __('Užsakymas nerastas', 'dokan-voucher-integration')
            ]);
        }

        $current_user_id = get_current_user_id();
        $order_vendor_id = get_post_field('post_author', $order_id);

        // Jei ne savininkas - error
        if ($order_vendor_id != $current_user_id) {
            wp_send_json_error([
                'message' => __('Neturite teisės redaguoti šio užsakymo', 'dokan-voucher-integration')
            ]);
        }

        // Validuojame kuponą
        $result = Voucher_Validator::validate_voucher($voucher_code, $order_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}
