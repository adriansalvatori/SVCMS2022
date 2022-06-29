<?php

namespace Objectiv\Plugins\Checkout\Managers;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Managers
 */
class PlanManager {
	const BASIC  = 1;
	const PLUS   = 5;
	const PRO    = 10;
	const AGENCY = 50;
	const PLANS  = array(
		self::BASIC  => 'Basic',
		self::PLUS   => 'Plus',
		self::PRO    => 'Pro',
		self::AGENCY => 'Agency',
	);

	/**
	 * @param int $minimum_required_plan
	 * @return bool
	 */
	static public function has_required_plan( int $minimum_required_plan ): bool {
		$updates = UpdatesManager::instance();

		return $updates->get_license_activation_limit() >= $minimum_required_plan;
	}

	/**
	 * @param int $minimum_required_plan
	 * @return array
	 */
	static public function get_required_plans( int $minimum_required_plan ): array {
		$offset = array_search( $minimum_required_plan, array_keys( self::PLANS ), true );

		return array_slice( self::PLANS, $offset, null, true );
	}

	/**
	 * Returns an English formatted list of plans
	 *
	 * Examples:
	 * - X or Y
	 * - X, Y, or Z
	 *
	 * @param array $array_of_strings
	 * @return string
	 */
	static public function get_formatted_english_list( array $array_of_strings ): string {
		if ( count( $array_of_strings ) <= 2 ) {
			return join( ' or ', $array_of_strings );
		}

		return implode( ', ', array_slice( $array_of_strings, 0, -1 ) ) . ', or ' . end( $array_of_strings );
	}

	/**
	 * @param int $minimum_required_plan
	 * @return string
	 */
	static public function get_english_list_of_required_plans_html( int $minimum_required_plan ): string {
		$plans = self::get_required_plans( $minimum_required_plan );

		$plans = array_map(
			function( $plan ) {
				return "<strong>{$plan}</strong>";
			},
			$plans
		);

		return self::get_formatted_english_list( $plans );
	}

	/**
	 * @param string $setting_key
	 * @param int $minimum_required_plan
	 * @return bool
	 */
	static public function can_access_feature( string $setting_key, int $minimum_required_plan ): bool {
		$has_correct_plan = UpdatesManager::instance()->get_license_activation_limit() >= $minimum_required_plan;

		$value = SettingsManager::instance()->get_setting( $setting_key );

		return $has_correct_plan && ( 'yes' === $value || 'enabled' === $value );
	}
}
