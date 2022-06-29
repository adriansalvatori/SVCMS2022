<?php

/**
 * ProjectHuddle Meta Boxes
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * PH_Admin_Meta_Boxes Class
 *
 * Parent class to handle addition, errors and saving of all meta boxes
 *
 * @since 1.0
 */
class PH_Admin_Meta_Boxes
{

	/**
	 * @var array Error strings
	 * @since 1.0
	 */
	private static $meta_errors = array();

	/**
	 * @var string Custom post type slug
	 * @since 1.0
	 */
	public $post_type_slug = 'ph-project';

	/**
	 * Unique prefix to settings
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $settings_base;

	/**
	 * Constructor
	 * @since 1.0
	 */
	public function __construct()
	{

		// run only on admin pages
		if (!is_admin()) {
			return;
		}

		// set base settings prefix
		$this->settings_base = 'ph_';

		// add meta boxes
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10);

		// save post action
		add_action('save_post_ph-project', array($this, 'save_meta_boxes'), 1, 2);

		// save meta box action
		add_action('ph_save_metaboxes', 'PH_Meta_Box_Images::save', 10, 2);
		add_action('ph_save_metaboxes', 'PH_Meta_Box_Project_Options::save', 20, 2);
		add_action('ph_save_metaboxes', 'PH_Meta_Box_Project_Members::save', 30, 2);

		// error handling
		add_action('admin_notices', array($this, 'output_errors'));
		add_action('shutdown', array($this, 'save_errors'));
	}

	/**
	 * Add an error message
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $text Error Message
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
		// get errors
		$errors = maybe_unserialize(get_option('ph_meta_errors'));

		if (!empty($errors)) {

			echo '<div id="ph_errors" class="error fade">';
			foreach ($errors as $error) {
				echo '<p>' . wp_kses_post($error) . '</p>';
			}
			echo '</div>';

			// Clear
			delete_option('ph_meta_errors');
		}
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
		if ('ph-project' !== get_post_type($post)) {
			return;
		}

		// latest activity
		add_meta_box(
			$this->post_type_slug . '-activity', // unique id based on post type
			__('Project Activity', 'project-huddle'), // title
			'PH_Meta_Box_Project_Activity::output', // get markup from static method
			$this->post_type_slug, // post type to apply meta box
			'side', // position
			'default' // priority
		);

		// project images
		add_meta_box(
			$this->post_type_slug . '-gallery', // unique id based on post type
			__('Images', 'project-huddle'), // title
			'PH_Meta_Box_Images::output', // get markup from static method
			$this->post_type_slug, // post type to apply meta box
			'normal', // position
			'high' // priority
		);

		// project options
		add_meta_box(
			$this->post_type_slug . '-options', // unique id based on post type
			__('Mockup Options', 'project-huddle'), // title
			'PH_Meta_Box_Project_Options::output', // get markup from static method
			$this->post_type_slug, // post type to apply meta box
			'normal'
		);

		// emails
		add_meta_box(
			$this->post_type_slug . '-emails', // unique id based on post type
			__('Project Members', 'project-huddle'), // title
			'PH_Meta_Box_Project_Members::output', // get markup from static method
			$this->post_type_slug, // post type to apply meta box
			'side', // position
			'default' // priority
		);

		// emails
		add_meta_box(
			$this->post_type_slug . '-email-notifications', // unique id based on post type
			__('My Email Notifications', 'project-huddle'), // title
			'PH_Meta_Box_Project_Email_Notifications::output', // get markup from static method
			$this->post_type_slug, // post type to apply meta box
			'side', // position
			'default' // priority
		);

		add_meta_box(
			'ph-project-approval-status', // unique id
			__('Approval Status', 'project-huddle'), // title
			function () {
				echo '<div class="meta-approval-wrap">';
				ph_approval_badge();
				ph_approval_progress_bar();
				echo '</div>';
			},
			'ph-project', // post type to apply meta box
			'side', // position
			'high' // priority
		);

		remove_meta_box('postcustom', $this->post_type_slug, 'normal');
		remove_meta_box('postcustom', $this->post_type_slug, 'side');
		remove_meta_box('postcustom', $this->post_type_slug, 'advanced');

		// hide editor on this page
		remove_post_type_support($this->post_type_slug, 'editor');
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param  int $post_id Project (Post) ID
	 * @param  object $post Project (Post) Object
	 *
	 * @return void
	 */
	public function save_meta_boxes($post_id, $post)
	{

		// $post_id and $post are required
		if (empty($post_id) || empty($post)) {
			return;
		}

		// verify nonce
		if (empty($_POST['project_huddle_meta_nonce']) || !wp_verify_nonce($_POST['project_huddle_meta_nonce'], 'project_huddle_save_data')) {
			return;
		}

		// Check that the user has correct permissions
		if (!$this->can_save_data($post_id)) {
			return;
		}

		// Don't save meta boxes for revisions or autosaves
		if (defined('DOING_AUTOSAVE') || is_int(wp_is_post_revision($post_id)) || is_int(wp_is_post_autosave($post_id))) {
			return;
		}

		// AJAX? Not used here
		if (defined('DOING_AJAX')) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if (empty($_POST['post_ID']) || $_POST['post_ID'] != $post_id) {
			return;
		}

		// init save action
		do_action('ph_save_metaboxes', $post_id, $post);
	}

	/**
	 * Determine if the current user has the relevant permissions
	 *
	 * @access private
	 * @since 1.0
	 *
	 * @param int $post_id Project (Post) ID
	 *
	 * @return bool If user can save data
	 */
	private function can_save_data($post_id)
	{

		// double check our post type and permissions
		if (get_post_type($post_id) == 'ph-project' && !current_user_can('edit_ph-project', $post_id)) {
			return false;
		}

		// double check our post type and permissions
		if (get_post_type($post_id) == 'ph-website' && !current_user_can('edit_ph-website', $post_id)) {
			return false;
		}


		// return true after checks
		return true;
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field($field, $post, $type = '')
	{
		$html = '';

		if (isset($field['prefix'])) {
			if ($field['prefix'] == false) {
				$option_name = $field['id'];
			} else {
				$option_name = $this->settings_base . $field['id'];
			}
		} else {
			$option_name = $this->settings_base . $field['id'];
		}

		if ($type != 'user') {
			$option = get_post_meta($post->ID, $option_name, true);
		} else {
			$option = get_user_meta(get_current_user_id(), $option_name, true);
		}

		$data = '';
		if (isset($field['default'])) {
			$data = $field['default'];
			if (!empty($option)) {
				$data = $option;
			}
		} else {
			$data = $option;
		}

		$html .= '<p class="form-field ' . $this->settings_base . $field['id'] . '">';

		if (isset($field['label']) && $field['label']) {
			$html .= '<label for="' . $this->settings_base . $field['id'] . '"><strong>' . $field['label'] . '</strong></label>';
		} else {
			$html .= '<label for="' . $this->settings_base . $field['id'] . '"></label>';
		}

		$html .= '<span class="ph-field ' . $field['type'] . '">';


		switch ($field['type']) {

			case 'text':
			case 'password':
			case 'number':
			case 'url':
				$html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="' . $data . '"/><br>' . "\n";
				break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr($field['id']) . '" type="text" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value=""/>' . "\n";
				break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr($field['id']) . '" rows="5" cols="50" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '">' . $data . '</textarea><br/>' . "\n";
				break;

			case 'checkbox':
				$checked = '';
				if (filter_var($data, FILTER_VALIDATE_BOOLEAN)) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . '/>' . "\n";
				$html .= '<span></span>';
				break;

			case 'checkbox_multi':
				foreach ($field['options'] as $k => $v) {
					$checked = false;
					if (is_array($data) && in_array($k, $data)) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'radio':
				foreach ($field['options'] as $k => $v) {
					$checked = false;
					if ($k == $data) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="radio" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'tab':

				// tab arg
				$arg = 'ph_tab';

				// be sure to store last saved tab
				$html .= '<input type="hidden" id="' . esc_attr($field['id']) . '" class="ph_current_tab" name="' . esc_attr($option_name) . '"  value="' . $data . '">';

				// get current url without our arg
				$base_url = remove_query_arg($arg, $_SERVER["REQUEST_URI"]);

				// get query arg
				if (isset($_REQUEST[$arg])) {
					$tab = sanitize_text_field($_REQUEST[$arg]);
				}

				// if no query arg, use stored data
				if (!$tab) {
					$tab = $data;
				}

				foreach ($field['options'] as $k => $v) {
					$active = '';
					if ($tab == $k) {
						$active = 'nav-tab-active';
					}
					$url      = esc_url(add_query_arg($arg, $k, $base_url)); // add our arg
					$html .= '<a href="' . $url . '" class="ph-tab ' . $active . ' nav-tab" data-value="' . $k . '" id="' . esc_attr($field['id'] . '_' . $k) . '"> ' . $v . '</a>';
				}
				break;

			case 'select':
				$html .= '<select class="' . $field['class'] . '" name="' . esc_attr($option_name) . '" id="' . esc_attr($field['id']) . '">';
				foreach ($field['options'] as $k => $v) {
					$selected = false;
					if ($k == $data) {
						$selected = true;
					}
					$html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_multi':
				$html .= '<select class="' . $field['class'] . '" name="' . esc_attr($option_name) . '[]" id="' . esc_attr($field['id']) . '" ';
				$html .= 'multiple="multiple">';
				foreach ($field['options'] as $k => $v) {
					if (is_array($v)) {
						$attributes = '';
						if (isset($v['attributes'])) {
							foreach ($v['attributes'] as $data => $value) {
								$attributes .= esc_attr($data) . '="' . esc_attr($value) . '" ';
							}
						}
						$html .= '<option selected value="' . esc_attr($k) . '"' . $attributes . '>' . $v['text'] . '</option> ';
					} else {
						$html .= '<option selected value="' . esc_attr($k) . '" >' . $v . '</option> ';
					}
				}
				$html .= '</select> ';
				break;

			case 'image':
				$image_thumb = '';
				if ($data) {
					$image_thumb = wp_get_attachment_thumb_url($data);
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __('Upload an image', 'project-huddle') . '" data-uploader_button_text="' . __('Use image', 'project-huddle') . '" class="image_upload_button button" value="' . __('New Image', 'project-huddle') . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="' . __('Remove image', 'project-huddle') . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
				break;

			case 'color':
?>

				<input type="text" name="<?php esc_attr_e($option_name); ?>" class="color-picker" data-default-color="<?php esc_attr_e($field['default']); ?>" value="<?php esc_attr_e($data); ?>" />

			<?php
				break;

			case 'divider':
			?>
				<hr>
			<?php
				break;

			case 'button':
			?>
				<a href="<?php echo esc_url($field['default']); ?>" class="button button-primary button-large">
					<?php echo esc_html($field['label']); ?>
				</a>
			<?php

			case 'message':
			?>
				<p>
					<?php echo esc_html($field['label']); ?>
				</p>
<?php

		}

		switch ($field['type']) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				if (isset($field['description'])) {
					$html .= '<br/><span class="description">' . $field['description'] . '</span>';
				}
				break;

			case 'tab':
				break;

			default:
				if (!empty($field['description'])) {
					$html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
				}
				break;
		}

		$html .= '</span></p>';

		echo $html;
	}
}
