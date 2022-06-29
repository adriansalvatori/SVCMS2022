<?php

namespace Uncanny_Automator_Pro;

/**
 * Class PMP_RENEWSMEMBERSHIP
 * @package Uncanny_Automator_Pro
 */
class PMP_RENEWSMEMBERSHIP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'PMP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'PMPRENEWSMEMBERSHIP';
		$this->trigger_meta = 'PMPMEMBERSHIP';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$options = $uncanny_automator->helpers->recipe->paid_memberships_pro->options->all_memberships( __( 'Membership', 'uncanny-automator' ) );

		$options['options'] = array( '-1' => __( 'Any membership', 'uncanny-automator' ) ) + $options['options'];

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/paid-memberships-pro/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - Paid Memberships Pro */
			'sentence'            => sprintf( __( 'A user renews {{an expired membership:%1$s}}', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Paid Memberships Pro */
			'select_option_name'  => __( 'A user renews {{an expired membership}}', 'uncanny-automator' ),
			'action'              => 'pmpro_before_change_membership_level',
			'priority'            => 100,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'pmpro_subscription_renewed' ),
			'options'             => [
				$options,
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Action to run before the membership level changes.
	 *
	 * @param int $level_id ID of the level changed to.
	 * @param int $user_id ID of the user changed.
	 * @param array $old_levels array of prior levels the user belonged to.
	 *                          $param int $cancel_level ID of the level being cancelled if specified
	 */
	public function pmpro_subscription_renewed( $level_id, $user_id, $old_levels, $cancel_level ) {

		global $uncanny_automator;

		if ( empty( $user_id ) || empty( $level_id ) ) {
			return;
		}

		if ( absint( $level_id ) ) {
			$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
			$required_level     = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
			$matched_recipe_ids = [];

			$expired_levels = [];

			foreach ( $old_levels as $old_level ) {
				if ( ! empty( $old_level->enddate ) ) {
					$todays_date     = current_time( 'timestamp' );
					$expiration_date = $old_level->enddate;
					$time_left       = $expiration_date - $todays_date;

					// is the membership expired
					if ( $time_left <= 0 ) {
						$expired_levels[] = $old_level->ID;
					}
				}
			}

			// The level being added to the user's levels must already be part of there expired level
			if ( in_array( $level_id, $expired_levels ) ) {
				//Add where option is set to Any membership
				foreach ( $recipes as $recipe_id => $recipe ) {
					foreach ( $recipe['triggers'] as $trigger ) {
						$trigger_id = $trigger['ID'];//return early for all memberships
						if ( - 1 === intval( $required_level[ $recipe_id ][ $trigger_id ] ) ) {
							$matched_recipe_ids[] = [
								'recipe_id'  => $recipe_id,
								'trigger_id' => $trigger_id,
							];

							break;
						}
					}
				}

				//Add where Membership ID is set for trigger
				foreach ( $recipes as $recipe_id => $recipe ) {
					foreach ( $recipe['triggers'] as $trigger ) {
						$trigger_id = $trigger['ID'];//return early for all memberships
						if ( (int) $required_level[ $recipe_id ][ $trigger_id ] === (int) $level_id ) {
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
					$args = [
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'user_id'          => $user_id,
						'recipe_to_match'  => $matched_recipe_id['recipe_id'],
						'trigger_to_match' => $matched_recipe_id['trigger_id'],
						'ignore_post_id'   => true,
						'is_signed_in'     => true,
					];

					$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

					if ( $result ) {
						foreach ( $result as $r ) {
							if ( true === $r['result'] ) {
								do_action( 'uap_save_pmp_membership_level', $level_id, $r['args'], $user_id, $this->trigger_meta );
								$uncanny_automator->maybe_trigger_complete( $r['args'] );
							}
						}
					}
				}
			}
		}

		return;
	}
}
