<?php

namespace Uncanny_Automator_Pro;

/**
 * Class UOA_ANON_WEBHOOKS
 */
class UOA_ANON_WEBHOOKS {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WEBHOOKS';

	/**
	 * Trigger code
	 *
	 * @var string
	 */
	private $trigger_code;
	/**
	 * Trigger meta
	 *
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		if ( ! class_exists( '\Uncanny_Automator_Pro\Webhook_Rest_Handler' ) ) {
			return;
		}
		// Migration the triggers if necessary.
		$this->maybe_migrate_trigger();
		$this->trigger_code = 'WP_ANON_WEBHOOKS';
		$this->trigger_meta = 'WEBHOOKID'; //'WEBHOOK';
		Webhook_Rest_Handler::set_trigger_codes( $this->trigger_code );
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'knowledge-base/webhook-triggers/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Anonymous trigger - Uncanny Automator */
			'sentence'            => sprintf( __( 'Receive data from {{a webhook:%1$s}}', 'uncanny-automator-pro' ), 'WEBHOOK_DATA' ),
			/* translators: Anonymous trigger - WordPress Core */
			'select_option_name'  => __( 'Receive data from {{a webhook}}', 'uncanny-automator-pro' ),
			'action'              => 'automator_pro_run_webhook',
			'priority'            => 10,
			'accepted_args'       => 2,
			'type'                => 'anonymous',
			'validation_function' => array( $this, 'run_webhook' ),
			'options_group'       => Webhook_Common_Options::get_webhook_options_group(),
			'buttons'             => Webhook_Common_Options::get_webhook_get_sample_button(),
			'inline_css'          => Webhook_Static_Content::inline_css(),
			'filter_tokens'       => Webhook_Static_Content::filter_tokens_js(),
			'can_log_in_new_user' => false,
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $param
	 * @param $recipe
	 */
	public function run_webhook( $param, $recipe ) {
		Webhook_Common_Options::run_webhook( $this->trigger_code, $this->trigger_meta, $param, $recipe );
	}

	/**
	 * Migrate all existing Automator -> webhook triggers.
	 *
	 * @return void
	 */
	public function maybe_migrate_trigger() {

		$option_key = 'automator_wpwebhooks_trigger_moved';

		if ( 'yes' === get_option( $option_key ) ) {
			return;
		}

		global $wpdb;

		$current_actions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %s AND meta_key = %s",
				'WP_ANON_WEBHOOKS',
				'code'
			)
		);

		if ( empty( $current_actions ) ) {
			update_option( $option_key, 'yes', false );

			return;
		}

		foreach ( $current_actions as $action ) {
			$action_id = $action->post_id;
			update_post_meta( $action_id, 'integration', 'WEBHOOKS' );
			update_post_meta( $action_id, 'integration_name', 'Webhooks' );
		}

		update_option( $option_key, 'yes', false );

	}
}
