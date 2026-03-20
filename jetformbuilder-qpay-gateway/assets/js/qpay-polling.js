(function($) {
    'use strict';

    let pollInterval = null;
    let currentInvoiceId = null;

    console.log('JFB QPay: Polling script initialized');

    $(document).on('jet-form-builder/ajax/on-success', function(event, response, $form) {
        console.log('JFB QPay: AJAX Success event received', response);
        
        if (response.qpay_checkout) {
            console.log('JFB QPay: Checkout data found, showing modal');
            
            // Prevent immediate redirect/reload by JFB core
            if (response.redirect) {
                console.log('JFB QPay: Preventing default redirect to', response.redirect);
                response.original_redirect = response.redirect;
                delete response.redirect;
            }
            if (response.reload) {
                console.log('JFB QPay: Preventing default reload');
                response.original_reload = response.reload;
                delete response.reload;
            }

            showQpayModal(response, $form);
        } else {
            console.log('JFB QPay: No checkout data in response');
        }
    });

    function showQpayModal(response, $form) {
        const data = response.qpay_checkout;
        currentInvoiceId = data.invoice_id;

        console.log('JFB QPay: Displaying modal for invoice', currentInvoiceId);

        if (!data.qr_text) {
            console.error('JFB QPay: No QR text found in checkout data');
            return;
        }

        const modalHtml = `
            <div class="jfb-qpay-modal-overlay">
                <div class="jfb-qpay-modal-card">
                    <div class="jfb-qpay-modal-header">
                        <h3>QPay Verification</h3>
                    </div>
                    <div class="jfb-qpay-qr-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qr_text)}" alt="QR Code">
                    </div>
                    <div class="jfb-qpay-modal-info">
                        Please scan the QR code with your bank app to complete the payment of <b>${data.amount} MNT</b>.
                    </div>
                    <div class="jfb-qpay-apps-grid">
                        ${(data.urls || []).map(app => `
                            <a href="${app.link}" class="jfb-qpay-app-btn" target="_blank">
                                <img src="${app.logo}" alt="${app.name}">
                                <span>${app.description}</span>
                            </a>
                        `).join('')}
                    </div>
                    <div class="jfb-qpay-polling-status">
                        <div class="jfb-qpay-spinner"></div>
                        <span>Waiting for payment...</span>
                    </div>
                    <button class="jfb-qpay-close-btn">Cancel Payment</button>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        setTimeout(() => $('.jfb-qpay-modal-overlay').addClass('active'), 10);

        startPolling(data.invoice_id, response, $form);

        $('.jfb-qpay-close-btn').on('click', function() {
            console.log('JFB QPay: Cancel button clicked');
            closeModal();
            // Restore redirect/reload if user canceled? 
            // Better to just let them stay on the form.
        });
    }

    function startPolling(invoiceId, originalResponse, $form) {
        if (pollInterval) clearInterval(pollInterval);

        console.log('JFB QPay: Starting polling for invoice', invoiceId);

        pollInterval = setInterval(function() {
            $.ajax({
                url: jfbQpay.apiUrl + '/check-status',
                method: 'GET',
                data: { invoice_id: invoiceId },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', jfbQpay.nonce);
                },
                success: function(response) {
                    console.log('JFB QPay: Status check response', response);
                    if (response.status === 'paid') {
                        console.log('JFB QPay: Payment confirmed!');
                        clearInterval(pollInterval);
                        $('.jfb-qpay-polling-status span').text('Payment Successful!');
                        $('.jfb-qpay-spinner').hide();
                        
                        setTimeout(() => {
                            closeModal();
                            
                            const redirectUrl = originalResponse.original_redirect || originalResponse.redirect;
                            if (redirectUrl) {
                                console.log('JFB QPay: Redirecting to', redirectUrl);
                                window.location.href = redirectUrl;
                            } else if (originalResponse.original_reload || originalResponse.reload) {
                                console.log('JFB QPay: Reloading page');
                                window.location.reload();
                            } else {
                                console.log('JFB QPay: Finalizing success via JFB');
                                // Restore original response but without qpay_checkout to avoid loop
                                const finalResponse = {...originalResponse};
                                delete finalResponse.qpay_checkout;
                                if (window.JetFormBuilder && window.JetFormBuilder.handleSuccess) {
                                    window.JetFormBuilder.handleSuccess(finalResponse, $form);
                                } else {
                                    // Fallback: search for a success message container or just alert
                                    console.log('JFB QPay: JFB handleSuccess not found, fallback');
                                }
                            }
                        }, 1500);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('JFB QPay: Status check failed', error);
                }
            });
        }, 5000);
    }

    function closeModal() {
        if (pollInterval) clearInterval(pollInterval);
        $('.jfb-qpay-modal-overlay').removeClass('active');
        setTimeout(() => $('.jfb-qpay-modal-overlay').remove(), 300);
    }

})(jQuery);
