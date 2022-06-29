<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;

/**
 * Class Whatsapp_Social_Integration
 *
 * @since 2.5.0
 */
class Whatsapp_Social_Integration extends Social_Integration {

	/**
	 * Button classes.
	 *
	 * @var string
	 */
	protected $button_class = 'btn-whatsapp';

	/**
	 * Button color.
	 *
	 * @var string
	 */
	protected $button_color = '#00d66f';

	/**
	 * Generate share URL for an advocate.
	 *
	 * @param Advocate $advocate
	 *
	 * @return string
	 */
	function get_share_url( $advocate ) {
		// Whatsapp doesn't seem to allow line breaks, we need to replace them with spaces
		$text               = Clean::string( AW_Referrals()->options()->social_share_text );
		$contains_share_url = strstr( $text, 'share_url' );
		$text               = $advocate->process_share_text( $text );

		// Append the share URL if not already added
		if ( ! $contains_share_url ) {
			$text .= ' ' . $advocate->get_social_share_url();
		}

		return add_query_arg(
			[
				'text' => rawurlencode( $text ),
			],
			'https://api.whatsapp.com/send'
		);
	}

	/**
	 * Get the text for the button.
	 *
	 * @param string $context
	 * @return string
	 */
	function get_button_text( $context = 'browser' ) {
		return __( 'Share via WhatsApp', 'automatewoo-referrals' );
	}

}

return new Whatsapp_Social_Integration();
