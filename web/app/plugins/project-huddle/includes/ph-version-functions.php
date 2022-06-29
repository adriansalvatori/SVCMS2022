<?php
/**
 * Functions for versions capability of ProjectHuddle
 */

/**
 * Returns all versions of a specified post
 *
 * @since 2.6.0
 *
 * @see get_children()
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param array|null  $args    Optional. Arguments for retrieving post revisions. Default null.
 * @return array An array of revisions, or an empty array if none.
 */
function ph_get_post_versions( $post_id = 0, $args = null ) {
	$post = get_post( $post_id );
	if ( ! $post || empty( $post->ID ) ) {
		return array();
	}

	$args = wp_parse_args(
		$args,
		array(
			'order'   => 'DESC',
			'orderby' => 'date ID',
		)
	);
	$args = array_merge(
		$args,
		array(
			'post_parent' => $post->ID,
			'post_type'   => 'ph_version',
		)
	);

	if ( ! $revisions = get_children( $args ) ) {
		return array();
	}

	return $revisions;
}
