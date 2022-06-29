<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_SETUSERMETA
 * @package Uncanny_Automator_Pro
 */
class WP_SETUSERMETA {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'SETUSERMETA';
		$this->action_meta = 'WPUMETAFIELDS';
		$this->define_action();
		add_filter( 'automator_api_setup', [ $this, 'migrate_meta_pairs' ], 10 );
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/wordpress-core/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WordPress Core */
			'sentence'           => sprintf( __( 'Set {{user meta:%1$s}}', 'uncanny-automator-pro' ), $this->action_code ),
			/* translators: Action - WordPress Core */
			'select_option_name' => __( 'Set {{user meta}}', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'set_user_meta' ),
			'options_callback'	  => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * load_options
	 *
	 * @return void
	 */
	public function load_options() {

		$options = array(
				'options_group'      => array(
					$this->action_code => array(
						array(
							'input_type' => 'repeater',
							'option_code' => 'META_PAIRS',
							'label' => __( 'Meta', 'uncanny-automator-pro' ),
							'required' => true,
							'fields'   => array(
								array(
									'input_type' => 'text',
									'option_code' => 'KEY',
									'label'       => __( 'Key', 'uncanny-automator' ),
									'supports_tokens' => true,
									'required'        => true
								),
								array(
									'input_type' => 'text',
									'option_code' => 'VALUE',
									'label'       => __( 'Value', 'uncanny-automator' ),
									'supports_tokens' => true,
									'required'        => true
								),
							),
							'add_row_button'    => __( 'Add pair', 'uncanny-automator-pro' ),
							'remove_row_button' => __( 'Remove pair', 'uncanny-automator-pro' ),
						),
					),
				),
			);

		return $options;
		
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function set_user_meta( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! empty( $user_id ) ) {
			if ( ! isset( $action_data['meta']['META_PAIRS'] ) ) {
				$meta_key   = sanitize_title( $action_data['meta']['WPUMETAKEY'] );
				$meta_value = sanitize_text_field( $action_data['meta']['WPUMETAVAL'] );
				$meta_value = sanitize_text_field( $uncanny_automator->parse->text( $action_data['meta']['WPUMETAVAL'], $recipe_id, $user_id, $args ) );
				update_user_meta( $user_id, $meta_key, $meta_value );
			} else {
				$meta_pairs = json_decode( $action_data['meta']['META_PAIRS'], true );
				if ( ! empty( $meta_pairs ) ) {
					foreach ( $meta_pairs as $pair ) {
						$meta_key   = sanitize_title( $uncanny_automator->parse->text( $pair['KEY'], $recipe_id, $user_id, $args ) );
						$meta_value = sanitize_text_field( $uncanny_automator->parse->text( $pair['VALUE'], $recipe_id, $user_id, $args ) );
						update_user_meta( $user_id, $meta_key, $meta_value );
					}
				}
			}
		} else {
			$error_msg = $uncanny_automator->error_message->get( 'not-logged-in' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

	/**
	 * Convert old meta pair into new  format.
	 *
	 */
	public function migrate_meta_pairs( $api_setup ) {
		if ( ! empty( $api_setup ) ) {
			if ( ! empty( $api_setup['recipes_object'] ) ) {
				foreach ( $api_setup['recipes_object'] as $recipe_key => $recipe ) {
					if ( 'trash' !== $recipe['post_status'] && ! empty( $recipe['actions'] ) ) {
						foreach ( $recipe['actions'] as $action_key => $action ) {
							if ( $action['meta']['code'] === $this->action_code && isset( $action['meta']['WPUMETAVAL'] ) && ! isset( $action['meta']['META_PAIRS'] ) ) {
								if ( isset( $action['meta']['WPUMETAVAL'] ) && isset( $action['meta']['WPUMETAKEY'] ) ) {
									$api_setup['recipes_object']
									[ $recipe_key ]
									['actions']
									[ $action_key ]
									['meta']
									['META_PAIRS']
									[]
										= [
										'KEY'   => $action['meta']['WPUMETAKEY'],
										'VALUE' => $action['meta']['WPUMETAVAL'],
									];
								}
							}
						}
					}
				}
			}
		}

		return $api_setup;
	}
}
