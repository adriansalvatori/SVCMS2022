<?php
/**
 * Controls checking of password
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PH_Password_Checker extends PH_Permission_Checker {
	/**
	 * Validate password
	 *
	 * @return boolean
	 */
	public function validate() {
		$post          = get_post( $this->data->id );
		$post_type_obj = get_post_type_object( $post->post_type );

		return (bool) current_user_can( $post_type_obj->cap->read_post, $post->ID );
	}
}
