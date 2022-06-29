<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;

/**
 * Class Facebook_Social_Integration
 */
class Facebook_Social_Integration extends Social_Integration {

	/** @var string  */
	protected $button_class = 'btn-facebook';

	/** @var string */
	protected $button_color = '#3B5998';


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function get_share_url( $advocate ) {
		return add_query_arg(
			[
				'u'     => urlencode( $advocate->get_social_share_url() ),
				'quote' => urlencode( $advocate->process_share_text( Clean::string( AW_Referrals()->options()->social_share_text ) ) )
			],
			'https://www.facebook.com/sharer/sharer.php'
		);
	}


	/**
	 * @param string $context
	 * @return string
	 */
	function get_button_text( $context = 'browser' ) {
		return __( 'Share via Facebook', 'automatewoo-referrals' );
	}

}

return new Facebook_Social_Integration();
