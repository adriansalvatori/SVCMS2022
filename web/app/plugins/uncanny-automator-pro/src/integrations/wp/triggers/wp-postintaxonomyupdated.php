<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_POSTINTAXONOMYUPDATED
 *
 * @package Uncanny_Automator_Pro
 */
class WP_POSTINTAXONOMYUPDATED {

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

		$this->trigger_code = 'WPPOSTINTAXONOMY';

		$this->trigger_meta = 'SPECIFICTAXONOMY';

		if ( Automator()->helpers->recipe->is_edit_page() ) {

			add_action(
				'wp_loaded',
				function () {
					$this->define_trigger();
				},
				99
			);

			return;

		}

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
			'sentence'            => sprintf( __( 'A user updates a post in {{a specific taxonomy:%1$s}}', 'uncanny-automator-pro' ), 'WPTAXONOMIES:' . $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( 'A user updates a post in {{a specific taxonomy}}', 'uncanny-automator-pro' ),
			'action'              => 'post_updated',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wp_post_updated' ),
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
						__( 'Post Type', 'uncanny-automator-pro' ),
						'WPPOSTTYPES',
						array(
							'token'           => true,
							'is_ajax'         => true,
							'relevant_tokens' => array(),
							'endpoint'        => 'select_post_type_taxonomies',
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
							'label'                 => esc_html__( 'Term', 'uncanny-automator-pro' ),
							'token'                 => true,
							'relevant_tokens'       => array(
								'POSTID'            => esc_html__( 'Post ID', 'uncanny-automator-pro' ),
								'POSTURL'           => esc_html__( 'Post URL', 'uncanny-automator' ),
								'POSTCONTENT'       => esc_html__( 'Post content', 'uncanny-automator-pro' ),
								'POSTEXCERPT'       => esc_html__( 'Post excerpt', 'uncanny-automator-pro' ),
								'POSTTITLE'         => esc_html__( 'Post title', 'uncanny-automator-pro' ),
								'WPPOSTTYPES'       => esc_html__( 'Post type', 'uncanny-automator-pro' ),
								$this->trigger_meta => esc_html__( 'Term title', 'uncanny-automator-pro' ),
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
	public function wp_post_updated( $post_ID, $post_after, $post_before ) {

		$user_id = get_current_user_id();

		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );

		$required_post_type = Automator()->get->meta_from_recipes( $recipes, 'WPPOSTTYPES' );

		$post = $post_after;

		$required_taxonomy = Automator()->get->meta_from_recipes( $recipes, 'WPTAXONOMIES' );

		$required_term = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );

		$term_ids = array();

		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];

				// is post type
				if (
					'-1' === $required_post_type[ $recipe_id ][ $trigger_id ] // any post type
					|| $post->post_type === $required_post_type[ $recipe_id ][ $trigger_id ] // specific post type
					|| empty( $required_post_type[ $recipe_id ][ $trigger_id ] ) // Backwards compatibility -- the trigger didnt have a post type selection pre 2.10
				) {

					// is post taxonomy
					if (
						'-1' === $required_taxonomy[ $recipe_id ][ $trigger_id ] // any taxonomy
					) {

						// any taxonomy also automatically means any term
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
						continue;

					} else {

						// specific taxonomy
						$post_terms = wp_get_post_terms( $post_ID, $required_taxonomy[ $recipe_id ][ $trigger_id ] );

						// is post term
						if (
							isset( $post_terms ) && ! empty( $post_terms ) // the taxomomy has terms
						) {

							// get all taxonomy term ids
							foreach ( $post_terms as $term ) {
								$term_ids[] = $term->term_id;
							}

							if (
								'-1' === $required_term[ $recipe_id ][ $trigger_id ] // any terms
								|| in_array( absint( $required_term[ $recipe_id ][ $trigger_id ] ), $term_ids, true ) // specific term
							) {
								$matched_recipe_ids[] = array(
									'recipe_id'  => $recipe_id,
									'trigger_id' => $trigger_id,
								);
							}
						}
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
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							$taxonomies = '';
							$terms      = '';

							// get terms and taxonomies
							if ( '-1' === $required_taxonomy[ $recipe_id ][ $trigger_id ] ) {
								$all_taxonomies = array();
								$all_terms      = array();
								foreach ( get_object_taxonomies( $post ) as $taxonomy ) {
									$all_taxonomies[] = $taxonomy->label;
									if ( '-1' === $required_term[ $recipe_id ][ $trigger_id ] ) {
										$tax_terms = wp_get_post_terms( $post_ID, $taxonomy->name );
										if (
											isset( $tax_terms ) && ! empty( $tax_terms ) // the taxomomy has terms
										) {
											// get all taxonomy term names
											foreach ( $tax_terms as $term ) {
												$all_terms[] = $term->name;
											}
										}
									} else {
										$tax_terms = wp_get_post_terms( $post_ID, $required_term[ $recipe_id ][ $trigger_id ] );
										if (
											isset( $post_terms ) && ! empty( $post_terms ) // the taxomomy has terms
										) {
											// get all taxonomy term names
											foreach ( $tax_terms as $term ) {
												$all_terms[] = $term->name;
											}
										}
									}
								}

								$taxonomies = implode( ', ', $all_taxonomies );
								$terms      = implode( ', ', $all_terms );
							} else {

								$taxonomy = get_taxonomy( $required_taxonomy[ $recipe_id ][ $trigger_id ] );
								if ( false !== $taxonomy ) {
									$taxonomies = $taxonomy->label;

									if ( '-1' === $required_term[ $recipe_id ][ $trigger_id ] ) {
										$tax_terms = wp_get_post_terms( $post_ID, $taxonomy->name );
										if (
											isset( $tax_terms ) && ! empty( $tax_terms ) // the taxomomy has terms
										) {
											// get all taxonomy term names
											foreach ( $tax_terms as $term ) {
												$all_terms[] = $term->name;
											}
											$terms = implode( ', ', $all_terms );
										}
									} else {
										$term = get_term( $required_term[ $recipe_id ][ $trigger_id ], $taxonomy->name );
										if (
											isset( $term ) && ! empty( $term ) // the taxomomy has terms
										) {
											$terms = $term->name;
										}
									}
								}
							}

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPTAXONOMIES';
							$trigger_meta['meta_value'] = maybe_serialize( $taxonomies );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $terms );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTCONTENT';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_content );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTEXCERPT';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_excerpt( $post_after->ID ) );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTID';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->ID );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTTITLE';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_title );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPPOSTTYPES';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_type );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $required_term[ $recipe_id ][ $trigger_id ] );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
