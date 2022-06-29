<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WM_USEREGISTER
 * @package Uncanny_Automator_Pro
 */
class WM_USERREGISTER {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WISHLISTMEMBER';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WLMUSERREGISTERED';
		$this->trigger_meta = 'WLMFORMS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wishlist-member/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - Wishlist Member */
			'sentence'            => sprintf( esc_attr__( 'A user submits {{a registration form:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Wishlist Member */
			'select_option_name'  => esc_attr__( 'A user submits {{a registration form}}', 'uncanny-automator-pro' ),
			'action'              => 'wishlistmember_user_registered',
			'priority'            => 99,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'wm_user_registered' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->wishlist_member->options->pro->wm_get_all_forms( null, $this->trigger_meta,
					[ 'any' => true ] ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $user_id
	 * @param $data
	 */
	public function wm_user_registered( $user_id, $data ) {
		global $uncanny_automator;

		if ( ! $user_id ) {
			return;
		}

		if ( ! isset( $data['wpm_id'] ) ) {
			return;
		}

		//Get form id from level settings
		global $wpdb;
		$wlm_option_table = $wpdb->prefix . 'wlm_options';
		$wpm_levels       = $wpdb->get_row( "Select * from {$wlm_option_table} WHERE option_name LIKE 'wpm_levels' LIMIT 1" );
		if ( ! empty( $wpm_levels ) ) {
			$wpm_levels = maybe_unserialize( $wpm_levels->option_value );
		} else {
			return;
		}

		if ( ! isset( $wpm_levels[ $data['wpm_id'] ] ) ) {
			return;
		}
		$level_info      = $wpm_levels[ $data['wpm_id'] ];
		$regpage_form_id = 'default';
		if ( isset( $level_info['custom_reg_form'] ) && isset( $level_info['enable_custom_reg_form'] ) ) {
			$regpage_form_id = $level_info['custom_reg_form'];
		}
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_form      = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];

		$form_title = 'Default Registration Form';
		if ( strpos( $regpage_form_id, 'DEFAULT-' ) === false ) {
			global $wpdb;
			$form       = $wpdb->get_var( "SELECT option_value FROM `{$wpdb->prefix}wlm_options` WHERE `option_name` LIKE '%{$regpage_form_id}%' ORDER BY `option_name` ASC" );
			$form_value = maybe_unserialize( wlm_serialize_corrector( $form ) );
			$form_title = $form_value['form_name'];
		}

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( - 1 === intval( $trigger['meta'][ $this->trigger_meta ] ) || $regpage_form_id === $trigger['meta'][ $this->trigger_meta ]
				) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
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

							// From Title Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $form_title );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );
							// All Data Token
							$trigger_meta['meta_key']   = 'parsed_data';
							$trigger_meta['meta_value'] = maybe_serialize( $data );
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