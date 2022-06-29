<?php

namespace Uncanny_Automator_pro;

/**
 * Class NF_SUBFORM
 * @package Uncanny_Automator
 */
class ANON_NEWSLETTER_SUBSCRIBES_LIST {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'NEWSLETTER';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'SUBSCRIBESLIST';
		$this->trigger_meta = 'NEWSLETTERLIST';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/newsletter/' ),
			'integration'         => self::$integration,
			'is_pro'              => true,
			'code'                => $this->trigger_code,
			/* translators: Anonymous trigger - Newsletter */
			'sentence'            => sprintf( esc_attr__( 'A subscription form is submitted with  {{a specific list:%1$s}}', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Anonymous trigger - Newsletter */
			'select_option_name'  => esc_attr__( 'A subscription form is submitted with  {{a specific list}}', 'uncanny-automator' ),
			// THIS ONLY FIRES IN THE FRONTEND AND NOT IN WP-ADMIN
			'action'              => 'newsletter_user_post_subscribe',
			'type'                => 'anonymous',
			'priority'            => 20,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'subscribes_to_list' ),
			'options'             => [ $this->options() ],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	private function options() {

		$options = [];
		/* translators: Anon option - Newsletter */
		$options['-1'] = esc_attr__( 'Any List', 'uncanny-automator' );

		$lists = get_option( 'newsletter_subscription_lists', [] );


		for ( $i = 1; $i <= NEWSLETTER_LIST_MAX; $i ++ ) {
			// not a valid list item
			if ( empty( $lists[ 'list_' . $i ] ) ) {
				continue;
			}
			// Don't show private lists. They are admin only
			if ( '1' !== $lists[ 'list_' . $i . '_status' ] ) {
				continue;
			}

			$options[ 'list_' . $i ] = $lists[ 'list_' . $i ];
		}

		$option = [
			'option_code'     => $this->trigger_meta,
			'label'           => esc_attr__( 'Lists', 'uncanny-automator' ),
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => true,
			'options'         => $options,
		];

		return $option;
	}

	/**
	 * @param $user (object)
	 *
	 * @return object
	 */
	public function subscribes_to_list( $user ) {
		global $uncanny_automator;

		// This is a newsletter subscriber user ID and not a wp user ID
		$user_id = $user->id;

		/*
		 * When a subscription fires, two things happen
		 * The user in the `wp_newsletter` table gets updated.
		 * The plugins logs what happened in the `wp_newsletter_user_logs` table.
		 *  -- the log date sets source(subscribe in our case), the lists selected in the form, the user id, and
		 *     the date.
		 *
		 * We have some information to work with.
		 *
		 * The time in the logs and in the user object may be different but will be very close. The DB inserts both use
		 * time() and it is highly unlikely the elapsed time will be more than a second.
		 */

		// Get all the subscribe logs for the user
		global $wpdb;

		$logs_table = $wpdb->prefix . 'newsletter_user_logs';

		$logs = $wpdb->get_row(
			"SELECT MAX(id), data FROM $logs_table WHERE user_id = $user_id AND source = 'subscribe'"
		);


		$recipes              = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$list_id_from_trigger = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );

		if ( null !== $logs ) {
			if ( isset( $logs->data ) && null !== $logs->data ) {

				/*
				 * json_decode($logs->data) will create:
				 *
				 * object(stdClass)[1620]
				 * public 'list_1' => string '1' ... '1' means the list was selected
				 * public 'list_2' => string '0' ... '0' means the list was not selected
				 * public 'status' => string 'C' ... 'C' means the subscription in confirmed(irrelevant for this trigger)
				 */

				$data = json_decode( $logs->data );

				foreach ( $recipes as $recipe_id => $recipe ) {
					foreach ( $recipe['triggers'] as $trigger ) {
						$trigger_id = $trigger['ID'];//return early for all memberships
						$list_id    = $list_id_from_trigger[ $recipe_id ][ $trigger_id ];
						if ( '-1' === $list_id ) {
							$matched_recipe_ids[] = [
								'recipe_id'  => $recipe_id,
								'trigger_id' => $trigger_id,
								'lists'      => $data
							];

							break;
						} elseif ( isset( $data->$list_id ) && '1' === $data->$list_id ) {
							// Handle a specific list option
							$matched_recipe_ids[] = [
								'recipe_id'  => $recipe_id,
								'trigger_id' => $trigger_id,
								'lists'      => $data
							];
						}
					}
				}

				if ( ! empty( $matched_recipe_ids ) ) {
					foreach ( $matched_recipe_ids as $matched_recipe_id ) {
						$args = [
							'code'             => $this->trigger_code,
							'meta'             => $this->trigger_meta,
							'user_id'          => $user_id,
							'recipe_to_match'  => $matched_recipe_id['recipe_id'],
							'trigger_to_match' => $matched_recipe_id['trigger_id'],
							'ignore_post_id'   => true,
						];

						$lists = $matched_recipe_id['lists'];

						$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

						if ( $result ) {
							foreach ( $result as $r ) {
								if ( true === $r['result'] ) {
									$this->save_newsletter_meta( $user, $lists, $matched_recipe_id, $r );
									$uncanny_automator->maybe_trigger_complete( $r['args'] );
								}
							}
						}
					}
				}

			}
		}

		return $user;

	}

	/**
	 * @param $user
	 * @param $data
	 * @param $args
	 */
	private function save_newsletter_meta( $user, $lists, $matched_recipe_id, $r ) {

		global $uncanny_automator;

		$trigger_id     = (int) $matched_recipe_id['trigger_id'];
		$trigger_log_id = (int) $r['args']['get_trigger_id'];
		$run_number     = (int) $r['args']['run_number'];

		$args = [
			'user_id'        => 0,
			'trigger_id'     => $trigger_id,
			'meta_key'       => 'USEREMAIL',
			'meta_value'     => $user->email,
			'run_number'     => $run_number, //get run number
			'trigger_log_id' => $trigger_log_id,
		];

		$uncanny_automator->insert_trigger_meta( $args );

		$args = [
			'user_id'        => 0,
			'trigger_id'     => $trigger_id,
			'meta_key'       => 'LISTSDATA',
			'meta_value'     => maybe_serialize( $lists ),
			'run_number'     => $run_number, //get run number
			'trigger_log_id' => $trigger_log_id,
		];

		$uncanny_automator->insert_trigger_meta( $args );
	}
}
