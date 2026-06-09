/**
 * Dokan Voucher Integration Dashboard JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    const $form = $('#dvi-voucher-form');
    const $messageBox = $('#dvi-voucher-message');
    const $submitBtn = $('#dvi-submit-btn');

    // Form submission
    $form.on('submit', function(e) {
        e.preventDefault();

        const voucherCode = $('#dvi_voucher_code').val().trim();
        const orderId = $('#dvi_order_id').val().trim();

        // Validacija
        if (!voucherCode || !orderId) {
            showMessage('Prašome užpildyti visus laukus', 'error');
            return;
        }

        // AJAX request
        submitVoucher(voucherCode, orderId);
    });

    /**
     * AJAX kupono validavimui
     */
    function submitVoucher(voucherCode, orderId) {
        // Disable button
        $submitBtn.prop('disabled', true);
        
        // Show loading
        showMessage(dviVoucher.messages.validating, 'loading');

        $.ajax({
            type: 'POST',
            url: dviVoucher.ajaxUrl,
            data: {
                action: 'dvi_validate_voucher',
                nonce: dviVoucher.nonce,
                voucher_code: voucherCode,
                order_id: orderId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    // Išvalome formą
                    $form[0].reset();
                    // Po 3 sekundžių perkraunama puslapis
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showMessage(response.data.message, 'error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                showMessage(dviVoucher.messages.error, 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
            }
        });
    }

    /**
     * Rodo žinutę
     */
    function showMessage(message, type) {
        $messageBox.removeClass('success error loading');
        
        if (type === 'loading') {
            $messageBox.html('<span class="dvi-loading"></span>' + message);
        } else {
            $messageBox.html(message);
            $messageBox.addClass(type);
        }

        // Auto remove error messages po 5 sekundžių
        if (type === 'error') {
            setTimeout(function() {
                $messageBox.fadeOut(function() {
                    $messageBox.html('').show();
                });
            }, 5000);
        }
    }

    // Clear message on input focus
    $('#dvi_voucher_code, #dvi_order_id').on('focus', function() {
        $messageBox.removeClass('success error').html('').hide();
    });
});
