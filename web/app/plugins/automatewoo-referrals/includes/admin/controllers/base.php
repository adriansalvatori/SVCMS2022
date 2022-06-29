<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals\Admin\Controllers;

use AutomateWoo\Clean;
use AutomateWoo\Exception;

/**
 * @class Base
 */
abstract class Base extends \AutomateWoo\Admin\Controllers\Base {


	/**
	 * @param $view
	 * @param array $args
	 * @param bool|string $path
	 */
	function output_view( $view, $args = [], $path = false ) {
		parent::output_view( $view, $args, AW_Referrals()->path( '/includes/admin/views' ) );
	}

	/**
	 * Get cleaned IDs from a given request parameter.
	 *
	 * @param string $request_parameter
	 *
	 * @return array
	 * @throws Exception When no valid IDs are found.
	 */
	protected function get_clean_ids( $request_parameter ) {
		$ids = Clean::ids( aw_request( $request_parameter ) );
		if ( empty( $ids ) ) {
			throw new Exception( __( 'Please select some items to bulk edit.', 'automatewoo-referrals' ) );
		}

		return $ids;
	}
}
