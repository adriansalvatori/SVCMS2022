<?php

namespace AutomateWoo\Birthdays;

defined( 'ABSPATH' ) || exit;

/**
 * Manages plugin frontend.
 *
 * @package AutomateWoo\Birthdays
 */
class Frontend {

	/**
	 * Init and add frontend hooks.
	 */
	public static function init() {
		/**
		 * Class name (for IDE).
		 *
		 * @var $self Frontend
		 */
		$self = __CLASS__;

		add_action( 'woocommerce_edit_account_form', [ $self, 'add_birthday_field_to_edit_account_form' ] );
		add_action( 'woocommerce_save_account_details_errors', [ $self, 'save_account_details_form' ], 10, 2 );
		add_action( 'woocommerce_before_checkout_form', [ $self, 'init_checkout' ] );

		add_action( 'woocommerce_after_checkout_validation', [ $self, 'validate_checkout_birthday_field' ], 10, 3 );
		add_action( 'woocommerce_checkout_update_user_meta', [ $self, 'save_checkout_birthday_field' ], 10, 3 );
	}

	/**
	 * Enqueues frontend CSS and JS.
	 */
	public static function enqueue_css_and_js() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$dir    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min/';

		wp_enqueue_script( 'automatewoo-birthdays', AW_Birthdays()->url( "/assets/js/{$dir}automatewoo-birthdays{$suffix}.js" ), [ 'jquery' ], AW_Birthdays()->version, true );
		wp_enqueue_style( 'automatewoo-birthdays', AW_Birthdays()->url( '/assets/css/automatewoo-birthdays.css' ), [], AW_Birthdays()->version );

		wp_localize_script(
			'automatewoo-birthdays',
			'aw_birthdays_params',
			[
				'is_logged_in' => is_user_logged_in(),
			]
		);
	}

	/**
	 * Output a template.
	 *
	 * @param string $template
	 * @param array  $args
	 */
	public static function output_template( $template, $args = [] ) {
		$args['context'] = $args;
		wc_get_template( $template, $args, 'automatewoo/birthdays', AW_Birthdays()->path( '/templates/' ) );
	}

	/**
	 * Add checkout hooks for birthday field.
	 */
	public static function init_checkout() {
		$hook = false;

		switch ( AW_Birthdays()->options()->checkout_field_placement() ) {
			case 'after_order_notes':
				$hook = 'woocommerce_after_order_notes';
				break;
			case 'before_order_notes':
				$hook = 'woocommerce_before_order_notes';
				break;
			case 'after_billing_details':
				if ( ! is_user_logged_in() && WC()->checkout()->is_registration_enabled() ) {
					$hook = 'woocommerce_after_checkout_registration_form';
				} else {
					$hook = 'woocommerce_after_checkout_billing_form';
				}
				break;
		}

		$hook = apply_filters( 'automatewoo/birthdays/checkout_field_placement', $hook );
		add_action( $hook, [ __CLASS__, 'add_birthday_field_to_checkout_form' ] );
	}

	/**
	 * Add the birthday field in the my account > account details form.
	 */
	public static function add_birthday_field_to_edit_account_form() {
		if ( ! AW_Birthdays()->options()->show_field_in_account_details() ) {
			return;
		}

		self::enqueue_css_and_js();
		self::output_template( 'birthday-section-account.php', self::get_birthday_field_template_args() );
	}

	/**
	 * Add birthday field to checkout additional details section.
	 */
	public static function add_birthday_field_to_checkout_form() {
		if ( ! AW_Birthdays()->options()->show_field_on_checkout() ) {
			return;
		}

		if ( AW_Birthdays()->get_user_birthday( get_current_user_id() ) ) {
			return;
		}

		self::enqueue_css_and_js();
		self::output_template( 'birthday-section-checkout.php', self::get_birthday_field_template_args() );
	}

	/**
	 * Get the data needed in the birthday field templates.
	 *
	 * @return array
	 */
	public static function get_birthday_field_template_args() {
		$base_location = wc_get_base_location();
		$use_us_format = apply_filters( 'automatewoo/birthdays/use_us_format', 'US' === $base_location['country'] );

		$args = [
			'current_birthday'  => AW_Birthdays()->get_user_birthday( get_current_user_id() ),
			'field_description' => AW_Birthdays()->options()->birthday_field_description(),
			'require_year'      => AW_Birthdays()->options()->require_year_of_birth(),
			'use_us_format'     => $use_us_format,
		];

		return $args;
	}

	/**
	 * Save birthday fields when saving account details.
	 *
	 * @param \WP_Error $errors
	 * @param \WP_User  $user
	 */
	public static function save_account_details_form( &$errors, &$user ) {
		if ( $errors->get_error_messages() || wc_notice_count( 'error' ) !== 0 ) {
			// if there are already errors don't attempt to save the birthday
			return;
		}

		try {
			self::save_birthday_field( $user->ID );
		} catch ( \Exception $e ) {
			$errors->add( 1, $e->getMessage() );
		}
	}

	/**
	 * Validate the checkout birthday field.
	 *
	 * @param array     $data
	 * @param \WP_Error $errors
	 */
	public static function validate_checkout_birthday_field( $data, $errors ) {
		$day   = absint( aw_get_post_var( 'automatewoo_birthday_day' ) );
		$month = absint( aw_get_post_var( 'automatewoo_birthday_month' ) );
		$year  = absint( aw_get_post_var( 'automatewoo_birthday_year' ) );

		// check if field was left blank
		if ( AW_Birthdays()->is_date_empty( $day, $month, $year ) ) {
			return;
		}

		if ( ! AW_Birthdays()->validate_date( $day, $month, $year ) ) {
			$errors->add( 'birthday-invalid', AW_Birthdays()->get_form_response_message( 'invalid' ) );
		}
	}

	/**
	 * Save birthday field after checkout customer is processed.
	 *
	 * @param int $user_id
	 */
	public static function save_checkout_birthday_field( $user_id ) {
		try {
			self::save_birthday_field( $user_id );
		} catch ( \Exception $e ) {
			wc_add_notice( $e->getMessage() );
		}
	}

	/**
	 * Saves a birthday based on a form submission.
	 *
	 * @param int $user_id
	 *
	 * @throws \Exception When save fails.
	 * @return bool
	 */
	public static function save_birthday_field( $user_id ) {
		$year  = absint( aw_get_post_var( 'automatewoo_birthday_year' ) );
		$month = absint( aw_get_post_var( 'automatewoo_birthday_month' ) );
		$day   = absint( aw_get_post_var( 'automatewoo_birthday_day' ) );

		if ( AW_Birthdays()->get_user_birthday( $user_id ) ) {
			// user already has a birthday set
			return false;
		}

		// check if field was left blank
		if ( AW_Birthdays()->is_date_empty( $day, $month, $year ) ) {
			return false;
		}

		if ( ! AW_Birthdays()->validate_date( $day, $month, $year ) ) {
			throw new \Exception( AW_Birthdays()->get_form_response_message( 'invalid' ) );
		}

		AW_Birthdays()->set_user_birthday( $user_id, $day, $month, $year );
		return true;
	}

}
