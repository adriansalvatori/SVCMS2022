<?php

namespace Uncanny_Automator_Pro;

use PeepSo;
use PeepSoUser;
use PeepSoActivityStream;

/**
 * Class PEEPSO_CREATEACTIVITYPOST
 *
 * @package Uncanny_Automator
 */
class PEEPSO_CREATEACTIVITYPOST {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'PP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'PPCREATEACTIVITYPOST';
		$this->action_meta = 'PPPOSTCONTENT';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/peepso/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'requires_user'      => true,
			'is_pro'             => true,
			/* translators: Action - WordPress Core */
			'sentence'           => sprintf( esc_attr__( 'Add {{a post:%1$s}} to the site wide activity stream', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WordPress Core */
			'select_option_name' => esc_attr__( 'Add {{a post}} to the site wide activity stream', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 4,
			'execution_function' => array( $this, 'create_post' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
	}

	public function plugins_loaded() {
		$this->define_action();
	}

	/**
	 * load_options
	 */
	public function load_options() {
		$options = array(
			$this->action_meta => array(
				Automator()->helpers->recipe->field->text(
					array(
						'option_code'      => $this->action_meta,
						/* translators: Activity Content field */
						'label'            => esc_attr__( 'Content', 'uncanny-automator' ),
						'input_type'       => 'textarea',
						'required'         => true,
						'supports_tinymce' => false,
					)
				),
				//Temporary fix for the UI
				array(
					'input_type'  => 'textarea',
					'option_code' => $this->action_meta . '_HIDDEN',
					'is_hidden'   => true,
				),
			),
		);

		$options = Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => $options,
			)
		);

		return $options;
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function create_post( $user_id, $action_data, $recipe_id, $args ) {

		$author     = $user_id;
		$table_name = 'peepso_activities';
		$content    = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$content    = str_replace( '<br>', '\n', $content );

		if ( empty( $content ) ) {
			return;
		}

		// check owner's permissions
		if ( PeepSo::check_permissions( $user_id, PeepSo::PERM_POST, $user_id ) === false ) {
			$error_message                       = __( 'Unable to create activities entry', 'uncanny-automator-pro' );
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		// create post
		$a_post_data = array(
			'post_title'   => "{$user_id}-{$author}-" . time(),
			'post_excerpt' => $content,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_author'  => $author,
			'post_type'    => PeepSoActivityStream::CPT_POST,
		);

		$content = $a_post_data['post_content'];

		$id = wp_insert_post( $a_post_data );

		// add metadata to indicate whether or not to display link previews for this post
		add_post_meta( $id, '_peepso_display_link_preview', ( isset( $extra['show_preview'] ) ? $extra['show_preview'] : 1 ), true );

		// check $id for failure?
		if ( 0 === $id ) {
			$error_message                       = __( 'Unable to create activities entry', 'uncanny-automator-pro' );
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		// add data to Activity Stream data table
		$privacy    = PeepSoUser::get_instance( $user_id )->get_profile_accessibility();
		$a_act_data = array(
			'act_owner_id'    => $user_id,
			'act_module_id'   => 1,
			'act_external_id' => $id,
			'act_access'      => $privacy,
			'act_ip'          => PeepSo::get_ip_address(),
		);

		$a_act_data = apply_filters( 'peepso_activity_insert_data', $a_act_data );

		global $wpdb;
		$res = $wpdb->insert( $wpdb->prefix . $table_name, $a_act_data );

		if ( ! is_int( $res ) ) {
			$error_message                       = sprintf( __( 'Unable to create activities entry: %s', 'uncanny-automator-pro' ), $wpdb->last_error );
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			wp_delete_post( $id );

			return;
		}

		if ( 1 === absint( $a_act_data['act_module_id'] ) ) {
			update_user_meta( $user_id, 'peepso_last_used_post_privacy', $privacy );
		}

		$filtered_content = apply_filters( 'peepso_activity_post_content', $content, $id );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => $filtered_content,
			)
		);

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
