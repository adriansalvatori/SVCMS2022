<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LF_CANCELMEMBERSHIP
 * @package Uncanny_Automator_Pro
 */
class LF_CANCELMEMBERSHIP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LF';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LFCANCELMEMBERSHIP';
		$this->trigger_meta = 'LFMEMBERSHIPS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/lifterlms/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - LifterLMS */
			'sentence'            => sprintf( esc_attr__( 'A user cancels {{a membership:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - LifterLMS */
			'select_option_name'  => esc_attr__( 'A user cancels {{a membership}}', 'uncanny-automator-pro' ),
			'action'              => 'llms_subscription_cancelled_by_student',
			'priority'            => 20,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'lf_cancel_membership' ),
			'options'             => [
				Automator()->helpers->recipe->lifterlms->options->all_lf_memberships( esc_attr__( 'Membership', 'uncanny-automator-pro' ), $this->trigger_meta ),
			],
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $order
	 * @param $user_id
	 */
	public function lf_cancel_membership( $order, $user_id ) {

		$product_id = $order->get( 'product_id' );

		if ( ! $product_id ) {
			return;
		}

		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes             = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_membership = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids  = [];

		//Add where Membership Matches
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( absint( $product_id ) === absint( $required_membership[ $recipe_id ][ $trigger_id ] ) ||
				     intval( $required_membership[ $recipe_id ][ $trigger_id ] ) === intval( '-1' ) ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'post_id'          => $product_id,
					'is_signed_in'     => true,
				];

				Automator()->process->user->maybe_add_trigger_entry( $args );
			}
		}
	}
}
