<?php


namespace Jet_FB_Paypal\Proxy;


use Jet_FB_Paypal\Pages\MetaBoxes\PaymentDetailsBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionToPaymentBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionToRecordBox;
use Jet_FB_Paypal\Pages\SinglePaymentPage;
use Jet_FB_Paypal\Pages\SingleSubscriptionPage;
use Jet_FB_Paypal\TableViews\Columns\PaymentStatusColumn;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Containers\Base_Meta_Container;
use Jet_Form_Builder\Gateways\Meta_Boxes\Payment_Details_Box;
use JetPaypalCore\JetFormBuilder\AdminSinglePagesProxy;

class AdminSinglePages extends AdminSinglePagesProxy {

	public function plugin_version_compare(): string {
		return '2.0.3';
	}

	public function pages(): array {
		add_filter(
			'jet-form-builder/page-containers/jfb-records-single',
			array( $this, 'add_box_to_record' ),
			20
		);
		add_filter(
			'jet-form-builder/page-containers/jfb-payments-single',
			array( $this, 'add_box_to_payment' ),
			20
		);

		return array(
			new SinglePaymentPage(),
			new SingleSubscriptionPage(),
		);
	}

	/**
	 * @param Base_Meta_Container[] $containers
	 *
	 * @return array
	 */
	public function add_box_to_record( array $containers ): array {
		$containers[1]->add_meta_box( new SubscriptionToRecordBox() );

		return $containers;
	}

	/**
	 * @param Base_Meta_Container[] $containers
	 *
	 * @return array
	 */
	public function add_box_to_payment( array $containers ): array {
		$containers[1]->add_meta_box( new PaymentDetailsBox() );
		$containers[1]->add_meta_box( new SubscriptionToPaymentBox() );

		return $containers;
	}

	public function on_base_need_update() {
	}

	public function on_base_need_install() {
	}
}