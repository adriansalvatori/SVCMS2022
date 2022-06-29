<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Action\SmartyStreetsAddressValidationAction;
use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class SmartyStreets extends FeaturesAbstract {
	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter ) {
		parent::__construct( $enabled, $available, $required_plans_list, $settings_getter );
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_after_admin_page_address_options_section', array( $this, 'output_admin_settings' ) );
	}

	protected function run_if_cfw_is_enabled() {
		add_action( 'cfw_checkout_customer_info_tab', array( $this, 'output_modal' ), 60 );
		add_filter( 'cfw_event_data', array( $this, 'add_localized_settings' ) );
	}

	/**
	 * @param PageAbstract $checkout_admin_page
	 */
	public function output_admin_settings( PageAbstract $checkout_admin_page ) {
		if ( ! $this->available ) {
			$notice = $checkout_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$checkout_admin_page->output_checkbox_row(
			'enable_smartystreets_integration',
			cfw__( 'Enable Smarty Address Validation', 'checkout-wc' ),
			cfw__( 'Validates shipping address with Smarty.com and provides alternative, corrected addresses for incorrect or incomplete addresses.', 'checkout-wc' ),
			array(
				'enabled' => PlanManager::has_required_plan( PlanManager::PRO ),
				'notice'  => $notice ?? '',
			)
		);

		$checkout_admin_page->output_text_input_row(
			'smartystreets_auth_id',
			cfw__( 'Smarty Auth ID', 'checkout-wc' ),
			cfw__( 'Smarty Auth ID. Available in your <a target="_blank" href="https://www.smarty.com/account/keys">Smarty Account</a>.', 'checkout-wc' ),
			array( 'nested' => true )
		);

		$checkout_admin_page->output_text_input_row(
			'smartystreets_auth_token',
			cfw__( 'Smarty Auth Token', 'checkout-wc' ),
			cfw__( 'Smarty Auth Token. Available in your <a target="_blank" href="https://www.smarty.com/account/keys">Smarty Account</a>.', 'checkout-wc' ),
			array( 'nested' => true )
		);
	}

	/**
	 * @param array $event_data
	 * @return array
	 */
	public function add_localized_settings( array $event_data ): array {
		$event_data['settings']['enable_smartystreets_integration'] = apply_filters( 'cfw_enable_smartystreets_integration', true );

		return $event_data;
	}

	public function output_modal() {
		$translated_button_label = __( 'Use This Address', 'checkout-wc' );
		?>
		<a href="#cfw_smartystreets_confirm_modal" class="cfw-smartystreets-modal-trigger cfw-hidden"></a>
		<div id="cfw_smartystreets_confirm_modal" class="cfw-hidden">
			<h2><?php _e( 'Address Verification', 'checkout-wc' ); ?></h2>

			<h4 class="cfw-small">
				<?php _e( 'The shipping address you provided does not match the suggested address from our verification service. Please verify your address.', 'checkout-wc' ); ?>
			</h4>

			<div class="container">
				<div class="row">
					<div class="cfw-smartystreets-option-wrap col-5 cfw-selected-smartystreets-address">
						<h4>
							<label>
								<input type="radio" name="cfw_smartystreets_address_selection" class="cfw-radio-user-address" value="user-value" checked /> <?php _e( 'You Entered', 'checkout-wc' ); ?>
							</label>
						</h4>

						<p class="cfw-smartystreets-user-address"></p>

						<p class="cfw-smartystreets-button-wrap">
							<a href="javascript:" class="cfw-smartystreets-button cfw-primary-btn cfw-smartystreets-user-address-button"><?php echo $translated_button_label; ?></a> 
						</p>
					</div>

					<div class="cfw-smartystreets-option-wrap col-5 offset-2">
						<h4>
							<label>
								<input type="radio" name="cfw_smartystreets_address_selection" class="cfw-radio-suggested-address" value="suggested-value" /> <?php _e( 'We Suggest', 'checkout-wc' ); ?>
							</label>
						</h4>

						<p class="cfw-smartystreets-suggested-address"></p>

						<p class="cfw-smartystreets-button-wrap">
							<a href="javascript:" class="cfw-smartystreets-button cfw-primary-btn cfw-smartystreets-suggested-address-button"><?php echo $translated_button_label; ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function load_ajax_action() {
		( new SmartyStreetsAddressValidationAction( $this->settings_getter->get_setting( 'smartystreets_auth_id' ), $this->settings_getter->get_setting( 'smartystreets_auth_token' ) ) )->load();
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_smartystreets_integration', 'no' );
	}
}
