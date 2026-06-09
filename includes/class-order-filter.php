<?php
/**
 * Order Filter Class - filtruoja orders kad rodytų tik "completed"
 */

namespace Dokan_Voucher_Integration;

if (!defined('ABSPATH')) {
    exit;
}

class Order_Filter {
    
    public function __construct() {
        // Filtruojame orders kurie rodomi Dokan dashboard'e
        add_filter('dokan_get_seller_orders', [$this, 'filter_completed_orders'], 10, 2);
        add_filter('dokan_dashboard_orders_args', [$this, 'filter_dashboard_orders_args'], 10, 2);
    }

    /**
     * Filtruoja orders - rodo tik "completed"
     * Šis filter naudojamas kai gauti orders per Dokan funkcijas
     */
    public function filter_completed_orders($orders, $seller_id) {
        // Sufiltrinom tik completed orders
        $filtered_orders = [];
        
        foreach ($orders as $order) {
            if (is_a($order, 'WC_Order')) {
                if ($order->get_status() === 'completed') {
                    $filtered_orders[] = $order;
                }
            }
        }
        
        return $filtered_orders;
    }

    /**
     * Filtruoja orders query arguments
     */
    public function filter_dashboard_orders_args($args, $seller_id) {
        // Jei nėra status parametro - pridedame
        if (!isset($args['post_status'])) {
            $args['post_status'] = ['wc-completed'];
        } else {
            // Jei yra - paverčiame į array ir įtraukiame 'wc-completed'
            if (is_string($args['post_status'])) {
                $args['post_status'] = [$args['post_status']];
            }
            
            // Jei 'any' - keičiame į 'wc-completed'
            if (in_array('any', $args['post_status'])) {
                $args['post_status'] = ['wc-completed'];
            }
        }
        
        return $args;
    }
}
