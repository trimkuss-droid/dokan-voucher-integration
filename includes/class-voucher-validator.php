<?php
/**
 * Voucher Validator Class
 */

namespace Dokan_Voucher_Integration;

if (!defined('ABSPATH')) {
    exit;
}

class Voucher_Validator {
    
    public function __construct() {
        // Hook'ai suspense'ui
    }

    /**
     * Validuoja kupono kodą
     * 
     * @param string $voucher_code - Kupono kodas
     * @param int $order_id - Užsakymo ID
     * @return array - ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function validate_voucher($voucher_code, $order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return [
                'success' => false,
                'message' => __('Užsakymas nerastas', 'dokan-voucher-integration')
            ];
        }

        // Patikrinama ar kuponas jau panaudotas
        $already_used = self::is_voucher_used($voucher_code);
        if ($already_used) {
            return [
                'success' => false,
                'message' => __('Šis kuponas jau panaudotas', 'dokan-voucher-integration')
            ];
        }

        // Gaunamos kupono duomenys iš WooCommerce PDF Vouchers
        $voucher_data = self::get_voucher_data($voucher_code);
        
        if (!$voucher_data) {
            return [
                'success' => false,
                'message' => __('Kupono kodas neteisingas', 'dokan-voucher-integration')
            ];
        }

        // Patikrinama kupono suma / tipas
        $validation = self::validate_voucher_amount($voucher_data, $order);
        if (!$validation['success']) {
            return $validation;
        }

        // Visa OK - saugom loginą
        self::log_voucher_usage($order_id, $voucher_code, $voucher_data);

        // Keičiame orderio statusą į 'completed'
        $order->set_status('completed');
        $order->add_order_note(__('Kupono kodas patvirtintas ir užsakymas baigtas', 'dokan-voucher-integration'));
        $order->save();

        return [
            'success' => true,
            'message' => __('Kuponas sėkmingai patvirtintas!', 'dokan-voucher-integration'),
            'data' => [
                'order_id' => $order_id,
                'voucher_code' => $voucher_code,
                'amount' => $voucher_data['amount']
            ]
        ];
    }

    /**
     * Patikrina ar kuponas jau panaudotas
     */
    private static function is_voucher_used($voucher_code) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dokan_voucher_logs 
            WHERE voucher_code = %s AND status = %s",
            $voucher_code,
            'completed'
        ));
        
        return intval($result) > 0;
    }

    /**
     * Gauna kupono duomenis iš WooCommerce PDF Vouchers
     */
    private static function get_voucher_data($voucher_code) {
        // WooCommerce PDF Vouchers naudoja custom post type
        $args = [
            'post_type' => 'wc_voucher',
            'meta_query' => [
                [
                    'key' => '_voucher_code',
                    'value' => sanitize_text_field($voucher_code),
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ];

        $vouchers = get_posts($args);
        
        if (empty($vouchers)) {
            return false;
        }

        $voucher = $vouchers[0];
        
        // Gaunami metaduomenys
        return [
            'id' => $voucher->ID,
            'code' => $voucher_code,
            'amount' => floatval(get_post_meta($voucher->ID, '_voucher_amount', true)),
            'type' => get_post_meta($voucher->ID, '_voucher_type', true), // fixed, percentage
            'status' => get_post_meta($voucher->ID, '_voucher_status', true),
            'expiry' => get_post_meta($voucher->ID, '_voucher_expiry_date', true),
            'max_uses' => get_post_meta($voucher->ID, '_voucher_max_uses', true),
        ];
    }

    /**
     * Validuoja kupono sumą pagal orderį
     */
    private static function validate_voucher_amount($voucher_data, $order) {
        $order_total = floatval($order->get_total());
        
        // Jei tipo nėra arba blogas - error
        if (empty($voucher_data['type'])) {
            return [
                'success' => false,
                'message' => __('Kupono konfiguracija neteisinga', 'dokan-voucher-integration')
            ];
        }

        // Jei fiksuota suma - tiesiog patikrinkite
        if ($voucher_data['type'] === 'fixed') {
            if ($voucher_data['amount'] > $order_total) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        __('Kupono suma (%.2f) viršija užsakymo sumą (%.2f)', 'dokan-voucher-integration'),
                        $voucher_data['amount'],
                        $order_total
                    )
                ];
            }
        }

        // Jei procentinis - jei < 0 arba > 100
        if ($voucher_data['type'] === 'percentage') {
            if ($voucher_data['amount'] < 0 || $voucher_data['amount'] > 100) {
                return [
                    'success' => false,
                    'message' => __('Kupono procentas neteisingas', 'dokan-voucher-integration')
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Saugom kupono panaudojimo loginą
     */
    private static function log_voucher_usage($order_id, $voucher_code, $voucher_data) {
        global $wpdb;

        $vendor_id = get_post_field('post_author', $order_id);
        
        $wpdb->insert(
            $wpdb->prefix . 'dokan_voucher_logs',
            [
                'order_id' => intval($order_id),
                'vendor_id' => intval($vendor_id),
                'voucher_code' => sanitize_text_field($voucher_code),
                'voucher_amount' => floatval($voucher_data['amount']),
                'status' => 'completed'
            ],
            ['%d', '%d', '%s', '%f', '%s']
        );
    }
}
