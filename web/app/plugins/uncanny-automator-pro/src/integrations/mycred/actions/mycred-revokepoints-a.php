<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MYCRED_REVOKEPOINTS_A
 * @package Uncanny_Automator_Pro
 */
class MYCRED_REVOKEPOINTS_A {
	/**
	 * integration code
	 * @var string
	 */
	public static $integration = 'MYCRED';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'MYCREDREVOKEPOINTS';
		$this->action_meta = 'MYCREDPOINTS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/mycred/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - myCred */
			'sentence'           => sprintf( __( 'Revoke {{a number:%1$s}} {{of a specific type of:%2$s}} points from the user', 'uncanny-automator-pro' ), 'MYCREDPOINTVALUE:' . $this->action_meta, $this->action_meta ),
			/* translators: Action - myCred */
			'select_option_name' => __( 'Revoke {{points}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'revoke_mycred_points' ),
			'options'            => array(),
			'options_group'      => array(
				$this->action_meta => array(
					$uncanny_automator->helpers->recipe->mycred->options->list_mycred_points_types(
						__( 'Point type', 'uncanny-automator-pro' ),
						$this->action_meta,
						array(
							'token'       => false,
							'is_ajax'     => false,
							'include_all' => true,
						)
					),
					array(
						'input_type'      => 'float',
						'option_code'     => 'MYCREDPOINTVALUE',
						'label'           => __( 'Points', 'uncanny-automator-pro' ),
						'supports_tokens' => true,
						'required'        => true,
					),
					array(
						'input_type'      => 'text',
						'option_code'     => 'MYCREDDESCRIPTION',
						'label'           => __( 'Description', 'uncanny-automator-pro' ),
						'description'     => __( 'If this is left blank, the description "Revoked by Uncanny Automator" will be used', 'uncanny-automator-pro' ),
						'supports_tokens' => true,
						'required'        => false,
					),
				),

			),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function revoke_mycred_points( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$points_type = $action_data['meta'][ $this->action_meta ];

		$points = $uncanny_automator->parse->text( $action_data['meta']['MYCREDPOINTVALUE'], $recipe_id, $user_id, $args );

		$description = __( 'Revoked by Uncanny Automator', 'uncanny-automator-pro' );

		if ( ! empty( $action_data['meta']['MYCREDDESCRIPTION'] ) ) {
			$description = $uncanny_automator->parse->text( $action_data['meta']['MYCREDDESCRIPTION'], $recipe_id, $user_id, $args );
		}

		if ( $points_type == 'ua-all-mycred-points' ) {
			$pointTypes = mycred_get_types();
			if ( is_array( $pointTypes ) && ! empty( $pointTypes ) ) {
				foreach ( $pointTypes as $key => $value ) {
					mycred_subtract( $value, absint( $user_id ), absint( - $points ), $description, '', '', $key );
				}
			}
		} else {
			$reference = $uncanny_automator->parse->text( $action_data['meta']['MYCREDPOINTS_readable'], $recipe_id, $user_id, $args );
			mycred_subtract( $reference, absint( $user_id ), absint( - $points ), $description, '', '', $points_type );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
