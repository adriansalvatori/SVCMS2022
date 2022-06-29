<?php

namespace Uncanny_Automator_Pro;

use Groundhogg\DB\Tags;

/**
 * Class GH_TAGREMOVED
 *
 * @package Uncanny_Automator_Pro
 */
class GH_TAGREMOVED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GH';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'GHTAGREMOVED';
		$this->trigger_meta = 'GHTAG';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$tags = new Tags();

		$tag_options = array();
		foreach ( $tags->get_tags() as $tag ) {
			$tag_options[ $tag->tag_id ] = $tag->tag_name;
		}

		$option = array(
			'option_code'     => $this->trigger_meta,
			'label'           => __( 'Tags', 'uncanny-automator' ),
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $tag_options,
			'relevant_tokens' => array(
				$this->trigger_meta         => esc_attr__( 'Tag title', 'uncanny-automator-pro' ),
				$this->trigger_meta . '_ID' => esc_attr__( 'Tag ID', 'uncanny-automator-pro' ),
			),
		);

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/groundhogg/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - Groundhogg */
			'sentence'            => sprintf( esc_attr__( '{{A tag:%1$s}} is removed from a user', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Groundhogg */
			'select_option_name'  => esc_attr__( '{{A tag}} is removed from a user', 'uncanny-automator-pro' ),
			'action'              => 'groundhogg/contact/tag_removed',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'tag_removed' ),
			'options'             => array( $option ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 */
	public function tag_removed( $class, $tag_id ) {
		$user_id = $class->get_user_id();

		if ( ! $user_id ) {
			return;
		}

		// Get all recipes that have the "$this->trigger_code = 'GHTAGADDED'" trigger
		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		// Get the specific GHTAG meta data from the recipes
		$recipe_trigger_meta_data = Automator()->get->meta_from_recipes( $recipes, 'GHTAG' );
		$required_tag_name        = Automator()->get->meta_from_recipes( $recipes, 'GHTAG_readable' );
		$matched_recipe_ids       = array();

		// Loop through recipe
		foreach ( $recipe_trigger_meta_data as $recipe_id => $trigger_meta ) {
			// Loop through recipe GHTAG trigger meta data
			foreach ( $trigger_meta as $trigger_id => $required_tag_id ) {
				if (
					0 === absint( $required_tag_id ) || // Any tag is set as the option
					$tag_id === absint( $required_tag_id ) // Match specific tag
				) {
					$matched_recipe_ids[] = array(
						'recipe_id'        => $recipe_id,
						'trigger_id'       => $trigger_id,
						'matched_tag_name' => $required_tag_name[ $recipe_id ][ $trigger_id ],
						'matched_tag_id'   => $required_tag_id,
					);
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
					'is_signed_in'     => true,
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => (int) $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							$trigger_meta['meta_key']   = $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $matched_recipe_id['matched_tag_name'] );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $this->trigger_meta . '_ID';
							$trigger_meta['meta_value'] = maybe_serialize( $matched_recipe_id['matched_tag_id'] );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
