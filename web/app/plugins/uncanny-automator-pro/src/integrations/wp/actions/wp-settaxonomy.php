<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_SETTAXONOMY
 *
 * @package Uncanny_Automator_Pro
 */
class WP_SETTAXONOMY {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {

		$this->action_code = 'SETTAXONOMY';
		$this->action_meta = 'WPSETTAXONOMY';

		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action( 'wp_loaded', array( $this, 'plugins_loaded' ), 99 );
		} else {
			$this->define_action();
		}
	}

	public function plugins_loaded() {
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
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
				__( 'Add {{a taxonomy:%2$s}} {{term:%3$s}} to {{a post:%1$s}} in {{a post type:%4$s}}', 'uncanny-automator-pro' ),
				$this->action_meta,
				'WPTAXONOMIES:' . $this->action_meta,
				'WPTAXONOMYTERM:' . $this->action_meta,
				'WPSPOSTTYPES:' . $this->action_meta
			),
			/* translators: Action - WordPress Core */
			'select_option_name' => __( 'Add {{a taxonomy}} {{term}} to {{a post}} in {{a post type}}', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'set_taxonomy' ),
			'options_callback'   => array( $this, 'load_options' ),

		);

		Automator()->register->action( $action );

	}

	public function load_options() {

		$options = Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => array(
					$this->action_meta => array(
						Automator()->helpers->recipe->wp->options->pro->all_wp_post_types_set_taxonomy(
							__( 'Post type', 'uncanny-automator-pro' ),
							'WPSPOSTTYPES',
							array(
								'token'        => false,
								'is_ajax'      => true,
								'target_field' => $this->action_meta,
								'is_any'       => false,
								'endpoint'     => 'select_all_post_of_selected_post_type',
							)
						),
						Automator()->helpers->recipe->field->select_field_ajax(
							$this->action_meta,
							__( 'Post', 'uncanny-automator-pro' ),
							array(),
							'',
							'',
							false,
							true,
							array(
								'target_field' => 'WPTAXONOMIES',
								'endpoint'     => 'select_post_type_taxonomies_SELECTEDTAXONOMY',
							)
						),
						Automator()->helpers->recipe->wp->options->pro->all_wp_taxonomy(
							__( 'Taxonomy', 'uncanny-automator-pro' ),
							'WPTAXONOMIES',
							array(
								'token'        => false,
								'is_ajax'      => true,
								'target_field' => 'WPTAXONOMYTERM',
								'is_any'       => false,
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
	 * Set taxonomy.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function set_taxonomy( $user_id, $action_data, $recipe_id, $args ) {

		$post_type     = $action_data['meta']['WPSPOSTTYPES'];
		$post_id       = $action_data['meta'][ $this->action_meta ];
		$post_taxonomy = $action_data['meta']['WPTAXONOMIES'];
		$post_term     = $action_data['meta']['WPTAXONOMYTERM'];

		if ( is_numeric( $post_term ) && 'category' !== $post_taxonomy && ! is_taxonomy_hierarchical( $post_taxonomy ) ) {
			$term      = get_term( $post_term, $post_taxonomy );
			$post_term = $term->slug;
		}

		if ( intval( '-1' ) === intval( $post_id ) ) {

			$args = array(
				'post_type'      => $post_type,
				'orderby'        => 'ID',
				'post_status'    => 'publish',
				'order'          => 'DESC',
				'posts_per_page' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			);

			$result = get_posts( $args );
			if ( ! empty( $result ) ) {
				foreach ( $result as $post ) {
					if ( 'category' === $post_taxonomy ) {
						wp_set_post_categories( $post->ID, array( $post_term ), true );
					} else {
						wp_set_post_terms( $post->ID, array( $post_term ), $post_taxonomy, true );
					}
				}
			}
		} else {
			if ( 'category' === $post_taxonomy ) {
				wp_set_post_categories( $post_id, array( $post_term ), true );
			} else {
				wp_set_post_terms( $post_id, array( $post_term ), $post_taxonomy, true );
				wp_set_object_terms( $post_id, array( $post_term ), $post_taxonomy, true );
			}
		}

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}

}



