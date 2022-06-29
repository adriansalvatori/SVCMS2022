<?php

namespace AutomateWoo\Birthdays;

use AutomateWoo\Exceptions\InvalidArgument;
use AutomateWoo\Fields;
use AutomateWoo\Triggers\AbstractBatchedDailyTrigger;
use AutomateWoo\Workflow;
use AutomateWoo\Customer_Factory;
use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customer_Birthday_Trigger.
 *
 * @package AutomateWoo\Birthdays
 */
class Customer_Birthday_Trigger extends AbstractBatchedDailyTrigger {

	/**
	 * Sets the supplied data items for the trigger.
	 *
	 * @var array
	 */
	public $supplied_data_items = [ 'customer' ];

	/**
	 * Load trigger admin details.
	 */
	public function load_admin_details() {
		$this->title       = __( 'Customer Birthday', 'automatewoo-birthdays' );
		$this->description = __( 'This trigger runs once a day at the time specified by the <b>Time of day</b> field.', 'automatewoo-birthdays' );
		$this->group       = __( 'Customers', 'automatewoo-birthdays' );
	}

	/**
	 * Load trigger fields.
	 */
	public function load_fields() {
		$date = new Fields\Before_After_Day();
		$date->set_name( 'when_to_run' );
		$date->set_required();

		$this->add_field( $date );
		$this->add_field( $this->get_field_time_of_day() );
	}

	/**
	 * Get a batch of items to process for given workflow.
	 *
	 * @param Workflow $workflow
	 * @param int      $offset The batch query offset.
	 * @param int      $limit  The max items for the query.
	 *
	 * @return array[] Array of items in array format. Items will be stored in the database so they should be IDs not objects.
	 */
	public function get_batch_for_workflow( Workflow $workflow, int $offset, int $limit ): array {
		$tasks = [];

		$when_to_run = $workflow->get_trigger_option( 'when_to_run' );

		if ( false === $when_to_run ) {
			return [];
		}

		// reverse when to run field so it's logical
		$when_to_run *= -1;

		// calculate the target date in site time
		$target_date = aw_normalize_date( time() + $when_to_run * DAY_IN_SECONDS );
		$target_date->convert_to_site_time();

		$query_args = [
			'number' => $limit,
			'offset' => $offset,
		];

		$users = AW_Birthdays()->get_users_by_birthday( $target_date->format( 'd' ), $target_date->format( 'm' ), $query_args );

		foreach ( $users as $user_id ) {
			$tasks[] = [
				'user_id' => $user_id,
			];
		}

		return $tasks;
	}

	/**
	 * Process a single item for a workflow to process.
	 *
	 * @param Workflow $workflow
	 * @param array    $item
	 *
	 * @throws InvalidArgument If customer is not set.
	 * @throws RuntimeException If there is an error.
	 */
	public function process_item_for_workflow( Workflow $workflow, array $item ) {
		if ( ! isset( $item['user_id'] ) ) {
			throw InvalidArgument::missing_required( 'user_id' );
		}

		$customer = Customer_Factory::get_by_user_id( $item['user_id'] );
		if ( ! $customer ) {
			throw new RuntimeException( 'Customer was not found.' );
		}

		$workflow->maybe_run(
			[
				'customer' => $customer,
			]
		);
	}

	/**
	 * Validate workflow.
	 *
	 * @param Workflow $workflow
	 *
	 * @return bool
	 */
	public function validate_workflow( $workflow ) {
		$customer = $workflow->data_layer()->get_customer();

		if ( ! $customer ) {
			return false;
		}

		// fallback to avoid duplication, ensures that the workflow hasn't already run in last 24 hours
		if ( $workflow->has_run_for_data_item( 'customer', DAY_IN_SECONDS ) ) {
			return false;
		}

		return true;
	}

}
