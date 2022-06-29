<?php

/**
 * Handles the front-end view of the upgrade process
 */
if (!defined('ABSPATH')) {
	exit;
}

class PH_UpgradeHandlerPage
{
	/**
	 * Slug used for page, etc.
	 * @var string
	 */
	public $slug = '';

	/**
	 * Name of the plugin
	 * @var string
	 */
	public $name = 'Plugin';

	public function __construct($args)
	{
		// must have a slug
		if (!isset($args['slug'])) {
			return;
		}

		$this->slug = $args['slug'];
		$this->name = isset($args['name']) ? $args['name'] : $this->name;

		add_action('admin_menu', array($this, 'register'));
		add_action('admin_notices', array($this, 'show_upgrade_notices'));
	}

	public function register()
	{
		$page = add_submenu_page(
			/* Parent Slug  */
			null,
			/* Page Title   */
			sprintf(__('%s Upgrade', 'project-huddle'), $this->name),
			/* Menu Title   */
			__('Upgrade', 'project-huddle'),
			/* Capabilities */
			apply_filters('ph_admin_menu_capabilities', 'manage_options'),
			/* Menu Slug    */
			$this->slug,
			/* Function     */
			array($this, 'display')
		);

		add_action('admin_print_styles-' . $page, array($this, 'scripts'));
		add_action('admin_print_styles-' . $page, array($this, 'styles'));
	}

	public function display()
	{
		include 'upgrade-handler-html.php';
	}

	public function scripts()
	{

		wp_enqueue_script(
			/* Handle       */
			$this->slug,
			/* Source       */
			PH_PLUGIN_URL . 'assets/js/dist/ph-upgrade-handler.js',
			/* Dependencies */
			array('jquery', 'jquery-ui-core', 'jquery-ui-progressbar'),
			/* Version      */
			'0.0.1',
			/* In Footer    */
			true
		);

		$upgrades      = PH_UpgradeHandler()->upgrades;
		$first_upgrade = null;
		if (empty($upgrades)) {
			return;
		}

		foreach ($upgrades as $upgrade) {

			if (!$upgrade->isComplete()) {
				$first_upgrade = $upgrade->name;
				break;
			}
		}

		wp_localize_script(
			$this->slug,
			'phUpgradeHandler',
			array(
				'upgrade' => $first_upgrade,
			)
		);
	}

	public function styles()
	{
		wp_enqueue_style(
			/* Handle */
			$this->slug,
			/* Source */
			PH_PLUGIN_URL . 'assets/css/ph-upgrade-handler.css'
		);
	}

	public function show_upgrade_notices()
	{
		// Don't show notices on the upgrade handler page.
		if (isset($_GET['page']) && $this->slug == $_GET['page']) {
			return;
		}

		$upgrades = PH_UpgradeHandler()->upgrades;
		$name     = PH_UpgradeHandler()->name;

		$upgrade_count = 0;

		if (empty($upgrades)) {
			return;
		}

		foreach ($upgrades as $upgrade) {

			if (!$upgrade->isComplete()) {
				$upgrade_count++;
			}
		}

		if (0 < $upgrade_count) {
			printf(
				'<div class="notice notice-info"><p>' . __('%1$s needs to process %2$s upgrade(s). This may take a few minutes to complete. It\'s recommended that you create a backup of your database before you upgrade. %3$s Start Upgrade %4$s', 'project-huddle') . '</p></div>',
				$name,
				$upgrade_count,
				'<p><a class="button button-primary" href="' . admin_url('admin.php?page=' . $this->slug) . '">',
				'</a></p>'
			);
		}
	}
}
