<?php


namespace Uncanny_Automator_Pro;


/**
 * Class Automator_Pro_Handle_Anonymous
 * @package Uncanny_Automator_Pro
 */
class Automator_Pro_Handle_Anonymous {

	/**
	 * Automator_Pro_Handle_Anonymous constructor.
	 */
	public function __construct() {
		add_filter( 'automator_recipe_types', [ $this, 'uap_recipe_types_func' ], 10 );
		add_filter( 'uap_error_messages', [ $this, 'uap_error_messages_func' ], 10 );
	}

	/**
	 * @param $recipe_types
	 *
	 * @return array
	 */
	public function uap_recipe_types_func( $recipe_types ) {
		$recipe_types[] = 'anonymous';

		return $recipe_types;
	}

	/**
	 * @param $error_messages
	 *
	 * @return mixed
	 */
	public function uap_error_messages_func( $error_messages ) {

		$error_messages['anon-user-action-do-nothing'] = __( 'Anonymous recipe user action set to do nothing.', 'uncanny-automator-pro' );

		return $error_messages;
	}
}
