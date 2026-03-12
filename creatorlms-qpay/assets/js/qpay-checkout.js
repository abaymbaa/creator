/**
 * QPay Checkout Handler for CreatorLMS
 *
 * Handles QPay QR code payment flow on the checkout page.
 * Shows QR code modal and polls for payment confirmation.
 *
 * @package CreatorLMS_QPay
 * @since 1.0.0
 */

(function($) {
    'use strict';

    if (typeof crlms_qpay_params === 'undefined') {
        return;
    }

    var crlms_qpay = {

        pollTimer: null,
        pollStartTime: null,

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.createModalMarkup();
        },

        /**
         * Bind checkout form events
         */
        bindEvents: function() {
            var self = this;

            $('form.creator-lms-checkout-form').on('submit', function(e) {
                if ($('input[name="payment_method"]:checked').val() === 'qpay') {
                    e.preventDefault();
                    self.processCheckout($(this));
                    return false;
                }
            });
        },

        /**
         * Create the QR code modal markup
         */
        createModalMarkup: function() {
            if ($('#crlms-qpay-modal').length) {
                return;
            }

            var i18n = crlms_qpay_params.i18n;

            var html = '<div id="crlms-qpay-modal" class="crlms-qpay-modal" style="display:none;">' +
                '<div class="crlms-qpay-modal-overlay"></div>' +
                '<div class="crlms-qpay-modal-content">' +
                    '<div class="crlms-qpay-modal-header">' +
                        '<h3>' + i18n.scanning_title + '</h3>' +
                        '<button type="button" class="crlms-qpay-modal-close">&times;</button>' +
                    '</div>' +
                    '<div class="crlms-qpay-modal-body">' +
                        '<div class="crlms-qpay-qr-container">' +
                            '<img id="crlms-qpay-qr-image" src="" alt="QPay QR Code" />' +
                        '</div>' +
                        '<div class="crlms-qpay-status">' +
                            '<div class="crlms-qpay-spinner"></div>' +
                            '<p id="crlms-qpay-status-text">' + i18n.waiting + '</p>' +
                        '</div>' +
                        '<div id="crlms-qpay-bank-links" class="crlms-qpay-bank-links">' +
                            '<p class="crlms-qpay-bank-links-title">' + i18n.pay_with_app + '</p>' +
                            '<div class="crlms-qpay-bank-grid"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="crlms-qpay-modal-footer">' +
                        '<button type="button" class="crlms-qpay-cancel-btn">' + i18n.close + '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

            $('body').append(html);

            // Bind close actions.
            $('#crlms-qpay-modal .crlms-qpay-modal-close, #crlms-qpay-modal .crlms-qpay-cancel-btn').on('click', function() {
                crlms_qpay.closeModal();
            });

            $('#crlms-qpay-modal .crlms-qpay-modal-overlay').on('click', function() {
                crlms_qpay.closeModal();
            });
        },

        /**
         * Process checkout — submit form to create order then show QR
         */
        processCheckout: function($form) {
            var self = this;
            this.blockForm($form);

            var formData = $form.serialize();
            if (formData.indexOf('action=') === -1) {
                formData += '&action=creator_lms_checkout';
            }

            $.ajax({
                type: 'POST',
                url: crlms_qpay_params.ajax_url,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.result === 'success' && response.payment_method === 'qpay') {
                        self.showQRModal(response);
                        self.startPolling(response.order_id);
                    } else if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        self.showCheckoutError(response.message || 'Payment failed.');
                        self.unblockForm($form);
                    }
                },
                error: function() {
                    self.showCheckoutError('Something went wrong. Please try again.');
                    self.unblockForm($form);
                }
            });
        },

        /**
         * Show the QR code modal
         */
        showQRModal: function(data) {
            var i18n = crlms_qpay_params.i18n;

            // Set QR image.
            if (data.qr_image) {
                $('#crlms-qpay-qr-image').attr('src', 'data:image/png;base64,' + data.qr_image);
            }

            // Render bank deep links.
            var $grid = $('#crlms-qpay-bank-links .crlms-qpay-bank-grid');
            $grid.empty();

            if (data.urls && data.urls.length > 0) {
                $.each(data.urls, function(i, bank) {
                    var $link = $('<a>')
                        .attr('href', bank.link)
                        .attr('target', '_blank')
                        .addClass('crlms-qpay-bank-link')
                        .text(bank.description || bank.name);
                    $grid.append($link);
                });
                $('#crlms-qpay-bank-links').show();
            } else {
                $('#crlms-qpay-bank-links').hide();
            }

            // Reset status.
            $('#crlms-qpay-status-text').text(i18n.waiting);
            $('.crlms-qpay-spinner').show();

            // Show modal.
            $('#crlms-qpay-modal').fadeIn(200);
            $('body').css('overflow', 'hidden');
        },

        /**
         * Close the modal and stop polling
         */
        closeModal: function() {
            this.stopPolling();
            $('#crlms-qpay-modal').fadeOut(200);
            $('body').css('overflow', '');
            this.unblockForm($('form.creator-lms-checkout-form'));
        },

        /**
         * Start polling for payment status
         */
        startPolling: function(orderId) {
            var self = this;
            this.pollStartTime = Date.now();

            this.pollTimer = setInterval(function() {
                // Check timeout.
                if (Date.now() - self.pollStartTime > crlms_qpay_params.poll_timeout) {
                    self.stopPolling();
                    $('#crlms-qpay-status-text').text(crlms_qpay_params.i18n.expired);
                    $('.crlms-qpay-spinner').hide();
                    return;
                }

                self.checkPayment(orderId);
            }, crlms_qpay_params.poll_interval);
        },

        /**
         * Stop polling
         */
        stopPolling: function() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        /**
         * Check payment status via AJAX
         */
        checkPayment: function(orderId) {
            var self = this;

            $.ajax({
                type: 'POST',
                url: crlms_qpay_params.ajax_url,
                data: {
                    action: crlms_qpay_params.check_action,
                    nonce: crlms_qpay_params.check_payment_nonce,
                    order_id: orderId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        if (response.data.status === 'paid' && response.data.redirect_url) {
                            self.stopPolling();
                            $('#crlms-qpay-status-text').text('✓ ' + (crlms_qpay_params.i18n.paid || 'Payment confirmed!'));
                            $('.crlms-qpay-spinner').hide();
                            setTimeout(function() {
                                window.location.href = response.data.redirect_url;
                            }, 500);
                        }
                        // status === 'pending' — keep polling
                    }
                },
                error: function() {
                    // Silently continue polling on network errors.
                }
            });
        },

        /**
         * Show checkout error
         */
        showCheckoutError: function(message) {
            $('.crlms-notices-wrapper').empty();
            $('.crlms-NoticeGroup-checkout, .crlms-error, .crlms-message, .is-error, .is-success').remove();
            $('.crlms-notices-wrapper').prepend('<div class="creator-lms-checkout-notice">' + message + '</div>');

            var scrollElement = $('.crlms-NoticeGroup-updateOrderReview, .crlms-notices-wrapper');
            if (scrollElement.length) {
                $('html, body').animate({
                    scrollTop: (scrollElement.offset().top - 100)
                }, 1000);
            }
        },

        /**
         * Block form
         */
        blockForm: function($form) {
            if (typeof $.fn.block !== 'undefined') {
                $form.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
            } else {
                $form.addClass('processing');
            }
        },

        /**
         * Unblock form
         */
        unblockForm: function($form) {
            if (typeof $.fn.unblock !== 'undefined') {
                $form.unblock();
            } else {
                $form.removeClass('processing');
            }
        }
    };

    $(document).ready(function() {
        crlms_qpay.init();
    });

})(jQuery);
