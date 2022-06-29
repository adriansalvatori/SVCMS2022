<?php

namespace Uncanny_Automator_Pro;

use PeepSo;
use PeepSoUser;
use PeepSoUserFollower;

/**
 * Class PeepSo_USERUPDATESPECIFICFIELDTOSPECIFICVALUE
 *
 * @package Uncanny_Automator
 */
class PeepSo_USERUPDATESPECIFICFIELDTOSPECIFICVALUE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'PP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'PPUSERUPDATESPECIFICFIELDTOSPECIFICVALUE';
		$this->trigger_meta = 'USERUPDATESPECIFICFIELDTOSPECIFICVALUE';
		$this->define_trigger();

		add_filter(
			'automator_option_text_field',
			array(
				$this,
				'change_the_token_label',
			)
		);
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/peepso/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			'is_pro'              => true,
			/* translators: Logged-in trigger - PeepSo Core */
			'sentence'            => sprintf( esc_attr__( 'A user updates {{a specific field:%1$s}} to {{a specific field value:%2$s}} in their profile', 'uncanny-automator' ), $this->trigger_meta, 'PPSPVALUE' ),
			/* translators: Logged-in trigger - PeepSo Core */
			'select_option_name'  => __( 'A user updates {{a specific field}} to {{a specific field value}} in their profile', 'uncanny-automator-pro' ),
			'action'              => 'peepso_ajax_start',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'profile_update' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );

		return;
	}

	/**
	 * load_options
	 */
	public function load_options() {
		$comment            = __( 'Format is important. For <strong>birthdates</strong> use <code>YY-MM-DD</code>; for <strong>checkboxes</strong>: <code>0</code> (disabled) or <code>1</code> (enabled); for <strong>timezones</strong>: <code>UTC-7:30</code>, and for <strong>gender</strong> use <code>Male</code>, <code>Female</code>, and for <strong>Permissions:</strong> use <code>10: Public</code>, <code>20: Site Members</code>, <code>40: Private</code> ', 'uncanny-automator-pro' );
		$text_field_options = array(
			'option_code'      => 'PPSPVALUE',
			'input_type'       => 'text',
			'label'            => esc_attr__( 'Field value', 'uncanny-automator-pro' ),
			'token_name'       => __( 'Updated field value', 'uncanny-automator-pro' ),
			'description'      => $comment,
			'required'         => true,
			'relevant_tokens'  => array(),
			'tokens'           => true,
			'default'          => null,
			'supports_tinymce' => null,
		);
		$options            = array(
			'options' => array(
				Automator()->helpers->recipe->peepso->get_profile_fields( __( 'Profile fields', 'uncanny-automator-pro' ), $this->trigger_meta, array( 'uo_include_any' => false ) ),
				Automator()->helpers->recipe->field->text( $text_field_options ),
			),
		);

		$options = Automator()->utilities->keep_order_of_options( $options );

		return $options;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 */
	public function profile_update( $data ) {

		$recipes             = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post       = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_post_value = Automator()->get->meta_from_recipes( $recipes, 'PPSPVALUE' );
		$user_id             = automator_filter_input( 'view_user_id', INPUT_POST );

		if ( ! $recipes ) {
			return;
		}

		if ( ! $required_post ) {
			return;
		}

		$ajax_actions = array(
			'profilefieldsajax.savefield',
			'profilefieldsajax.save_acc',
			'profilepreferencesajax.savepreference',
		);

		if ( ! in_array( $data, $ajax_actions ) ) {
			return;
		}

		$user_fields     = Automator()->helpers->recipe->peepso->get_user_fields( 0 );
		$user_fields_ids = array();
		foreach ( $user_fields as $key => $value ) {
			$user_fields_ids[] = $key;
		}

		//Add where option is set to Any field
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if (
					( in_array( $required_post[ $recipe_id ][ $trigger_id ], $user_fields_ids, false ) )
				) {
					$matched_recipe_ids[] = array(
						'recipe_id'        => $recipe_id,
						'trigger_id'       => $trigger_id,
						'user_field_id'    => $required_post[ $recipe_id ][ $trigger_id ],
						'user_field_value' => $required_post_value[ $recipe_id ][ $trigger_id ],
					);
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args     = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
				);
				$user_field_id = $matched_recipe_id['user_field_id'];

				if ( 0 === intval( $matched_recipe_id['user_field_value'] ) ) {
					$user_field_value = __( 'Disabled', 'uncanny-automator-pro' );
				} elseif ( 1 === intval( $matched_recipe_id['user_field_value'] ) ) {
					$user_field_value = __( 'Enabled', 'uncanny-automator-pro' );
				} else {
					$user_field_value = $matched_recipe_id['user_field_value'];
				}

				$args        = Automator()->maybe_add_trigger_entry( $pass_args, false );
				$peepso_user = PeepSoUser::get_instance( $user_id );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							if ( 'profilefieldsajax.savefield' === $data ) {
								$user_field_value = $matched_recipe_id['user_field_value'];
								$pp_field_id      = automator_filter_input( 'id', INPUT_POST );
								$field_value      = automator_filter_input( 'value', INPUT_POST );
								if ( 'm' === (string) $field_value ) {
									$field_value = __( 'Male', 'uncanny-automator-pro' );
									if ( 1 === strlen( $matched_recipe_id['user_field_value'] ) && 0 === intval( $matched_recipe_id['user_field_value'] ) ) {
										$user_field_value = __( 'Disabled', 'uncanny-automator-pro' );
									}
								}
								if ( 'f' === (string) $field_value ) {
									$field_value = __( 'Female', 'uncanny-automator-pro' );
									if ( 1 === strlen( $matched_recipe_id['user_field_value'] ) && 1 === intval( $matched_recipe_id['user_field_value'] ) ) {
										$user_field_value = __( 'Enabled', 'uncanny-automator-pro' );
									}
								}
							} elseif ( 'profilefieldsajax.save_acc' === $data ) {
								$pp_field_id = automator_filter_input( 'id', INPUT_POST );
								$field_value = automator_filter_input( 'acc', INPUT_POST );
								$field_value = $this->get_privacy_status( $field_value );

								if ( intval( $user_field_id ) === intval( '-1' ) ) {
									$user_field_id = $pp_field_id;
								}
							} elseif ( 'profilepreferencesajax.savepreference' === $data ) {
								$pp_field_id = automator_filter_input( 'meta_key', INPUT_POST );
								if ( 'usr_profile_acc' === $pp_field_id || 'peepso_profile_post_acc' === $pp_field_id ) {
									$field_value = automator_filter_input( 'value', INPUT_POST );
									$pre_value   = $field_value;
									$field_value = $this->get_privacy_status( $field_value );
									if ( 'usr_profile_acc' === $pp_field_id || 'peepso_profile_post_acc' === $pp_field_id ) {
										if ( intval( $pre_value ) === intval( $matched_recipe_id['user_field_value'] ) ) {
											$user_field_value = $this->get_privacy_status( $matched_recipe_id['user_field_value'] );
										}
									}
								} elseif ( 'peepso_gmt_offset' === $pp_field_id ) {
									$field_value = Automator()->helpers->recipe->peepso->get_gmt_value( automator_filter_input( 'value', INPUT_POST ) );
									if ( $field_value === $matched_recipe_id['user_field_value'] ) {
										$user_field_value = $matched_recipe_id['user_field_value'];
									}
								} else {
									$field_value = ( 1 === intval( automator_filter_input( 'value', INPUT_POST ) ) ) ? __( 'Enabled', 'uncanny-automator-pro' ) : __( 'Disabled', 'uncanny-automator-pro' );
								}
							}

							if ( $user_field_id === $pp_field_id && (string) $field_value === (string) $user_field_value ) {

								$run_number = Automator()->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $result['args']['user_id'] );
								$save_meta  = array(
									'user_id'        => $result['args']['user_id'],
									'trigger_id'     => $result['args']['trigger_id'],
									'run_number'     => $run_number,
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'ignore_user_id' => true,
								);

								$save_meta['meta_key']   = 'USR_AVATARURL';
								$save_meta['meta_value'] = $peepso_user->get_avatar();
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_GENDER';
								$save_meta['meta_value'] = Automator()->helpers->recipe->peepso->get_gender( $user_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_BIRTHDATE';
								$save_meta['meta_value'] = Automator()->helpers->recipe->peepso->get_birthdate( $user_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_FOLLOWERS';
								$save_meta['meta_value'] = PeepSoUserFollower::count_followers( $user_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_FOLLOWING';
								$save_meta['meta_value'] = PeepSoUserFollower::count_following( $user_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_PROFILEURL';
								$save_meta['meta_value'] = $peepso_user->get_profileurl();
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_ABOUTME';
								$save_meta['meta_value'] = Automator()->helpers->recipe->peepso->get_bio( $user_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_WEBSITE';
								$save_meta['meta_value'] = Automator()->helpers->recipe->peepso->get_website( $user_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_ROLE';
								$save_meta['meta_value'] = $peepso_user->get_user_role();
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'USR_USERROLE';
								$save_meta['meta_value'] = $peepso_user->get_user_role();
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'PPFIELD_NAME';
								$save_meta['meta_value'] = $user_fields[ $user_field_id ];
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'PPSPVALUE';
								$save_meta['meta_value'] = $field_value;
								Automator()->insert_trigger_meta( $save_meta );

								Automator()->maybe_trigger_complete( $result['args'] );
							}
						}
					}
				}
			}
		}
	}

	public function get_privacy_status( $merit ) {

		//		const ACCESS_PUBLIC  = 10;
		//		const ACCESS_MEMBERS = 20;
		//		const ACCESS_PRIVATE = 40;
		if ( $merit == PeepSo::ACCESS_PUBLIC ) {
			$field_value = __( 'Public', 'uncanny-automator-pro' );
		}
		if ( $merit == PeepSo::ACCESS_MEMBERS ) {
			$field_value = __( 'Site Members', 'uncanny-automator-pro' );
		}
		if ( $merit == PeepSo::ACCESS_PRIVATE ) {
			$field_value = __( 'Only Me', 'uncanny-automator-pro' );
		}

		return $field_value;
	}

	public function change_the_token_label( $option ) {

		$option['token_name'] = __( 'Updated field value', 'uncanny-automator-pro' );

		return $option;
	}

}
