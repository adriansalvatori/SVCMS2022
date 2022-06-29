<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WF_TAGSAPPLIED
 *
 * @package Uncanny_Automator_Pro
 */
class WF_TAGSAPPLIED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WF';

	/**
	 * The trigger code.
	 * 
	 * @var string
	 */
	private $trigger_code;

	/**
	 * The trigger meta.
	 * 
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WFTAGSAPPLIED';
		$this->trigger_meta = 'TAGSAPPLIED';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wp-fusion/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WP Fusion */
			'sentence'            => sprintf( __( '{{A tag:%1$s}} is added to a user', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WP Fusion */
			'select_option_name'  => __( '{{A tag}} is added to a user', 'uncanny-automator-pro' ),
			'action'              => 'wpf_tags_applied',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'save_data' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	public function load_options() {

		$options = array(
			'options' => array(
				Wp_Fusion_Pro_Helpers::fusion_tags( '', $this->trigger_meta ),
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
	public function save_data( $user_id, $tags ) {
		global $uncanny_automator;

		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		if ( empty( $recipes ) ) {
			return;
		}
		foreach ( $recipes as $recipe_id => $recipe ) {

			foreach ( $recipe['triggers'] as $trigger ) {

				if ( ! array_key_exists( $this->trigger_meta, $trigger['meta'] ) ) {
					continue;
				}

				$result = $this->match_tag( $tags, $trigger['meta'][ $this->trigger_meta ] );

				if ( false === $result ) {
					continue;
				}

				$args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'post_id'          => - 1,
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
					'user_id'          => $user_id,
					'recipe_to_match'  => $recipe_id,
					'trigger_to_match' => $trigger['ID'],
				);

				$uncanny_automator->maybe_add_trigger_entry( $args );
			}
		}
	}

	/**
	 * Match tag.
	 * 
	 * @param $tags
	 * @param $to_match
	 *
	 * @return bool
	 */
	public function match_tag( $tags, $to_match ) {
		if ( is_array( $tags ) ) {
			foreach ( $tags as $tag ) {
				if ( $tag === $to_match ) {
					return true;
				}
			}
		}
		if ( ! is_array( $tags ) ) {
			return $tags === $to_match;
		}

		return false;
	}

}
