<?php

/**
 * Error Reporting Class
 *
 * If the user gives us permission, we track the errors on their install and report!
 *
 * @since       3.0.14
 * @author      Andre Gagnon
 */

if (!defined('ABSPATH')) {
	exit;
}

class PH_Error_Reporting
{
	// class instance
	static $instance;

	// key to use for options, etc
	public $error_key = 'ph_error_reporting';

	// Singleton design pattern
	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add the necessary hooks and call the installer
	 *
	 * @since 3.0.14
	 * @throws Raven_Exception
	 * @return void
	 */
	public function __construct()
	{
		// show notice
		// add_action('admin_notices', array($this, 'error_reporting_notice'));

		// save when clicked
		// add_action("wp_ajax_$this->error_key", array($this, 'save_preference'));

		// disable for now
		return false;
	}

	/**
	 * Shows notice to enable error reporting
	 */
	public function error_reporting_notice()
	{
		// bail if dismissed
		if (get_option("dismissed-$this->error_key", false)) {
			return;
		}
		// bail if enabled
		if (get_option("$this->error_key", false)) {
			return;
		}

		echo '<div class="notice notice-info is-dismissible ph-notice" data-notice="' . esc_attr($this->error_key) . '">
					<h3 style="margin-bottom: 0">' . __('Help Improve ProjectHuddle!', 'project-huddle') . '</h3>
					<p>' . esc_html(sprintf(__('Help improve ProjectHuddle! Enabling crash reporting will automatically send errors to the ProjectHuddle to help us improve.', 'project-huddle'), phpversion())) . '</p>
					<p>
						<a href="#" id="ph-error-notice-enable" class="button button-primary">
	                        ' . __("I'm in!", 'project-huddle') . '
	                    </a>
						<a href="#" class="button ph-notice-dismiss">
	                        ' . __('No Thanks', 'project-huddle') . '
	                    </a>
					</p>
				</div>';
		ph_dismiss_js();
		$this->send_preference();
	}

	/**
	 * Save error reporting preference
	 */
	public function save_preference()
	{
		check_ajax_referer($this->error_key, 'security');
		update_option($this->error_key, 'on');
		update_option("dismissed-$this->error_key", true);
		wp_die();
	}

	/**
	 * Sends preference via ajax
	 */
	public function send_preference()
	{ ?>
		<script>
			jQuery(function($) {
				$(document).on('click', '.ph-notice .ph-notice-dismiss', function() {
					$(this).closest('.notice').find('.notice-dismiss').click()
				});

				$(document).on('click', '.ph-notice #ph-error-notice-enable', function(e) {
					// Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					$.ajax(ajaxurl, {
						type: 'POST',
						data: {
							action: '<?php echo esc_attr($this->error_key); ?>',
							security: '<?php echo wp_create_nonce($this->error_key); ?>',
						},
					})

					$(this).closest('.notice').find('.notice-dismiss').click()

					e.preventDefault()
				});
			});
		</script>
<?php
	}

	/**
	 * Checks if we have the user's permission to track error data
	 *
	 * @since 3.0.14
	 * @return boolean
	 */
	public function is_reporting_enabled()
	{
		return get_site_option($this->error_key, '') == 'on' ? true : false;
	}

	/**
	 * Returns the environment to send to Sentry
	 *
	 * @since 3.0.14
	 * @return string
	 */
	public function get_environment()
	{
		return defined('WP_DEBUG') && WP_DEBUG ? 'development' : 'production';
	}
}

// Return the instance of the function
function PH_Error_Reporting()
{
	return PH_Error_Reporting::get_instance();
}
