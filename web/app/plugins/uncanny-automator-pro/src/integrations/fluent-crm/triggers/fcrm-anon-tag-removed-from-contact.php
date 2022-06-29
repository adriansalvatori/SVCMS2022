<?php

namespace Uncanny_Automator_Pro;

/**
 * Class FCRM_ANON_TAG_REMOVED_FROM_CONTACT
 * @package Uncanny_Automator_Pro
 */
class FCRM_ANON_TAG_REMOVED_FROM_CONTACT {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'FCRM';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ANONFCRMREMOVEUSERTAG';
		$this->trigger_meta = 'FCRMTAG';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/fluentcrm/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			'type'                => 'anonymous',
			/* translators: Logged-in trigger - Fluent CRM */
			'sentence'            => sprintf( esc_attr_x( '{{A tag:%1$s}}  is removed from a contact', 'Fluent CRM', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Fluent CRM */
			'select_option_name'  => esc_attr_x( '{{A tag}}  is removed from a contact', 'Fluent CRM', 'uncanny-automator' ),
			'action'              => 'fluentcrm_contact_removed_from_tags',
			'priority'            => 20,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'anon_contact_removed_from_tags' ),
			'options'             => [
				Automator()->helpers->recipe->fluent_crm->options->fluent_crm_tags( null, $this->trigger_meta ),
			],
		);

		Automator()->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $detached_tag_ids
	 * @param $subscriber
	 *
	 * @return void
	 */
	public function anon_contact_removed_from_tags( $detached_tag_ids, $subscriber ) {
		$user_id = $subscriber->user_id;
		$tag_ids = Automator()->helpers->recipe->fluent_crm->get_attached_tag_ids( $detached_tag_ids );

		if ( empty( $tag_ids ) ) {
			// sanity check
			return;
		}

		$matched_recipes = Automator()->helpers->recipe->fluent_crm->match_single_condition( $tag_ids, 'int', $this->trigger_meta, $this->trigger_code );
		if ( ! empty( $matched_recipes ) ) {
			foreach ( $matched_recipes as $matched_recipe ) {
				if ( ! Automator()->is_recipe_completed( $matched_recipe->recipe_id, $user_id ) ) {
					$args = [
						'code'            => $this->trigger_code,
						'meta'            => $this->trigger_meta,
						'recipe_to_match' => $matched_recipe->recipe_id,
						'ignore_post_id'  => true,
						'user_id'         => $user_id,
					];

					$result = Automator()->maybe_add_trigger_entry( $args, false );
					if ( $result ) {
						foreach ( $result as $r ) {
							if ( true === $r['result'] ) {
								if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
									$trigger_meta = [
										'user_id'        => $user_id,
										'trigger_id'     => (int) $r['args']['trigger_id'],
										'trigger_log_id' => $r['args']['get_trigger_id'],
										'run_number'     => $r['args']['run_number'],
									];

									$trigger_meta['meta_key']   = $this->trigger_meta;
									$trigger_meta['meta_value'] = maybe_serialize( $matched_recipe->matched_value );
									Automator()->insert_trigger_meta( $trigger_meta );

									$trigger_meta['meta_key']   = 'subscriber_id';
									$trigger_meta['meta_value'] = maybe_serialize( $subscriber->id );
									Automator()->insert_trigger_meta( $trigger_meta );
								}
								Automator()->maybe_trigger_complete( $r['args'] );
							}
						}
					}
				}
			}
		}
	}

}
