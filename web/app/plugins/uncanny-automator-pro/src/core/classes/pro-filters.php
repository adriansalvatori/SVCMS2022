<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Automator_DB;

/**
 * Class Pro_Filters
 *
 * @package Uncanny_Automator_Pro
 */
class Pro_Filters {

	/**
	 * Constructor.
	 */

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 999 );
	}

	/**
	 * Enqueue scripts only on custom post type edit pages
	 *
	 * @param $hook
	 */
	public function scripts( $hook ) {

		if (
			( strpos( $hook, 'uncanny-automator-recipe-log' ) !== false ) ||
			( strpos( $hook, 'uncanny-automator-trigger-log' ) !== false ) ||
			( strpos( $hook, 'uncanny-automator-action-log' ) !== false )
		) {
			// De-enqueue BadgeOS select2 assets
			wp_dequeue_script( 'badgeos-select2' );
			wp_dequeue_style( 'badgeos-select2-css' );

			// Select2
			wp_enqueue_script(
				'uap-logs-pro-select2',
				Utilities::get_vendor_asset( 'select2/js/select2.min.js' ),
				array( 'jquery' ),
				false,
				true
			);
			wp_enqueue_style( 'uap-logs-pro-select2', Utilities::get_vendor_asset( 'select2/css/select2.min.css' ), array(), AUTOMATOR_PRO_PLUGIN_VERSION );

			// DateRangePicker
			wp_enqueue_script(
				'uap-logs-pro-moment',
				Utilities::get_vendor_asset( 'daterangepicker/js/moment.min.js' ),
				array( 'jquery' ),
				AUTOMATOR_PRO_PLUGIN_VERSION
			);
			wp_enqueue_script(
				'uap-logs-pro-daterangepicker',
				Utilities::get_vendor_asset( 'daterangepicker/js/daterangepicker.js' ),
				array(
					'jquery',
					'uap-logs-pro-moment',
				),
				AUTOMATOR_PRO_PLUGIN_VERSION
			);
			wp_enqueue_style( 'uap-logs-pro-daterangepicker', Utilities::get_vendor_asset( 'daterangepicker/css/daterangepicker.css' ), array(), AUTOMATOR_PRO_PLUGIN_VERSION );

			// Load main JS
			wp_enqueue_script(
				'uap-logs-pro',
				Utilities::get_js( 'admin/logs.js' ),
				array(
					'jquery',
					'uap-logs-pro-select2',
					'uap-logs-pro-moment',
					'uap-logs-pro-daterangepicker',
				),
				AUTOMATOR_PRO_PLUGIN_VERSION
			);

			$i18n = new \Uncanny_Automator\Automator_Translations();

			// API data
			$api_setup = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'load-recipes-ref' ),
				'i18n'       => $i18n->get_all(),
			);
			wp_localize_script( 'uap-logs-pro', 'uapActivityLogApiSetup', $api_setup );

			wp_enqueue_style( 'uap-logs-pro', Utilities::get_css( 'admin/logs.css' ), array(), AUTOMATOR_PRO_PLUGIN_VERSION );
		}
	}

	/**
	 * Creates the filters HTML
	 *
	 * @param String $tab The identificator of the log. ( "recipe", "trigger" or "action" )
	 *
	 * @return String      The HTML
	 */

	public static function activities_filters_html( $tab ) {

		global $wpdb;
		$view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$view_exists = automator_db_view_exists();
		}
		// Get Recipes Name
		if ( $view_exists ) {
			$recipes = $wpdb->get_results(
				"SELECT DISTINCT(automator_recipe_id) AS id, recipe_title
												FROM {$wpdb->prefix}uap_recipe_logs_view
												ORDER BY recipe_title ASC",
				ARRAY_A
			);
		}

		// Get Triggers Name
		$trigger_view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$trigger_view_exists = automator_db_view_exists( 'trigger' );
		}
		if ( $trigger_view_exists ) {
			if ( $tab == 'trigger-log' ) {
				$triggers = $wpdb->get_results(
					"SELECT DISTINCT(automator_trigger_id) AS id, trigger_title
													FROM {$wpdb->prefix}uap_trigger_logs_view
													ORDER BY trigger_title ASC",
					ARRAY_A
				);
			}
		}
		// Get Actions Name
		$action_view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$action_view_exists = automator_db_view_exists( 'action' );
		}
		if ( $action_view_exists ) {
			if ( $tab == 'action-log' ) {
				$actions = $wpdb->get_results(
					"SELECT DISTINCT (automator_action_id) AS id, action_title
					FROM {$wpdb->prefix}uap_action_logs_view
					ORDER BY action_title ASC",
					ARRAY_A
				);

				$action_statuses = $wpdb->get_results(
					"SELECT DISTINCT action_completed AS action_completed
					FROM {$wpdb->prefix}uap_action_logs_view
					ORDER BY action_completed ASC",
					ARRAY_A
				);
			}
		}
		$recipe_view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$recipe_view_exists = automator_db_view_exists();
		}
		if ( $recipe_view_exists ) {
			$users = $wpdb->get_results(
				"SELECT DISTINCT (user_id) as id, display_name AS title
												FROM {$wpdb->prefix}uap_recipe_logs_view
												 ORDER BY title ASC",
				ARRAY_A
			);
		}

		if ( $view_exists && $recipe_view_exists && $trigger_view_exists && $action_view_exists ) {
			include Utilities::get_view( 'pro-filters-view.php' );
		}
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Prepare query for recipe
	 *
	 * @return string query
	 */
	public static function get_recipe_query() {
		global $wpdb;
		$view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$view_exists = automator_db_view_exists();
		}
		if ( $view_exists ) {
			$search_conditions = ' 1=1 AND recipe_completed != -1 ';
		} else {
			$search_conditions = ' 1=1 AND r.completed != -1 ';
		}
		if ( isset( $_GET['search_key'] ) && $_GET['search_key'] != '' ) {
			$search_key = sanitize_text_field( $_GET['search_key'] );
			if ( $view_exists ) {
				$search_conditions .= " AND ( (recipe_title LIKE '%$search_key%') OR (display_name  LIKE '%$search_key%' ) OR (user_email  LIKE '%$search_key%' ) ) ";
			} else {
				$search_conditions .= " AND ( (p.post_title LIKE '%$search_key%') OR (u.display_name  LIKE '%$search_key%' ) OR (u.user_email  LIKE '%$search_key%' ) ) ";
			}
		}
		if ( isset( $_GET['recipe_id'] ) && $_GET['recipe_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND automator_recipe_id = '" . absint( $_GET['recipe_id'] ) . "' ";
			} else {
				$search_conditions .= " AND r.automator_recipe_id = '" . absint( $_GET['recipe_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['user_id'] ) && $_GET['user_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND user_id = '" . absint( $_GET['user_id'] ) . "' ";
			} else {
				$search_conditions .= " AND r.user_id = '" . absint( $_GET['user_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['recipe_log_id'] ) && $_GET['recipe_log_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND recipe_log_id = '" . absint( $_GET['recipe_log_id'] ) . "' ";
			} else {
				$search_conditions .= " AND r.ID = '" . absint( $_GET['recipe_log_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['daterange'] ) && $_GET['daterange'] != '' ) {
			$date_range = explode( ' - ', $_GET['daterange'], 2 );
			if ( isset( $date_range[0] ) && isset( $date_range[1] ) ) {
				$date_range[0] = date( 'Y-m-d', strtotime( $date_range[0] ) );
				$date_range[1] = date( 'Y-m-d', strtotime( $date_range[1] ) );
				if ( $view_exists ) {
					$search_conditions .= " AND (recipe_date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				} else {
					$search_conditions .= " AND (r.date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				}
			}
		}

		if ( $view_exists ) {
			$query = "SELECT * FROM {$wpdb->prefix}uap_recipe_logs_view WHERE $search_conditions";
		} else {
			$query = Automator_DB::recipe_log_view_query() . " WHERE $search_conditions";
		}

		/**
		 * To support new logs..
		 * remove it after Automator 2.7 release
		 */
		if ( ! isset( $_GET['orderby'] ) ) {
			if ( $view_exists ) {
				$_GET['orderby'] = ' automator_recipe_id';
			} else {
				$_GET['orderby'] = ' r.automator_recipe_id';
			}
		}

		return $query;
	}

	/**
	 * Prepare query for trigger
	 *
	 * @return string query
	 */
	public static function get_trigger_query() {
		global $wpdb;
		$view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$view_exists = automator_db_view_exists( 'trigger' );
		}
		$search_conditions = ' 1=1 ';
		if ( isset( $_GET['search_key'] ) && $_GET['search_key'] != '' ) {
			$search_key = sanitize_text_field( $_GET['search_key'] );
			if ( $view_exists ) {
				$search_conditions .= " AND ( (recipe_title LIKE '%$search_key%') OR (trigger_title LIKE '%$search_key%') OR (display_name  LIKE '%$search_key%' ) OR (user_email LIKE '%$search_key%' ) ) ";
			} else {
				$search_conditions .= " AND ( (p.post_title LIKE '%$search_key%') OR (pt.post_title LIKE '%$search_key%') OR (u.display_name  LIKE '%$search_key%' ) OR (u.user_email LIKE '%$search_key%' ) ) ";
			}
		}
		if ( isset( $_GET['recipe_id'] ) && $_GET['recipe_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND automator_recipe_id = '" . absint( $_GET['recipe_id'] ) . "' ";
			} else {
				$search_conditions .= " AND  t.automator_recipe_id = '" . absint( $_GET['recipe_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['trigger_id'] ) && $_GET['trigger_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND automator_trigger_id = '" . absint( $_GET['trigger_id'] ) . "' ";
			} else {
				$search_conditions .= " AND t.automator_trigger_id = '" . absint( $_GET['trigger_id'] ) . "' ";
			}
		}

		if ( isset( $_GET['run_number'] ) && $_GET['run_number'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND recipe_run_number = '" . absint( $_GET['run_number'] ) . "' ";
			} else {
				$search_conditions .= " AND r.run_number = '" . absint( $_GET['run_number'] ) . "' ";
			}
		}
		if ( isset( $_GET['recipe_log_id'] ) && $_GET['recipe_log_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND recipe_log_id = '" . absint( $_GET['recipe_log_id'] ) . "' ";
			} else {
				$search_conditions .= " AND t.automator_recipe_log_id = '" . absint( $_GET['recipe_log_id'] ) . "' ";
			}
		}

		if ( isset( $_GET['user_id'] ) && $_GET['user_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND user_id = '" . absint( $_GET['user_id'] ) . "' ";
			} else {
				$search_conditions .= " AND u.ID = '" . absint( $_GET['user_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['daterange'] ) && $_GET['daterange'] != '' ) {
			$date_range = explode( ' - ', $_GET['daterange'], 2 );
			if ( isset( $date_range[0] ) && isset( $date_range[1] ) ) {
				$date_range[0] = date( 'Y-m-d', strtotime( $date_range[0] ) );
				$date_range[1] = date( 'Y-m-d', strtotime( $date_range[1] ) );
				if ( $view_exists ) {
					$search_conditions .= " AND (recipe_date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				} else {
					$search_conditions .= " AND (r.date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				}
			}
		}
		if ( isset( $_GET['trigger_daterange'] ) && $_GET['trigger_daterange'] != '' ) {
			$date_range = explode( ' - ', $_GET['trigger_daterange'], 2 );
			if ( isset( $date_range[0] ) && isset( $date_range[1] ) ) {
				$date_range[0] = date( 'Y-m-d', strtotime( $date_range[0] ) );
				$date_range[1] = date( 'Y-m-d', strtotime( $date_range[1] ) );
				if ( $view_exists ) {
					$search_conditions .= " AND (trigger_date BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				} else {
					$search_conditions .= " AND (t.date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				}
			}
		}
		if ( $view_exists ) {
			$query = "SELECT * FROM {$wpdb->prefix}uap_trigger_logs_view WHERE ($search_conditions) ";
		} else {
			$query = Automator_DB::trigger_log_view_query() . " WHERE ($search_conditions) ";
		}
		/**
		 * To support new logs..
		 * remove it after Automator 2.7 release
		 */
		if ( ! isset( $_GET['orderby'] ) ) {
			if ( $view_exists ) {
				$_GET['orderby'] = 'automator_trigger_id';
			} else {
				$_GET['orderby'] = 't.automator_trigger_id';
			}
		}

		return $query;
	}

	/**
	 * Prepare query for action
	 *
	 * @return string query
	 */
	public static function get_action_query() {
		global $wpdb;
		$view_exists = true;
		if ( function_exists( 'automator_db_view_exists' ) ) {
			$view_exists = automator_db_view_exists( 'action' );
		}
		$search_conditions = ' 1=1 ';
		if ( isset( $_GET['search_key'] ) && $_GET['search_key'] != '' ) {
			$search_key = sanitize_text_field( $_GET['search_key'] );
			if ( $view_exists ) {
				$search_conditions .= " AND ( (recipe_title LIKE '%$search_key%') OR (action_title LIKE '%$search_key%') OR (display_name LIKE '%$search_key%' ) OR (user_email LIKE '%$search_key%' ) OR (error_message LIKE '%$search_key%' ) ) ";
			} else {
				$search_conditions .= " AND ( (p.post_title LIKE '%$search_key%') OR (pa.post_title LIKE '%$search_key%') OR (u.display_name LIKE '%$search_key%' ) OR (u.user_email LIKE '%$search_key%' ) OR (error_message LIKE '%$search_key%' ) ) ";
			}
		}
		if ( isset( $_GET['recipe_id'] ) && $_GET['recipe_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND automator_recipe_id = '" . absint( $_GET['recipe_id'] ) . "' ";
			} else {
				$search_conditions .= " AND a.automator_recipe_id = '" . absint( $_GET['recipe_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['action_id'] ) && $_GET['action_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND automator_action_id = '" . absint( $_GET['action_id'] ) . "' ";
			} else {
				$search_conditions .= " AND a.automator_action_id = '" . absint( $_GET['action_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['user_id'] ) && $_GET['user_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND user_id = '" . absint( $_GET['user_id'] ) . "' ";
			} else {
				$search_conditions .= " AND u.ID = '" . absint( $_GET['user_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['run_number'] ) && $_GET['run_number'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND recipe_run_number = '" . absint( $_GET['run_number'] ) . "' ";
			} else {
				$search_conditions .= " AND r.run_number = '" . absint( $_GET['run_number'] ) . "' ";
			}
		}

		if ( ! empty( automator_filter_input( 'action_completed' ) ) ) {

			$action_completed = automator_filter_input( 'action_completed' );
			// Do make exception for not_completed status because '0' evaluates to false.
			if ( 'not_completed' === $action_completed ) {
				$action_completed = '0';
			}
			if ( $view_exists ) {
				$search_conditions .= " AND action_completed ='" . absint( $action_completed ) . "' ";
			} else {
				$search_conditions .= " AND a.action_completed = '" . absint( $action_completed ) . "' ";
			}
		}

		if ( isset( $_GET['recipe_log_id'] ) && $_GET['recipe_log_id'] != '' ) {
			if ( $view_exists ) {
				$search_conditions .= " AND recipe_log_id = '" . absint( $_GET['recipe_log_id'] ) . "' ";
			} else {
				$search_conditions .= " AND a.automator_recipe_log_id = '" . absint( $_GET['recipe_log_id'] ) . "' ";
			}
		}
		if ( isset( $_GET['daterange'] ) && $_GET['daterange'] != '' ) {
			$date_range = explode( ' - ', $_GET['daterange'], 2 );
			if ( isset( $date_range[0] ) && isset( $date_range[1] ) ) {
				$date_range[0] = date( 'Y-m-d', strtotime( $date_range[0] ) );
				$date_range[1] = date( 'Y-m-d', strtotime( $date_range[1] ) );
				if ( $view_exists ) {
					$search_conditions .= " AND (recipe_date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				} else {
					$search_conditions .= " AND (r.date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				}
			}
		}
		if ( isset( $_GET['action_daterange'] ) && $_GET['action_daterange'] != '' ) {
			$date_range = explode( ' - ', $_GET['action_daterange'], 2 );
			if ( isset( $date_range[0] ) && isset( $date_range[1] ) ) {
				$date_range[0] = date( 'Y-m-d', strtotime( $date_range[0] ) );
				$date_range[1] = date( 'Y-m-d', strtotime( $date_range[1] ) );
				if ( $view_exists ) {
					$search_conditions .= " AND (action_date BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				} else {
					$search_conditions .= " AND (a.date_time BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' )";
				}
			}
		}

		if ( $view_exists ) {
			$query = "SELECT * FROM {$wpdb->prefix}uap_action_logs_view WHERE ($search_conditions)";
		} else {
			$sql = Automator_DB::action_log_view_query( false );

			$query = "$sql WHERE ($search_conditions) GROUP BY a.ID";
		}
		/**
		 * To support new logs..
		 * remove it after Automator 2.7 release
		 */
		if ( ! isset( $_GET['orderby'] ) ) {
			if ( $view_exists ) {
				$_GET['orderby'] = 'automator_action_id';
			} else {
				$_GET['orderby'] = 'a.automator_action_id';
			}
		}

		return $query;
	}
}
