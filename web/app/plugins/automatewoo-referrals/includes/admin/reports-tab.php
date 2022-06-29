<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Referrals_Reports_Tab
 */
class AW_Referrals_Reports_Tab extends AW_Admin_Reports_Tab_Abstract {

	function __construct() {
		$this->id   = 'referrals';
		$this->name = __( 'Referrals', 'automatewoo-referrals' );
	}


	/**
	 * @return object
	 */
	function get_report_class() {
		include_once AW()->admin_path( '/reports/abstract-graph.php' );
		include_once AW_Referrals()->path( '/includes/admin/referrals-report.php' );

		return new AW_Referrals_Report();
	}
}

return new AW_Referrals_Reports_Tab();
