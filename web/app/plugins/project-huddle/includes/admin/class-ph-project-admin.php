<?php

/**
 * Projects in Admin
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Admin_Project_Images Class
 *
 * This class handles the metabox for the project images in the admin
 *
 * @since 1.0
 */
class PH_Project_Admin
{

	/**
	 * Custom post type slug
	 *
	 * @since 1.0
	 */
	public $post_type_slug = 'ph-project';

	/**
	 * Setup project admin
	 *
	 * @since 10
	 */
	public function __construct()
	{

		// run only on admin pages
		if (!is_admin()) {
			return;
		}

		// remove project shortlink
		remove_action('wp_head', 'wp_shortlink_wp_head', 10);

		// remove shortlink
		add_filter('pre_get_shortlink', array($this, 'remove_shortlink'), 10, 2);

		// add collaborators ui
		add_action('post_submitbox_start', array($this, 'collaboratorsBox'));

		// add help beacon
		add_action('admin_footer', array($this, 'beacon'));

		// add help url to menu
		add_action('admin_menu', array($this, 'help_url'), 9999999);

		// maybe add update notice
		add_action('admin_notices', array($this, 'maybe_add_update_notice'));
	}

	/**
	 * Add helpscout beacon for help
	 */
	public function beacon()
	{
		if (!current_user_can('edit_ph-projects')) {
			return;
		}
?>

		<script type="text/javascript">
			! function(e, t, n) {
				function a() {
					var e = t.getElementsByTagName("script")[0],
						n = t.createElement("script");
					n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
				}
				if (e.Beacon = n = function(t, n, a) {
						e.Beacon.readyQueue.push({
							method: t,
							options: n,
							data: a
						})
					}, n.readyQueue = [], "complete" === t.readyState) return a();
				e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
			}(window, document, window.Beacon || function() {});
		</script>
		<script type="text/javascript">
			window.Beacon('init', '254a5f05-8475-4e0d-bcfd-6f91fbc83273')
		</script>

		<script>
			jQuery('[href="#show-help"]').click(function(e) {
				if (typeof Beacon === 'undefined') {
					return true
				}
				Beacon('toggle')
				e.preventDefault()
			})
		</script>

		<script>
            <?php $fs_license = ph_licensing()->_get_license() ?>
			<?php if (is_object($fs_license)) { ?>
                <?php $fs_user = ph_licensing()->get_user() ?>
				Beacon('identify', {
					name: '<?php echo esc_html($fs_user->get_name()); ?>',
					email: '<?php echo esc_html($fs_user->email); ?>',
					'License': '<?php echo esc_html($fs_license->id); ?>',
					'Expires': '<?php echo esc_html(date('F j, Y, g:i a', strtotime($fs_license->expiration))); ?>',
					'Key': '<?php echo esc_html($fs_license->secret_key); ?>',
					'Price Tier': '<?php echo (int) $fs_license->pricing_id; ?>',
					'Version': '<?php echo defined('PH_VERSION') ? PH_VERSION : 'unknown'; ?>'
				})
			<?php } else { ?>
				Beacon('identify', {
					'Key': '',
					'Version': '<?php echo defined('PH_VERSION') ? PH_VERSION : 'unknown'; ?>',
				})
			<?php } ?>

			Beacon('config', {
				display: {
					'zIndex': 999999
				}
			});
		</script>

		<?php
		$screen = get_current_screen();
		if ('ph-website' === $screen->post_type && 'post' == $screen->base) {
		?>
			<script>
				setTimeout(function() {
					if (typeof Beacon === 'function') {
						Beacon('show-message', '5165aa7d-406e-4949-85b0-9fe5729c43a6')
					}
				}, 7000);
			</script>
		<?php } ?>

		<?php
	}

	public function maybe_add_update_notice()
	{
		if (isset($_GET['ph_message'])) {
		?>
			<div class="notice notice-warning">
				<p><?php echo esc_html($_GET['ph_message']); ?></p>
			</div>
		<?php
		}
	}

	public function help_url()
	{
		global $submenu;
		if (!current_user_can('manage_options')) {
			return;
		}
		if ( ! ph_licensing()->has_active_valid_license()) {
			$permalink = esc_url(sprintf('admin.php?page=project-huddle-account&ph_message="%s"', __('Please enter a valid license key for help.', 'project-huddle')));
		} else {
			$permalink = '#show-help';
		}
		$submenu['project-huddle'][] = array(
			__('Help', 'project-huddle'),
			'manage_options',
			$permalink,
		);
	}

	/**
	 * Shows collaborators and controls
	 *
	 * @since 1.0
	 */
	// TODO: Maybe separate functionality of collaborators
	public function collaboratorsBox()
	{
		?>
<?php
	}


	/**
	 * Removes wp.me shortlink functionality
	 *
	 * @param $false
	 * @param $post_id
	 *
	 * @return string
	 */
	public function remove_shortlink($false, $post_id)
	{
		return 'ph-project' === get_post_type($post_id) ? '' : $false;
	}
}
