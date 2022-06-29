<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class OrderPay extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Order Pay', 'checkout-wc' ), 'manage_options', 'order-pay' );
	}

	public function output() {
		$this->output_form_open();
		?>
		<div class="space-y-6">
			<?php
			cfw_admin_page_section(
				'Order Pay',
				'Control the Order Pay endpoint.',
				$this->get_settings()
			);
			?>
		</div>
		<?php
		$this->output_form_close();
	}

	protected function get_settings() {
		ob_start();

		if ( ! PlanManager::has_required_plan( PlanManager::PLUS ) ) {
			$notice = $this->get_upgrade_required_notice( PlanManager::get_english_list_of_required_plans_html( PlanManager::PLUS ) );
		}

		$this->output_checkbox_row(
			'enable_order_pay',
			cfw__( 'Enable Order Pay Page', 'checkout-wc' ),
			cfw__( 'Use CheckoutWC templates for Order Pay page.', 'checkout-wc' ),
			array(
				'enabled' => PlanManager::has_required_plan( PlanManager::PLUS ),
				'notice'  => $notice ?? '',
			)
		);

		return ob_get_clean();
	}
}
