<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_REMOVETAXONOMY
 *
 * @package Uncanny_Automator_Pro
 */
class WP_REMOVETAXONOMY {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

	/**
	 * The action code.
	 *
	 * @var string
	 */
	private $action_code;

	/**
	 * The action meta.
	 *
	 * @var string
	 */
	private $action_meta;


	public function __construct() {

		$this->action_code = 'REMOVETAXONOMY';

		$this->action_meta = 'WPREMOVETAXONOMY';

		if ( Automator()->helpers->recipe->is_edit_page() ) {

			add_action( 'wp_loaded', array( $this, 'define_action' ), 99 );

		} else {

			$this->define_action();

		}

	}

	/**
	 * Define the action.
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/wordpress-core/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'requires_user'      => false,
			'sentence'           => sprintf(
			/* translators: Action - WordPress Core */
				__( 'Remove {{a taxonomy:%2$s}} {{term:%3$s}} from {{a post:%1$s}} in {{a post type:%4$s}}', 'uncanny-automator-pro' ),
				'WPPOSTS:' . $this->action_meta,
				'WPTAXONOMIES:' . $this->action_meta,
				'WPTAXONOMYTERM:' . $this->action_meta,
				'WPSPOSTTYPES:' . $this->action_meta
			),
			/* translators: Action - WordPress Core */
			'select_option_name' => __( 'Remove {{a taxonomy}} {{term}} from {{a post}} in {{a post type}}', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'remove_taxonomy' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Defer load options option_callback callback method.
	 *
	 * @return array
	 */
	public function load_options() {

		$options = Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => array(
					$this->action_meta => array(
						Automator()->helpers->recipe->wp->options->pro->all_wp_post_types_set_taxonomy(
							esc_html__( 'Post type', 'uncanny-automator-pro' ),
							'WPSPOSTTYPES',
							array(
								'token'        => false,
								'is_ajax'      => true,
								'target_field' => 'WPPOSTS',
								'is_any'       => false,
								'endpoint'     => 'select_all_post_of_selected_post_type',
							)
						),
						array(
							'option_code'           => 'WPPOSTS',
							/* translators: Email field */
							'label'                 => esc_html__( 'Post', 'uncanny-automator-pro' ),
							'input_type'            => 'select',
							'is_ajax'               => true,
							'endpoint'              => 'select_post_type_taxonomies_SELECTEDTAXONOMY',
							'fill_values_in'        => 'WPTAXONOMIES',
							'supports_custom_value' => false,
							'required'              => true,
						),

						Automator()->helpers->recipe->wp->options->pro->all_wp_taxonomy(
							esc_html__( 'Taxonomy', 'uncanny-automator-pro' ),
							'WPTAXONOMIES',
							array(
								'token'        => false,
								'is_ajax'      => true,
								'target_field' => 'WPTAXONOMYTERM',
								'is_any'       => false,
								'is_all'       => false,
								'placeholder'  => esc_html__( 'Select a taxonomy', 'uncanny-automator-pro' ),
								'endpoint'     => 'select_all_terms_of_SELECTEDTAXONOMY',
							)
						),
						Automator()->helpers->recipe->field->select_field( 'WPTAXONOMYTERM', __( 'Taxonomy term', 'uncanny-automator-pro' ) ),
					),
				),
			)
		);

		return $options;

	}

	/**
	 * Remove taxonomy.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function remove_taxonomy( $user_id, $action_data, $recipe_id, $args ) {

		$post_type     = Automator()->parse->text( $action_data['meta']['WPSPOSTTYPES'] );
		$post_id       = Automator()->parse->text( $action_data['meta']['WPPOSTS'] );
		$post_taxonomy = Automator()->parse->text( $action_data['meta']['WPTAXONOMIES'] );
		$post_term     = Automator()->parse->text( $action_data['meta']['WPTAXONOMYTERM'] );

		$error = array();

		// If the user has selected 'Any post' option from Post field.
		if ( intval( '-1' ) === intval( $post_id ) ) {

			// Get all posts under specific post type.
			$args = array(
				'post_type'      => $post_type,
				'orderby'        => 'ID',
				'post_status'    => 'publish',
				'order'          => 'DESC',
				'posts_per_page' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			);

			$result = get_posts( $args );

			// Process all the posts found.
			if ( ! empty( $result ) ) {

				foreach ( $result as $post ) {

					try {
						$this->terms_remove(
							array(
								'post'          => $post,
								'post_type'     => $post_type,
								'post_taxonomy' => $post_taxonomy,
								'terms'         => $post_term,
							)
						);

					} catch ( \Exception $e ) {

						$error = array(
							'message' => $e->getMessage(),
							'code'    => $e->getCode(),
						);

					}
				}
			}
		} else {

			// Process 1 post.
			$args = array(
				'post'          => $post_id,
				'post_type'     => $post_type,
				'post_taxonomy' => $post_taxonomy,
				'terms'         => $post_term,
			);

			try {

				$this->terms_remove( $args );

			} catch ( \Exception $e ) {

				$error = array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				);

			}
		}

		if ( ! empty( $error ) ) {

			$action_data['complete_with_errors'] = true;

			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error['code'] . ': ' . $error['message'] );

		} else {

			Automator()->complete_action( $user_id, $action_data, $recipe_id );

		}

	}

	/**
	 * Remove the terms.
	 *
	 * @param $args array The arguments.
	 *
	 * @return mixed \Exception on error. Otherwise @see wp_remove_object_terms.
	 */
	public function terms_remove( $args = array() ) {

		// Set defaults.
		$defaults = array(
			'post'          => '', // Can be post ID or post object.
			'post_type'     => '',
			'post_taxonomy' => '',
			'terms'         => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$this->handle_common_errors( $args );

		$terms_collection = array();

		// Handle 'Any terms'.
		if ( '-1' === $args['terms'] ) {

			$terms = get_the_terms( $args['post'], $args['post_taxonomy'] );

			// Handle wp errors from get_terms.
			if ( is_wp_error( $terms ) ) {
				// Invalid taxonomy. Most of the time.
				throw new \Exception( 'WordPress error: ' . $terms->get_error_message() . ' : ' . $args['post_taxonomy'], 500 );
			}

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$terms_collection[] = $term->term_id;
				}
			}
		}

		// Handle singular term. Allow non-arrays, but only process numeric. Made exception for '-1'.
		if ( is_numeric( $args['terms'] ) && '-1' !== $args['terms'] ) {

			$terms_collection[] = absint( $args['terms'] );

		}

		$post_id = $args['post'];

		if ( is_object( $args['post'] ) ) {

			$post_id = $args['post']->ID;

		}

		$result = wp_remove_object_terms( $post_id, $terms_collection, $args['post_taxonomy'] );

		// Handle wp errors.
		if ( is_wp_error( $result ) ) {

			throw new \Exception( 'WordPress error: ' . $terms->get_error_message(), 500 );

		}

		return $result;

	}

	/**
	 * Throws an Exception if required arguments is missing.
	 */
	public function handle_common_errors( $args = array() ) {

		// Empty post type is not allowed.
		if ( ! isset( $args['post_type'] ) || empty( $args['post_type'] ) ) {
			throw new \Exception( 'Argument `post_type` is empty.', 403 );
		}

		// Empty post is not allowed.
		if ( ! isset( $args['post'] ) || empty( $args['post'] ) ) {
			throw new \Exception( 'Argument `post` is empty.', 403 );
		}

		// Empty terms is not allowed.
		if ( ! isset( $args['terms'] ) || empty( $args['terms'] ) ) {
			throw new \Exception( 'Argument `terms` is empty.', 403 );
		}

		// Non string taxonomy is not allowed.
		if ( ! is_string( $args['post_taxonomy'] ) ) {
			throw new \Exception( 'Taxonomy should be a valid string.', 403 );
		}

	}

}
