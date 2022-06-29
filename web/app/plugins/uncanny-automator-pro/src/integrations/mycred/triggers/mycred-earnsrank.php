<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MYCRED_EARNSRANK
 * @package Uncanny_Automator_Pro
 */
class MYCRED_EARNSRANK {

	/**
	 * integration code
	 * @var string
	 */
	public static $integration = 'MYCRED';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'MYCREDEARNSRANK';
		$this->trigger_meta = 'EARNSRANK';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/mycred/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - myCred */
			'sentence'            => sprintf( esc_attr__( 'A user earns {{a rank:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta, 'MYCREDPOINTSTYPES' . ':' . $this->trigger_meta ),
			/* translators: Logged-in trigger - myCred */
			'select_option_name'  => esc_attr__( 'A user earns {{a rank}}', 'uncanny-automator-pro' ),
			'action'              => [
				'mycred_user_got_demoted',
				'mycred_user_got_promoted'
			],
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'mycred_user_earns_rank' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->mycred->options->list_mycred_points_types( esc_attr__( 'Points type', 'uncanny-automator-pro' ),
						'MYCREDPOINTSTYPES',
						[
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_ranks_of_selected_POINTTYPES',
						]
					),
					$uncanny_automator->helpers->recipe->field->select_field(
						$this->trigger_meta,
						__( 'Rank', 'uncanny-automator-pro' )
					),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;

	}

	/**
	 * @param $user_id
	 * @param $rank_id
	 * @param $results
	 */
	public function mycred_user_earns_rank( $user_id, $rank_id, $results ) {
		global $uncanny_automator;

		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes             = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_rank       = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_point_type = $uncanny_automator->get->meta_from_recipes( $recipes, 'MYCREDPOINTSTYPES' );
		$matched_recipe_ids  = [];

		//Add where Assigned rank matches
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( isset( $required_rank[ $recipe_id ] ) && isset( $required_rank[ $recipe_id ][ $trigger_id ] ) ) {
					if ( $required_rank[ $recipe_id ][ $trigger_id ] == $rank_id ) {
						$matched_recipe_ids[] = [
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						];
					}
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $required_rank[ $recipe_id ][ $trigger_id ] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . 'MYCREDPOINTSTYPES';
							$trigger_meta['meta_value'] = maybe_serialize( $required_point_type[ $recipe_id ][ $trigger_id ] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}

		}

		return;
	}

}