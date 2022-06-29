<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class ThankYou extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Thank You', 'checkout-wc' ), 'manage_options', 'thank-you' );
	}

	public function output() {
		$this->output_form_open();
		?>
		<div class="space-y-6">
			<?php
			cfw_admin_page_section(
				'Thank You',
				'Control the Order Received / Thank You endpoint.',
				$this->get_settings()
			);
			?>
		</div>
		<?php
		$this->output_form_close();
	}

	protected function get_settings() {
		$settings                 = SettingsManager::instance();
		$thank_you_order_statuses = false === $settings->get_setting( 'thank_you_order_statuses' ) ? array() : (array) $settings->get_setting( 'thank_you_order_statuses' );

		ob_start();

		if ( ! PlanManager::has_required_plan( PlanManager::PLUS ) ) {
			$notice = $this->get_upgrade_required_notice( PlanManager::get_english_list_of_required_plans_html( PlanManager::PLUS ) );
		}

		$this->output_checkbox_row(
			'enable_thank_you_page',
			cfw__( 'Enable Thank You Page Template', 'checkout-wc' ),
			cfw__( 'Enable thank you page / order received template.', 'checkout-wc' ),
			array(
				'enabled' => PlanManager::has_required_plan( PlanManager::PLUS ),
				'notice'  => $notice ?? '',
			)
		);

		$this->output_checkbox_group(
			'thank_you_order_statuses',
			cfw__( 'Order Statuses', 'checkout-wc' ),
			cfw__( 'Choose which Order Statuses are shown as a progress bar on the Thank You page.', 'checkout-wc' ),
			wc_get_order_statuses(),
			$thank_you_order_statuses,
			array(
				'enabled' => PlanManager::has_required_plan( PlanManager::PLUS ),
				'nested'  => true,
			)
		);

		$this->output_checkbox_row(
			'enable_map_embed',
			cfw__( 'Enable Map Embed', 'checkout-wc' ),
			cfw__( 'Enable or disable Google Maps embed on Thank You page. Requires Google API key.', 'checkout-wc' ),
			array(
				'enabled' => PlanManager::has_required_plan( PlanManager::PLUS ),
				'nested'  => true,
			)
		);

		$this->output_checkbox_row(
			'override_view_order_template',
			cfw__( 'Enable Thank You Page Template For Viewing Orders in My Account', 'checkout-wc' ),
			cfw__( 'When checked, viewing orders in My Account will use the Thank You page template.', 'checkout-wc' ),
			array(
				'enabled' => PlanManager::has_required_plan( PlanManager::PLUS ),
				'nested'  => true,
			)
		);

		return ob_get_clean();
	}
}





