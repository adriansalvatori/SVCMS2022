<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MP_ADDSUBACCOUNT
 *
 * @package Uncanny_Automator_Pro
 */
class MP_ADDSUBACCOUNT {

	/**
	 * Integration code
	 *
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
		if ( defined( 'MPCA_PLUGIN_SLUG' ) ) {
			$this->trigger_code = 'MPCAADDSUBACC';
			$this->trigger_meta = 'MPCAPARENTACC';
			$this->define_trigger();
		}
	}


	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name(),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/memberpress/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - MemberPress */
			'sentence'            => sprintf( esc_attr__( "A sub account is added to {{a parent account:%1\$s}}", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - MemberPress */
			'select_option_name'  => esc_attr__( "A sub account is added to {{a parent account}}", 'uncanny-automator-pro' ),
			'action'              => 'mpca_add_sub_account',
			'priority'            => 99,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'add_sub_account' ),
			'options'             => array(
				Automator()->helpers->recipe->memberpress->options->pro->all_mpca_corporate_accounts( null, $this->trigger_meta, array( 'uo_include_any' => true ) ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $txn_id
	 * @param $parent_txn_id
	 */
	public function add_sub_account( $txn_id, $parent_txn_id ) {
		$user                    = ( new \MeprTransaction( $txn_id ) )->user();
		$parent_user             = ( new \MeprTransaction( $parent_txn_id ) )->user();
		$user_id                 = absint( $user->ID );
		$parent_id               = absint( $parent_user->ID );
		$recipes                 = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_parent_account = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids      = array();
		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( absint( $required_parent_account[ $recipe_id ][ $trigger_id ] ) === $parent_id || intval( '-1' ) === intval( $required_parent_account[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( empty( $matched_recipe_ids ) ) {
			return;
		}
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			$args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'user_id'          => $user_id,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'ignore_post_id'   => true,
				'is_signed_in'     => true,
			);
			$args = Automator()->maybe_add_trigger_entry( $args, false );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						$trigger_meta = array(
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'trigger_log_id' => $result['args']['trigger_log_id'],
							'run_number'     => $result['args']['run_number'],
						);

						$trigger_meta['meta_key']   = $this->trigger_meta;
						$trigger_meta['meta_value'] = $parent_user->user_email;
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = $this->trigger_meta . '_SUBACCUNAME';
						$trigger_meta['meta_value'] = $user->user_login;
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = $this->trigger_meta . '_SUBACCFNAME';
						$trigger_meta['meta_value'] = $user->first_name;
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = $this->trigger_meta . '_SUBACCLNAME';
						$trigger_meta['meta_value'] = $user->last_name;
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = $this->trigger_meta . '_SUBACCEMAIL';
						$trigger_meta['meta_value'] = $user->user_email;
						Automator()->insert_trigger_meta( $trigger_meta );

						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}

}
