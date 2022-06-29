<?php
/**
 * REST API: Controller for manual notifications, (i.e. share this project)
 *
 * @since 2.7.0
 */

/**
 * Controller for batch requests
 *
 * @since 2.7.0
 *
 * @see   PH_REST_Controller
 */
class PH_REST_Manual_Notifications_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since  2.7.0
	 * @access public
	 */
	public function __construct() {
		// namespace
		$this->namespace = 'projecthuddle/v2';
	}

	/**
	 * Register the batch route
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/notify', array(
			// Supported methods for this endpoint.
			'methods'             => WP_REST_Server::CREATABLE,
			// Register the callback for the endpoint.
			'callback'            => array( $this, 'send_notification' ),
			// user must be logged in.
			'permission_callback' => array( $this, 'send_notification_permissions_check' ),
			// Register args for the endpoint.
			'args'                => array(
				'to'      => array(
					'description' => __( 'Email addresses for the email notification, separated by commas.', 'project-huddle' ),
					'type'        => 'string',
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'subject' => array(
					'description' => __( 'Subject line for the message.', 'project-huddle' ),
					'type'        => 'string',
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'message' => array(
					'description' => __( 'Message.', 'project-huddle' ),
					'type'        => 'string',
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'post_id' => array(
					'description' => __( 'ID of the post to share', 'project-huddle' ),
					'type'        => 'integer',
					'required'    => true,
				)
			)
		) );
	}

	public function send_notification_permissions_check( $request ) {
		// must be logged in
		if ( ! apply_filters( 'ph_rest_send_notification_login_requirement', is_user_logged_in() ) ) {
			return new WP_Error( 'rest_login_required', __( 'Sorry, you must be logged in to share.', 'project-huddle' ), array( 'status' => 403 ) );
		}

		// must be a project member
		if ( ! apply_filters( 'ph_rest_send_notification_membership_requirement', ph_user_is_member( $request['post_id'] ) ) ) {
			return new WP_Error( 'rest_no_access', __( 'Sorry, you must have access to this project to share.', 'project-huddle' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Triggers the notification hook with the required information
	 *
	 * @param WP_REST_Request $request The request object
	 *
	 * @return bool|WP_Error Whether it was successfully triggered
	 */
	public function send_notification( $request ) {
		// check honeypot
		if ( isset( $request['website'] ) && $request['website'] ) {
			return true;
		}

		$to = explode( ",", $request['to'] );

		foreach ( $to as $email ) {
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				return new WP_Error( 'rest_invalid_email', __( 'Sorry, the email you entered is invalid.' ), array( 'status' => 400 ) );
			}
		}

		// run share post hook
		do_action( 'ph_email_share_post', explode( ",", $request['to'] ), $request['subject'], $request['message'], $request['post_id'] );

		// return success
		return true;
	}
}

// Function to register our new routes from the controller.
function ph_register_notification_routes() {
	$controller = new PH_REST_Manual_Notifications_Controller();
	$controller->register_routes();
}

add_action( 'rest_api_init', 'ph_register_notification_routes' );