<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ANON_WP_SUBMITCOMMENT
 *
 * @package Uncanny_Automator_Pro
 */
class ANON_WP_SUBMITCOMMENT {

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
		$this->trigger_code = 'WPCOMMENTSUBMITTED';
		$this->trigger_meta = 'SUBMITCOMMENTONPOST';
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
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - WordPress */
			'sentence'            => sprintf( esc_attr__( "A guest comment is submitted on a user's {{post:%1\$s}}", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress */
			'select_option_name'  => esc_attr__( "A guest comment is submitted on a user's {{post}}", 'uncanny-automator-pro' ),
			'action'              => 'comment_post',
			'priority'            => 90,
			'accepted_args'       => 3,
			'type'                => 'anonymous',
			'validation_function' => array( $this, 'anon_submit_comment' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * load_options
	 *
	 * @return void
	 */
	public function load_options() {

		$options = Automator()->utilities->keep_order_of_options(
				array(
					'options_group'       => array(
						$this->trigger_meta => array(
							Automator()->helpers->recipe->wp->options->pro->all_wp_post_types(
								__( 'Post type', 'uncanny-automator-pro' ),
								'WPPOSTTYPES',
								array(
									'token'        => false,
									'is_ajax'      => true,
									'comments'     => true,
									'target_field' => $this->trigger_meta,
									'endpoint'     => 'select_all_post_from_SELECTEDPOSTTYPE',
								)
							),
							Automator()->helpers->recipe->field->select_field(
								$this->trigger_meta,
								__( 'Post', 'uncanny-automator-pro' ),
								array(),
								null,
								false,
								false,
								$relevant_tokens = array(
									'POSTEXCERPT'        => __( 'Post excerpt', 'uncanny-automator-pro' ),
									'COMMENTID'          => __( 'Comment ID', 'uncanny-automator-pro' ),
									'COMMENTAUTHOR'      => __( 'Comment author name', 'uncanny-automator-pro' ),
									'COMMENTAUTHOREMAIL' => __( 'Comment author email', 'uncanny-automator-pro' ),
									'COMMENTAUTHORWEB'   => __( 'Comment author website', 'uncanny-automator-pro' ),
									'COMMENTCONTENT'     => __( 'Comment content', 'uncanny-automator-pro' ),
								)
							),
						),
					),
				)
			);

		return $options;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $comment_id
	 * @param $comment_approved
	 * @param $commentdata
	 */
	public function anon_submit_comment( $comment_id, $comment_approved, $commentdata ) {

		global $uncanny_automator;

		if ( 0 !== $commentdata['user_id'] ) {
			return;
		}
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post      = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		//Add where option is set to Any post / specific post
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( intval( '-1' ) === intval( $required_post[ $recipe_id ][ $trigger_id ] ) ||
				     $required_post[ $recipe_id ][ $trigger_id ] == $commentdata['comment_post_ID'] ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		//	If recipe matches
		if ( ! empty( $matched_recipe_ids ) ) {
			$user_id = get_current_user_id();
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'post_id'          => $commentdata['comment_post_ID'],
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

							// Comment ID
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':COMMENTID';
							$trigger_meta['meta_value'] = maybe_serialize( $comment_id );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Comment Author
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':COMMENTAUTHOR';
							$trigger_meta['meta_value'] = maybe_serialize( $commentdata['comment_author'] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Comment Author Email
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':COMMENTAUTHOREMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $commentdata['comment_author_email'] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Comment Author Website
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':COMMENTAUTHORWEB';
							$trigger_meta['meta_value'] = maybe_serialize( $commentdata['comment_author_url'] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Comment Author Content
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':COMMENTCONTENT';
							$trigger_meta['meta_value'] = maybe_serialize( $commentdata['comment_content'] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// Post Tokens
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPPOSTTYPES_ID';
							$trigger_meta['meta_value'] = maybe_serialize( $commentdata['comment_post_ID'] );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPPOSTTYPES_URL';
							$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $commentdata['comment_post_ID'] ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPPOSTTYPES';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_title( $commentdata['comment_post_ID'] ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPPOSTEXCERPT';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_excerpt( $commentdata['comment_post_ID'] ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
