<?php
/**
 * URL Rewrites used in projectHuddle
 * @since 3.0.0
 */

/**
 * Change url of comment locations to comment/{id} instead of slug
 *
 * @param $link
 * @param $post
 *
 * @return mixed
 */
function ph_project_thread_post_type_link( $link, $post ) {
	if ( ! in_array( $post->post_type, array( 'phw_comment_loc', 'ph_comment_location' ) ) ) {
		return $link;
	}

	$shortened = substr( $link, 0, strpos( $link, "%thread_id%" ) + 12 );

	return str_replace( '%thread_id%', $post->ID, $shortened );
}

add_filter( 'post_type_link', 'ph_project_thread_post_type_link', 1, 3 );

/**
 * Set rewrite rules for new permalink structure.
 */
function ph_project_thread_post_type_rewrite() {
	$post_types = array( 'phw_comment_loc', 'ph_comment_location' );

	foreach ( $post_types as $post_type ) {
		$object = get_post_type_object( $post_type );
		$slug   = str_replace( '%thread_id%', '', $object->rewrite['slug'] );
		add_rewrite_rule( '^' . untrailingslashit( $slug ) . '/([0-9]+)/?', 'index.php?post_type=' . $post_type . '&p=$matches[1]', 'top' );
	}
}

add_action( 'init', 'ph_project_thread_post_type_rewrite', 11 );