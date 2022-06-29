<?php

namespace AutomateWoo\Birthdays;

defined( 'ABSPATH' ) || exit;

/**
 * Class Privacy_Policy_Guide
 *
 * @package AutomateWoo\Birthdays
 */
class Privacy_Policy_Guide extends \AutomateWoo\Privacy_Policy_Guide {

	/**
	 * Get privacy content.
	 *
	 * @return string
	 */
	public static function get_content() {
		ob_start();
		?>
<div class="wp-suggested-text">
	<h2><?php esc_html_e( 'What we collect and store', 'automatewoo-birthdays' ); ?></h2>
	<p class="privacy-policy-tutorial"><?php esc_html_e( "AutomateWoo - Birthdays gives your customers the ability to add their birthday to their WooCommerce account data. Depending on your settings, the customer's year of birth may or may not be stored. The birth month and day are always stored when the customer chooses to provide it.", 'automatewoo-birthdays' ); ?></p>
	<h2><?php esc_html_e( 'How we use your data', 'automatewoo-birthdays' ); ?></h2>
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'Customer birthday data is used for marketing purposes on your store. This can include sending emails that are timed to coincide with the birthday of a customer.', 'automatewoo-birthdays' ); ?></p>
	<h2><?php esc_html_e( 'What we share with others', 'automatewoo-birthdays' ); ?></h2>
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'AutomateWoo does not transmit your personal customer data to our servers or to any other service. However, it is possible to create a workflow that shares customer data with third parties by using actions such as <b>MailChimp - Update List Contact Form</b>.', 'automatewoo-birthdays' ); ?></p>
</div>
		<?php
		return ob_get_clean();
	}

}
