<?php

/**
 * Register Settings
 *
 * @package     Project Huddle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_Settings
{

	/**
	 * Unique prefix to settings
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $settings_base = 'ph_';

	/**
	 * Stores settings within class
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $settings;

	/**
	 * Setup the options page
	 *
	 * Creates settings, menu items and settings link on plugin page
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		// Initialise settings
		add_action('admin_init', array($this, 'init'));

		// Register plugin settings
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_init', array($this, 'activate_license'));

		// Add settings page to menu
		add_action('admin_menu', array($this, 'add_menu_item'), 90);

		// Add settings link to plugins page
		add_filter('plugin_action_links_' . plugin_basename(PH_PLUGIN_FILE), array($this, 'add_settings_link'));
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init()
	{
		$this->settings = ph_settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_menu_item()
	{
		$page = add_submenu_page(
			'project-huddle',
			__('ProjectHuddle Settings', 'project-huddle'),
			__('Settings', 'project-huddle'),
			'manage_ph_settings',
			'project_huddle_settings',
			array($this, 'settings_page')
		);
		add_action('admin_print_styles-' . $page, array($this, 'settings_assets'));
	}

	/**
	 * Load settings JS & CSS
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function settings_assets()
	{

		// store javascript directory
		$js_dir = PH_PLUGIN_URL . 'assets/js/';

		// store css directory
		$css_dir = PH_PLUGIN_URL . 'assets/css/';

		// color picker
		wp_enqueue_style('wp-color-picker');

		// dialog
		wp_enqueue_script('jquery-ui');
		wp_enqueue_script('jquery-ui-dialog'); // jquery and jquery-ui should be dependencies, didn't check though...
		wp_enqueue_style('wp-jquery-ui-dialog');

		// Image Upload
		wp_enqueue_media();

		// color picker and image upload
		wp_register_script('ph-settings-admin', $js_dir . 'settings-scripts.js', array('wp-color-picker', 'jquery'), '1.0.0');
		wp_enqueue_script('ph-settings-admin');

		wp_enqueue_style('project-huddle', $css_dir . 'project-huddle-settings.css', false, PH_VERSION);

		// Select2 JS
		wp_register_script('select2', $js_dir . 'includes/select2.full.min.js', array('jquery'), '4.0.2');
		wp_enqueue_script('select2');
		wp_register_style('select2', $css_dir . 'includes/select2.min.css', array(), '4.0.2');
		wp_enqueue_style('select2');
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links
	 *
	 * @since 1.0.0
	 * @return array        Modified links
	 */
	public function add_settings_link($links)
	{
		$settings_link = '<a href="' . admin_url('admin.php?page=project_huddle_settings') . '">' . __('Settings', 'project-huddle') . '</a>';
		array_push($links, $settings_link);
		return $links;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings()
	{
		
		if (is_array($this->settings)) {
			foreach ($this->settings as $section => $data) {

				// Add section to page
				add_settings_section(
					$section,
					$data['title'],
					array($this, 'settings_section'),
					'project_huddle_settings_' . $section
				);
				
				foreach ($data['fields'] as $field) {
					$defaults = array(
						'id'       => false,
						'callback' => false,
						'type'     => false,
						'label'    => false,
					);
					$field    = wp_parse_args($field, $defaults);
					
;					// Validation callback for field
					$validation = '';
					if ($field['callback']) {
						$validation = array($this, $field['callback']);
					}
					// Register field
					$option_name = $this->settings_base . $field['id'];
					
					register_setting('project_huddle_settings_' . $section, $option_name, $validation);

					if ($field['type'] == 'divider') {
						$field['label'] = '<strong class="divider">' . $field['label'] . '</strong>';
					}

					// Add field to page
					add_settings_field(
						$field['id'],
						$field['label'],
						array($this, 'display_field'),
						'project_huddle_settings_' . $section,
						$section,
						array('field' => $field)
					);
				}
			}
		}
	}

	public function settings_section($section)
	{
		do_action('ph_settings_section_' . $section['id'], $section);

		if (!isset($this->settings[$section['id']]['description'])) {
			return false;
		}

		$html = '<p> ' . $this->settings[$section['id']]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field($args)
	{
		
		
		$field = $args['field'];

		$html = '';
	
		$option_name = $this->settings_base . $field['id'];
		$option      = get_option($option_name);

		$data = '';
		if (isset($field['default'])) {
			$data = $field['default'];
			if (!empty($option) || $option === '') {
				$data = $option;
			}
		}

		$required = '';
		if (!empty($field['required']) && is_array($field['required'])) {
			reset($field['required']);
			$required_key   = key($field['required']);
			$required_value = $field['required'][$required_key];

			$required .= 'data-required="' . $required_key . '" ';
			$required .= 'data-required-value="' . $required_value . '" ';
		}

		$html .= '<span id="' . $field['id'] . '" ' . $required . ' >';

		switch ($field['type']) {

			case 'text':
			case 'password':
			case 'number':
				$placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : false;
				$html       .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . $placeholder . '" value="' . $data . '"/>' . "\n";
				break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr($field['id']) . '" type="text" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value=""/>' . "\n";
				break;

			case 'textarea':
				$placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : false;
				$html       .= '<textarea id="' . esc_attr($field['id']) . '" rows="5" cols="50" name="' . esc_attr($option_name) . '" placeholder="' . $placeholder . '">' . $data . '</textarea><br/>' . "\n";
				break;

			case 'editor':
				wp_editor(
					$data,
					esc_attr($field['id']),
					apply_filters(
						'ph_editor_options',
						array(
							'tinymce'       => array(
								'content_css' => PH_PLUGIN_URL . 'assets/css/email-styles.css',
								'toolbar1'    => 'bold,italic,underline,bullist,numlist,blockquote,link,unlink,forecolor,undo,redo',
							),
							'textarea_rows' => isset($field['rows']) ? $field['rows'] : 10,
							'media_buttons' => false,
							'textarea_name' => esc_attr($this->settings_base . $field['id']),
						)
					)
				);
				break;

			case 'checkbox':
				$checked = '';
				if ($data && 'on' == $data) {
					$checked = 'checked="checked"';
				}
				if ('uninstall_data_on_delete' === $field['id']) {
					$html .= "
					<script>
					function ph_confirmation_delete(item) {
						if (item.checked == false) {
							return false;
						} else {
							var box= confirm('" . __('Are you sure you want to completely remove all data when deleting ProjectHuddle? This is irreversible!', 'project-huddlle') . "');
        					if (box==true)
            					return true;
        					else
           					item.checked = false;
						}
					}
					</script>
					";
					$html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . ' onchange="ph_confirmation_delete(this)" />' . "\n";
				} else {
					$html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . '/>' . "\n";
				}
				break;

			case 'checkbox_multi':
				foreach ($field['options'] as $k => $v) {
					$checked = false;
					
					if ( is_array( $data ) && in_array($k, $data)) {

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

					$html .= '<label class="radio" for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="radio" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> <br>';
				}
				break;

			case 'select':
				$html .= '<select name="' . esc_attr($option_name) . '" id="' . esc_attr($field['id']) . '">';
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
				$html .= '<select name="' . esc_attr($option_name) . '[]" id="' . esc_attr($field['id']) . '" class="ph-css-multi-select2" multiple="multiple" required>';
				foreach ($field['options'] as $k => $v) {
					$selected = false;
					if (in_array($k, $data)) {
						$selected = true;
					}
					$html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '" />' . $v . '</label> ';
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

			case 'custom':
				echo $field['html'];
				break;

			case 'button':
			?>
				<a href="<?php echo esc_url($field['default']); ?>" class="button button-primary button-large">
					<?php echo esc_html($field['label']); ?>
				</a>
				<?php
				break;

			case 'extensions':
				if (!empty($field['plugins'])) {
					foreach ($field['plugins'] as $extension) {
				?>
						<div class="plugin-card plugin-card-<?php echo sanitize_text_field($extension['id']); ?>">
							<div class="plugin-card-top">
								<div class="name column-name" style="margin-right: 0;">
									<h3>
										<a href="<?php echo esc_url($extension['url']); ?>">
											<?php echo esc_html($extension['label']); ?>
											<img src="https://ps.w.org/akismet/assets/icon-256x256.png?rev=969272" class="plugin-icon" alt="">
										</a>
									</h3>
								</div>
								<div class="desc column-description" style="margin-right: 0;">
									<p><?php echo wp_kses_post($extension['description']); ?></p>
									<p class="authors"> <cite>By <a href="https://projecthuddle.com">ProjectHuddle</a></cite></p>
								</div>
							</div>
							<div class="plugin-card-bottom">
								<div class="vers column-rating">
								</div>
								<div class="column-compatibility">
									<?php

									// if not installed
									if (!$this->plugin_installed($extension['name'])) :
									?>
										<a href="<?php echo esc_url($extension['url']); ?>" class="button button-primary" target="_blank">
											<?php _e('Get It', 'project-huddle'); ?>
										</a>
									<?php
									// if installed and not activated
									elseif ($this->plugin_installed($extension['name']) && !is_plugin_active($extension['plugin_dir'])) :
									?>
										<?php
										$activate_url = sprintf(admin_url('plugins.php?action=activate&plugin=%s&plugin_status=all&paged=1&s'), $extension['plugin_dir']);
										// change the plugin request to the plugin to pass the nonce check
										$_REQUEST['plugin'] = $extension['plugin_dir'];
										$activate_url       = wp_nonce_url($activate_url, 'activate-plugin_' . $extension['plugin_dir']);
										?>
										<a href="<?php echo esc_url($activate_url); ?>" class="button activate-now">
											<?php _e('Activate', 'project-huddle'); ?>
										</a>
									<?php
									// installed and activated
									elseif ($this->plugin_installed($extension['name']) && is_plugin_active($extension['plugin_dir'])) :
									?>
										<button type="button" class="button button-disabled" disabled="disabled">
											<?php _e('Active', 'project-huddle'); ?>
										</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
				<?php
					}
				}
				?>

				<?php
				break;

			case 'license':
				// get license status
				$status       = get_option('ph_license_status');
				$license_data = get_option('ph_license_data');

				if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {
					switch ($_GET['sl_activation']) {
						case 'false':
							$message = urldecode($_GET['message']);
				?>
							<div class="error">
								<p><?php echo wp_kses_post($message); ?></p>
							</div>
			<?php
							break;
						case 'true':
						default:
							// Developers can put a custom success message here for when activation is successful if they way.
							break;
					}
				}

				// get license input
				$html .= '<input id="' . esc_attr($field['id']) . '" type="password" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="' . $data . '"/>' . "\n";

				if ($status !== false && $status == 'valid') {
					$html .= '<span style="color:green;">' . __('Active', 'project-huddle') . '</span> ';
					if ($license_data->expires) {
						$expires = $license_data->expires !== 'lifetime' ? 'Expires ' . date(get_option('date_format'), strtotime($license_data->expires)) : 'Never Expires';

						$html .= ' <small>(' . esc_html($expires) . ')</small>';
					}
				} else {
					$html .= wp_nonce_field('ph_license_nonce', 'ph_license_nonce', true, false);
					$html .= '<input type="submit" class="button-secondary" name="ph_license_activate" value="' . __('Activate License', 'project-huddle') . '"/>';
					if ($status !== false && $status == 'invalid') {
						$html .= '<span style="color:red;">' . __('Invalid. Please double check there are no extra spaces in your license code.', 'project-huddle') . '</span>';
					}
				}

				break;
		}

		switch ($field['type']) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
			case 'license':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
				break;

			case 'extensions':
				break;

			default:
				if (!empty($field['description'])) {
					$html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
				}
				break;
		}

		if ('email_throttle' === $field['id'] && !get_option('monitor_receive_notification', false)) {

			ob_start(); ?>

			<div id="jetpack-dialog" class="hidden" style="max-width:400px">
				<p style="font-size: 15px; margin-top: 0;">To ensure emails send on time, we recommend that you</p>
				<ol style="font-size: 15px; font-weight: bold;">
					<li>Install Jetpack</li>
					<li>Enable Downtime Monitoring</li>
				</ol>
				<img src="<?php echo esc_url(PH()->url('assets/img/jetpack-monitor.png')); ?>" style="max-width: 100%; height: auto; margin-top: 10px;" />
				<a href="#" data-beacon-article-modal="5dcdc48e2c7d3a7e9ae3f9b7" style="text-align: center; display: block; margin: 5px 0 0;" target="_blank">Why do we recommend this?</a>
			</div>

			<!-- This script should be enqueued properly in the footer -->
			<script>
				(function($) {
					$(document).ready(function() {
						// initalise the dialog
						$('#jetpack-dialog').dialog({
							title: 'Recommendation',
							dialogClass: 'wp-dialog',
							autoOpen: false,
							draggable: false,
							width: 'auto',
							modal: true,
							resizable: false,
							closeOnEscape: true,
							position: {
								my: "center",
								at: "center",
								of: window
							},
							buttons: [
								// {
								// 	text: "Install Jetpack",
								// 	class: 'button button-primary',
								// 	autofocus: true,
								// 	click: function() {
								// 		var win = window.open('<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=jetpack'); ?>', '_blank');
								// 		win.focus();
								// 		$(this).dialog("close");
								// 	}
								// },

								{
									text: "Cancel",
									classes: 'button button-primary',
									click: function() {
										$(this).dialog("close");
										$('input[name="ph_email_throttle"][value="immediate"]').attr('checked', 'checked');
									}
								},
								{
									text: "Ok",
									class: 'button button-primary',
									autofocus: "autofocus",
									click: function() {
										$(this).dialog("close");
									}
								},
							],
							open: function() {
								// close dialog by clicking the overlay behind it
								$('.ui-widget-overlay').bind('click', function() {
									$('#jetpack-dialog').dialog('close');
									$('input[name="ph_email_throttle"][value="immediate"]').attr('checked', 'checked');
								});
							},
							create: function() {
								// style fix for WordPress admin
								$('.ui-dialog-titlebar-close').addClass('ui-button');
							},
						});
						// bind a button or a link to open the dialog
						$('input[name="ph_email_throttle"]').click(function(e) {
							var value = $(this).val();
							if ('immediate' !== value && 'off' !== value) {
								$('#jetpack-dialog').dialog('open');
							}
						});
					});
				})(jQuery);
			</script>

		<?php
			$html .= ob_get_clean();
		}

		$html .= '</span>';

		echo $html;
	}

	/**
	 * Validate individual settings field
	 *
	 * @param  string $data Inputted value
	 * @return string       Validated value
	 */
	public function validate_license($data)
	{
		$old = get_option('ph_license_key');
		if ($old && $old != $data) {
			delete_option('ph_license_status'); // new license has been entered, so must reactivate
		}
		return $data;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page()
	{

		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'customize';

		ob_start();
		?>

		<div class="wrap" id="project_huddle_settings">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ($this->settings as $section => $data) {

					$tab_url = add_query_arg(
						'tab',
						$section,
						remove_query_arg('settings-updated')
					);

					$active = $active_tab == $section ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url($tab_url) . '" title="' . esc_attr($data['title']) . '" class="nav-tab' . $active . '">';
					echo esc_html($data['title']);
					echo '</a>';
				}
				?>
			</h2>
<?php $_REQUEST['ph_admin_nonce'] = wp_create_nonce( 'ph_admin_nonce' ); ?>
			<div id="tab_container">
				<form method="post" action="options.php">
					<?php
					settings_fields('project_huddle_settings_' . $active_tab);
					do_settings_sections('project_huddle_settings_' . $active_tab);
					submit_button();
					?>
				</form>
			</div>
			<!-- #tab_container-->
		</div><!-- .wrap -->

	<?php
	if ( isset( $_REQUEST['ph_admin_nonce'] ) && wp_verify_nonce( $_REQUEST['ph_admin_nonce'], 'ph_admin_nonce' ) ) {
		if( isset( $_REQUEST['settings-updated'] ) ) {
			?>
			<div id="message" class="notice notice-success is-dismissible ph-setting-admin-notice"><p> <?php esc_html_e( 'Settings saved.', 'project-huddle' ); ?> </p></div>
			<?php 
		}
	}
	// output it
	echo ob_get_clean();
	}
	
	public function plugin_installed($name = '')
	{
		// get all the plugins
		$installedPlugins = get_plugins();

		foreach ($installedPlugins as $installedPlugin => $data) {
			// check for the plugin title
			if ($data['Title'] == $name) {
				return $data;
			}
		}

		return false;
	}

	public function activate_license()
	{
		// listen for our activate button to be clicked
		if (isset($_POST['ph_license_activate'])) {
			// run a quick security check
			if (!check_admin_referer('ph_license_nonce', 'ph_license_nonce')) {
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim($_POST['ph_license_key']);

			// data to send in our API request using the id
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_id'    => PH_SL_ITEM_ID,
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				PH_SL_STORE_URL,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay
			if (is_wp_error($response)) {
				$message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.');
			} else {
				// decode the license data
				$license_data = json_decode(wp_remote_retrieve_body($response));
				if (false === $license_data->success) {
					switch ($license_data->error) {
						case 'expired':
							$message = sprintf(
								__('Your license key expired on %s. Please renew your license for updates.'),
								date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
							);
							break;
						case 'revoked':
							$message = __('Your license key has been disabled. Please contact support@projecthuddle.com for more information.');
							break;
						case 'missing':
							$message = __('Invalid license. Please double-check to make sure you\'ve entered it correctly!');
							break;
						case 'invalid':
						case 'site_inactive':
							$message = __('Your license is not active for this URL. Please update the active site on projecthuddle.com.');
							break;
						case 'item_name_mismatch':
							$message = sprintf(__('This appears to be an invalid license key for %s.'), EDD_SAMPLE_ITEM_NAME);
							break;
						case 'no_activations_left':
							$message = __('Your license key has reached its activation limit. You can upgrade your account on projecthuddle.com to enable more activations.');
							break;
						default:
							$message = __('An error occurred, please try again.');
							break;
					}
				}
			}

			// Check if anything passed on a message constituting a failure
			if (!empty($message)) {
				$base_url = admin_url('admin.php?page=project_huddle_settings&tab=updates');
				$redirect = add_query_arg(
					array(
						'sl_activation' => 'false',
						'message'       => urlencode($message),
					),
					$base_url
				);
				wp_redirect($redirect);
				exit();
			}

			if (!empty($_GET['message'])) {
				unset($_GET['message']);
			}

			// store all data
			update_option('ph_license_data', $license_data);

			// save license
			update_option('ph_license_key', $license);

			// $license_data->license will be either "active" or "inactive"
			update_option('ph_license_status', $license_data->license);

			// update key
			update_option('ph_license_key', $license);

			// license activated
			do_action('ph_license_activated', $license_data, $license);

			// redirect
			wp_redirect(admin_url('admin.php?page=project_huddle_settings&tab=updates'));
			exit();
		}
	}
}
