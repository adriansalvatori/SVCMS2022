<?php

/**
 * Script loader for website commenting
 *
 * @package ProjectHuddle
 * @subpackage File Uploads
 */

/**
 * Script loader class
 */
class PHF_Scripts
{
	/**
	 * Stores js directory
	 *
	 * @var string
	 */
	protected $js_dir = '';

	/**
	 * Stores css directory
	 *
	 * @var string
	 */
	protected $css_dir = '';

	/**
	 * Our script handle
	 *
	 * @var string
	 */
	protected $handle = 'ph-file-uploads';

	/**
	 * PHF_Scripts constructor.
	 */
	public function __construct()
	{
		// js directory.
		$this->js_dir = PH_UPLOADS_PLUGIN_URL . 'assets/dist/js/';

		// add styles.
		add_action('ph_website_thread_css', array($this, 'website_styles'));
		add_action('wp_enqueue_scripts', array($this, 'mockup_styles'));

		// if we are 3.9 or above, use new function
		if (defined('PH_VERSION') && version_compare(PH_VERSION, '3.8.9999', '>')) {
			$this->scripts_new();
		} else {
			add_action('wp_enqueue_scripts', [$this, 'scripts']);
		}

		// whitelist our script.
		add_filter('ph_allowed_website_scripts', array($this, 'allow'));
		add_filter('ph_allowed_scripts', array($this, 'allow'));
		add_filter('ph_allowed_styles', array($this, 'allow'));

		// add data
		add_action('wp_enqueue_scripts', [$this, 'localize']);

		// add our scripts.
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
	}

	public function scripts_new()
	{
		if (function_exists('ph_enqueue_website_script')) {
			ph_enqueue_website_script(
				$this->handle . '-websites',
				$this->js_dir . 'ph-file-uploads-websites-v4.js',
				[],
				PH_UPLOADS_PLUGIN_VERSION,
				false
			);
		}
		if (function_exists('ph_enqueue_mockup_script')) {
			ph_enqueue_mockup_script(
				$this->handle . '-mockups',
				$this->js_dir . 'ph-file-uploads-mockups.js',
				[],
				PH_UPLOADS_PLUGIN_VERSION,
				false
			);
		}
	}

	public function localize()
	{
		wp_localize_script(
			$this->handle . '-mockups',
			'PHF_Settings',
			array(
				'types' => ph_file_input_types(),
			)
		);
		wp_localize_script(
			$this->handle . '-websites',
			'PHF_Settings',
			array(
				'types' => ph_file_input_types(),
			)
		);
	}

	/**
	 * Register our scripts
	 */
	public function scripts()
	{
		// return for other pages.
		if (!is_singular('ph-website') && !is_singular('ph-project')) {
			return;
		}

		// js directory.
		$this->js_dir = PH_UPLOADS_PLUGIN_URL . 'assets/dist/js/';

		// register our script.
		if (is_singular('ph-project')) {
			wp_enqueue_script($this->handle . '-mockups', $this->js_dir . 'ph-file-uploads-mockups.js', array('project-huddle'), PH_UPLOADS_PLUGIN_VERSION, false);
			wp_localize_script(
				$this->handle . '-mockups',
				'PHF_Settings',
				array(
					'types' => ph_file_input_types(),
				)
			);
		}
		if (is_singular('ph-website')) {
			if (defined('PH_VERSION') && version_compare(PH_VERSION, '3.9.0') < 0) {
				$filename = 'ph-file-uploads-websites.js';
			} else {
				$filename = 'ph-file-uploads-websites-v4.js';
			}

			wp_enqueue_script($this->handle . '-websites', $this->js_dir . sanitize_text_field($filename), array('ph-website-comments'), PH_UPLOADS_PLUGIN_VERSION, false);

			wp_localize_script(
				$this->handle . '-websites',
				'PHF_Settings',
				array(
					'types' => ph_file_input_types(),
				)
			);
		}
	}

	/**
	 * Admin scripts
	 *
	 * @param string $hook PHP file called.
	 *
	 * @return void
	 */
	public function admin_scripts($hook)
	{
		// get post type.
		global $post_type;

		// bail out early if we are not on a project add/edit screen.
		if ('ph-website' !== $post_type || ('post.php' !== $hook && 'post-new.php' !== $hook)) {
			return;
		}

		// bail for newer version
		if (defined('PH_VERSION') && version_compare(PH_VERSION, '3.7.0-beta1') === 1) {
			return;
		}

		// directories.
		$this->js_dir  = PH_UPLOADS_PLUGIN_URL . 'assets/dist/js/';
		$this->css_dir = PH_UPLOADS_PLUGIN_URL . 'assets/dist/css/';

		wp_enqueue_script($this->handle . '-websites-admin', $this->js_dir . 'ph-file-uploads-websites-admin.js', array('ph-website-admin-js'), PH_UPLOADS_PLUGIN_VERSION, false);
		wp_enqueue_style($this->handle . '-websites-admin', $this->css_dir . 'ph-file-uploads-websites-admin.css', array('ph-website-admin-css'), PH_UPLOADS_PLUGIN_VERSION, false);
	}

	/**
	 * Allow our script on website pages
	 *
	 * @param array $scripts An array of script handles to allow.
	 *
	 * @return array With our handle
	 */
	public function allow($scripts = array())
	{
		$scripts[] = 'ph-file-uploads-mockups';
		$scripts[] = 'ph-file-uploads-websites';

		return $scripts;
	}

	/**
	 * Output mockup styles.
	 *
	 * @return void
	 */
	public function mockup_styles()
	{
		// js directory.
		$this->css_dir = PH_UPLOADS_PLUGIN_URL . 'assets/dist/css/';

		wp_enqueue_style('ph-file-uploads-mockups', $this->css_dir . 'ph-file-uploads-mockups.css', array(), PH_UPLOADS_PLUGIN_VERSION);
	}

	/**
	 * Get css file and read it to prevent cross origin issues.
	 */
	public function website_styles()
	{
		$file = PH_UPLOADS_PLUGIN_DIR . 'assets/dist/css/ph-file-uploads-websites.css';

		if (file_exists($file)) {
			// phpcs:ignore
			readfile($file);
		}
	}
}
