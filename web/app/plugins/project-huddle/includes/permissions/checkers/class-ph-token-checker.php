<?php
/**
 * Controls checking of access tokens
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PH_Token_Checker extends PH_Permission_Checker {
	/**
	 * Validate Token
	 *
	 * @return boolean
	 */
	public function validate() {
		// cannot verify if token is not set
		if ( ! $token = get_post_meta( $this->data->id, 'access_token', true ) ) {
			return false;
		}

		return $token === $this->get_users_token();
	}

	/**
	 * Get the users token from a cookie if not set
	 */
	public function get_users_token() {
		// store access token in cookie if set in url
		if ( $this->data->token ) {
			PH()->session->set(
				'project_access',
				array(
					$this->data->id => $this->data->token,
				)
			);
		} else {
			$access            = PH()->session->get( 'project_access' );
			$this->data->token = isset( $access[ $this->data->id ] ) ? $access[ $this->data->id ] : $this->data->token;
		}

		return $this->data->token;
	}
}
