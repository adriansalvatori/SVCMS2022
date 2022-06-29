<?php

/**
 * Add our query vars
 *
 * @param $query_vars array Array of vars
 *
 * @return array New array with ours added
 */
function ph_query_vars($query_vars)
{
	$query_vars[] = 'ph_apikey'; // our API key
	$query_vars[] = 'ph_website'; // our website
	$query_vars[] = 'ph_handler'; // our ajax handler
	$query_vars[] = "ph_safari_cookie"; // safari fix
	$query_vars[] = "ph_website_query_test"; // website_query_test
	return $query_vars;
}
add_filter('query_vars', 'ph_query_vars');

/**
 * Loads specific files based on query var
 *
 * ph_handler for ajax requests
 * ph_website for loading javascript file
 *
 * @param $wp
 */
function ph_parse_request($wp)
{
	if (array_key_exists('ph_handler', $wp->query_vars)) {
		include PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-ajax-handler.php';
		exit();
	}
	if (array_key_exists('ph_website', $wp->query_vars)) {
		include PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-template.php';
		exit();
	}

	return;
}
add_action('parse_request', 'ph_parse_request');

function ph_create_website_project($args)
{
	$args = wp_parse_args(
		$args,
		array(
			'title' => 'New Website Project',
			'url'   => '',
		)
	);

	// need a url
	if (!$args['url']) {
		return new WP_Error('empty_url', __('You cannot create a website project without a URL'));
	}

	// Insert the project into the database
	$project_id = wp_insert_post(
		array(
			'post_title'  => $args['title'],
			'post_status' => 'publish',
			'post_type'   => 'ph-website',
		)
	);

	// add meta
	update_post_meta($project_id, 'website_url', esc_url($args['url']));

	// maybe regenerate key
	ph_generate_api_key($project_id, false, esc_url($args['url']));

	return get_post($project_id);
}

/**
 * When website is saved, generate api key
 *
 * @param $post_id
 * @param $post
 *
 * @return string|false
 */
function ph_generate_api_key($post_id, $post = false, $url = false)
{
	$requested  = isset($_REQUEST['ph_website_url']) ? $_REQUEST['ph_website_url'] : false;
	$url        = $url == false ? $requested : $url;
	$stored_api = get_post_meta($post_id, 'ph_website_api', true);

	// we need a website url
	if (!isset($url) || !$url) {
		return false;
	}

	// if there's no api set yet
	if (!$stored_api) {
		// generate api key based on post id, url and time
		$api_key = md5($post_id . $url . time());

		// update post meta
		update_post_meta($post_id, 'ph_website_api', sanitize_text_field($api_key));
	} else {
		$api_key = $stored_api;
	}

	return isset($api_key) ? sanitize_text_field($api_key) : false;
}
add_action('save_post_ph-website', 'ph_generate_api_key', 1, 2);

/**
 * Create and echo API url
 *
 * @param mixed $post_id Post ID or Post Object
 */
function ph_the_api_url($post_id)
{
	// convert post object to id
	if (is_object($post_id)) {
		$post_id = $post_id->ID;
	}

	// get nonce and API key
	$api_key = get_post_meta($post_id, 'ph_website_api', true);

	$base_url = home_url('?p=' . $post_id);
	$base_url = str_replace('http:', '', $base_url);
	$base_url = str_replace('https:', '', $base_url);

	// echo URL
	echo add_query_arg(
		array(
			'ph_apikey' => esc_html($api_key),
		),
		$base_url
	);
}

/**
 * Checks if the public API key matches the post we're trying to get
 *
 * @param string $key API key to check
 *
 * @return bool True if it matches
 */
function ph_public_api_matches($key)
{
	global $post;

	$saved = get_post_meta((int) $post->ID, 'ph_website_api', true);

	if (!$saved) {
		return false;
	}

	// they match
	if ($saved == $key) {
		return true;
	}

	// don't match
	return false;
}

/**
 * Check if two domains match
 *
 * @param string $url        Domain to check against
 * @param array  $wl_domains White-listed domains
 *
 * @return bool True if matches anything in white-list
 */
function ph_domain_matches($url, $wl_domains = array())
{
	$domain = parse_url($url, PHP_URL_HOST);

	// Check if we match the domain exactly
	if (in_array($domain, $wl_domains)) {
		return true;
	}

	// check for partial matches
	foreach ($wl_domains as $wl_domain) {
		$wl_domain = '.' . $wl_domain; // Prevent things like 'evilsitetime.com'

		if (strpos($domain, $wl_domain) === (strlen($domain) - strlen($wl_domain))) {
			return true;
			break;
		}
		if (parse_url($wl_domain, PHP_URL_HOST) == $domain) {
			return true;
		}
	}
	return false;
}

/**
 * Hides admin bar on website toolbar pages
 *
 * @return bool
 */
function ph_hide_website_admin_bar($bool)
{
	if (is_singular('ph-website')) {
		return false;
	}

	return $bool;
}
add_filter('show_admin_bar', 'ph_hide_website_admin_bar', 1000);

function add_ph_code()
{
	global $post, $wp;

	// make sure it's enabled
	if (get_option('ph_disable_self') == true) {
		return false;
	}

	// get site post
	if (!$post_id = (int) get_option('ph_site_post')) {
		return;
	}

	// only if published
	if (get_post_status($post_id) != 'publish') {
		return;
	}

	//.. get post type
	$post_type = get_post_type($post);
	if (in_array($post_type, ph_get_child_post_types()) || in_array($post_type, ph_get_post_types())) {
		return;
	}

	if (isset($wp->query_vars['ph_user_settings']) && $wp->query_vars['ph_user_settings']) {
		return;
	}

	if (!apply_filters('ph_website_script_output_enable', true, ( isset( $post ) ? $post->ID : null ))) {
		return;
	}
?>

	<script>
		(function(d, t, g) {
			var ph = d.createElement(t),
				s = d.getElementsByTagName(t)[0];
			ph.type = 'text/javascript';
			ph.async = true;
			ph.charset = 'UTF-8';
			ph.src = g + '&v=' + (new Date()).getTime();
			s.parentNode.insertBefore(ph, s);
		})(document, 'script', '<?php ph_the_api_url($post_id); ?>');
	</script>
<?php
}
add_action('wp_footer', 'add_ph_code');

/**
 * Adds status indicator for this website.
 *
 * @param $states array States array.
 *
 * @return array States array with ours added
 */
function ph_this_website_state($states)
{
	global $post;

	// bail if not our post type
	if (get_post_type($post) !== 'ph-website') {
		return $states;
	}

	// get site post
	$post_id = (int) get_option('ph_site_post');

	if ($post->ID === $post_id) {
		$states[] = __('This Website', 'project-huddle');
	}

	return $states;
}

add_filter('display_post_states', 'ph_this_website_state');

if (!function_exists('ph_url_origin')) :
	/**
	 * ph_url_origin
	 */
	function ph_url_origin($url = '')
	{
		if (!$url) {
			return '';
		}
		$parsed_url = parse_url($url);

		$host  = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		$host .= isset($parsed_url['port']) && $parsed_url['port'] ? ':' . $parsed_url['port'] : '';

		return esc_url($host);
	}
endif;


function ph_get_website_resolve_status($website_id = 0)
{
	$defaults = array(
		'total'    => 0,
		'resolved' => 0,
	);

	if (!$website_id) {
		$defaults;
	}

	$resolve_status = get_transient('ph_resolved_status_' . $website_id);

	// this code runs when there is no valid transient set
	if (false === $resolve_status) {
		// get pages
		$threads = new WP_Query(
			array(
				'post_type'      => 'phw_comment_loc',
				'posts_per_page' => -1,
				'meta_value'     => $website_id,
				'meta_key'       => 'project_id',
			)
		);

		$resolved = 0;
		if (!empty($threads->posts)) {
			foreach ($threads->posts as $thread) {
				if (filter_var(get_post_meta($thread->ID, 'resolved', true), FILTER_VALIDATE_BOOLEAN)) {
					$resolved++;
				}
			}
		}

		$resolve_status = array(
			'total'    => $threads->post_count,
			'resolved' => $resolved,
		);
		set_transient('ph_resolved_status_' . $website_id, $resolve_status, 30 * DAY_IN_SECONDS); // expires in 1 month
	}

	return wp_parse_args($resolve_status, $defaults);
}

/**
 * Clear website transients by id
 *
 * @param $post_id int Post ID
 */
function ph_website_clear_resolved_transients($post_id)
{
	if (get_post_type($post_id) != 'phw_comment_loc') {
		return;
	}

	$project = get_post_meta($post_id, 'website_id', true);

	delete_transient('ph_resolved_status_' . $project);
}

/**
 * Clears transient when approval meta is updated
 *
 * @param $meta_id    int       ID of meta value
 * @param $post_id    int       Post ID
 * @param $meta_key   string    Meta key
 * @param $meta_value mixed     Value of meta
 */
function ph_website_post_meta_clear_transient($meta_id, $post_id, $meta_key, $meta_value)
{
	ph_website_clear_resolved_transients($post_id);
}
// clear transients when post meta is updated
add_action('added_post_meta', 'ph_website_post_meta_clear_transient', 10, 4);
add_action('updated_post_meta', 'ph_website_post_meta_clear_transient', 10, 4);

/**
 * Clears transient when post status changes
 *
 * @param $new_status    string      ID of meta value
 * @param $old_status    string      Post ID
 * @param $post          WP_Post   WP_Post object
 */
function ph_website_transition_clear_transient($new_status, $old_status, $post)
{
	ph_website_clear_resolved_transients($post->ID);
}

add_action('transition_post_status', 'ph_website_transition_clear_transient', 10, 3);
