<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;

/**
 * Class Twitter_Social_Integration
 */
class Twitter_Social_Integration extends Social_Integration {

	/** @var string  */
	protected $button_class = 'btn-twitter';

	/** @var string */
	protected $button_color = '#55acee';


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function get_share_url( $advocate ) {
		$text = Clean::string( AW_Referrals()->options()->social_share_text_twitter ? AW_Referrals()->options()->social_share_text_twitter : AW_Referrals()->options()->social_share_text );

		return add_query_arg(
			[
				'text' => urlencode( $advocate->process_share_text( $text ) ),
				'url'  => urlencode( $advocate->get_social_share_url() )
			],
			'https://twitter.com/intent/tweet'
		);
	}


	/**
	 * @param string $context
	 * @return string
	 */
	function get_button_text( $context = 'browser' ) {
		return __( 'Share via Twitter', 'automatewoo-referrals' );
	}

}

return new Twitter_Social_Integration();
