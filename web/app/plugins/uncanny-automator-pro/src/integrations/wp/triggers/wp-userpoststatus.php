<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_USERPOSTSTATUS
 *
 * @package Uncanny_Automator_Pro
 */
class WP_USERPOSTSTATUS {

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
		$this->trigger_code = 'WPPOSTSTATUS';
		$this->trigger_meta = 'POSTSTATUSUPDATED';
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
			'sentence'            => sprintf( __( "{{A user's post:%1\$s}} is set to {{a specific:%2\$s}} status", 'uncanny-automator-pro' ), 'WPPOST', $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( "{{A user's post}} is set to {{a specific}} status", 'uncanny-automator-pro' ),
			'action'              => 'transition_post_status',
			'priority'            => 190,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wp_post_updated' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$relevant_tokens = array(
			'POSTCONTENT'     => __( 'Post content', 'uncanny-automator-pro' ),
			'POSTEXCERPT'     => __( 'Post excerpt', 'uncanny-automator-pro' ),
			'POSTAUTHORFN'    => __( 'Author first name', 'uncanny-automator-pro' ),
			'POSTAUTHORLN'    => __( 'Author last name', 'uncanny-automator-pro' ),
			'POSTAUTHORDN'    => __( 'Author display name', 'uncanny-automator-pro' ),
			'POSTAUTHOREMAIL' => __( 'Author email', 'uncanny-automator-pro' ),
			'POSTID'          => __( 'Post ID', 'uncanny-automator-pro' ),
			'POSTURL'         => __( 'Post URL', 'uncanny-automator-pro' ),
		);

		return Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => array(
					'WPPOST' => array(
						Automator()->helpers->recipe->wp->options->pro->all_wp_post_types(
							__( 'Post type', 'uncanny-automator-pro' ),
							'WPPOSTTYPES',
							array(
								'token'           => false,
								'is_ajax'         => true,
								'target_field'    => 'WPPOST',
								'endpoint'        => 'select_all_post_from_SELECTEDPOSTTYPE',
								'relevant_tokens' => $relevant_tokens,
							)
						),
						Automator()->helpers->recipe->field->select_field(
							'WPPOST',
							__( 'Post', 'uncanny-automator-pro' )
						),
					),
				),
				'options'       => array(
					Automator()->helpers->recipe->wp->options->pro->wp_post_statuses(
						__( 'Status', 'uncanny-automator-pro' ),
						$this->trigger_meta,
						array(
							'is_any' => true,
						)
					),
				),
			)
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function wp_post_updated( $new_status, $old_status, $post ) {

		// Avoid double call. T#25676
		if ( isset( $_GET['meta-box-loader'] ) === true ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		global $uncanny_automator;

		$recipes              = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post_status = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_post_id     = $uncanny_automator->get->meta_from_recipes( $recipes, 'WPPOST' );

		$matched_recipe_ids = array();

		$user_obj = get_user_by( 'ID', (int) $post->post_author );

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				//Add where option is set to Any post type
				if ( ! isset( $required_post_status[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_post_status[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}

				//Add where option is set to Any post type
				if ( ! isset( $required_post_id[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_post_id[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}

				if ( ( intval( '-1' ) === intval( $required_post_id[ $recipe_id ][ $trigger_id ] ) ||
				       intval( $required_post_id[ $recipe_id ][ $trigger_id ] ) === intval( $post->ID ) )
				     && ( intval( '-1' ) === intval( $required_post_status[ $recipe_id ][ $trigger_id ] ) ||
				          (string) $required_post_status[ $recipe_id ][ $trigger_id ] === (string) $new_status ) ) {
					$matched_recipe_ids[ $recipe_id ] = array(
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
					'user_id'          => $user_obj->ID,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				);

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = array(
								'user_id'        => $user_obj->ID,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							// Post ID Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTID';
							$trigger_meta['meta_value'] = maybe_serialize( $post->ID );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post URL Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTURL';
							$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $post->ID ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Content Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTCONTENT';
							$trigger_meta['meta_value'] = maybe_serialize( $post->post_content );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Excerpt Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTEXCERPT';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_excerpt( $post->ID ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Status Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTSTATUSUPDATED';
							$trigger_meta['meta_value'] = maybe_serialize( $post->post_status );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Author First Name Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHORFN';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->first_name );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Author Last Name Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHORLN';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->last_name );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Author Display Name Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHORDN';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->display_name );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Author Email Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHOREMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->user_email );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

		return;
	}

}
