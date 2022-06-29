<?php

/**
 * REST API: PH_REST_Key_Pair class.
 *
 * This class is responsible for adding/revoking a users API key:secret pairs.
 *
 * This class only allows REST authentication to the `{api_prefix}/projecthuddle/v2/token`
 * endpoint with an API key:secret. This allows a user to be identified via the
 * REST API without using their login credentials to generate a JSON Web Token.
 *
 * @package ProjectHuddle
 * @subpackage REST_API
 * @since 3.3.0
 */

// require wp-config library
require_once PH_PLUGIN_DIR . 'includes/libraries/wp-config-transformer/wp-config-transformer.php';

/**
 * Core class used to manage REST API key-pairs which are used to generate JSON Web Tokens.
 *
 * @since 3.3.0
 */
class PH_REST_Key_Pair
{

	/**
	 * The user meta key-pair key.
	 *
	 * @since 3.3.0
	 * @type string
	 */
	const _USERMETA_KEY_ = 'ph_key_pairs';

	/**
	 * The namespace of the authentication route.
	 *
	 * @since 3.3.0
	 * @type string
	 */
	const _NAMESPACE_ = 'projecthuddle/v2';

	/**
	 * The base of the authentication route.
	 *
	 * @since 3.3.0
	 * @type string
	 */
	const _REST_BASE_ = 'key-pair';

	/**
	 * Initializes the class.
	 *
	 * @since 3.3.0
	 *
	 * @see add_action()
	 * @see add_filter()
	 */
	public function init()
	{
		add_action('rest_api_init', array($this, 'register_routes'), 99);
		add_action('admin_init', array($this, 'maybe_create_constant'), 9);
		add_action('show_user_profile', array($this, 'show_user_profile'));
		add_action('edit_user_profile', array($this, 'show_user_profile'));
		add_action('after_password_reset', array($this, 'after_password_reset'));
		add_action('profile_update', array($this, 'profile_update'));

		add_filter('rest_authentication_require_token', array($this, 'require_token'), 10, 3);
		add_filter('rest_authentication_user', array($this, 'authenticate'), 10, 2);
		add_filter('rest_authentication_token_private_claims', array($this, 'payload'), 10, 2);
		add_filter('rest_authentication_validate_token', array($this, 'validate_token'));
	}

	/**
	 * Return the REST URI for the endpoint.
	 *
	 * @since 3.3.0
	 *
	 * @static
	 */
	public static function get_rest_uri()
	{
		$blog_id = get_current_blog_id();
		$prefix  = 'index.php?rest_route=';

		if (is_multisite() && get_blog_option($blog_id, 'permalink_structure') || get_option('permalink_structure')) {
			$prefix = rest_get_url_prefix();
		}

		return sprintf('%s/%s/%s', untrailingslashit(esc_url_raw(get_rest_url())), self::_NAMESPACE_, self::_REST_BASE_);
	}

	/**
	 * Registers the routes for the authentication method.
	 *
	 * @since 3.3.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes()
	{
		$args = array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array($this, 'generate_key_pair'),
			'permission_callback' => 'is_user_logged_in',
			'args'     => array(
				'name'    => array(
					'description'       => esc_html__('The name of the key-pair.', 'project-huddle'),
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'user_id' => array(
					'description'       => esc_html__('The ID of the user.', 'project-huddle'),
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
					'validate_callback' => 'rest_validate_request_arg',
				),
			),
			'schema'   => array($this, 'get_item_schema'),
		);
		register_rest_route(self::_NAMESPACE_, '/' . self::_REST_BASE_ . '/(?P<user_id>[\d]+)', $args);

		$args = array(
			'methods'  => WP_REST_Server::DELETABLE,
			'callback' => array($this, 'delete_all_key_pairs'),
			'permission_callback' => 'is_user_logged_in',
			'args'     => array(
				'user_id' => array(
					'description'       => esc_html__('The ID of the user.', 'project-huddle'),
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
					'validate_callback' => 'rest_validate_request_arg',
				),
			),
		);
		register_rest_route(self::_NAMESPACE_, '/' . self::_REST_BASE_ . '/(?P<user_id>[\d]+)/revoke-all', $args);

		$args = array(
			'methods'  => WP_REST_Server::DELETABLE,
			'callback' => array($this, 'delete_key_pair'),
			'permission_callback' => 'is_user_logged_in',
			'args'     => array(
				'user_id' => array(
					'description'       => esc_html__('The ID of the user.', 'project-huddle'),
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'api_key' => array(
					'description'       => esc_html__('The API key being revoked.', 'project-huddle'),
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
			),
		);
		register_rest_route(self::_NAMESPACE_, '/' . self::_REST_BASE_ . '/(?P<user_id>[\d]+)/(?P<api_key>[\w-]+)/revoke', $args);
	}

	/**
	 * Retrieves the item schema, conforming to JSON Schema.
	 *
	 * @since 3.3.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema()
	{
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => esc_html__('Key-pair', 'project-huddle'),
			'type'       => 'object',
			'properties' => array(
				'api_secret' => array(
					'description' => esc_html__('The raw API secret, which is not stored in the database.', 'project-huddle'),
					'type'        => 'string',
					'readonly'    => true,
				),
				'row'        => array(
					'description' => esc_html__('The stored key-pair data.', 'project-huddle'),
					'type'        => 'object',
					'readonly'    => true,
					'properties'  => array(
						'name'       => array(
							'description' => esc_html__('The name of the key-pair.', 'project-huddle'),
							'type'        => 'string',
							'readonly'    => true,
						),
						'api_key'    => array(
							'description' => esc_html__('The API key.', 'project-huddle'),
							'type'        => 'string',
							'readonly'    => true,
						),
						'api_secret' => array(
							'description' => esc_html__('The hashed API secret.', 'project-huddle'),
							'type'        => 'string',
							'readonly'    => true,
						),
						'created'    => array(
							'description' => esc_html__('The date the key-pair was created.', 'project-huddle'),
							'type'        => 'string',
							'readonly'    => true,
						),
						'last_used'  => array(
							'description' => esc_html__('The last time the key-pair was used.', 'project-huddle'),
							'type'        => 'string',
							'readonly'    => true,
						),
						'last_ip'    => array(
							'description' => esc_html__('The last IP address that used the key-pair.', 'project-huddle'),
							'type'        => 'string',
							'readonly'    => true,
						),
					),
				),
			),
		);

		/**
		 * Filters the REST endpoint schema.
		 *
		 * @param string $schema The endpoint schema.
		 */
		return apply_filters('rest_authentication_key_pair_schema', $schema);
	}


	/**
	 * Find the correct wp-config.php file. It supports one-level up.
	 *
	 * @since 3.4.0
	 *
	 * @return string|bool The path of the wp-config.php or false if it's not found
	 */
	public function config_file_path()
	{
		$salts_file_name = apply_filters('ph_wp_config_file_name', 'wp-config');
		$config_file = ABSPATH . $salts_file_name . '.php';
		$config_file_up = ABSPATH . '../' . $salts_file_name . '.php';
		if (file_exists($config_file) && is_writable($config_file)) {
			return $config_file;
		} elseif (file_exists($config_file_up) && is_writable($config_file_up) && !file_exists(dirname(ABSPATH) . '/wp-settings.php')) {
			return $config_file_up;
		}
		return false;
	}

	/**
	 * Maybe create constant in wp-config
	 *
	 * @since 3.3.0
	 */
	public function maybe_create_constant()
	{
		try {
			$config_transformer = new WPConfigTransformer($this->config_file_path());
		} catch (Exception $e) {
			return; // fail silently
		}


		// check to see if it exists
		$exists = false;
		try {
			$exists = $config_transformer->exists('constant', 'PH_SECURE_AUTH_KEY');
		} catch (Exception $e) {
			return; // fail silently
		}

		// if not yet in configuration, create it!
		if (!$exists) {
			try {
				$config_transformer->add('constant', 'PH_SECURE_AUTH_KEY', wp_generate_password(64, true, true));
			} catch (Exception $e) {
				return; // fail silently
			}
		}
	}

	/**
	 * Display the key-pair section in a users profile.
	 *
	 * @since 3.3.0
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function show_user_profile(WP_User $user)
	{
		wp_enqueue_script('ph-key-pair-js', PH_PLUGIN_URL . 'assets/js/dist/ph-key-pair.js', array(), PH_VERSION, true);
		wp_enqueue_style('ph-key-pair-css', PH_PLUGIN_URL . 'assets/css/dist/ph-key-pair.css', array(), PH_VERSION);
		wp_localize_script(
			'ph-key-pair-js',
			'keyPair',
			array(
				'nonce'   => wp_create_nonce('wp_rest'),
				'root'    => self::get_rest_uri(),
				'token'   => PH_REST_Token::get_rest_uri(),
				'user_id' => $user->ID,
				'text'    => array(
					/* translators: %s: key-pair name */
					'confirm_one' => esc_html__('Revoke the %s key-pair? This action cannot be undone.', 'project-huddle'),
					'confirm_all' => esc_html__('Revoke all key-pairs? This action cannot be undone.', 'project-huddle'),
				),
			)
		);
		$this->show_key_pair_section($user);
		$this->template_new_key_pair();
		$this->template_new_token_key_pair();
		$this->template_key_pair_row();
	}

	/**
	 * Fires after the user's password is reset.
	 *
	 * @param WP_User $user The user.
	 */
	public function after_password_reset(WP_User $user)
	{
		if ('after_password_reset' !== current_filter()) {
			return;
		}

		$keypairs = $this->get_user_key_pairs($user->ID);
		if (!empty($keypairs)) {
			$this->set_user_key_pairs($user->ID, array());
		}
	}

	/**
	 * Fires after the user's password is reset.
	 *
	 * When a user resets their password this method will deleted all of
	 * the application passwords associated with their account. In turn
	 * this will renders all JSON Web Tokens invalid for their account
	 *
	 * @param int $user_id The user ID.
	 */
	public function profile_update($user_id)
	{
		if ('profile_update' !== current_filter()) {
			return;
		}

		if (isset($_POST['pass1']) && !empty($_POST['pass1'])) { // phpcs:ignore
			$keypairs = $this->get_user_key_pairs($user_id);
			if (!empty($keypairs)) {
				$this->set_user_key_pairs($user_id, array());
			}
		}
	}

	/**
	 * Filters `rest_authentication_require_token` to exclude the key-pair endpoint,
	 *
	 * @param bool   $require_token Whether a token is required.
	 * @param string $request_uri The URI which was given by the server.
	 * @param string $request_method Which request method was used to access the server.
	 *
	 * @return bool
	 */
	public function require_token($require_token, $request_uri, $request_method)
	{

		// Don't require token authentication to manage key-pairs.
		if (('POST' === $request_method || 'DELETE' === $request_method) && strpos($request_uri, sprintf('/%s/%s', self::_NAMESPACE_, self::_REST_BASE_))) {
			$require_token = false;
		}

		return $require_token;
	}

	/**
	 * Authenticate the key-pair if API key and API secret is provided and return the user.
	 *
	 * If not authenticated, send back the original $user value to allow other authentication
	 * methods to attempt authentication. If the initial value of `$user` is false this method
	 * will return a `WP_User` object on success or a `WP_Error` object on failure. However,
	 * if the value is not `false` it will return that value, which could be any type of object.
	 *
	 * @filter rest_authentication_user
	 *
	 * @param mixed           $user    The user that's being authenticated.
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return bool|object|mixed
	 */
	public function authenticate($user, WP_REST_Request $request)
	{

		if (false !== $user) {
			return $user;
		}

		$key    = $request->get_param('api_key');
		$secret = $request->get_param('api_secret');

		if (!$key || !$secret) {
			return $user;
		}

		// Retrieves a user if a valid key & secret is given.
		$get_user = get_users(
			array(
				'meta_key'   => $key, // phpcs:ignore
				'meta_value' => wp_hash($secret), // phpcs:ignore
			)
		);

		$get_user = is_array($get_user) && !empty($get_user) ? array_shift($get_user) : false;

		if (false === $get_user) {
			return new WP_Error(
				'rest_authentication_invalid_api_key_secret',
				__('The API key-pair is invalid.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		$found    = false;
		$keypairs = $this->get_user_key_pairs($get_user->ID);
		foreach ($keypairs as $_key => $item) {
			if (isset($item['api_key']) && $item['api_key'] === $key) {
				$keypairs[$_key]['last_used'] = time();

				$ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP) : null;
				if ($ip) {
					$keypairs[$_key]['last_ip'] = $ip;
				}
				$this->set_user_key_pairs($get_user->ID, $keypairs);
				$found = true;
				break;
			}
		}

		if (false === $found) {
			return new WP_Error(
				'rest_authentication_revoked_api_key',
				__('Token is invalid the API key has been revoked.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		// Add the api_key to use when encoding the JWT.
		$get_user->data->api_key = $key;

		return $get_user;
	}

	/**
	 * Filters the JWT Payload.
	 *
	 * Due to the fact that `$user` could have been filtered the object type is technically
	 * unknown. However, likely a `WP_User` object if auth has not been filtered. In any
	 * case, the object must have the `$user->data->api_key` property in order to connect
	 * the API key to the JWT payload and allow for token invalidation.
	 *
	 * @filter rest_authentication_token_private_claims
	 *
	 * @param array          $payload The payload used to generate the token.
	 * @param WP_User|Object $user The authenticated user object.
	 *
	 * @return array
	 */
	public function payload($payload, $user)
	{

		// Set the api_key. which we use later to validate a key-pair has not already been revoked.
		if (isset($user->data->api_key) && isset($payload['data']['user'])) {
			$payload['data']['user']['api_key'] = $user->data->api_key;
		}

		return $payload;
	}

	/**
	 * Authenticate the key-pair if API key and API secret is provided and return the user.
	 *
	 * If not authenticated, send back the original $user value to allow other authentication
	 * methods to attempt authentication.
	 *
	 * @filter rest_authentication_validate_token
	 *
	 * @param object $jwt The JSON Web Token.
	 *
	 * @return object|WP_Error
	 */
	public function validate_token($jwt)
	{

		if (!isset($jwt->data->user->api_key) || !isset($jwt->data->user->id)) {
			return $jwt;
		}

		$found    = false;
		$keypairs = $this->get_user_key_pairs($jwt->data->user->id);
		foreach ($keypairs as $key => $item) {
			if (isset($item['api_key']) && $item['api_key'] === $jwt->data->user->api_key) {
				$found = true;
				break;
			}
		}

		if (false === $found) {
			return new WP_Error(
				'rest_authentication_revoked_api_key',
				__('Token is invalid the API key has been revoked.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		return $jwt;
	}

	public function generate_core_key_pair(\WP_User $user)
	{
		if (!$user->ID) {
			return;
		}
		$request = new WP_REST_Request('POST', '/projecthuddle/v2/key-pair/');
		$request->set_param('name', 'core');
		$request->set_param('user_id', $user->ID);
		return $this->generate_key_pair($request);
	}

	/**
	 * Generate new API key-pair for user.
	 *
	 * A user must be logged in and have permission to create a key-pair.
	 * This means a request must be made in the wp-admin using a nonce and
	 * ajax, or through some other means of authentication like basic-auth.
	 *
	 * @param WP_REST_Request $request The requests.
	 *
	 * @return object|\WP_Error The key-pair or error.
	 */
	public function generate_key_pair(WP_REST_Request $request)
	{
		$name    = $request->get_param('name');
		$user_id = $request->get_param('user_id');
		$user    = get_user_by('id', $user_id);

		if (empty($name)) {
			return new WP_Error(
				'rest_authentication_required_name_error',
				__('The key-pair name is required.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		if (false === $user || !($user instanceof WP_User)) {
			return new WP_Error(
				'rest_authentication_invalid_user_error',
				__('The user does not exist.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		if (!current_user_can('edit_user', $user_id)) {
			return new WP_Error(
				'rest_authentication_edit_user_error',
				__('You do not have permission to edit this user.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		$api_key       = $user->ID . wp_generate_password(24, false);
		$api_secret    = wp_generate_password(32);
		$hashed_secret = wp_hash($api_secret);

		$new_item = array(
			'name'       => $name,
			'api_key'    => $api_key,
			'api_secret' => $hashed_secret,
			'created'    => time(),
			'last_used'  => null,
			'last_ip'    => null,
		);

		$keypairs   = $this->get_user_key_pairs($user_id);
		$keypairs[] = $new_item;
		$this->set_user_key_pairs($user_id, $keypairs);

		$new_item['created']   = date('F j, Y g:i a', $new_item['created']);
		$new_item['last_used'] = '—';
		$new_item['last_ip']   = '—';

		return json_decode(
			wp_json_encode(
				array(
					'api_secret' => $api_secret,
					'row'        => $new_item,
				)
			)
		);
	}

	/**
	 * Delete API key-pair for user.
	 *
	 * @param WP_REST_Request $request The requests.
	 *
	 * @return bool|WP_Error Whether the key-pair was deleted or error.
	 */
	public function delete_key_pair(WP_REST_Request $request)
	{
		$api_key = $request->get_param('api_key');
		$user_id = $request->get_param('user_id');

		if (!current_user_can('edit_user', $user_id)) {
			return new WP_Error(
				'rest_authentication_edit_user_error',
				__('You do not have permission to edit this user.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		$keypairs = $this->get_user_key_pairs($user_id);
		foreach ($keypairs as $key => $item) {
			if (isset($item['api_key']) && $item['api_key'] === $api_key) {
				unset($keypairs[$key]);
				$this->set_user_key_pairs($user_id, $keypairs);
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete all API key-pairs for a user.
	 *
	 * @param WP_REST_Request $request The requests.
	 *
	 * @return bool|WP_Error Number of key-pairs deleted or error.
	 */
	public function delete_all_key_pairs(WP_REST_Request $request)
	{
		$user_id = $request->get_param('user_id');

		if (!current_user_can('edit_user', $user_id)) {
			return new WP_Error(
				'rest_authentication_edit_user_error',
				__('You do not have permission to edit this user.', 'project-huddle'),
				array(
					'status' => 403,
				)
			);
		}

		$keypairs = $this->get_user_key_pairs($user_id);
		if (!empty($keypairs)) {
			$this->set_user_key_pairs($user_id, array());
			return count($keypairs);
		}

		return 0;
	}

	/**
	 * Get a users key-pairs.
	 *
	 * @since 3.3.0
	 *
	 * @param int $user_id User ID.
	 * @return array
	 */
	public function get_user_key_pairs($user_id)
	{
		$keypairs = get_user_meta($user_id, self::_USERMETA_KEY_, true);

		if (!is_array($keypairs)) {
			return array();
		}

		return $keypairs;
	}

	/**
	 * Set a users keypairs.
	 *
	 * @since 3.3.0
	 *
	 * @param int   $user_id User ID.
	 * @param array $keypairs Keypairs.
	 *
	 * @return bool
	 */
	public function set_user_key_pairs($user_id, $keypairs)
	{
		if (is_array($keypairs) && !empty($keypairs)) {
			foreach ($keypairs as $keypair) {
				if (isset($keypair['api_key']) && isset($keypair['api_secret'])) {
					add_user_meta($user_id, $keypair['api_key'], $keypair['api_secret'], true);
				}
			}
		}

		return update_user_meta($user_id, self::_USERMETA_KEY_, array_values($keypairs));
	}

	/**
	 * The key-pair section.
	 *
	 * @since 3.3.0
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function show_key_pair_section(WP_User $user)
	{
?>
		<div class="key-pairs hide-if-no-js" id="key-pairs-section">
			<h2 id="key-pairs"><?php esc_html_e('ProjectHuddle Rest API', 'project-huddle'); ?></h2>
			<?php if (!defined('PH_SECURE_AUTH_KEY')) { ?>
				<div class="ph-key-notice">
					<p>
						<?php _e('<strong>ProjectHuddle</strong> REST API needs a secret key defined in wp-config.php. To add the secret key edit your wp-config.php file and add a new constant called PH_SECURE_AUTH_KEY:', 'project-huddle'); ?>
					</p>
					<pre style="padding: 15px; background: #f3f3f3; display: inline-block; margin: 0;">define('PH_SECURE_AUTH_KEY', 'your-top-secret-key');</pre>
					<p>
						<?php _e('You can use a string from <a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank">here</a>.', 'project-huddle'); ?>
					</p>
				</div>
			<?php
				return;
			} ?>
			<table class="form-table create-key-pair">
				<tbody>
					<tr>
						<th scope="row">
							<label for="new_key_pair_name"><?php esc_attr_e('Add Key', 'project-huddle'); ?></label>
						</th>
						<td>
							<input type="text" size="30" name="new_key_pair_name" id="new_key_pair_name" placeholder="<?php esc_attr_e('Description', 'project-huddle'); ?>" class="input regular-text code" autocomplete="new-password" />
							<?php submit_button(esc_html__('Add New', 'project-huddle'), 'secondary', 'do_new_keypair', false); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="key-pairs-list-table-wrapper">
				<?php
				$key_pair_list_table        = new PH_Key_Pair_List_Table(array('screen' => 'profile'));
				$key_pair_list_table->items = array_reverse($this->get_user_key_pairs($user->ID));
				$key_pair_list_table->prepare_items();
				$key_pair_list_table->display();
				?>
			</div>
		</div>
	<?php
	}

	/**
	 * The new key-pair template.
	 *
	 * @since 3.3.0
	 */
	public function template_new_key_pair()
	{
	?>
		<script type="text/html" id="tmpl-new-key-pair">
			<div class="new-key-pair notification-dialog-wrap">
				<div class="key-pair-dialog-background notification-dialog-background">
					<div class="key-pair-dialog notification-dialog">
						<div class="new-key-pair-content">
							<h3>{{ data.name }}</h3>
							<?php
							printf(
								/* translators: %s: key-pair api_secret */
								esc_html_x('Your new API secret password is: %s', 'API key-pair', 'project-huddle'),
								'<input type="text" value="{{ data.api_secret }}" class="input-select" />'
							);
							?>
						</div>
						<p><?php esc_attr_e('Be sure to save this password in a safe location, you will not be able to retrieve it ever again. Your API secret password is stored in the database like your login password and cannot be recovered. Once you click dismiss it is gone forever.', 'project-huddle'); ?></p>
						<p><?php esc_attr_e('You will need both the API key/secret to generate a JSON Web Token. You can download the key-pair.json file that contains both the API key/secret by clicking the button below.', 'project-huddle'); ?></p>
						<button class="button button-secondary key-pair-download" data-key="{{ data.api_key }}" data-secret="{{ data.api_secret }}"><?php esc_attr_e('Download', 'project-huddle'); ?></button>
						<button class="button button-primary key-pair-modal-dismiss"><?php esc_attr_e('Dismiss', 'project-huddle'); ?></button>
					</div>
				</div>
			</div>
		</script>
	<?php
	}

	/**
	 * The new token key-pair template.
	 *
	 * @since 3.3.0
	 */
	public function template_new_token_key_pair()
	{
	?>
		<script type="text/html" id="tmpl-new-token-key-pair">
			<div class="new-key-pair notification-dialog-wrap" data-api_key="{{ data.api_key }}" data-name="{{ data.name }}">
				<div class="key-pair-dialog-background notification-dialog-background">
					<div class="key-pair-dialog notification-dialog">
						<h3><?php esc_attr_e('JSON Web Token', 'project-huddle'); ?></h3>
						<# if ( data.message ) { #>
							<div class="notice notice-error">
								<p>{{{ data.message }}}</p>
							</div>
							<# } #>
								<# if ( ! data.access_token || ! data.refresh_token ) { #>
									<p>
										<?php
										printf(
											/* translators: %s: key-pair api_secret */
											esc_html_x('To generate a new JSON Web Token please enter your API Secret password for the %s key-pair below.', 'API key-pair', 'project-huddle'),
											'<strong>{{ data.name }}</strong>'
										);
										?>
									</p>
									<p>
										<?php
										printf(
											/* translators: %s: key-pair api_secret */
											esc_html_x('The API Secret must be a key-pair match for the API Key: %s.', 'API key-pair', 'project-huddle'),
											'<strong>{{ data.api_key }}</strong>'
										);
										?>
									</p>
									<input type="text" size="30" name="new_token_api_secret" placeholder="<?php esc_attr_e('API Secret', 'project-huddle'); ?>" class="input" autocomplete="new-password" />
									<button class="button button-secondary key-pair-token"><?php esc_attr_e('New Token', 'project-huddle'); ?></button>
									<# } else { #>
										<div class="new-key-pair-token">
											<?php
											printf(
												/* translators: %s: JSON Web Token */
												esc_html_x('Your new access token is: %s', 'Access Token', 'project-huddle'),
												'<input type="text" value="{{ data.access_token }}" class="input-select" />'
											);
											?>
											<?php
											printf(
												/* translators: %s: JSON Web Token */
												esc_html_x('Your new refresh token is: %s', 'Refresh Token', 'project-huddle'),
												'<input type="text" value="{{ data.refresh_token }}" class="input-select" />'
											);
											?>
											<p><?php esc_attr_e('Be sure to save these JSON Web Tokens in a safe location, you will not be able to retrieve them ever again. Once you click dismiss they\'re gone forever.', 'project-huddle'); ?></p>
										</div>
										<button class="button button-secondary key-pair-token-download"><?php esc_attr_e('Download', 'project-huddle'); ?></button>
										<# } #>
											<button class="button button-primary key-pair-modal-dismiss"><?php esc_attr_e('Dismiss', 'project-huddle'); ?></button>
					</div>
				</div>
			</div>
		</script>
	<?php
	}

	/**
	 * The key-pair row template.
	 *
	 * @since 3.3.0
	 */
	public function template_key_pair_row()
	{
	?>
		<script type="text/html" id="tmpl-key-pair-row">
			<tr data-api_key="{{ data.api_key }}" data-name="{{ data.name }}">
				<td class="name column-name has-row-actions column-primary" data-colname="<?php esc_attr_e('Name', 'project-huddle'); ?>">
					{{ data.name }}
				</td>
				<td class="name column-name column-api_key" data-colname="<?php esc_attr_e('API Key', 'project-huddle'); ?>">
					{{ data.api_key }}
				</td>
				<td class="created column-created" data-colname="<?php esc_attr_e('Created', 'project-huddle'); ?>">
					{{ data.created }}
				</td>
				<td class="last_used column-last_used" data-colname="<?php esc_attr_e('Last Used', 'project-huddle'); ?>">
					{{ data.last_used }}
				</td>
				<td class="last_ip column-last_ip" data-colname="<?php esc_attr_e('Last IP', 'project-huddle'); ?>">
					{{ data.last_ip }}
				</td>
				<td class="token column-token" data-colname="<?php esc_attr_e('Token', 'project-huddle'); ?>">
					<input type="submit" name="token-key-pair-{{ data.api_key }}" class="button" id="token-key-pair-{{ data.api_key }}" value="<?php esc_attr_e('New Token', 'project-huddle'); ?>">
				</td>
				<td class="revoke column-revoke" data-colname="<?php esc_attr_e('Revoke', 'project-huddle'); ?>">
					<input type="submit" name="revoke-key-pair" class="button delete" id="revoke-key-pair-{{ data.api_key }}" value="<?php esc_attr_e('Revoke', 'project-huddle'); ?>">
				</td>
			</tr>
		</script>
<?php
	}
}

function ph_rest_key_pair_init()
{
	// Initialize JSON Web Tokens.
	$ph_rest_keypair = new PH_REST_Key_Pair();
	$ph_rest_keypair->init();
}
add_action('plugins_loaded', 'ph_rest_key_pair_init');
