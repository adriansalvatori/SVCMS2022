<?php

namespace Uncanny_Automator_Pro;

/**
 *Class ANON_WP_TAXONOMYTERMADDEDTO_POSTTYPE
 *
 * @package Uncanny_Automator_Pro
 */
class ANON_WP_TAXONOMYTERMADDEDTO_POSTTYPE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

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
		$this->trigger_code = 'WPPOSTTAXONOMY';
		$this->trigger_meta = 'SPECIFICTAXONOMY';
		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action(
				'wp_loaded',
				function () {
					$this->define_trigger();
				},
				99
			);
		} else {
			$this->define_trigger();
		}

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'is_pro'              => true,
			'type'                => 'anonymous',
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( esc_attr__( '{{A taxonomy term:%1$s}} is added to a {{specific type of post:%2$s}}', 'uncanny-automator-pro' ), $this->trigger_meta, 'WPPOSTTYPES:' . $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => esc_attr__( '{{A taxonomy term}} is added to a {{specific type of post}}', 'uncanny-automator-pro' ),
			'action'              => 'added_term_relationship',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'term_added' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {

		$options_array = array(
			'options_group' => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->wp->options->pro->all_wp_post_types(
						__( 'Post type', 'uncanny-automator-pro' ),
						'WPPOSTTYPES',
						array(
							'token'           => true,
							'is_ajax'         => true,
							'is_any'          => false,
							'relevant_tokens' => array(),
							'endpoint'        => 'select_post_type_taxonomies_SELECTEDTAXONOMY',
							'target_field'    => 'WPTAXONOMIES',
						)
					),
					Automator()->helpers->recipe->wp->options->pro->all_wp_taxonomy(
						__( 'Taxonomy', 'uncanny-automator-pro' ),
						'WPTAXONOMIES',
						array(
							'token'        => true,
							'is_ajax'      => true,
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_all_terms_of_SELECTEDTAXONOMY',
							'is_any'       => true,
						)
					),
					Automator()->helpers->recipe->field->select_field_args(
						array(
							'option_code'           => $this->trigger_meta,
							'options'               => array(),
							'required'              => true,
							'label'                 => esc_html__( 'Taxonomy term', 'uncanny-automator-pro' ),
							'token'                 => true,
							'relevant_tokens'       => array(
								$this->trigger_meta => esc_html__( 'Taxonomy term', 'uncanny-automator-pro' ),
								'POSTTITLE'         => esc_html__( 'Post title', 'uncanny-automator-pro' ),
								'POSTID'            => esc_html__( 'Post ID', 'uncanny-automator-pro' ),
								'POSTURL'           => esc_html__( 'Post URL', 'uncanny-automator' ),
								'WPPOSTTYPES'       => esc_html__( 'Post type', 'uncanny-automator-pro' ),
								'POSTCONTENT'       => esc_html__( 'Post content', 'uncanny-automator-pro' ),
								'POSTEXCERPT'       => esc_html__( 'Post excerpt', 'uncanny-automator-pro' ),
							),
							'supports_custom_value' => false,
						)
					),
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $post_ID
	 * @param $post_after
	 * @param $post_before
	 */
	public function term_added( $post_ID, $term_id, $taxonomy ) {
		$user_id            = get_current_user_id();
		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post_type = Automator()->get->meta_from_recipes( $recipes, 'WPPOSTTYPES' );
		$required_taxonomy  = Automator()->get->meta_from_recipes( $recipes, 'WPTAXONOMIES' );
		$required_term      = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();
		$post               = get_post( $post_ID );

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( $post->post_type === (string) $required_post_type[ $recipe_id ][ $trigger_id ] ) {
					if ( intval( '-1' ) === intval( $required_taxonomy[ $recipe_id ][ $trigger_id ] ) ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					} elseif ( (string) $taxonomy === (string) $required_taxonomy[ $recipe_id ][ $trigger_id ] &&
							   ( intval( '-1' ) === intval( $required_term[ $recipe_id ][ $trigger_id ] ) ||
								 absint( $term_id ) === absint( $required_term[ $recipe_id ][ $trigger_id ] ) ) ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					}
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
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => absint( $result['args']['trigger_log_id'] ),
								'run_number'     => absint( $result['args']['run_number'] ),
							);

							Automator()->db->token->save( $this->trigger_meta, maybe_serialize( get_term( $term_id )->name ), $trigger_meta );
							Automator()->db->token->save( 'WPTAXONOMIES', maybe_serialize( $taxonomy ), $trigger_meta );
							Automator()->db->token->save( 'POSTTITLE', maybe_serialize( $post->post_title ), $trigger_meta );
							Automator()->db->token->save( 'POSTID', maybe_serialize( $post_ID ), $trigger_meta );
							Automator()->db->token->save( 'POSTURL', maybe_serialize( get_permalink( $post_ID ) ), $trigger_meta );
							Automator()->db->token->save( 'WPPOSTTYPES', maybe_serialize( $post->post_type ), $trigger_meta );
							Automator()->db->token->save( 'POSTCONTENT', maybe_serialize( $post->post_content ), $trigger_meta );
							Automator()->db->token->save( 'POSTEXCERPT', maybe_serialize( $post->post_excerpt ), $trigger_meta );

							Automator()->process->user->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

}
