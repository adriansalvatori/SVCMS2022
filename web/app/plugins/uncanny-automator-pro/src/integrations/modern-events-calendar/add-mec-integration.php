<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Mec_Integration
 *
 * @package Uncanny_Automator_Pro
 */
class Add_Mec_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MEC';

	/**
	 * Add_Integration constructor. Do nothing for now.
	 *
	 * @return self.
	 */
	public function __construct() {
		return $this;
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool True if MEC class exists. Otherwise, false.
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {

			if ( class_exists( 'MEC' ) ) {

				$status = true;

				// Automator Base.
				$old_base_helper_file  = str_replace( 'uncanny-automator-pro', 'uncanny-automator', plugin_dir_path( __DIR__ ) );
				$old_base_helper_file .= 'modern-events-calendar' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mec-event-helpers.php';

				// Automator Pro.
				$old_pro_helper_file = plugin_dir_path( __DIR__ ) . 'modern-events-calendar' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mec-pro-event-helpers.php';

				// If the old pro helper file does not exists anymore, but the old helper file exists in base plugin. Disable this integration.
				if ( ! file_exists( $old_pro_helper_file ) && file_exists( $old_base_helper_file ) ) {
					add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
					$status = false;
				}
			} else {

				$status = false;

			}
		}

		return $status;
	}

	/**
	 * Set the directories that the auto loader will run in
	 *
	 * @param $directory
	 *
	 * @return array The list of directories.
	 */
	public function add_integration_directory_func( $directory ) {

		$directory[] = dirname( __FILE__ ) . '/helpers';
		$directory[] = dirname( __FILE__ ) . '/actions';
		$directory[] = dirname( __FILE__ ) . '/triggers';
		$directory[] = dirname( __FILE__ ) . '/tokens';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 *
	 * @return void.
	 */
	public function add_integration_func() {

		global $uncanny_automator;

		$uncanny_automator->register->integration(
			self::$integration,
			array(
				'name'     => 'M.E. Calendar',
				'icon_svg' => \Uncanny_Automator_Pro\Utilities::get_integration_icon( 'modern-events-calendar-icon.svg' ),
			)
		);
	}

	public function display_admin_notices() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php esc_html_e( 'A newer version Uncanny Automator is required to use Uncanny Automator Pro with Modern Events Calendar. Please update Uncanny Automator to the latest version.', 'uncanny-automator-pro' ); ?>
			</p>
		</div>
		<?php
	}
}
