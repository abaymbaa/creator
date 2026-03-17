<?php

namespace Jet_FB_Qpay\Rest_Endpoints;

use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;

class Receipt_Page {

	public function register_endpoint() {
		register_rest_route( 'jet-fb-qpay/v1', '/receipt/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'render_page' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function render_page( $request ) {
		$id      = $request->get_param( 'id' );
		$qr_image = get_post_meta( $id, '_qpay_qr_image', true );
		$urls     = json_decode( get_post_meta( $id, '_qpay_urls', true ), true );

		if ( ! $qr_image ) {
			return new \WP_Error( 'no_qr', 'QR code not found for this payment.' );
		}

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>QPay Payment</title>
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
				.container { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 90%; }
				h2 { color: #1a1a1a; margin-bottom: 10px; }
				p { color: #666; margin-bottom: 30px; }
				.qr-image { width: 250px; height: 250px; border: 1px solid #eee; border-radius: 12px; padding: 10px; margin-bottom: 30px; }
				.bank-links { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
				.bank-link { padding: 10px 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #333; font-size: 14px; transition: all 0.2s; }
				.bank-link:hover { background: #e9ecef; border-color: #ccc; }
				.status-indicator { margin-top: 30px; font-size: 14px; color: #007bff; display: flex; align-items: center; justify-content: center; gap: 8px; }
				.spinner { width: 16px; height: 16px; border: 2px solid #007bff; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; }
				@keyframes spin { to { transform: rotate(360deg); } }
			</style>
		</head>
		<body>
			<div class="container">
				<h2>Scan to Pay</h2>
				<p>Please open your bank app and scan the QR code below.</p>
				<img src="data:image/png;base64,<?php echo esc_attr( $qr_image ); ?>" class="qr-image" />
				
				<?php if ( ! empty( $urls ) ) : ?>
					<div class="bank-links">
						<?php foreach ( $urls as $url ) : ?>
							<a href="<?php echo esc_url( $url['link'] ); ?>" class="bank-link" target="_blank">
								<?php echo esc_html( $url['description'] ?: $url['name'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="status-indicator">
					<div class="spinner"></div>
					Waiting for payment...
				</div>
			</div>

			<script>
				const paymentId = <?php echo (int) $id; ?>;
				const checkUrl = '<?php echo get_rest_url( null, 'jet-fb-qpay/v1/check-status/' ); ?>' + paymentId;
				
				function pollStatus() {
					fetch(checkUrl)
						.then(response => response.json())
						.then(data => {
							if (data.status === 'success') {
								window.location.href = data.redirect;
							} else {
								setTimeout(pollStatus, 3000);
							}
						})
						.catch(() => setTimeout(pollStatus, 3000));
				}
				
				pollStatus();
			</script>
		</body>
		</html>
		<?php
		$html = ob_get_clean();

		header( 'Content-Type: text/html; charset=UTF-8' );
		echo $html;
		exit;
	}
}
