<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class CFWAction
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
abstract class CFWAction {
	/**
	 * @since 1.0.0
	 * @access protected
	 * @var string $id
	 */
	protected $id = '';

	/**
	 * Action constructor.
	 *
	 * @param $id
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function load() {
		remove_all_actions( "wc_ajax_{$this->get_id()}" );
		add_action( "wc_ajax_{$this->get_id()}", array( $this, 'execute' ) );

		/**
		 * These legacy handlers are here because Woo adds them and 3rd party plugins
		 * sometimes expect them. This is particularly important for WooCommerce Memberships
		 * which uses these handlers to detect valid WC ajax requests when the home page is
		 * restricted
		 */
		remove_all_actions( "wp_ajax_woocommerce_{$this->get_id()}" );
		add_action( "wp_ajax_woocommerce_{$this->get_id()}", array( $this, 'execute' ) );

		remove_all_actions( "wp_ajax_nopriv_woocommerce_{$this->get_id()}" );
		add_action( "wp_ajax_nopriv_woocommerce_{$this->get_id()}", array( $this, 'execute' ) );
	}

	public function execute() {
		/**
		 * PHP Warning / Notice Suppression
		 */
		if ( ! defined( 'CFW_DEV_MODE' ) || ! CFW_DEV_MODE ) {
			ini_set( 'display_errors', 'Off' );
		}

		if ( ! defined( 'CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER' ) ) {
			// Try to prevent errors and errata from leaking into AJAX responses
			// This output buffer is discarded on out();
			@ob_end_clean(); // phpcs:ignore
			ob_start();
		}

		$this->action();
	}

	/**
	 * @param $out
	 * @param int|null $status_code
	 * @since 1.0.0
	 * @access protected
	 */
	protected function out( $out, int $status_code = null ) {
		ini_set( 'display_errors', 'Off' );

		// TODO: Execute and out (in Action) should be final and not overrideable. Action needs to NOT force JSON as an object. Could use a parameter to flip JSON to object
		if ( ! defined( 'CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER' ) ) {
			@ob_end_clean(); // @phpcs:ignore
		}

		wp_send_json( $out, $status_code );
	}
}
