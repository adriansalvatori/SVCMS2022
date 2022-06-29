<?php if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('ph_is_func_disabled')) {
	function ph_is_func_disabled($function)
	{
		$disabled = explode(',', ini_get('disable_functions'));

		return in_array($function, $disabled);
	}
}

// inlcude upgrade items
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-metadata-2.6.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-fix-missing-pages-2.6.0.5.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-fix-homepage-link-2.6.0.7.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-meta-keys-3.0.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-data-3.0.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-access-key-3.0.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-comment-parents.3.1.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-thread-parents.3.1.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-thread-members.3.3.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-deleted-items-3.4.5.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-thread-members-3.5.0.php';
require_once PH_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-project-access-4.0.php';

/**
 * Class NF_Upgrade_Handler
 */
class PH_UpgradeHandler
{
	static $instance;

	public $upgrades;

	private $page;

	public $slug;

	public $name = 'Plugin';

	/**
	 * List of upgrade classes
	 *
	 * @var array
	 */
	static $classes = array();

	public static function instance($args)
	{
		if (!isset(self::$instance)) {
			self::$instance = new PH_UpgradeHandler($args);
		}

		return self::$instance;
	}

	public function __construct($args)
	{
		if (!isset($args['slug'])) {
			return new WP_Error('no_slug', __('You must provide a slug for your upgrades page'));
		}

		$this->slug = $args['slug'];
		$this->name = isset($args['name']) ? $args['name'] : 'Plugin';

		if (function_exists('ignore_user_abort') && !ph_is_func_disabled('ignore_user_abort')) {
			ignore_user_abort(true);
		}

		$this->register_upgrades();

		if (defined('DOING_AJAX') && DOING_AJAX) {
			add_action('wp_ajax_ph_upgrade_handler', array($this, 'ajax_response'));
			return;
		} else {
			$this->page = new PH_UpgradeHandlerPage(
				array(
					'slug' => $this->slug,
					'name' => $this->name,
				)
			);
		}
	}

	public function register_upgrades()
	{
		$this->upgrades = apply_filters('ph_upgrade_handler_register', $this->upgrades);

		if (!$this->upgrades) {
			return;
		}

		usort($this->upgrades, array($this, 'compare_upgrade_priority'));
	}

	/**
	 * Sorts by Priority
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	private function compare_upgrade_priority($a, $b)
	{
		return version_compare($a->priority, $b->priority);
	}

	public function ajax_response()
	{
		$current_step = (isset($_REQUEST['step'])) ? $_REQUEST['step'] : 0;

		$current_upgrade = $this->getUpgradeByName($_REQUEST['upgrade']);

		$current_upgrade->total_steps = isset($_REQUEST['total_steps']) ? $_REQUEST['total_steps'] : 1;

		if (isset($_REQUEST['args'])) {
			$current_upgrade->args = $_REQUEST['args'];
		}

		if (0 == $current_step) {
			$current_upgrade->loading();
		}

		$response = array(
			'upgrade'     => $current_upgrade->name,
			'total_steps' => (int) $current_upgrade->total_steps,
			'args'        => $current_upgrade->args,
		);

		if (0 != $current_step) {

			if (is_array($current_upgrade->errors) and $current_upgrade->errors) {
				$response['errors'] = $current_upgrade->errors;
			}

			if ($current_upgrade->total_steps < $current_step) {

				$current_upgrade->complete();
				$response['complete'] = true;
				$next_upgrade         = $this->getNextUpgrade($current_upgrade);

				if ($next_upgrade) {
					if (!$next_upgrade->isComplete()) {
						$response['nextUpgrade'] = $next_upgrade->name;
					} else {
						do_action('ph_all_upgrades_complete', $current_upgrade);
					}
				} else {
					do_action('ph_all_upgrades_complete', $current_upgrade);
				}
			} else {
				$current_upgrade->_step($current_step);
			}
		}

		$response['step'] = $current_step + 1;

		echo json_encode($response);
		die();
	}


	/*
	 * UTILITY METHODS
	 */
	public function getUpgradeByName($name)
	{
		foreach ($this->upgrades as $index => $upgrade) {
			if ($name == $upgrade->name) {
				return $upgrade;
			}
		}
	}

	public function getNextUpgrade($current_upgrade)
	{
		foreach ($this->upgrades as $index => $upgrade) {
			if ($current_upgrade->name == $upgrade->name) {

				if (isset($this->upgrades[$index + 1])) {
					return $this->upgrades[$index + 1];
				}
			}
		}

		return false;
	}
}

function PH_UpgradeHandler()
{
	return PH_UpgradeHandler::instance(
		array(
			'slug' => 'ph-upgrade-handler',
			'name' => 'ProjectHuddle',
		)
	);
}

PH_UpgradeHandler();
