<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_DELETEUSERMETA
 *
 * @package Uncanny_Automator_Pro
 */
class WP_DELETEUSERMETA {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'DELETEUSERMETA';
		$this->action_meta = 'WPUMETAFIELDS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/wordpress-core/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WordPress Core */
			'sentence'           => sprintf( esc_attr__( 'Delete {{user meta:%1$s}}', 'uncanny-automator-pro' ), $this->action_code ),
			/* translators: Action - WordPress Core */
			'select_option_name' => esc_attr__( 'Delete {{user meta}}', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'delete_user_meta' ),
			'options_callback'	  => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
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
								'input_type'        => 'repeater',
								'option_code'       => 'META_PAIRS',
								'label'             => esc_attr__( 'Meta', 'uncanny-automator-pro' ),
								'required'          => true,
								'fields'            => array(
									array(
										'input_type'      => 'text',
										'option_code'     => 'KEY',
										'label'           => esc_attr__( 'Key', 'uncanny-automator-pro' ),
										'supports_tokens' => true,
										'required'        => true,
									),
								),
								'add_row_button'    => esc_attr__( 'Add key', 'uncanny-automator-pro' ),
								'remove_row_button' => esc_attr__( 'Remove key', 'uncanny-automator-pro' ),
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
	 * @param $args
	 */
	public function delete_user_meta( $user_id, $action_data, $recipe_id, $args ) {

		if ( ! empty( $user_id ) ) {
			$meta_pairs = json_decode( $action_data['meta']['META_PAIRS'], true );
			if ( ! empty( $meta_pairs ) ) {
				foreach ( $meta_pairs as $pair ) {
					$meta_key = sanitize_title( Automator()->parse->text( $pair['KEY'], $recipe_id, $user_id, $args ) );
					delete_user_meta( $user_id, $meta_key );
				}
			}
		} else {
			$error_msg = Automator()->error_message->get( 'not-logged-in' );
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}

}
