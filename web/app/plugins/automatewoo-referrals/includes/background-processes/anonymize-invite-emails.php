<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Background_Processes;

/**
 * Background processor to anonymize all invite emails
 */
class Background_Process_Anonymize_Invite_Emails extends Background_Processes\Base {

	/** @var string  */
	public $action = 'referrals_anonymize_invite_emails';


	/**
	 * @param array $data
	 * @return bool
	 */
	protected function task( $data ) {
		$invite = isset( $data['invite'] ) ? Invite_Factory::get( $data['invite'] ) : false;

		if ( ! $invite ) {
			return false;
		}

		$email = $invite->get_email();

		if ( ! aw_is_email_anonymized( $email ) ) {
			$invite->set_email( $email );
			$invite->save();
		}

		return false;
	}

}

return new Background_Process_Anonymize_Invite_Emails();
