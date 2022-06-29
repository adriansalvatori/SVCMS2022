<?php
/**
 * Automatic postMessage scripts calculation for Xirki controls.
 *
 * @package     Xirki
 * @category    Modules
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds styles to the customizer.
 */
class Xirki_Modules_PostMessage {

	/**
	 * The object instance.
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor.
	 *
	 * @access protected
	 * @since 3.0.0
	 */
	protected function __construct() {
		add_action( 'customize_preview_init', array( $this, 'postmessage' ) );
	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Enqueues the postMessage script
	 * and adds variables to it using the wp_localize_script function.
	 * The rest is handled via JS.
	 */
	public function postmessage() {
		wp_enqueue_script( 'xirki_auto_postmessage', trailingslashit( Xirki::$url ) . 'modules/postmessage/postmessage.js', array( 'jquery', 'customize-preview' ), XIRKI_VERSION, true );
		$fields = Xirki::$fields;
		$data   = array();
		foreach ( $fields as $field ) {
			if ( isset( $field['transport'] ) && 'postMessage' === $field['transport'] && isset( $field['js_vars'] ) && ! empty( $field['js_vars'] ) && is_array( $field['js_vars'] ) && isset( $field['settings'] ) ) {
				$data[] = $field;
			}
		}
		wp_localize_script( 'xirki_auto_postmessage', 'xirkiPostMessageFields', $data );
		$extras = apply_filters( 'xirki_postmessage_script', false );
		if ( $extras ) {
			wp_add_inline_script( 'xirki_auto_postmessage', $extras, 'after' );
		}
	}
}
