<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Integrations extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Integrations', 'checkout-wc' ), 'manage_options', 'integrations' );
	}

	public function output() {
		$this->output_form_open();
		?>
		<div class="space-y-6">
			<?php
			cfw_admin_page_section(
				cfw__( 'Google API', 'checkout-wc' ),
				cfw__( 'Used for the maps embed on the thank you page as well as Google Address Autocomplete.', 'checkout-wc' ),
				$this->get_settings()
			);

			$integration_settings_output = $this->get_integration_settings();

			if ( ! empty( $integration_settings_output ) ) {
				cfw_admin_page_section(
					cfw__( 'Themes and Plugins', 'checkout-wc' ),
					cfw__( 'Integrations with 3rd party themes and plugins.', 'checkout-wc' ),
					$integration_settings_output
				);
			}
			?>
		</div>
		<?php
		$this->output_form_close();
	}

	protected function get_settings() {
		ob_start();

		$this->output_text_input_row(
			'google_places_api_key',
			cfw__( 'Google API Key', 'checkout-wc' ),
			cfw__( 'Used by Address Autocomplete and Thank You Page Maps Embed.' ) . '<br/>' . sprintf( '%s <a target="_blank" class="text-blue-600 underline" href="https://developers.google.com/places/web-service/get-api-key">Google Cloud Platform Console</a>.', cfw__( 'Available in the', 'checkout-wc' ) )
		);

		return ob_get_clean();
	}

	protected function get_integration_settings() {
		ob_start();

		/**
		 * Fires at top of WP Admin > CheckoutWC > Advanced > Integrations
		 *
		 * Use to add additional integration settings
		 *
		 * @since 5.0.0
		 *
		 * @param PageAbstract $integrations The integrations admin page class
		 */
		do_action( 'cfw_admin_integrations_settings', $this );

		return ob_get_clean();
	}
}
