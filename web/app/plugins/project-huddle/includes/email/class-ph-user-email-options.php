<?php

/**
 * User email subscribe/unsubscribe options
 */

class PH_User_Email_Options
{
	/**
	 * Holds the options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * User options prefix
	 *
	 * @var string
	 */
	protected $prefix = 'ph_';

	/**
	 * Start
	 */
	public function __construct()
	{
		$this->options = $this->options();

		// show fields
		add_action('show_user_profile', array($this, 'profile_fields'), 1);
		add_action('edit_user_profile', array($this, 'profile_fields'), 1);

		// save fields
		add_action('personal_options_update', array($this, 'save_fields'), 1);
		add_action('edit_user_profile_update', array($this, 'save_fields'), 1);

		// save front-end fields
		add_action('admin_post_ph_update_user_preferences', array($this, 'save_public_fields'), 1);

		// front-end template
		add_filter('template_redirect', array($this, 'user_settings_template'), 0);
		// add front-end template query-var
		add_filter('query_vars', array($this, 'user_query_var'));
		// load user settings styles
		add_action('wp_enqueue_scripts', array($this, 'load_user_styles'));
		// include script enqueues in custom header
		add_action('ph_user_header', 'wp_enqueue_scripts');
		// add styles
		add_action('ph_user_header', array($this, 'user_header_styles'));
	}

	/**
	 * Maybe bail on suppressions for activity
	 *
	 * @param integer $user_id
	 * @return void
	 */
	public function batch_suppressions($suppress, $user_id)
	{
		if (doing_filter('ph_disable_activity_emails')) {
			if (get_user_meta($user_id, 'ph_activity', true) === 'off') {
				return true;
			}
		}
		if (doing_filter('ph_disable_daily_emails')) {
			if (get_user_meta($user_id, 'ph_daily', true) === 'off') {
				return true;
			}
		}
		if (doing_filter('ph_disable_weekly_emails')) {
			if (get_user_meta($user_id, 'ph_weekly', true) === 'off') {
				return true;
			}
		}
		return $suppress;
	}

	public function disable_by_project($disabled, $post_id, $emails)
	{
		foreach ($emails as $email) {
			$user = get_user_by('email', $email);

			if ($user && $post_id) {
				$user_suppressions = get_user_meta($user->ID, 'ph_project_email_notifications_disable_all', true);

				// disable if in suppressions
				if ($user_suppressions && is_array($user_suppressions) && in_array($post_id, $user_suppressions)) {
					return true;
				}
			}
		}

		return $disabled;
	}

	/**
	 * Field options
	 * @return array
	 */
	public function options()
	{
		$options = [
			'activity'  => [
				'label'       => __('Activity', 'project-huddle'),
				'description' => __('Email me activity from projects I\'m following.', 'project-huddle'),
				'when' => PH()->activity_emails->is_throttled()
			],
			'daily' => [
				'label'       => __('Daily Summary', 'project-huddle'),
				'description' => __('Send me daily email summaries.', 'project-huddle'),
				'when' => PH()->activity_emails->is_throttled()
			],
			'weekly' => [
				'label'       => __('Weekly Summary', 'project-huddle'),
				'description' => __('Send me weekly email summaries.', 'project-huddle'),
				'when' => PH()->activity_emails->is_throttled()
			],
			'comments'          => [
				'label'       => __('Project Comments', 'project-huddle'),
				'description' => __('Email me all new comments on projects I\'m a member of.', 'project-huddle'),
				'when' => !PH()->activity_emails->is_throttled()
			],
			'image_approvals'   => [
				'label'       => __('Image Approvals', 'project-huddle'),
				'description' => __('Email me when a project image is approved/unapproved.', 'project-huddle'),
				'when' => !PH()->activity_emails->is_throttled()
			],
			'project_approvals' => [
				'label'       => __('Project Approvals', 'project-huddle'),
				'description' => __('Email me when an entire project is approved/unapproved.', 'project-huddle'),
				'when' => !PH()->activity_emails->is_throttled()
			],
			'resolves'          => [
				'label'       => __('Resolve Actions', 'project-huddle'),
				'description' => __('Email me when a conversation thread is resolved/unresolved.', 'project-huddle'),
				'when' => !PH()->activity_emails->is_throttled()
			],
			'assigns'           => [
				'label'       => __('Assignments', 'project-huddle'),
				'description' => __('Email me when I\'ve been assigned a conversation thread.', 'project-huddle'),
				'when' => !PH()->activity_emails->is_throttled()
			]
		];

		return apply_filters('ph_user_email_options', $options);
	}

	/**
	 * Show Fields
	 *
	 * @param WP_User $user User object of profile
	 */
	public function profile_fields($user)
	{
		if (!user_can($user, 'read_private_ph-websites') && !user_can($user, 'read_private_ph-projects')) {
			return;
		}
?>

		<h3 id="ph-emails"><?php _e("ProjectHuddle Global Email Settings", "project-huddle"); ?></h3>
		<p class="description"><?php _e('These settings will apply to all projects.', 'project-huddle'); ?></p>

		<table class="form-table">
			<?php foreach ($this->options as $id => $option) : if (!$option['when']) continue;  ?>
				<?php $pid = $this->prefix . $id; ?>
				<tr class="<?php echo sanitize_html_class($pid); ?>">
					<th scope="row"><?php echo esc_html($option['label']); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php echo esc_html($option['description']); ?></span></legend>
							<label for="<?php echo esc_attr($pid); ?>">
								<input name="<?php echo esc_attr($pid); ?>" type="checkbox" id="<?php echo esc_attr($pid); ?>" <?php echo get_the_author_meta($pid, $user->ID) !== 'off' ? 'checked="checked"' : ''; ?>>
								<?php echo esc_html($option['description']); ?></label><br>
						</fieldset>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

<?php
	}

	/**
	 * Save the fields
	 *
	 * @param int $user_id User id
	 *
	 * @return bool
	 */
	public function save_fields($user_id = 0)
	{
		// must be able to edit their own user account
		if (!current_user_can('edit_user', $user_id)) {
			return false;
		}

		// update user meta
		foreach ($this->options as $id => $option) {
			$value = isset($_POST[$this->prefix . $id]) && $_POST[$this->prefix . $id] ? 'on' : 'off'; // sanitize
			update_user_meta($user_id, $this->prefix . $id, $value); // update meta
		}

		return true;
	}

	/**
	 * Add our query vars
	 *
	 * @param $query_vars array Array of vars
	 *
	 * @return array New array with ours added
	 */
	public function user_query_var($query_vars)
	{
		$query_vars[] = 'ph_user_settings'; // our API key
		return $query_vars;
	}

	/**
	 * Front-end user settings template
	 */
	public function user_settings_template()
	{
		global $wp;
		// Check if the single post type is being viewed and check if template already exists
		if (isset($wp->query_vars['ph_user_settings']) && $wp->query_vars['ph_user_settings']) {
			show_admin_bar(false);
			if (!PH()->activity_emails->is_throttled()) {
				include PH_PLUGIN_DIR . 'templates/single-user.php';
			} else {
				include PH_PLUGIN_DIR . 'templates/single-user-batch.php';
			}
			exit;
		}
	}

	/**
	 * Save front-end fields
	 */
	public function save_public_fields()
	{
		// check nonce
		if (
			!isset($_POST['ph_user_preferences'])
			|| !wp_verify_nonce($_POST['ph_user_preferences'], 'ph_update_email_preferences')
		) {
			wp_die('Something went wrong. Please try again.');
		}

		$this->save_fields(get_current_user_id());
		ph_add_message(__('Updated', 'projecthuddle'));
		wp_safe_redirect(esc_url(get_home_url() . '/?ph_user_settings=1'));
	}

	/**
	 * Dynamic function caller
	 */
	public function __call($function, $args)
	{
		$emails = isset($args[0]) ? $args[0] : array();
		// return if not user meta
		if (!isset($this->options[$function])) {
			return $emails;
		}

		// return if no emails
		if (empty($emails)) {
			return $emails;
		}

		// loop through each email
		foreach ($emails as $key => $email) {
			if ($user = get_user_by('email', $email)) {
				// remove if turned off
				if (get_user_meta($user->ID, 'ph_' . $function, true) === 'off') {
					unset($emails[$key]);
				}
			}
		}

		// return maybe filtered emails
		return $emails;
	}

	/**
	 * Register user settings
	 */
	public function load_user_styles()
	{
		// store css directory
		$css_dir = PH_PLUGIN_URL . 'assets/css/';

		wp_register_style('ph-user-settings', $css_dir . '/dist/ph-user-settings.css', array(), '0.5.0 ');

		// add custom color
		$color      = esc_html(get_option('ph_highlight_color', '#4353ff'));
		$color_rgba = ph_hex2rgb(get_option('ph_highlight_color', '#4353ff'));
		$custom_css = "
		.form-checkbox input:checked + .form-icon, 
		.form-radio input:checked + .form-icon, 
		.form-switch input:checked + .form-icon {
            background: {$color};
            border-color: {$color};
        }
        .toast.toast-primary {
            background: {$color};
            border-color: {$color};
        }
        .form-switch input:focus + .form-icon {
          box-shadow: 0 0 0 .1rem rgba({$color_rgba}, .2);
          border-color: {$color};
        }
        .form-switch input:checked + .form-icon {
          background: {$color};
          border-color: {$color};
        }
        .btn.btn-primary {
            background: {$color};
            border-color: {$color};
        }
        .btn.btn-primary:focus, .btn.btn-primary:hover {
            background: {$color};
            border-color: {$color};
        }
        .btn:focus {
            box-shadow: 0 0 0 0.1rem rgba({$color_rgba}, 0.2);
        }
        ";
		wp_add_inline_style('ph-user-settings', $custom_css);
	}

	/**
	 * Add user header styles
	 */
	public function user_header_styles()
	{
		global $wp_styles;

		$styles = array(
			'ph-user-settings',
		);

		$allowed = apply_filters('ph_allowed_user_header_styles', array());
		if (!empty($allowed)) {
			foreach ($allowed as $style) {
				$styles[] = $style;
			}
		}

		$wp_styles->do_items(apply_filters('ph_user_header_styles', $styles));
	}
}
