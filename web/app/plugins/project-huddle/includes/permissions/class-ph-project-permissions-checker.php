<?php

/**
 * Project Permissions Checker
 * Uses the chain of resposibiltiy design pattern
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2019, Andre Gagnon
 * @since       3.6.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// permission checkers
foreach (glob(PH_PLUGIN_DIR . 'includes/permissions/checkers/*.php') as $filename) {
	require_once $filename;
}

class PH_Project_Permissions_Checker
{
	/**
	 * Access Override
	 *
	 * @var boolean
	 */
	protected $access = false;

	/**
	 * First function checker
	 *
	 * @var function
	 */
	protected $first_checker = null;

	/**
	 * Signature checking class
	 *
	 * @var PH_Permission_Checker
	 */
	protected $signature_checker;

	/**
	 * Token checking class
	 *
	 * @var PH_Permission_Checker
	 */
	protected $token_checker;

	/**
	 * Login checking class
	 *
	 * @var PH_Permission_Checker
	 */
	protected $login_checker;

	/**
	 * Project ID
	 *
	 * @var integer
	 */
	public $id = 0;

	/**
	 * Setup checking classes and set chain of responsibility order
	 *
	 * @param PH_Permissions_Data $data
	 */
	public function __construct(PH_Permissions_Data $data)
	{
		$this->signature_checker = new PH_Signature_Checker($data);
		$this->token_checker     = new PH_Token_Checker($data);
		$this->login_checker     = new PH_Login_Checker($data);
		$this->password_checker  = new PH_Password_Checker($data);

		// set chain of repsonsibility based on project setting
		$this->set_chain($data->guests);

		$this->id = $data->id;
	}

	/**
	 * Sets the dynamic chain of responsibiility
	 *
	 * @param string $access_setting
	 * @return void
	 */
	protected function set_chain($allow_guests)
	{

		if ($allow_guests) {
			$this->access = true;
			return;
		}

		$this->first_checker = $this->signature_checker;
		$this->password_checker->failWith($this->signature_checker);
		$this->signature_checker->failWith($this->token_checker);
		$this->token_checker->failWith($this->login_checker);
	}

	/**
	 * Check the current user permission for the project
	 *
	 * @return boolean|WP_Error
	 */
	public function check()
	{

		// check access override
		if ($this->access) {
			return apply_filters('ph_project_permissions_check', $this->access, $this->id);
		}

		// run checker if one is set
		if (is_a($this->first_checker, 'PH_Permission_Checker')) {
			return apply_filters('ph_project_permissions_check', $this->first_checker->check(), $this->id);
		}

	}
}
