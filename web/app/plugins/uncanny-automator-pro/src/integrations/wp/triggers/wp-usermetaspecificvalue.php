<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_USERMETASPECIFICVALUE
 * @package Uncanny_Automator_Pro
 */
class WP_USERMETASPECIFICVALUE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPUSERMETASPECIFCVAL';
		$this->trigger_meta = 'SPECIFICUMETAVAL';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( __( "A user's {{specific:%1\$s}} meta key is updated to {{a specific value:%2\$s}}", 'uncanny-automator-pro' ), 'SPECIFICUMETAKEY', $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( "A user's {{specific}} meta key is updated to {{a specific value}}", 'uncanny-automator-pro' ),
			'action'              => 'updated_user_meta',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'updated_user_meta_data' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	public function load_options() {
		return array(
			'options' => array(
				Automator()->helpers->recipe->field->text_field( 'SPECIFICUMETAKEY', __( 'Meta key', 'uncanny-automator-pro' ) ),
				Automator()->helpers->recipe->field->text_field( $this->trigger_meta, __( 'Meta value', 'uncanny-automator-pro' ) ),
			),
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $meta_id
	 * @param $object_id
	 * @param $meta_key
	 * @param $_meta_value
	 */
	public function updated_user_meta_data( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$user_id            = $object_id;
		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $this->trigger_meta, $trigger['meta'] ) ) {
					$trigger_field = $trigger['meta']['SPECIFICUMETAKEY'];
					$trigger_value = $trigger['meta'][ $this->trigger_meta ];
					if ( (string) $trigger_field === (string) $meta_key && (string) $trigger_value === (string) $_meta_value ) {
						$matched_recipe_ids[ $trigger['ID'] ] = array(
							'recipe_id'  => $recipe['ID'],
							'trigger_id' => $trigger['ID'],
							'meta_field' => $meta_key,
							'meta_value' => $_meta_value,
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

				if ( ! Automator()->is_recipe_completed( $recipe_id['recipe_id'], $user_id ) ) {
					$args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id['recipe_id'],
						'trigger_to_match' => $trigger_id,
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
						'post_id'          => - 1,
					);

					$result = Automator()->maybe_add_trigger_entry( $args, false );

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

									$save_meta['meta_key']   = $r['args']['trigger_id'] . ':' . $this->trigger_code . ':SPECIFICUMETAKEY';
									$save_meta['meta_value'] = $recipe_id['meta_field'];
									Automator()->insert_trigger_meta( $save_meta );

									$save_meta['meta_key']   = $r['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
									$save_meta['meta_value'] = $recipe_id['meta_value'];
									Automator()->insert_trigger_meta( $save_meta );

								}
								Automator()->maybe_trigger_complete( $r['args'] );
							}
						}
					}
				}
			}
		}
	}

}
