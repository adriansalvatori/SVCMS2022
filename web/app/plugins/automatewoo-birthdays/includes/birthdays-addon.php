<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AutomateWoo\Addon' ) ) {
	include WP_PLUGIN_DIR . '/automatewoo/includes/abstracts/addon.php';
}

/**
 * Main plugin class for add-on.
 *
 * @since 1.0.0
 */
final class AW_Birthdays_Addon extends AutomateWoo\Addon {


	/**
	 * Addon main class constructor.
	 *
	 * @param stdClass $plugin_data
	 */
	public function __construct( $plugin_data ) {
		parent::__construct( $plugin_data );

		spl_autoload_register( [ $this, 'autoload' ] );
		add_action( 'init', [ $this, 'set_plugin_name' ] );
	}

	/**
	 * Is only called if license is active.
	 */
	public function init() {
		if ( is_admin() ) {
			AutomateWoo\Birthdays\Admin::init();
		}

		AutomateWoo\Birthdays\Frontend::init();

		add_filter( 'automatewoo/triggers', [ $this, 'register_trigger' ] );
		add_filter( 'automatewoo/rules/includes', [ $this, 'register_rules' ] );
		add_filter( 'automatewoo/variables', [ $this, 'register_variables' ] );
		add_action( 'automatewoo/privacy/loaded', [ $this, 'load_privacy_class' ] );

		do_action( 'automatewoo/birthdays/after_init' );
	}

	/**
	 * Translatable plugin name must be defined after load_plugin_textdomain() is called.
	 *
	 * @since 1.3.3
	 */
	public function set_plugin_name() {
		$this->name = __( 'AutomateWoo - Birthdays', 'automatewoo-birthdays' );
	}

	/**
	 * Class autoload callback.
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {
		$path = $this->get_autoload_path( $class );

		if ( $path && file_exists( $path ) ) {
			include $path;
		}
	}

	/**
	 * Get auto load path for a class.
	 *
	 * @param string $class
	 *
	 * @return string|false
	 */
	private function get_autoload_path( $class ) {
		if ( 0 !== strpos( $class, 'AutomateWoo\Birthdays\\' ) ) {
			return false;
		}

		$file = str_replace( 'AutomateWoo\\Birthdays\\', '', $class );
		$file = str_replace( '_', '-', $file );
		$file = strtolower( $file );
		$file = str_replace( '\\', '/', $file );

		return $this->path( "/includes/$file.php" );
	}

	/**
	 * Birthday's options class.
	 *
	 * @return AutomateWoo\Birthdays\Options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			include_once $this->path( '/includes/options.php' );
			$this->options = new AutomateWoo\Birthdays\Options();
		}
		return $this->options;
	}

	/**
	 * Register birthday trigger.
	 *
	 * @param array $triggers
	 *
	 * @return array
	 */
	public function register_trigger( $triggers ) {
		$triggers['customer_birthday_trigger'] = 'AutomateWoo\Birthdays\Customer_Birthday_Trigger';

		return $triggers;
	}

	/**
	 * Register birthday rules.
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	public function register_rules( $rules ) {
		$rules['customer_next_birthday'] = AW_Birthdays()->path( '/includes/customer-next-birthday-rule.php' );
		$rules['customer_last_birthday'] = AW_Birthdays()->path( '/includes/customer-last-birthday-rule.php' );

		return $rules;
	}

	/**
	 * Register birthday variables.
	 *
	 * @param array $variables
	 *
	 * @return array
	 */
	public function register_variables( $variables ) {
		$variables['customer']['birthday'] = AW_Birthdays()->path( '/includes/customer-birthday-variable.php' );

		return $variables;
	}

	/**
	 * Get the birthday storage format when storing full date.
	 *
	 * @return string
	 */
	public function get_birthday_storage_format_full() {
		return apply_filters( 'automatewoo/birthdays/storage_format/full', 'Y-m-d' );
	}

	/**
	 * Get the birthday storage format when storing only the month and day.
	 *
	 * @return string
	 */
	public function get_birthday_storage_format_month_day() {
		return apply_filters( 'automatewoo/birthdays/storage_format/month_day', 'm-d' );
	}

	/**
	 * Set a user's birthday.
	 * The year will NOT be saved if year of birth is not required.
	 *
	 * @param int      $user_id
	 * @param int      $day
	 * @param int      $month
	 * @param bool|int $year
	 */
	public function set_user_birthday( $user_id, $day, $month, $year = false ) {
		// use a datetime to format the date
		$date = aw_normalize_date( "$year-$month-$day" );

		if ( $this->options()->require_year_of_birth() ) {
			// only save year if required
			update_user_meta( $user_id, '_automatewoo_birthday_full', $date->format( $this->get_birthday_storage_format_full() ) );
		}

		update_user_meta( $user_id, '_automatewoo_birthday_md', $date->format( $this->get_birthday_storage_format_month_day() ) );
	}

	/**
	 * Get a user's birthday.
	 * Returns an associative array or false.
	 * Day and month values will contain leading zeros.
	 *
	 * @param int    $user_id
	 * @param string $type Use to return the stored birthday or the customer's next/previous birth date.
	 *                     Accepts 'stored', 'next', 'last'.
	 *                     Default 'stored'.
	 *
	 * @return array|false
	 */
	public function get_user_birthday( $user_id, $type = 'stored' ) {
		if ( $this->options()->require_year_of_birth() ) {
			$full = AutomateWoo\Clean::string( get_user_meta( $user_id, '_automatewoo_birthday_full', true ) );
			$date = aw_normalize_date( $full );
		} else {
			$md = AutomateWoo\Clean::string( get_user_meta( $user_id, '_automatewoo_birthday_md', true ) );

			if ( ! $md ) {
				return false;
			}

			// validates the date
			$date = aw_normalize_date( '2001-' . $md );
		}

		if ( ! $date ) {
			return false;
		}

		$year  = false;
		$month = $date->format( 'm' );
		$day   = $date->format( 'd' );

		switch ( $type ) {
			case 'stored':
				if ( $this->options()->require_year_of_birth() ) {
					$year = $date->format( 'Y' );
				}
				break;
			case 'next':
				$year = $this->calculate_next_birthday_year( $day, $month );
				break;
			case 'last':
				$year = $this->calculate_next_birthday_year( $day, $month ) - 1;
				break;
		}

		// if no year is set, use their next birthday year
		if ( empty( $year ) ) {
			$year = $this->calculate_next_birthday_year( $day, $month );
		}

		$array          = [];
		$array['year']  = (string) $year;
		$array['month'] = $month;
		$array['day']   = $day;

		return $array;
	}

	/**
	 * Check if a birthday already happened this year?
	 *
	 * @param int|string $day
	 * @param int|string $month
	 *
	 * @return bool
	 */
	public function is_birthday_past_for_this_year( $day, $month ) {
		// birthdays should be calculated in local time
		$now  = aw_normalize_date( 'now' )->convert_to_site_time();
		$year = $now->format( 'Y' );
		// make birthday last the whole day
		$birthday = aw_normalize_date( "$year-$month-$day 23:59:59" );
		return $now > $birthday;
	}

	/**
	 * Calculate the next birthday year based on the month and day.
	 *
	 * @param int|string $day
	 * @param int|string $month
	 *
	 * @return int
	 */
	public function calculate_next_birthday_year( $day, $month ) {
		$year    = (int) aw_normalize_date( 'now' )->convert_to_site_time()->format( 'Y' );
		$is_past = $this->is_birthday_past_for_this_year( $day, $month );

		// if birthday is past this year, their next birthday is next year
		if ( $is_past ) {
			$year++;
		}

		return $year;
	}

	/**
	 * Get datetime object from birthday array.
	 *
	 * @param array $birthday
	 *
	 * @return \AutomateWoo\DateTime|false
	 */
	public function get_date_from_birthday_array( $birthday ) {
		return $birthday ? aw_normalize_date( implode( '-', $birthday ) ) : false;
	}

	/**
	 * Remove a user's birthday.
	 *
	 * @param int $user_id
	 */
	public function clear_user_birthday( $user_id ) {
		delete_user_meta( $user_id, '_automatewoo_birthday_full' );
		delete_user_meta( $user_id, '_automatewoo_birthday_md' );
	}

	/**
	 * Get an array of user IDs by their birthday.
	 *
	 * Paginate with $query_args['number'] and $query_args['offset'].
	 *
	 * @param int|string $day
	 * @param int|string $month
	 * @param array      $query_args Query vars, as passed to `WP_User_Query`.
	 *
	 * @return array
	 */
	public function get_users_by_birthday( $day, $month, $query_args = [] ) {
		$date = aw_normalize_date( "2001-$month-$day" );

		if ( ! $date ) {
			return [];
		}

		$query_args = wp_parse_args(
			$query_args,
			[
				'fields'      => 'ID',
				'meta_query'  => [],
				'count_total' => false,
				'number'      => - 1,
			]
		);

		$query_args['meta_query'][] = [
			'key'   => '_automatewoo_birthday_md',
			'value' => $date->format( $this->get_birthday_storage_format_month_day() ),
		];

		$query = new WP_User_Query( $query_args );

		return $query->get_results();
	}

	/**
	 * Check if the date is empty.
	 * Birthday field is optional so the user can may have left it blank.
	 *
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 *
	 * @return bool
	 */
	public function is_date_empty( $day, $month, $year ) {
		if ( $this->options()->require_year_of_birth() ) {
			if ( ! $year && ! $month && ! $day ) {
				return true;
			}
		} else {
			if ( ! $month && ! $day ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a date is valid.
	 *
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 *
	 * @return bool
	 */
	public function validate_date( $day, $month, $year ) {
		// if the year isn't required, set a dummy date to validate the date (not a leap year)
		if ( ! $this->options()->require_year_of_birth() ) {
			$year = 2001;
		}

		if ( ! $month || ! $day || ! $year ) {
			return false;
		}

		return checkdate( $month, $day, $year );
	}

	/**
	 * Get birthday form response message.
	 *
	 * Used in admin and front end forms.
	 *
	 * @param string $message_id
	 *
	 * @return string
	 */
	public function get_form_response_message( $message_id ) {
		$message = false;

		switch ( $message_id ) {
			case 'invalid':
				$message = __( 'The birthday you entered was not a valid date.', 'automatewoo-birthdays' );
		}

		return $message;
	}

	/**
	 * Privacy loader.
	 */
	public function load_privacy_class() {
		new AutomateWoo\Birthdays\Privacy();
	}

	/**
	 * Set link to getting started doc.
	 *
	 * @return string
	 */
	public function get_getting_started_url() {
		return AutomateWoo\Admin::get_docs_link( 'getting-started-with-birthdays', 'activation-notice' );
	}


	/**
	 * Instance of the class.
	 *
	 * @var AW_Birthdays_Addon
	 */
	protected static $_instance;

}

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

/**
 * Plugin singleton.
 *
 * @return AW_Birthdays_Addon
 */
function AW_Birthdays() {
	return AW_Birthdays_Addon::instance( AW_Birthdays_Loader::$data );
}
AW_Birthdays();
