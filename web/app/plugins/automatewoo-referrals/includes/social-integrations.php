<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Registry;

/**
 * Registry class for social integrations
 */
class Social_Integrations extends Registry {

	/** @var array */
	static $includes;

	/** @var array  */
	static $loaded = [];


	/**
	 * @return array
	 */
	static function load_includes() {

		$path = AW_Referrals()->path( '/includes/social-integrations/' );

		$includes = [];

		if ( AW_Referrals()->options()->enable_facebook_share ) {
			$includes['facebook'] = $path . 'facebook.php';
		}

		if ( AW_Referrals()->options()->enable_twitter_share ) {
			$includes['twitter'] = $path . 'twitter.php';
		}

		if ( AW_Referrals()->options()->enable_whatsapp_share ) {
			$includes['whatsapp'] = $path . 'whatsapp.php';
		}

		return apply_filters( 'automatewoo/referrals/social_integrations/includes', $includes );
	}


	/**
	 * @return int
	 */
	static function get_count() {
		return count( self::get_all() );
	}


	/**
	 * @return Social_Integration[]
	 */
	static function get_all() {
		return parent::get_all();
	}


	/**
	 * @param $id
	 * @return Social_Integration|false
	 */
	static function get( $id ) {
		return parent::get( $id );
	}


	/**
	 * @param string $integration_id
	 * @param Social_Integration $integration
	 */
	static function after_loaded( $integration_id, $integration ) {
		$integration->set_id( $integration_id );
	}

}
