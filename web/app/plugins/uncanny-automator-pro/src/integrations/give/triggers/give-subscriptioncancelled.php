<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GIVE_SUBSCRIPTIONCANCELLED
 *
 * @package Uncanny_Automator_Pro
 */
class GIVE_SUBSCRIPTIONCANCELLED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GIVEWP';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'GIVESUBSCRIPTION';
		$this->trigger_meta = 'SUBSCRIPTIONCANCELLED';
		$this->define_trigger();
	}

	/**
	 * Define trigger settings
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/givewp/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - GiveWP */
			'sentence'            => sprintf( esc_attr__( 'A user cancels a recurring donation from {{a specific form:%1$s}}', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - GiveWP */
			'select_option_name'  => esc_attr__( 'A user cancels a recurring donation from {{a specific form}}', 'uncanny-automator-pro' ),
			'action'              => 'give_subscription_cancelled',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'give_subscription_cancelled' ),
			'options'             => array(
				Automator()->helpers->recipe->give->options->pro->list_all_give_recurring_forms( null, $this->trigger_meta ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $subscription_id
	 * @param $subscription
	 *
	 * @return void
	 */
	public function give_subscription_cancelled( $subscription_id, $subscription ) {
		$give_form_id = $subscription->form_id;
		$amount       = $subscription->recurring_amount;
		$donor        = $subscription->donor;
		$user_id      = $donor->user_id;

		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_form      = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_form[ $recipe_id ] ) && isset( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
					//Add where option is set to Any Form
					if ( intval( '-1' ) === intval( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					} //Add where option is set to Specific Form
					elseif ( $required_form[ $recipe_id ][ $trigger_id ] == $give_form_id ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					}
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_ID';
							$trigger_meta['meta_value'] = maybe_serialize( $give_form_id );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( get_the_title( $give_form_id ) );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_AMOUNT';
							$trigger_meta['meta_value'] = maybe_serialize( number_format( $amount, 2, '.' ) );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_DONOR';
							$trigger_meta['meta_value'] = maybe_serialize( $donor->name );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_DONOREMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $donor->email );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
