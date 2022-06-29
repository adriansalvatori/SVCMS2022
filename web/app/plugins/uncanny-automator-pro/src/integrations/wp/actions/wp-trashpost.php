<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_TRASHPOST
 *
 * @package Uncanny_Automator_Pro
 */
class WP_TRASHPOST {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'TRASHPOST';
		$this->action_meta = 'WPTRASHAPOST';
		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action(
				'wp_loaded',
				function () {
					$this->define_action();
				},
				99
			);
		} else {
			$this->define_action();
		}
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
			/* translators: Action - WordPress Core */
			'sentence'           => sprintf( esc_attr__( 'Move {{a post:%1$s}} to the trash', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WordPress Core */
			'select_option_name' => esc_attr__( 'Move {{a post}} to the trash', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'post_trashed' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
	}

	/**
	 * @return \array[][]
	 */
	public function load_options() {
		return Automator()->utilities->keep_order_of_options( 
			array(
				'options_group' => array(
					$this->action_meta => array(
						Automator()->helpers->recipe->wp->options->pro->all_wp_post_types(
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
						Automator()->helpers->recipe->field->select_field_ajax( $this->action_meta, __( 'Post', 'uncanny-automator-pro' ) ),
						array(
							'option_code' => 'FORCEDELETE',
							'label'       => __( 'Also permanently delete the post(s) from the trash?', 'uncanny-automator-pro' ),
							'input_type'  => 'checkbox',
							'is_toggle'   => true,
						),
					),
				),
			)
		);
	}

	/**
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function post_trashed( $user_id, $action_data, $recipe_id, $args ) {

		$post_id   = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$post_type = Automator()->parse->text( $action_data['meta']['WPSPOSTTYPES'], $recipe_id, $user_id, $args );
		$force_del = (string) $action_data['meta']['FORCEDELETE'];

		// All posts are selected
		if ( intval( '-1' ) === intval( $post_id ) ) {
			//Only use WordPress function if force delete is set!
			if ( 'true' === $force_del ) {
				$args   = array(
					'post_type'      => $post_type,
					'orderby'        => 'ID',
					'post_status'    => 'publish',
					'order'          => 'DESC',
					'posts_per_page' => 99999, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				);
				$result = get_posts( $args );
				if ( empty( $result ) ) {
					$error_message                       = esc_html__( 'There are no posts in the selected post type.', 'uncanny-automator-pro' );
					$action_data['do-nothing']           = true;
					$action_data['complete_with_errors'] = true;
					Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

					return;
				}
				foreach ( $result as $post ) {
					wp_delete_post( $post->ID, true );
				}
				Automator()->complete_action( $user_id, $action_data, $recipe_id );

				return;
			}

			// Changing status of all the posts in a post type to `trash`.
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_status = %s WHERE post_type = %s", 'trash', $post_type ) );
			Automator()->complete_action( $user_id, $action_data, $recipe_id );

			return;
		}
		if ( 'true' !== $force_del ) {
			wp_trash_post( $post_id );
			Automator()->complete_action( $user_id, $action_data, $recipe_id );

			return;
		}
		wp_delete_post( $post_id, true );

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
