<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referral_Query
 * @since 1.6
 */
class Referral_Query extends AutomateWoo\Query_Abstract {

	/** @var string  */
	public $table_id = 'referrals';

	/** @var string  */
	protected $model = 'AutomateWoo\Referrals\Referral';


	/**
	 * status include: approved, potential-fraud, pending, rejected
	 * @since 1.9.1
	 * @param string|array $status
	 * @param $compare bool|string - defaults to '=' or 'IN' if array
	 * @return $this
	 */
	public function where_status( $status, $compare = false ) {
		return $this->where( 'status', $status, $compare );
	}


	/**
	 * @since 1.9.1
	 * @param int|string|array $order_id
	 * @param $compare bool|string - defaults to '=' or 'IN' if array
	 * @return $this
	 */
	public function where_order( $order_id, $compare = false ) {
		return $this->where( 'order_id', $order_id, $compare );
	}

	/**
	 * Find referrals by advocate.
	 *
	 * @since 2.5.0
	 *
	 * @param Advocate|int $advocate Accepts advocate ID or object.
	 * @param string       $compare  Defaults to '=' or 'IN' if array.
	 *
	 * @return $this
	 */
	public function where_advocate( $advocate, $compare = null ) {
		$advocate_id = $advocate instanceof Advocate ? $advocate->get_id() : (int) $advocate;

		return $this->where( 'advocate_id', $advocate_id, $compare );
	}

	/**
	 * Query for Referrals within the current year.
	 *
	 * @return Referral_Query
	 */
	public function where_this_year() {
		$this_jan = $this->get_datetime_site();
		$this_jan->setDate( $this_jan->format( 'Y' ), 1, 1 )->setTime( 0, 0, 0, 0 );
		$this_jan->convert_to_utc_time();

		$next_jan = clone $this_jan;
		$next_jan->modify( '+1 year' );

		return $this->where_date_after( $this_jan )->where_date_before( $next_jan );
	}

	/**
	 * Query for Referrals within the current month.
	 *
	 * @return Referral_Query
	 */
	public function where_this_month() {
		$this_month = $this->get_datetime_site();
		$this_month->setDate( $this_month->format( 'Y' ), $this_month->format( 'n' ), 1 )->setTime( 0, 0, 0, 0 );
		$this_month->convert_to_utc_time();

		$next_month = clone $this_month;
		$next_month->modify( '+1 month' );

		return $this->where_date_after( $this_month )->where_date_before( $next_month );
	}

	/**
	 * Query for Referrals within the current week.
	 *
	 * @return Referral_Query
	 */
	public function where_this_week() {
		$first_day = absint( get_option( 'start_of_week' ) );
		$this_week = $this->get_datetime_site();
		$today     = (int) date( 'w', $this_week->getTimestamp() );

		/*
		 * Week calculation (as opposed to weak calculation).
		 *
		 * - beginning of week is today: no modification!
		 * - beginning of week is past: ($today - $first_day) days ago
		 * - beginning of week is future: (7 - ($first_day - $today)) days ago
		 */
		if ( $today > $first_day ) {
			$days = absint( $today - $first_day );
			$this_week->modify( "{$days} days ago" );
		} elseif ( $first_day > $today ) {
			$days = 7 - ( $first_day - $today );
			$this_week->modify( "{$days} days ago" );
		}

		$this_week->setTime( 0, 0, 0, 0 )->convert_to_utc_time();

		$next_week = clone $this_week;
		$next_week->modify( '+1 week' );

		return $this->where_date_after( $this_week )->where_date_before( $next_week );
	}

	/**
	 * Query for referrals since a certain date.
	 *
	 * @param DateTime $since
	 *
	 * @return Referral_Query
	 */
	private function where_date_after( $since ) {
		return $this->where( 'date', $since, '>=' );
	}

	/**
	 * Query for referrals before a certain date.
	 *
	 * @param DateTime $before
	 *
	 * @return Referral_Query
	 */
	private function where_date_before( $before ) {
		return $this->where( 'date', $before, '<' );
	}

	/**
	 * @return Referral[]
	 */
	public function get_results() {
		return parent::get_results();
	}

	/**
	 * Get a DateTime object in the site's timezone.
	 *
	 * @return DateTime
	 */
	private function get_datetime_site() {
		$datetime = new DateTime();
		$datetime->convert_to_site_time();
		return $datetime;
	}
}
