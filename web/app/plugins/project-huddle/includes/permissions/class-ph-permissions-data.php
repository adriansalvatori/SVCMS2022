<?php

/**
 * Data transfer object for visitors permission data for a project
 */

// Exit if guestsed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_Permissions_Data
{
	/**
	 * ID of the project
	 *
	 * @var integer
	 */
	public $id = 0;

	/**
	 * Signature
	 *
	 * @var string
	 */
	public $signature = '';

	/**
	 * Email
	 *
	 * @var string
	 */
	public $username = 'guest';

	/**
	 * Email
	 *
	 * @var string
	 */
	public $email = 'guest';

	/**
	 * Token
	 *
	 * @var string
	 */
	public $token = '';

	/**
	 * Project guests Setting
	 *
	 * @var string
	 */
	public $guests = false;

	/**
	 * Store everthing on construct
	 *
	 * @param integer $id
	 * @param string $guests
	 * @param string $signature
	 * @param string $token
	 * @param string $email
	 * @param string $username
	 */
	public function __construct($id, $guests = false, $signature = '', $token = '', $email = 'guest', $username = '')
	{
		$this->id        = $id ?: $this->id;
		$this->signature = $signature ?: $this->signature;
		$this->username  = $username ?: $this->username;
		$this->email     = $email ?: $this->email;
		$this->token     = $token ?: $this->token;
		$this->guests    = $guests ?: $this->guests;
	}
}
