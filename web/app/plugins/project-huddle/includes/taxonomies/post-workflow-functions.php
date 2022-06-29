<?php
/**
 * Functions for thread workflows
 */

function ph_get_default_workflow_status( $post_id = 0 ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post ? $post->ID : 0;
	}

	return apply_filters( 'ph_default_workflow_status', 'backlog', $post_id );
}
