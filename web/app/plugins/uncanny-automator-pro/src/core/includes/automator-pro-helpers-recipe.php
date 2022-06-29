<?php


namespace Uncanny_Automator_Pro;

/**
 * Class Automator_Pro_Helpers_Recipe
 * @package Uncanny_Automator_Pro
 */
class Automator_Pro_Helpers_Recipe extends \Uncanny_Automator\Automator_Helpers_Recipe {

	/**
	 * @var \Uncanny_Automator_Pro\Wp_Fusion_Pro_Helpers
	 */
	public $wp_fusion;
	/**
	 * @var \Uncanny_Automator_Pro\Zapier_Pro_Helpers
	 */
	public $zapier;
	/**
	 * @var \Uncanny_Automator_Pro\Gotowebinar_Pro_Helpers
	 */
	public $gotowebinar;
	/**
	 * @var \Uncanny_Automator_Pro\Gototraining_Pro_Helpers
	 */
	public $gototraining;
	/**
	 * @var \Uncanny_Automator_Pro\Happyforms_Pro_Helpers
	 */
	public $happyforms;

	/**
	 * @var \Uncanny_Automator_Pro\Wpwh_Pro_Helpers
	 */
	public $wp_webhooks;

	/**
	 * @var \Uncanny_Automator_Pro\Google_Sheet_Pro_Helpers
	 */
	public $google_sheet;

	/**
	 * @var \Uncanny_Automator_Pro\Slack_Pro_Helpers
	 */
	public $slack;

	/**
	 * @var Wp_User_Manager_Pro_Helpers
	 */
	public $wp_user_manager;

	/**
	 * @var \Uncanny_Automator_Pro\Mailchimp_Pro_Helpers
	 */
	public $mailchimp;

	/**
	 *
	 */
	public static function load_pro_recipe_helpers() {
		global $uncanny_automator;
		$helpers           = Utilities::get_all_helper_instances();
		$automator_version = \Uncanny_Automator\InitializePlugin::PLUGIN_VERSION;
		$version_compare   = version_compare( $automator_version, '2.8.2', '<' );
		if ( $helpers ) {
			foreach ( $helpers as $integration => $class ) {
				// Fix in place to avoid fatal errors!
				if ( $version_compare && 'integromat' === $integration ) {
					continue;
				}

				if ( isset( $uncanny_automator->helpers->recipe->$integration ) ) {
					if ( property_exists( $uncanny_automator->helpers->recipe->$integration, 'pro' ) ) {
						$uncanny_automator->helpers->recipe->$integration->setPro( $class );
					}
				} else {
					$uncanny_automator->helpers->recipe->$integration = $class;
					if ( method_exists( $uncanny_automator->helpers->recipe->$integration, 'setOptions' ) ) {
						$uncanny_automator->helpers->recipe->$integration->setOptions( $class );
					}
					if ( method_exists( $uncanny_automator->helpers->recipe->$integration, 'setPro' ) ) {
						$uncanny_automator->helpers->recipe->$integration->setPro( $class );
					}
				}
			}
		}
	}

	/**
	 * Decode data coming from Automator API.
	 *
	 * @param string $message Original message string to decode.
	 * @param string $secret Secret Key used for encription
	 *
	 * @return string|array
	 */
	public static function automator_api_decode_message( $message, $secret ) {
		$tokens = false;
		if ( ! empty( $message ) and ! empty( $secret ) ) {
			$message           = base64_decode( $message );
			$method            = 'AES128';
			$iv                = substr( $message, 0, 16 );
			$encrypted_message = substr( $message, 16 );
			$tokens            = openssl_decrypt( $encrypted_message, $method, $secret, 0, $iv );
			$tokens            = maybe_unserialize( $tokens );
		}

		return $tokens;
	}
}
