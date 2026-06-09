<?php
/**
 * Dokan Dashboard Page Class
 */

namespace Dokan_Voucher_Integration;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Page {
    
    public function __construct() {
        add_action('dokan_dashboard_content_after', [$this, 'add_voucher_section']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Prideda voucher sekcijį prie Dokan dashboard
     */
    public function add_voucher_section() {
        // Tik vendor'iams
        if (!dokan_is_seller_dashboard()) {
            return;
        }

        // Tikrinama ar esi vendor'is
        $current_user = wp_get_current_user();
        $vendor = dokan_get_store_info($current_user->ID);
        
        if (!$vendor) {
            return;
        }

        echo $this->render_voucher_form();
    }

    /**
     * Renderina voucher formą
     */
    private function render_voucher_form() {
        ob_start();
        ?>
        <div class="dokan-dashboard-content">
            <div class="dokan-panel dokan-voucher-panel" style="margin-top: 20px;">
                <div class="dokan-panel-heading">
                    <h3 class="dokan-panel-title">
                        <i class="fa fa-ticket"></i> 
                        <?php esc_html_e('Patvirtinti dovanų kuponą', 'dokan-voucher-integration'); ?>
                    </h3>
                </div>
                
                <div class="dokan-panel-body">
                    <div id="dvi-voucher-message" style="margin-bottom: 15px;"></div>
                    
                    <form id="dvi-voucher-form" method="post">
                        <div class="form-group">
                            <label for="dvi_voucher_code">
                                <?php esc_html_e('Kupono kodas', 'dokan-voucher-integration'); ?>
                            </label>
                            <input 
                                type="text" 
                                id="dvi_voucher_code" 
                                name="voucher_code"
                                class="form-control"
                                placeholder="<?php esc_attr_e('Įveskite kupono kodą', 'dokan-voucher-integration'); ?>"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="dvi_order_id">
                                <?php esc_html_e('Užsakymo ID', 'dokan-voucher-integration'); ?>
                            </label>
                            <input 
                                type="number" 
                                id="dvi_order_id" 
                                name="order_id"
                                class="form-control"
                                placeholder="<?php esc_attr_e('Įveskite užsakymo numerį', 'dokan-voucher-integration'); ?>"
                                required
                            />
                        </div>

                        <button 
                            type="submit" 
                            class="btn btn-primary"
                            id="dvi-submit-btn"
                        >
                            <i class="fa fa-check"></i> 
                            <?php esc_html_e('Patvirtinti kuponą', 'dokan-voucher-integration'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Įkelia JS ir CSS
     */
    public function enqueue_scripts() {
        if (!is_admin() && dokan_is_seller_dashboard()) {
            wp_enqueue_style(
                'dvi-dashboard-style',
                DVI_PLUGIN_URL . 'assets/css/dashboard.css',
                [],
                DVI_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'dvi-dashboard-script',
                DVI_PLUGIN_URL . 'assets/js/dashboard.js',
                ['jquery'],
                DVI_PLUGIN_VERSION,
                true
            );

            wp_localize_script('dvi-dashboard-script', 'dviVoucher', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dvi_voucher_nonce'),
                'messages' => [
                    'validating' => __('Validuojama...', 'dokan-voucher-integration'),
                    'success' => __('Kuponas sėkmingai patvirtintas!', 'dokan-voucher-integration'),
                    'error' => __('Klaida patvirtinant kuponą', 'dokan-voucher-integration'),
                ]
            ]);
        }
    }
}
