<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MP_USERMEMPAUSEDPROD
 * @package Uncanny_Automator_Pro
 */
class MP_USERMEMPAUSEDPROD {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MP';

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
		$this->trigger_code = 'USERMEMPAUSEDPROD';
		$this->trigger_meta = 'MPPRODUCT';
		$this->define_trigger();
	}


	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/memberpress/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - MemberPress */
			'sentence'            => sprintf( esc_attr__( "A user's membership to {{a specific product:%1\$s}} is paused", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - MemberPress */
			'select_option_name'  => esc_attr__( "A user's membership to {{a specific product}} is paused", 'uncanny-automator-pro' ),
			'action'              => 'mepr_subscription_transition_status',
			'priority'            => 99,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'mp_product_paused' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->memberpress->options->pro->all_memberpress_products_recurring( null, $this->trigger_meta, [ 'uo_include_any' => true ] ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $old_status
	 * @param $new_status
	 * @param $sub
	 */
	public function mp_product_paused( $old_status, $new_status, $sub ) {

		if ( 'suspended' !== (string) $new_status ) {
			return;
		}

		global $uncanny_automator;
		$product_id = absint( $sub->rec->product_id );
		// Do what you need
		$user_id = absint( $sub->rec->user_id );

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product   = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( absint( $required_product[ $recipe_id ][ $trigger_id ] ) === $product_id || intval( '-1' ) === intval( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
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
					'ignore_post_id'   => true,
				];
				$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = $this->trigger_meta;
							$trigger_meta['meta_value'] = $product_id;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
