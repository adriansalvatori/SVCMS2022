<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class WP_TRASH_SPECIFICTERMPOST
 *
 * @package Uncanny_Automator_Pro
 */
class WP_TRASH_SPECIFICTERMPOST {

	use Recipe\Actions;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action(
				'wp_loaded',
				function () {
					$this->setup_action();
				},
				99
			);
		} else {
			$this->setup_action();
		}
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'WP' );
		$this->set_action_code( 'TRASHAPOST' );
		$this->set_action_meta( 'WPTAXONOMIES' );
		$this->set_requires_user( false );
		$this->set_is_pro( true );
		/* translators: Action - WordPress */
		$this->set_sentence(
			sprintf(
				esc_attr__( 'Move all {{of a specific type of posts:%1$s}} with {{a taxonomy term:%3$s}} in {{a taxonomy:%2$s}} to the trash', 'uncanny-automator-pro' ),
				'WPSPOSTTYPES:' . $this->get_action_meta(),
				$this->get_action_meta(),
				'WPTAXONOMYTERM:' . $this->get_action_meta()
			)
		);
		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Move all {{of a specific type of posts}} with {{a taxonomy term}} in {{a taxonomy}} to the trash', 'uncanny-automator-pro' ) );
		$this->set_options_callback( array( $this, 'load_options' ) );
		$this->register_action();
	}

	/**
	 * load_options
	 *
	 * @return array
	 */
	public function load_options() {
		$options = array(
			'options_group' => array(
				$this->get_action_meta() => array(
					Automator()->helpers->recipe->wp->options->pro->all_wp_post_types_set_taxonomy(
						esc_html__( 'Post type', 'uncanny-automator-pro' ),
						'WPSPOSTTYPES',
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->get_action_meta(),
							'is_any'       => false,
							'endpoint'     => 'select_post_type_taxonomies_SELECTEDTAXONOMY',
						)
					),
					Automator()->helpers->recipe->wp->options->pro->all_wp_taxonomy(
						esc_html__( 'Taxonomy', 'uncanny-automator-pro' ),
						$this->get_action_meta(),
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => 'WPTAXONOMYTERM',
							'is_any'       => true,
							'is_all'       => false,
							'placeholder'  => esc_html__( 'Select a taxonomy', 'uncanny-automator-pro' ),
							'endpoint'     => 'select_all_terms_of_SELECTEDTAXONOMY',
						)
					),
					Automator()->helpers->recipe->field->select_field( 'WPTAXONOMYTERM', esc_html__( 'Taxonomy term', 'uncanny-automator-pro' ) ),
					array(
						'option_code' => 'FORCEDELETE',
						'label'       => __( 'Also permanently delete the post(s) from the trash?', 'uncanny-automator-pro' ),
						'input_type'  => 'checkbox',
						'is_toggle'   => true,
					),
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options );
	}

	/**
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$post_type          = Automator()->parse->text( $action_data['meta']['WPSPOSTTYPES'], $recipe_id, $user_id, $args );
		$post_taxonomy      = Automator()->parse->text( $action_data['meta']['WPTAXONOMIES'], $recipe_id, $user_id, $args );
		$post_taxonomy_term = Automator()->parse->text( $action_data['meta']['WPTAXONOMYTERM'], $recipe_id, $user_id, $args );
		$force_del          = (string) $action_data['meta']['FORCEDELETE'];

		$tax_query = array(
			'taxonomy'         => $post_taxonomy,
			'field'            => 'id',
			'terms'            => $post_taxonomy_term, /// Where term_id of Term 1 is "1".
			'include_children' => true,
		);
		if ( intval( '-1' ) === intval( $post_taxonomy_term ) ) {
			$tax_query = array( $post_taxonomy );
		}
		$args  = array(
			'post_type'      => $post_type,
			'orderby'        => 'ID',
			'post_status'    => 'publish',
			'order'          => 'DESC',
			'posts_per_page' => 9999,
			'tax_query'      => array(
				$tax_query,
			),
		);
		$posts = get_posts( $args );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( 'true' === $force_del ) {
					wp_delete_post( $post->ID, true );
				} else {
					wp_trash_post( $post->ID );
				}
			}
			Automator()->complete->action( $user_id, $action_data, $recipe_id );

			return;
		}
		$action_data['complete_with_errors'] = true;
		$action_data['do-nothing']           = true;
		$error_message                       = esc_html__( 'No published post found is specified taxonomy and term.', 'uncanny-automator' );
		Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );
	}
}
