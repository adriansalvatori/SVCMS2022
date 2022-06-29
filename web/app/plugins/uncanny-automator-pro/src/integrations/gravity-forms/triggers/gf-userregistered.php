<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GF_USERREGISTERED
 *
 * @package Uncanny_Automator_Pro
 */
class GF_USERREGISTERED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GF';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'GFUSERCREATED';
		$this->trigger_meta = 'USERCREATED';
		if ( defined( 'GF_USER_REGISTRATION_VERSION' ) ) {
			$this->define_trigger();
		}
	}

	/**
	 *
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/gravity-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - Gravity Forms */
			'sentence'            => esc_attr__( 'A user is registered', 'uncanny-automator-pro' ),
			/* translators: Logged-in trigger - Gravity Forms */
			'select_option_name'  => esc_attr__( 'A user is registered', 'uncanny-automator-pro' ),
			'action'              => 'gform_user_registered',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'save_data' ),
			'options'             => array(),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $user_id
	 * @param $feed
	 * @param $entry
	 * @param $password
	 */
	public function save_data( $user_id, $feed, $entry, $password ) {
		$pass_args = array(
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'post_id'        => - 1,
			'ignore_post_id' => true,
			'user_id'        => $user_id,
			'is_signed_in'   => true,
		);

		$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

		if ( ! empty( $args ) ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {
					$trigger_meta = array(
						'user_id'        => $user_id,
						'trigger_id'     => $result['args']['trigger_id'],
						'trigger_log_id' => $result['args']['get_trigger_id'],
						'run_number'     => $result['args']['run_number'],
					);

					$trigger_meta['meta_key']   = 'GFENTRYID';
					$trigger_meta['meta_value'] = $entry['id'];
					Automator()->insert_trigger_meta( $trigger_meta );

					$trigger_meta['meta_key']   = 'GFUSERIP';
					$trigger_meta['meta_value'] = maybe_serialize( $entry['ip'] );
					Automator()->insert_trigger_meta( $trigger_meta );

					$trigger_meta['meta_key']   = 'GFENTRYDATE';
					$trigger_meta['meta_value'] = maybe_serialize( \GFCommon::format_date( $entry['date_created'], false, 'Y/m/d' ) );
					Automator()->insert_trigger_meta( $trigger_meta );

					$trigger_meta['meta_key']   = 'GFENTRYSOURCEURL';
					$trigger_meta['meta_value'] = maybe_serialize( $entry['source_url'] );
					Automator()->insert_trigger_meta( $trigger_meta );

					$trigger_meta ['meta_key']  = 'user_id';
					$trigger_meta['meta_value'] = maybe_serialize( $user_id );
					Automator()->insert_trigger_meta( $trigger_meta );

					Automator()->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
