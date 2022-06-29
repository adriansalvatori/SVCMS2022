<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_USERPROFILEUPDATED
 * @package Uncanny_Automator_Pro
 */
class WP_USERPROFILEUPDATED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

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
		$this->trigger_code = 'WPUSERPROFILEUPDATED';
		$this->trigger_meta = 'USERFIELD';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( __( "A user's {{profile field:%1\$s}} is updated", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( "A user's {{profile field}} is updated", 'uncanny-automator-pro' ),
			'action'              => 'profile_update',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'updated_user_data' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array[]
	 */
	public function load_options() {
		$relevant_tokens = array(
			$this->trigger_meta               => esc_attr__( 'Profile field', 'uncanny-automator-pro' ),
			$this->trigger_meta . '_OLDVALUE' => esc_attr__( 'Previous value', 'uncanny-automator-pro' ),
			$this->trigger_meta . '_NEWVALUE' => esc_attr__( 'New value', 'uncanny-automator-pro' ),
		);

		return Automator()->utilities->keep_order_of_options(
			array(
				'options' => array(
					Automator()->helpers->recipe->wp->options->pro->wp_user_profile_fields(
						__( 'Profile field', 'uncanny-automator-pro' ),
						$this->trigger_meta,
						array(
							'is_any'          => true,
							'relevant_tokens' => $relevant_tokens,
						)
					),
				),
			)
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 */
	public function updated_user_data( $user_id, $old_user_data ) {

		global $uncanny_automator;
		$user_fields    = array( 'display_name', 'user_login', 'user_email', 'user_url', 'user_pass' );
		$new_user_data  = get_userdata( $user_id );
		$is_changed     = false;
		$changed_fields = array();
		foreach ( $user_fields as $user_field ) {
			if ( $new_user_data->$user_field !== $old_user_data->$user_field ) {
				$is_changed                    = true;
				$changed_fields[ $user_field ] = $old_user_data->$user_field;
			}
		}

		if ( ! $is_changed ) {
			return;
		}

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $this->trigger_meta, $trigger['meta'] ) ) {
					$trigger_field = $trigger['meta'][ $this->trigger_meta ];
					if ( '-1' === $trigger['meta'][ $this->trigger_meta ] ) {
						$first_key                            = key( $changed_fields );
						$matched_recipe_ids[ $trigger['ID'] ] = array(
							'recipe_id'       => $recipe['ID'],
							'trigger_id'      => $trigger['ID'],
							'user_field'      => $first_key,
							'old_field_value' => $changed_fields[ $first_key ],
							'new_field_value' => $new_user_data->$first_key,
						);
					} elseif ( isset( $changed_fields[ $trigger_field ] ) ) {
						$field_key                            = $trigger['meta'][ $this->trigger_meta ];
						$matched_recipe_ids[ $trigger['ID'] ] = array(
							'recipe_id'       => $recipe['ID'],
							'trigger_id'      => $trigger['ID'],
							'user_field'      => $trigger['meta'][ $this->trigger_meta ],
							'old_field_value' => $changed_fields[ $field_key ],
							'new_field_value' => $new_user_data->$field_key,
						);
					}
				}
			}
		}

		if ( ! $matched_recipe_ids ) {
			return;
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $trigger_id => $recipe_id ) {

				if ( ! $uncanny_automator->is_recipe_completed( $recipe_id['recipe_id'], $user_id ) ) {
					$args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id['recipe_id'],
						'trigger_to_match' => $trigger_id,
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
						'post_id'          => - 1,
					);

					$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

					if ( $result ) {
						foreach ( $result as $r ) {
							if ( true === $r['result'] ) {
								if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
									//Saving form values in trigger log meta for token parsing!
									$save_meta = array(
										'user_id'        => $user_id,
										'trigger_id'     => $r['args']['trigger_id'],
										'run_number'     => $r['args']['run_number'],
										'trigger_log_id' => $r['args']['get_trigger_id'],
										'ignore_user_id' => true,
									);

									$save_meta['meta_key']   = $r['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
									$save_meta['meta_value'] = $recipe_id['user_field'];
									$uncanny_automator->insert_trigger_meta( $save_meta );

									$save_meta['meta_key']   = $r['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_OLDVALUE';
									$save_meta['meta_value'] = $recipe_id['old_field_value'];
									$uncanny_automator->insert_trigger_meta( $save_meta );

									$save_meta['meta_key']   = $r['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_NEWVALUE';
									$save_meta['meta_value'] = $recipe_id['new_field_value'];
									$uncanny_automator->insert_trigger_meta( $save_meta );

								}
								$uncanny_automator->maybe_trigger_complete( $r['args'] );
							}
						}
					}
				}
			}
		}
	}
}
