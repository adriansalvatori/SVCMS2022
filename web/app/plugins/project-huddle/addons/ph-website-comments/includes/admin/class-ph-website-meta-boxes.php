<?php

/**
 * ProjectHuddle Meta Boxes
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Admin_Meta_Boxes Class
 *
 * Parent class to handle addition, errors and saving of all meta boxes
 *
 * @since 1.0
 */
class PH_Website_Meta_Boxes
{

	/**
	 * Error strings
	 *
	 * @var array Error strings.
	 * @since 1.0
	 */
	public static $meta_errors = array();

	/**
	 * Custom post type slug
	 *
	 * @var string Custom post type slug
	 * @since 1.0
	 */
	public $post_type_slug = 'ph-website';

	/**
	 * Unique prefix to settings
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $settings_base;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct()
	{
		// run only on admin pages.
		if (!is_admin()) {
			return;
		}

		// set base settings prefix.
		$this->settings_base = 'ph_website';

		// hide other meta boxes by default.
		add_action('hidden_meta_boxes', array($this, 'hide_others'), 10, 3);

		// add meta boxes.
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10);

		// save action.
		add_action('save_post_ph-website', array($this, 'save_meta_boxes'), 1, 2);

		// save meta box action.
		add_action('ph_save_website_metaboxes', 'PH_Website_Meta_Box_Setup::save', 10, 2);

		// error handling.
		add_action('admin_notices', array($this, 'output_errors'));
		add_action('shutdown', array($this, 'save_errors'));
	}

	/**
	 * Add Meta boxes
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_meta_boxes()
	{
		global $post;

		if (!$post) {
			return;
		}

		// must be our post type
		if ('ph-website' !== get_post_type($post)) {
			return;
		}

		$installed = get_post_meta($post->ID, 'ph_installed', true);

		$this->force_metabox_position($installed);

		// we have some webpages.
		if ($installed) {
			// project pages
			add_meta_box(
				$this->post_type_slug . '-website-pages', // unique id based on post type.
				__('Pages', 'project-huddle'), // title.
				'PH_Website_Meta_Box_Pages::output', // get markup from static method.
				$this->post_type_slug, // post type to apply meta box.
				'normal', // position.
				'high' // priority.
			);
		}

		// notice.
		add_meta_box(
			$this->post_type_slug . '-website-notice', // unique id based on post type.
			__('Publish', 'project-huddle'), // title.
			array($this, 'notice'), // get markup from static method.
			$this->post_type_slug, // post type to apply meta box.
			'side', // position.
			'high' // priority.
		);

		// emails.
		add_meta_box(
			$this->post_type_slug . '-emails', // unique id based on post type.
			__('Project Members', 'project-huddle'), // title.
			'PH_Meta_Box_Project_Members::output', // get markup from static method.
			$this->post_type_slug, // post type to apply meta box.
			'side', // position.
			'default' // priority.
		);

		$post_id = get_option('ph_site_post');

		// website setup.
		if (current_user_can('publish_ph-websites', $post->ID)) :
			add_meta_box(
				$this->post_type_slug . '-website', // unique id based on post type.
				__('Website Setup', 'project-huddle'), // title.
				'PH_Website_Meta_Box_Setup::output', // get markup from static method.
				$this->post_type_slug, // post type to apply meta box.
				$installed ? 'side' : 'normal', // position.
				'low' // priority.
			);
		endif;

		// project images
		add_meta_box(
			$this->post_type_slug . '-activity', // unique id based on post type
			__('Latest Activity', 'project-huddle'), // title
			'PH_Meta_Box_Project_Activity::output', // get markup from static method
			$this->post_type_slug, // post type to apply meta box
			'side', // position
			'default' // priority
		);

		// email notifications.
		add_meta_box(
			$this->post_type_slug . '-email-notifications', // unique id based on post type.
			__('My Email Notifications', 'project-huddle'), // title.
			'PH_Meta_Box_Project_Email_Notifications::output', // get markup from static method.
			$this->post_type_slug, // post type to apply meta box.
			'side', // position.
			'default' // priority.
		);

		add_meta_box(
			$this->post_type_slug . '-status', // unique id
			__('Approval Status', 'project-huddle'), // title
			function () {
				echo '<style>
				#ph-website-status {
					background: none;
					border: none;
					box-shadow: none;
				}
				#ph-website-status > :not(.inside) {
					display:none;
				}
				</style>';
				echo '<div id="ph-website-status"></div>';
			},
			$this->post_type_slug, // post type to apply meta box
			'side', // position
			'high' // priority
		);

		// // page feedback.
		// add_meta_box(
		// 	$this->post_type_slug . '-project-options', // unique id based on post type.
		// 	__( 'Project Options', 'project-huddle' ), // title.
		// 	'PH_Meta_Box_Website_Options::output', // get markup from static method.
		// 	$this->post_type_slug, // post type to apply meta box.
		// 	'side', // position.
		// 	'high' // priority.
		// );

		// if we have the post and comments aren't disabled.
		if (false !== get_post_status($post_id) && true !== get_option('ph_disable_self')) {
			// page feedback.
			add_meta_box(
				$this->post_type_slug . '-ph-comments', // unique id based on post type.
				__('Pending Page Feedback', 'project-huddle'), // title.
				'PH_Website_Meta_Box_Page_Feedback::output', // get markup from static method.
				array('post', 'page'), // post type to apply meta box.
				'side', // position.
				'low' // priority.
			);
		}

		remove_meta_box('submitdiv', $this->post_type_slug, 'side');
		remove_meta_box('slugdiv', $this->post_type_slug, 'normal');
		remove_meta_box('postcustom', $this->post_type_slug, 'normal');
		remove_meta_box('postcustom', $this->post_type_slug, 'side');
		remove_meta_box('postcustom', $this->post_type_slug, 'advanced');
	}

	/**
	 * Force setup metabox position depending on installation status
	 *
	 * @param $installed Boolean If website is installed
	 */
	public function force_metabox_position($installed)
	{
		$screen     = get_current_screen();
		$page       = $screen->id;
		$user_order = get_user_option("meta-box-order_$page");

		if ('ph-website' !== $screen->id) {
			return;
		}

		// make them all open
		update_user_option(get_current_user_id(), "closedpostboxes_$page", '', true);

		if ($installed) {
			if (isset($user_order['normal'])) {
				$user_order['normal'] = str_replace(',ph-website-website', '', $user_order['normal']);
			}
			if (isset($user_order['side'])) {
				$user_order['side']  = str_replace(',ph-website-website', '', $user_order['side']);
				$user_order['side'] .= ',ph-website-website';
			}
		} else {
			if (isset($user_order['side'])) {
				$user_order['side'] = str_replace(',ph-website-website', '', $user_order['side']);
			}
			if (isset($user_order['normal'])) {
				$user_order['normal']  = str_replace(',ph-website-website', '', $user_order['normal']);
				$user_order['normal'] .= ',ph-website-website';
			}
		}

		update_user_option(get_current_user_id(), "meta-box-order_$page", $user_order);
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param  int    $post_id Project (Post) ID.
	 * @param  object $post Project (Post) Object.
	 *
	 * @return void
	 */
	public function save_meta_boxes($post_id, $post)
	{

		// $post_id and $post are required
		if (empty($post_id) || empty($post)) {
			return;
		}

		// verify nonce.
		if (empty($_POST['project_huddle_meta_nonce']) || !wp_verify_nonce(wp_unslash($_POST['project_huddle_meta_nonce']), 'project_huddle_save_data')) {
			return;
		}

		// Check that the user has correct permissions.
		if (!$this->can_save_data($post_id)) {
			return;
		}

		// Don't save meta boxes for revisions or autosaves.
		if (defined('DOING_AUTOSAVE') || is_int(wp_is_post_revision($post_id)) || is_int(wp_is_post_autosave($post_id))) {
			return;
		}

		// AJAX? Not used here.
		if (defined('DOING_AJAX')) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if (empty($_POST['post_ID']) || (int) $_POST['post_ID'] !== (int) $post_id) {
			return;
		}

		// init save action.
		do_action('ph_save_website_metaboxes', $post_id, $post);
	}

	/**
	 * Determine if the current user has the relevant permissions
	 *
	 * @access private
	 * @since 1.0
	 *
	 * @param int $post_id Project (Post) ID.
	 *
	 * @return bool If user can save data
	 */
	private function can_save_data($post_id)
	{

		// double check our post type and permissions.
		if (!current_user_can('edit_ph-website', $post_id)) {
			return false;
		}

		// return true after checks.
		return true;
	}

	/**
	 * Hide other meta boxes on page
	 *
	 * @param bool   $hidden Is the meta box hidden.
	 * @param string $screen Current screen.
	 * @param bool   $use_defaults Use defaults.
	 * @return bool
	 */
	public function hide_others($hidden, $screen, $use_defaults)
	{
		global $wp_meta_boxes, $post;

		if (!isset($post->ID)) {
			return $hidden;
		}

		$cpt = 'ph-website';

		// add our meta boxes.
		$allowed = array(
			'ph-website-emails',
			'ph-website-approval',
			'ph-website-status',
			'ph-website-activity',
			'ph-website-email-notifications',
			'ph-website-website-notice',
			'ph-website-website-pages',
			'ph-website-project-options',
			'ph-website-website',
		);

		if ($cpt === $screen->id && isset($wp_meta_boxes[$cpt])) {
			$tmp = array();
			foreach ((array) $wp_meta_boxes[$cpt] as $context_key => $context_item) {
				foreach ($context_item as $priority_key => $priority_item) {
					foreach ($priority_item as $metabox_key => $metabox_item) {
						if (!in_array($metabox_key, $allowed)) {
							$tmp[] = $metabox_key;
						}
					}
				}
			}
			$hidden = $tmp;  // Override the user option.
		}

		return $hidden;
	}

	/**
	 * Add an error message
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $text Error Message.
	 *
	 * @return void
	 */
	public static function add_error($text)
	{
		self::$meta_errors[] = $text;
	}

	/**
	 * Save errors to an option to be recalled after page load
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function save_errors()
	{
		update_option('ph_meta_errors', self::$meta_errors);
	}

	/**
	 * Show any stored error messages.
	 *
	 * Gets the error message stored in options and clears them after displayed
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return void
	 */
	public function output_errors()
	{
		// get errors.
		$errors = maybe_unserialize(get_option('ph_meta_errors'));

		if (!empty($errors)) {

			echo '<div id="ph_errors" class="error fade">';
			foreach ($errors as $error) {
				echo '<p>' . esc_html($error) . '</p>';
			}
			echo '</div>';

			// Clear.
			delete_option('ph_meta_errors');
		}
	}

	/**
	 * Add notice where publish box should be
	 *
	 * @param string $text Notice text.
	 * @return void
	 */
	public static function notice($text)
	{ ?>
		<script>
			setInterval(function(){ 
				var element = document.getElementById( 'ph-website-notice-id' );
				element.innerHTML = '<div class="circle-loader loading"></div>';
			}, 9000);

			setInterval(function(){ 
				var element = document.getElementById( 'ph-website-notice-id' );
				element.innerHTML = '<div class="circle-loader load-complete"><div class="checkmark draw" style="display: block;"></div>';
			}, 5000);
      </script>
	  <?php
		$notice_text = '<div class="ph-website-notice"><div id="ph-website-notice-id"><div class="circle-loader load-complete"><div class="checkmark draw" style="display: block;"></div></div></div><p>' . esc_html__('This page is automatically saved.', 'project-huddle') . '</p></div>';

		echo $notice_text;
	}
}
