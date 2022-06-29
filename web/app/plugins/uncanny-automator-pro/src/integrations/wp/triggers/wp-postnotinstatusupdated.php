<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_POSTNOTINSTATUSUPDATED
 *
 * @package Uncanny_Automator_Pro
 */
class WP_POSTNOTINSTATUSUPDATED {

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
		$this->trigger_code = 'WPPOSTNOTINSTATUS';
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
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( esc_attr__( 'A user updates a post not in {{a specific:%1$s}} status', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => esc_attr__( 'A user updates a post not in {{a specific}} status', 'uncanny-automator-pro' ),
			'action'              => 'post_updated',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wp_post_updated' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return \array[][]
	 */
	public function load_options() {
		$relevant_tokens = array(
			'POSTSTATUSUPDATED' => __( 'Status', 'uncanny-automator-pro' ),
			'POSTTYPE'          => __( 'Post type', 'uncanny-automator-pro' ),
			'POSTTITLE'         => __( 'Post title', 'uncanny-automator-pro' ),
			'POSTID'            => __( 'Post ID', 'uncanny-automator-pro' ),
			'POSTCONTENT'       => __( 'Post content', 'uncanny-automator-pro' ),
			'POSTEXCERPT'       => __( 'Post excerpt', 'uncanny-automator-pro' ),
			'POSTURL'           => __( 'Post URL', 'uncanny-automator-pro' ),
			'POSTAUTHORFN'      => __( 'Author first name', 'uncanny-automator-pro' ),
			'POSTAUTHORLN'      => __( 'Author last name', 'uncanny-automator-pro' ),
			'POSTAUTHORDN'      => __( 'Author display name', 'uncanny-automator-pro' ),
			'POSTAUTHOREMAIL'   => __( 'Author email', 'uncanny-automator-pro' ),
		);

		return Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => array(
					$this->trigger_meta => array(
						Automator()->helpers->recipe->wp->options->pro->all_wp_post_types(
							__( 'Post type', 'uncanny-automator-pro' ),
							'WPPOSTTYPES',
							array(
								'token'           => true,
								'is_ajax'         => false,
								'relevant_tokens' => array(),
							)
						),
						Automator()->helpers->recipe->wp->options->pro->wp_post_statuses(
							__( 'Status', 'uncanny-automator-pro' ),
							$this->trigger_meta,
							array(
								'relevant_tokens' => $relevant_tokens,
							)
						),
					),
				),
			)
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $post_ID
	 * @param $post_after
	 * @param $post_before
	 */

	public function wp_post_updated( $post_ID, $post_after, $post_before ) {
		$recipes              = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post_type   = Automator()->get->meta_from_recipes( $recipes, 'WPPOSTTYPES' );
		$required_post_status = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$post                 = $post_after;
		$matched_recipe_ids   = array();

		$user_obj = get_user_by( 'ID', (int) $post_before->post_author );

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				// is post type
				if (
					intval( '-1' ) === intval( $required_post_type[ $recipe_id ][ $trigger_id ] ) // any post type
					|| $post->post_type === $required_post_type[ $recipe_id ][ $trigger_id ] // specific post type
					|| empty( $required_post_type[ $recipe_id ][ $trigger_id ] ) // Backwards compatibility -- the trigger didnt have a post type selection pre 2.10
				) {
					// a post status is set and is NOT equal to the previous post status
					if ( (string) $required_post_status[ $recipe_id ][ $trigger_id ] !== (string) $post_before->post_status ) {
						$matched_recipe_ids[ $recipe_id ] = array(
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
					'user_id'          => $user_obj->ID,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = array(
								'user_id'        => $user_obj->ID,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							// Post Title Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTTITLE';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_title );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post ID Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTID';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->ID );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post URL Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTURL';
							$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $post_after->ID ) );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Content Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTCONTENT';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_content );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Excerpt Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTEXCERPT';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_excerpt( $post_after->ID ) );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Status Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTSTATUS';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_status );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Author First Name Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHORFN';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->first_name );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Author Last Name Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHORLN';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->last_name );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Author Display Name Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHORDN';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->display_name );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post Author Email Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTAUTHOREMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $user_obj->user_email );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Status
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = $post_before->post_status;
							Automator()->insert_trigger_meta( $trigger_meta );

							// Post type
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':POSTTYPE';
							$trigger_meta['meta_value'] = $post_before->post_type;
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
