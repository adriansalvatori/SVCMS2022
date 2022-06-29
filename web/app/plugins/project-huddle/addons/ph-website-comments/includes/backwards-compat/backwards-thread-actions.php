<?php

/**
 * Filters for backwards compatibility
 */
class PH_Website_Backwards_Thread_Actions {
	public function __construct() {
		add_action( 'ph_website_rest_publish_thread', array( $this, 'new_thread' ), 10, 2 );
		add_action( 'ph_website_rest_edit_thread', array( $this, 'edit_thread' ), 10, 3 );
		add_action( 'ph_website_rest_update_thread_resolved', array( $this, 'resolve_thread' ), 10, 3 );
		add_action( 'ph_website_rest_delete_thread', array( $this, 'delete_thread' ), 10, 3 );
	}


	/**
	 * Pass legacy format to comment
	 *
	 * @param WP_Post $post_data
	 *
	 * @return mixed
	 */
	public function thread_legacy_format( $post_data ) {
		// get parents
		$parents = ph_get_parents_ids( $post_data );

		if ( ! isset( $parents['website'] ) ) {
			return;
		}

		$post_data->title           = $post_data->post_title;
		$post_data->avatar          = get_avatar( sanitize_email( $post_data->post_author ), 64 );
		$post_data->comment_content = $post_data->post_title;
		$post_data->comment_author  = $post_data->post_author;
		$post_data->relativeX       = $post_data->relativeX;
		$post_data->relativeY       = $post_data->relativeY;
		$post_data->path            = $post_data->path;
		$post_data->html            = $post_data->html;
		$post_data->domain          = get_post_meta( $parents['website'], 'website_url', true );
		$post_data->resolved        = (bool) $post_data->resolved;
		$post_data->resX            = $post_data->resX;
		$post_data->resY            = $post_data->resY;
		$post_data->browser         = $post_data->browser;
		$post_data->browserVersion  = $post_data->browserVersion;
		$post_data->browserOS       = $post_data->browserOS;
		$post_data->assigned        = $post_data->assigned;

		return (array) $post_data;
	}

	/**
	 * New comment after
	 *
	 * @param WP_Comment      $data    Inserted or updated comment object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function new_thread( $data, $request ) {
		// must be our comment type
		if ( $data->post_type !== 'phw_comment_loc' ) {
			return;
		}

		$data_legacy = $this->thread_legacy_format( $data );

		do_action( 'ph_website_new_thread', $data_legacy );
	}

	/**
	 * Edit thread after
	 *
	 * @param WP_Comment      $data    Inserted or updated thread post object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function edit_thread( $data, $request ) {
		// must be our post type
		if ( $data->post_type !== 'phw_comment_loc' ) {
			return;
		}

		$data_legacy = $this->thread_legacy_format( $data );

		do_action( 'ph_website_edit_thread', $data_legacy );
	}

	/**
	 * Delete post after
	 *
	 * @param WP_Comment      $data    Inserted or updated post object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function delete_thread( $data, $request ) {
		// must be our post type
		if ( $data->post_type !== 'phw_comment_loc' ) {
			return;
		}

		$data_legacy = $this->thread_legacy_format( $data );

		do_action( 'ph_website_delete_thread', $data_legacy );
	}

	/**
	 * Resolve thread after
	 *
	 * @param $meta
	 * @param $object
	 */
	public function resolve_thread( $meta, $object ) {
		$object = $this->thread_legacy_format( $object );

		do_action( 'ph_website_resolve_thread', $object );
	}
}

new PH_Website_Backwards_Thread_Actions();