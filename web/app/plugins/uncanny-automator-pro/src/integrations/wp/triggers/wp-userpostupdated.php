<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_USERPOSTUPDATED
 *
 * @package Uncanny_Automator_Pro
 */
class WP_USERPOSTUPDATED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPUSERSPOSTUPDATED';
		$this->trigger_meta = 'USERSSPECIFICPOSTTYPE';
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

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( __( "A user's {{specific type of post:%1\$s}} is updated", 'uncanny-automator-pro' ), 'WPPOSTTYPES' ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( "A user's {{specific type of post}} is updated", 'uncanny-automator-pro' ),
			'action'              => 'post_updated',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'users_post_updated' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array[]
	 */
	public function load_options() {
		return Automator()->utilities->keep_order_of_options(
			array(
				'options' => array(
					Automator()->helpers->recipe->wp->options->pro->all_wp_post_types(
						__( 'Post type', 'uncanny-automator-pro' ),
						'WPPOSTTYPES',
						array(
							'relevant_tokens' => array(
								'POSTTITLE'    => __( 'Post title', 'uncanny-automator-pro' ),
								'POSTID'       => __( 'Post ID', 'uncanny-automator-pro' ),
								'POSTURL'      => __( 'Post URL', 'uncanny-automator-pro' ),
								'WPPOSTTYPES'  => __( 'Post type', 'uncanny-automator-pro' ),
								'POSTCONTENT'  => __( 'Post content', 'uncanny-automator-pro' ),
								'POSTEXCERPT'  => __( 'Post excerpt', 'uncanny-automator-pro' ),
								'POSTAUTHORDN' => __( 'Post author', 'uncanny-automator-pro' ),
							),
						)
					),
				),
			)
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $postID
	 * @param $post_after
	 * @param $post_before
	 *
	 * @return bool|void
	 */
	public function users_post_updated( $post_ID, $post_after, $post_before ) {
		// Avoid double call. T#25676
		if ( isset( $_GET['meta-box-loader'] ) === true ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		global $uncanny_automator;

		$user_id            = get_current_user_id();
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post_type = $uncanny_automator->get->meta_from_recipes( $recipes, 'WPPOSTTYPES' );

		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				//Add where option is set to Any post type
				if ( $required_post_type[ $recipe_id ][ $trigger_id ] === $post_before->post_type || intval( '-1' ) === intval( $required_post_type[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $post_before->post_author,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
				);

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							// Post Title Token
							$trigger_meta['meta_key']   = 'POSTTITLE';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_title );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post ID Token
							$trigger_meta['meta_key']   = 'POSTID';
							$trigger_meta['meta_value'] = maybe_serialize( $post_ID );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post URL Token
							$trigger_meta['meta_key']   = 'POSTURL';
							$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $post_ID ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Type Token
							$trigger_meta['meta_key']   = 'WPPOSTTYPES';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_type );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Author Token
							$trigger_meta['meta_key']   = 'POSTAUTHORDN';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_author_meta( 'display_name', $post_before->post_author ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Content Token
							$trigger_meta['meta_key']   = 'POSTCONTENT';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_content );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Excerpt Token
							$trigger_meta['meta_key']   = 'POSTEXCERPT';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_excerpt );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
