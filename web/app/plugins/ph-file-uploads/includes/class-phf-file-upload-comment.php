<?php
/**
 * File upload comment type class
 *
 * @package ProjectHuddle
 */

/**
 * Create the file upload comment type
 */
class PHF_File_Upload_Comment {

	/**
	 * Get things going
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_image_size' ) );

		// add attachment id to threads.
		add_action( 'rest_api_init', array( $this, 'attachments_field' ) );

		// save attachments to comment when new thread is created.
		add_filter( 'ph_new_website_thread_comment', array( $this, 'save_new_thread_attachments' ), 10, 3 );

		// allow empty comment content if there's an attachment.
		add_filter( 'rest_preprocess_comment', array( $this, 'allow_empty_comment_if_attachments' ), 10, 2 );

		// delete attachment when comment is deleted.
		add_action( 'deleted_comment', array( $this, 'delete_attachment' ) );

		// trash attachment when comment is trashed.
		add_action( 'trash_comment', array( $this, 'trash_attachment' ) );
	}

	/**
	 * Sets an image thumbnail size for attachments
	 */
	public function add_image_size() {
		add_image_size( 'ph_comment_attachment', 200, 200, true );
	}

	/**
	 * Allow empty comments if there are attachments
	 *
	 * @param WP_Comment      $prepared_comment Prepared comment.
	 * @param WP_Rest_Request $request Rest request.
	 * @return WP_Comment
	 */
	public function allow_empty_comment_if_attachments( $prepared_comment, $request ) {
		// if there's not comment content and there are attachment ids.
		if ( empty( $prepared_comment['comment_content'] ) && ! empty( $request['attachment_ids'] ) ) {
			$prepared_comment['comment_content'] = '&nbsp;';
		}

		return $prepared_comment;
	}

	/**
	 * Delete attachment when comment is deleted
	 *
	 * @param integer $comment_id Comment id.
	 * @return void
	 */
	public function delete_attachment( $comment_id ) {
		if ( ! apply_filters( 'ph_delete_comment_attachments', true ) ) {
			return;
		}
		$attachments = get_comment_meta( $comment_id, 'attachment_ids' );

		if ( empty( $attachments ) ) {
			return;
		}
		foreach ( $attachments as $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
	}

	/**
	 * Trash attachment when comment is trashed
	 *
	 * @param integer $comment_id Comment id.
	 * @return void
	 */
	public function trash_attachment( $comment_id ) {
		if ( ! apply_filters( 'ph_trash_comment_attachments', true ) ) {
			return;
		}
		$attachments = get_comment_meta( $comment_id, 'attachment_ids' );

		if ( empty( $attachments ) ) {
			return;
		}
		foreach ( $attachments as $attachment_id ) {
			wp_delete_attachment( $attachment_id, false );
		}
	}

	/**
	 * Make sure we store new attachments in comment when thread is saved
	 *
	 * @param WP_Rest_Response $response Response.
	 * @param WP_Post          $post Post object.
	 * @param WP_Rest_Request  $request Request.
	 *
	 * @return mixed
	 */
	public function save_new_thread_attachments( $response, $post, $request ) {
		if ( ! empty( $request['attach'] ) ) {
			foreach ( $request['attach'] as $attachment_id ) {
				$response['attachments'][] = $attachment_id;
			}
		}

		return $response;
	}

	/**
	 * Add attachments to threads
	 */
	public function attachments_field() {
		register_rest_field(
			'phw_comment_loc',
			'attachments',
			array(
				'get_callback'    => null,
				'update_callback' => array( $this, 'save_thread_attachments' ),
				'schema'          => $this->get_thread_attachment_schema(),
			)
		);

		register_rest_field(
			'phw_comment_loc',
			'attach',
			array(
				'get_callback'    => null,
				'update_callback' => array( $this, 'save_thread_attachments' ),
				'schema'          => $this->get_thread_attachment_schema(),
			)
		);

		register_rest_field(
			'comment',
			'attachment_ids',
			array(
				'get_callback'    => array( $this, 'get_comment_attachments' ),
				'update_callback' => array( $this, 'save_comment_attachments' ),
				'schema'          => $this->get_comment_attachment_schema(),
			)
		);
	}

	/**
	 * Attachment thread schema.
	 *
	 * @return array
	 */
	public function get_thread_attachment_schema() {
		return apply_filters(
			'ph_comment_attachment_schema', array(
				'description' => esc_html__( 'Array of comment attachment ids on the thread.', 'project-huddle' ),
				'type'        => 'array',
				'items'       => array(
					'description' => esc_html__( 'Attachment Object.', 'project-huddle' ),
					'type'        => 'object',
				),
			)
		);
	}

	/**
	 * Comment attachment schema
	 *
	 * @return array
	 */
	public function get_comment_attachment_schema() {
		return apply_filters(
			'ph_comment_attachment_schema', array(
				'description' => esc_html__( 'Array of comment attachment ids on the thread.', 'project-huddle' ),
				'type'        => 'array',
				'items'       => array(
					'description' => esc_html__( 'Attachment ID.', 'project-huddle' ),
					'type'        => 'integer',
				),
			)
		);
	}

	/**
	 * Save attachments to comments
	 *
	 * @param array           $value       Array of attachment IDs.
	 * @param WP_Comment      $comment     Comment object.
	 * @param string          $attr        Attachments attribute name.
	 * @param WP_REST_Request $request     Request.
	 * @param object          $object_type Post object type.
	 *
	 * @return true|WP_Error
	 */
	public function save_comment_attachments( $value, $comment, $attr, $request, $object_type ) {
		// add permissions filter for granular control.
		if ( ! apply_filters( "ph_website_update_comment_{$attr}_allowed", true, $comment, $value ) ) {
			return new WP_Error( 'rest_forbidden_meta', __( 'Sorry, you are not allowed to do this.', 'project-huddle' ), array( 'status' => rest_authorization_required_code() ) );
		}

		// update.
		update_comment_meta( $comment->comment_ID, $attr, $value );

		// run through get to make sure it's a sanitized value to prevent xss.
		$meta = get_comment_meta( $comment->comment_ID, $attr, true );

		// sanitize.
		return rest_sanitize_value_from_schema( $meta, $this->get_comment_attachment_schema() );
	}

	/**
	 * Get comment attachment information
	 *
	 * @param array           $post        Post object as array.
	 * @param string          $attr        Attachments attribute name.
	 * @param WP_REST_Request $request     Rest request.
	 * @param object          $object_type Post object type.
	 *
	 * @return true|WP_Error
	 */
	public function get_comment_attachments( $post, $attr, $request, $object_type ) {
		$data = get_comment_meta( $post['id'], $attr, true );

		return $data ? rest_sanitize_value_from_schema( $data, $this->get_comment_attachment_schema() ) : false;
	}

	/**
	 * When a thread is saved with attachments, attach to post
	 *
	 * @param array           $value    Array of attachment ids.
	 * @param WP_Post         $post     Post object.
	 * @param string          $attr     Attribute name.
	 * @param WP_Rest_Request $request  Request.
	 * @param string          $object_type Object type.
	 *
	 * @return true|WP_Error
	 */
	public function save_thread_attachments( $value, $post, $attr, $request, $object_type ) {
		// add permissions filter for granular control.
		if ( ! apply_filters( "ph_website_update_thread_{$attr}_allowed", true, $post, $value ) ) {
			return new WP_Error( 'rest_forbidden_meta', __( 'Sorry, you are not allowed to do this.', 'project-huddle' ), array( 'status' => rest_authorization_required_code() ) );
		}

		// attach to current post.
		if ( ! empty( $value ) ) {
			foreach ( $value as $attachment_id ) {
				// update attachment to post parent.
				wp_update_post(
					array(
						'ID'          => (int) $attachment_id,
						'post_parent' => $post->ID,
					)
				);
			}
		}

		// sanitize.
		return rest_sanitize_value_from_schema( $value, $this->get_comment_attachment_schema() );
	}

	/**
	 * Get comment attachment information
	 *
	 * @param WP_Post         $post     Post object.
	 * @param string          $attr     Attribute name.
	 * @param WP_Rest_Request $request  Request.
	 * @param string          $object_type Object type.
	 *
	 * @return true|WP_Error
	 */
	public function get_thread_attachments( $post, $attr, $request, $object_type ) {
		$args = array(
			'post_parent'    => $post['id'],
			'post_type'      => 'attachment',
			'post_mime_type' => '',
			'posts_per_page' => - 1, // get all.
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'fields'         => 'ids', // get only ids (faster, will fetch later).
		);

		$attachments = new WP_Query( $args );

		return rest_sanitize_value_from_schema( $attachments->posts, $this->get_comment_attachment_schema() );
	}
}
