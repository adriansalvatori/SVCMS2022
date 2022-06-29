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
class PH_PDF_Scripts {
	/**
	 * Stores js directory
	 *
	 * @var string
	 */
	protected $js_dir = PH_PDF_PLUGIN_URL . 'assets/dist/js/';

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
	protected $handle = 'ph-pdf-mockups';

	/**
	 * PH_PDF_Scripts constructor.
	 */
	public function __construct() {
		// mockup admin
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// dashboard admin
		add_action( 'admin_enqueue_scripts', array( $this, 'dashboard_scripts' ) );

		// mockup front
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
		add_filter( 'ph_allowed_scripts', array( $this, 'allow' ) );

		// shortcodes
		add_action( 'wp_enqueue_scripts', array( $this, 'shortcode_scripts' ) );
		add_action( 'ph_shortcodes_enqueue_script', array( $this, 'enqueue_shortcode_scripts' ) );
	}

	/**
	 * Adds pdf image support to our dashboard page
	 *
	 * @param string $hook
	 * @return void
	 */
	public function dashboard_scripts( $hook ) {
		if ( $hook != 'toplevel_page_project-huddle' ) {
			return;
		}
		wp_enqueue_script( 'ph-pdf-mockups-dashboard', $this->js_dir . 'ph-pdf-mockups-dashboard.js', array( 'jquery', 'project-huddle-dashboard' ), PH_PDF_PLUGIN_VERSION, true );
		wp_localize_script(
			'ph-pdf-mockups-dashboard',
			'phPdf',
			array(
				'workerSrc' => $this->js_dir . 'ph-pdf-worker.js',
			)
		);
	}

	/**
	 * Adds pdf support to our shortcode
	 *
	 * @return void
	 */
	public function shortcode_scripts() {
		wp_register_script( 'ph-pdf-mockups-shortcode', $this->js_dir . 'ph-pdf-mockups-dashboard.js', array( 'jquery', 'project-huddle-shortcodes' ), PH_PDF_PLUGIN_VERSION, true );
		wp_localize_script(
			'ph-pdf-mockups-shortcode',
			'phPdf',
			array(
				'workerSrc' => $this->js_dir . 'ph-pdf-worker.js',
			)
		);
	}

	/**
	 * Enqueue our shortcode scripts when needed
	 *
	 * @return void
	 */
	public function enqueue_shortcode_scripts() {
		wp_enqueue_script( 'ph-pdf-mockups-shortcode' );
	}

	/**
	 * Pdf support on mockup pages
	 *
	 * @return void
	 */
	public function front_scripts() {
		// return for other pages
		if ( ! is_singular( 'ph-project' ) ) {
			return;
		}

		wp_enqueue_script( 'ph-pdf-mockups-front', $this->js_dir . 'ph-pdf-mockups-front.js', array( 'project-huddle' ), PH_PDF_PLUGIN_VERSION );

		wp_localize_script(
			'ph-pdf-mockups-front',
			'phPdf',
			array(
				'workerSrc' => $this->js_dir . 'ph-pdf-worker.js',
			)
		);
	}

	/**
	 * Pdf support on mockup admin
	 *
	 * @param string $hook PHP file called.
	 *
	 * @return void
	 */
	public function admin_scripts( $hook ) {
		// get post type.
		global $post_type;

		// bail out early if we are not on a project add/edit screen.
		if ( 'ph-project' !== $post_type || ( 'post.php' !== $hook && 'post-new.php' !== $hook ) ) {
			return;
		}

		wp_enqueue_script( $this->handle, $this->js_dir . 'ph-pdf-mockups.js', array( 'project-huddle-admin-js' ), PH_PDF_PLUGIN_VERSION, false );

		wp_localize_script(
			$this->handle,
			'phPdf',
			array(
				'workerSrc' => $this->js_dir . 'ph-pdf-worker.js',
			)
		);
	}

	/**
	 * Allow our script on website pages
	 *
	 * @param array $scripts An array of script handles to allow.
	 *
	 * @return array With our handle
	 */
	public function allow( $scripts = array() ) {
		$scripts[] = 'ph-pdf-mockups';
		$scripts[] = 'ph-pdf-mockups-front';

		return $scripts;
	}

	/**
	 * Output mockup styles.
	 *
	 * @return void
	 */
	public function mockup_styles() {
		// js directory.
		$this->css_dir = PH_PDF_PLUGIN_URL . 'assets/dist/css/';

		wp_enqueue_style( 'ph-pdf-mockups-mockups', $this->css_dir . 'ph-pdf-mockups-mockups.css', array(), PH_PDF_PLUGIN_VERSION );
	}
}
