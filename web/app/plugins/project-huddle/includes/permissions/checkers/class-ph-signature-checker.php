<?php

/**
 * Controls checking of signature
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_Signature_Checker extends PH_Permission_Checker
{
	/**
	 * Validate Signature
	 *
	 * @return boolean
	 */
	public function validate()
	{
		// not valid if no signature
		if (!$this->data->signature) {
			return false;
		}

		// get security signature
		if (!$signature_key = get_post_meta($this->data->id, 'security-signature', true)) {
			return false;
		}

		// check email first
		$valid = hash_equals(hash_hmac('sha256', $this->data->email, $signature_key), strval($this->data->signature));

		// otherwise fall back to guest if we are not checking strict identity
		if (!$valid) {
			$valid = hash_equals(hash_hmac('sha256', 'guest', $signature_key), strval($this->data->signature));
		}

		return $valid;
	}
}
