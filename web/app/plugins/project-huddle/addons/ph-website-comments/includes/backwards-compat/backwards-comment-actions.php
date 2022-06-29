<?php

/**
 * Filters for backwards compatibility
 */
class PH_Website_Backwards_Comment_Actions {
	public function __construct() {
		// after comment actions
		add_action( 'ph_website_rest_publish_comment', array( $this, 'new_comment' ), 10, 3 );
		add_action( 'ph_website_rest_edit_comment', array( $this, 'edit_comment' ), 10, 3 );
		add_action( 'ph_website_rest_delete_comment', array( $this, 'delete_comment' ), 10, 3 );
	}


	/**
	 * Pass legacy format to comment
	 *
	 * @param $comment
	 *
	 * @return mixed
	 */
	public function comment_legacy_format( $comment ) {
		// get parents
		$parents = ph_get_parents_ids( $comment );

		// old format
		if ( $comment->comment_date ) {
			$comment->date       = ph_human_time_diff( strtotime($comment->comment_date) );
		}

		$comment->avatar     = get_avatar( sanitize_email( $comment->comment_author_email ), 64 );
		$comment->id         = (int) $comment->comment_ID;
		$comment->website_id = $parents['website'];
		$comment->domain     = get_post_meta( $parents['website'], 'website_url', true );
		$comment->page_title = get_the_title( $parents['website-page'] );

		// force array
		return (array) $comment;
	}

	/**
	 * New comment after
	 *
	 * @param WP_Comment      $comment Inserted or updated comment object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function new_comment( $comment, $request ) {
		$comment_legacy = $this->comment_legacy_format( $comment );

		$comment_legacy = apply_filters( 'ph_website_filter_new_comment', $comment_legacy );
		do_action( 'ph_website_new_comment', $comment_legacy );
	}

	/**
	 * Edit comment after
	 *
	 * @param WP_Comment      $comment Inserted or updated comment object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function edit_comment( $comment, $request ) {
		$comment_legacy = $this->comment_legacy_format( $comment );

		$comment_legacy = apply_filters( 'ph_website_filter_edit_comment', $comment_legacy );
		do_action( 'ph_website_edit_comment', $comment_legacy );
	}

	/**
	 * Delete comment after
	 *
	 * @param WP_Comment      $comment Inserted or updated comment object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function delete_comment( $comment, $request ) {
		$comment_legacy = $this->comment_legacy_format( $comment );

		$comment_legacy = apply_filters( 'ph_website_filter_delete_comment', $comment_legacy );
		do_action( 'ph_website_delete_comment', $comment_legacy );
	}
}

new PH_Website_Backwards_Comment_Actions();