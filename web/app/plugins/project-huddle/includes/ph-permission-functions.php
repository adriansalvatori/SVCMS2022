<?php

/**
 * Permission functions for ProjectHuddle
 *
 * @since   2.3.1
 * @package ProjectHuddle
 */

function ph_template_access($slug, $name = '')
{
	// access token
	$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : '';
	// newup validatation controller
	$validator = new PH_Permissions_Controller(get_the_ID(), '', $access_token);
	// check if visitor can access
	$access = $validator->visitor_can_access();

	// should we force login
	$force_login = filter_var(get_post_meta(get_the_ID(), 'force_login', true), FILTER_VALIDATE_BOOLEAN);

	// short circuit and require login if not logged in
	if (apply_filters('ph_require_login', $force_login && !is_user_logged_in(), get_the_ID())) {
		ph_get_template_part('content', 'login');
		exit;
	}

	if (!$access) {
		if (post_password_required(get_the_ID())) {
			ph_get_template_part('content', 'password');
			exit;
		}
		if (!is_user_logged_in()) {
			ph_get_template_part('content', 'login');
			exit;
		}

		// show error message
		wp_die(
			'<h1>' . __('No Access', 'project-huddle') . '</h1>' .
				'<p>' . __('You do not have access to this project', 'project-huddle') . '</p>',
			false,
			[
				'link_url' => 'mailto:' . get_option('admin_email'),
				'link_text' => 'Request Access'
			]
		);
	} else {
		ph_get_template_part($slug, $name);
	}
}

/**
 * Programatically add edit permissions to project collaborators
 * based on siloing.
 *
 * @param array  $allcaps All the capabilities of the user
 * @param array  $cap     [0] Required capability
 * @param array  $args    [0] Requested capability
 *                        [1] User ID
 *                        [2] Associated object ID
 * @param object $user    User object
 *
 * @return array Maybe modified caps
 */
function ph_user_has_edit_cap($allcaps, $cap, $args, $user)
{
	// Bail out if we're not asking about a post
	$post_types = (array) ph_get_post_types();

	if ('edit_post' !== $args[0]) {
		return $allcaps;
	}

	// Load the post data:
	$post = get_post($args[2]);

	// bail if it's not one of our post types
	if (!$post || !in_array($post->post_type, $post_types)) {
		return $allcaps;
	}

	// Bail out if post author can edit others posts:
	if ($user->has_cap('edit_others_' . $post->post_type . 's') || $user->has_cap('edit_others_posts')) {
		return $allcaps;
	}

	// if unsiloing is checked, we need to enable permissions if they can edit their own
	if (filter_var(get_option('ph_un_silo', false), FILTER_VALIDATE_BOOLEAN)) {
		// only if they can edit their own
		if ($user->has_cap('edit_' . $post->post_type . 's') || $user->has_cap('edit_posts')) {
			$ptype                                       = get_post_type_object($post->post_type);
			$allcaps[$ptype->cap->edit_others_posts]   = true;
			$allcaps[$ptype->cap->delete_others_posts] = true;
		}
	}

	$project_ids = ph_get_users_project_ids($user->ID);

	if (in_array($post->ID, $project_ids)) {
		// only if they can edit their own
		if ($user->has_cap('edit_' . $post->post_type . 's') || $user->has_cap('edit_posts')) {
			$ptype                                       = get_post_type_object($post->post_type);
			$allcaps[$ptype->cap->edit_others_posts]   = true;
			$allcaps[$ptype->cap->delete_others_posts] = true;
		}
	}

	return $allcaps;
}

add_action('user_has_cap', 'ph_user_has_edit_cap', 99, 4);

// Fixes bug with edit_post on post types failing using user_has_cap
// https://core.trac.wordpress.org/ticket/30452
// https://gist.github.com/danielbachhuber/18850d571c5dce419f8b
function ph_fix_map_meta_cap($caps, $cap, $user_id, $args)
{
	global $pagenow, $post;

	switch ($cap) {
		case 'edit_post':
		case 'edito_others_posts':
		case 'edit_others_ph-projects':
		case 'edit_others_ph-websites':
			$post_obj = false;

			if ('edit_post' === $cap) {
				$post_obj = get_post($args[0]);
			} elseif ('post.php' === $pagenow && !empty($post)) {
				$post_obj = get_post($post);
			}

			// need a post
			if (!$post_obj) {
				break;
			}

			// must be our post type
			if (!in_array($post_obj->post_type, ph_get_post_types())) {
				break;
			}

			// get post type and users project ids
			$post_type   = get_post_type_object($post_obj->post_type);
			$project_ids = ph_get_users_project_ids($user_id);

			// if the user is a project member or unsiloing is off
			if (in_array($post_obj->ID, $project_ids) || filter_var(get_option('ph_un_silo', false), FILTER_VALIDATE_BOOLEAN)) {
				// Don't require editing others' posts capability if user is project member
				if (false !== ($key = array_search($post_type->cap->edit_others_posts, $caps))) {
					unset($caps[$key]);
				}

				// use edit published_posts, edit_posts check instead instead
				if ('publish' == $post_obj->post_status) {
					$caps[] = $post_type->cap->edit_published_posts;
				} elseif ('trash' == $post_obj->post_status) {
					if ('publish' == get_post_meta($post_obj->ID, '_wp_trash_meta_status', true)) {
						$caps[] = $post_type->cap->edit_published_posts;
					}
				} else {
					// If the post is draft...
					$caps[] = $post_type->cap->edit_posts;
				}
			}
			break;
	}

	return $caps;
}

add_filter('map_meta_cap', 'ph_fix_map_meta_cap', 10, 4);

/**
 * Restricts All Mockups page to show only subscribed projects
 *
 * @param $query
 *
 * @return mixed
 */
function ph_restrict_for_current_author($query)
{
	// if unsilo of on, don't restrict view
	if (filter_var(get_option('ph_un_silo', false), FILTER_VALIDATE_BOOLEAN)) {
		return $query;
	}

	global $pagenow, $typenow;

	// need to be on all posts page and admin
	if ('edit.php' != $pagenow || !$query->is_admin) {
		return $query;
	}

	// check against post types
	if (!in_array($typenow, ph_get_post_types())) {
		return $query;
	}

	// bail if they can edit others posts
	if (current_user_can('edit_others_posts') || current_user_can('edit_others_' . $typenow . 's')) {
		return $query;
	}

	// if it's a project collaborator and the user is not the post author
	global $user_ID;

	// get users project ids only
	$query->set('post__in', (array) ph_get_users_project_ids($user_ID));

	return $query;
}
add_filter('pre_get_posts', 'ph_restrict_for_current_author');

function hide_private_media_posts($query) {
	global $pagenow;

	$is_accessible = apply_filters('ph_check_private_comments_access', false, get_the_ID());	 

	if( ( ! $is_accessible && 'upload.php' == $pagenow && $query->is_admin ) ) {
		$query->set( 'meta_query', array(
			array(
				'key'   => 'is_private_comment',
				'compare' => 'NOT EXISTS'
			)
		));

		return $query;
	}

	return $query;
}
// Hide private attachments from list view of media.
add_filter( 'pre_get_posts', 'hide_private_media_posts' );

/**
 * Hide attachment files from the Media Library's grid view
 * if they have a certain meta key set.
 * 
 * @param array $args An array of query variables.
 */
add_filter( 'ajax_query_attachments_args', 'ph_hide_media_grid_view' );

function ph_hide_media_grid_view( $args ) {

	$is_accessible = apply_filters('ph_private_comments_access', false, get_the_ID());	 

	if( ( ! $is_accessible && is_admin() ) ) {

		$args['meta_query'] = array(
			array(
				'key'   => 'is_private_comment',
				'compare' => 'NOT EXISTS'
			)
		);

		return $args;
	}
	return $args;
}

if (!function_exists('ph_is_user_subscribed')) :
	/**
	 * Is the user subscribed?
	 *
	 * @param WP_User $user        int|object User object or integer.
	 * @param WP_Post $post_object Post object.
	 *
	 * @return boolean If the user is subscribed to the project
	 */
	function ph_is_user_subscribed($user = 0, $post_object = 0)
	{
		// normalize user.
		if ($user) {
			if (is_int($user)) {
				$user = get_user_by('ID', $user);
			} elseif (!is_object($user)) {
				return false;
			}
		} else {
			$user = wp_get_current_user();
		}

		global $post;
		$project = $post_object ? $post_object : $post;

		if (is_a($project, 'WP_Post')) {
			$subscribed = (array) ph_get_project_member_ids($project->ID);

			return in_array($user->ID, $subscribed);
		} else {
			return false;
		}
	}
endif;

if (!function_exists('ph_user_is_member')) :
	/**
	 * Checks if a user is a member of a specific project
	 *
	 * @param $post int|object Post ID
	 *
	 * @return bool
	 */
	function ph_user_is_member($post, $user_id = 0)
	{
		// if unsilo is on
		if (get_option('ph_un_silo', 'off') === 'on') {
			return true;
		}

		// normalize post object
		if (is_int($post)) {
			$post = get_post($post);
		}

		if (!$post || !is_a($post, 'WP_Post')) {
			return false;
		}

		if ($user_id && is_int($user_id)) {
			$user = get_user_by('id', $user_id);
		} else {
			$user = wp_get_current_user();
		}

		// true if subscribed, no matter the role
		if (ph_is_user_subscribed($user, $post)) {
			return true;
		}

		$type = get_post_type_object($post->post_type);

		// otherwise true if current user can view unsubscribed projects or they are the author
		return user_can($user->ID, $type->cap->edit_post, $post->ID) || $post->post_author == $user_id;
	}
endif;

if (!function_exists('ph_visitor_can_access')) :
	/**
	 * Can the visitor access the website project
	 *
	 * @param int    $post_id      Project ID
	 * @param string $access_token Unique private access token for the project
	 *
	 * @return bool|WP_Error True if visitor should have access
	 */
	function ph_visitor_can_access($access_token = '', $post_id = 0, $signature = '', $email = '')
	{
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}

		// if we have a valid signature, allow no matter what
		if ($post_id && $signature && $email) {
			if (is_string($signature) && is_string($email) && ph_verify_security_signature($post_id, $signature, $email)) {
				return apply_filters('ph_visitor_can_access', true);
			}
		}

		// switch project access
		switch (ph_get_project_access($post_id)) {
			case 'public':
				return apply_filters('ph_visitor_can_access', true);
				break;
			case 'link':
				// check access token
				if ($access_token === get_post_meta($post_id, 'access_token', true)) {
					return apply_filters('ph_visitor_can_access', true);
				} else {
					if (is_user_logged_in() && ph_user_is_member($post_id)) {
						return apply_filters('ph_visitor_can_access', true);
					} else {
						return apply_filters('ph_visitor_can_access', new WP_Error('token_incorrect_missing', __('The access link provided is incorrect. Please check to make sure you copied it correctly!', 'project-huddle'), array('status' => 403)));
					}
				}
				break;
			case 'password':
				if (post_password_required()) {
					// if user can read the post (is a member) ignore password requirement
					$post          = get_post($post_id);
					$post_type_obj = get_post_type_object($post->post_type);
					if (current_user_can($post_type_obj->cap->read_post, $post_id)) {
						return apply_filters('ph_visitor_can_access', true);
					}

					return apply_filters('ph_visitor_can_access', new WP_Error('enter_password', __('Please enter a password to view this project', 'project-huddle'), array('status' => 403)));
				} else {
					return apply_filters('ph_visitor_can_access', true);
				}
				break;
			default:
				if (is_user_logged_in() && ph_user_is_member($post_id)) {
					return apply_filters('ph_visitor_can_access', true);
				} elseif (is_user_logged_in() && !ph_user_is_member($post_id)) {
					return apply_filters('ph_visitor_can_access', new WP_Error('not_member', __('You must be a member of this project to access it.', 'project-huddle'), array('status' => 403)));
				} else {
					return apply_filters('ph_visitor_can_access', new WP_Error('not_logged_in', __('You must login to access this project', 'project-huddle'), array('status' => 403)));
				}
				break;
		}
	}
endif;

/**
 * Set filters for signature override
 *
 * @param  integer $post_id
 * @param  string $signature
 * @param  string $email
 * @return void
 */
// function ph_signature_override( $post_id, $signature, $email = 'guest', $add_member = false ) {
//     // if the security signature matches
//     if ( !$signature ) {
//         return false;
//     }

//     $email = $email ? $email : 'guest';

//     // if signature is valid
//     if ( ! ph_verify_security_signature( $post_id, $signature, $email ) ) {
//         return false;
//     }

//     // allow access link login
//     add_filter( 'ph_allow_login_via_access_link', '__return_true' );
//     // let visitor access
//     add_filter( 'ph_visitor_can_access', '__return_true' );
//     // set access token
//     $access_token = get_post_meta( $post_id, 'access_token', true );
//     // store project access
//     PH()->session->set(
//         'project_access',
//         array(
//             (int) $post_id => $access_token,
//         )
//     );
//     // filter project access settings
//     add_filter('get_post_metadata', function($metadata, $object_id, $meta_key, $singl) {
//         if('project_access' === $meta_key) {
//             return 'public';
//         }
//     }, 10, 4);
//     // add user
//     if ( $add_member && $current_user = wp_get_current_user() ) {
//         ph_add_project_member( (int) $post_id, $current_user );
//     }
//     return true;
// }

// if ( ! function_exists( 'ph_user_validate_access' ) ) :
//     /**
//      * This function processes user access
//      * Adds the user to the project if they have access
//      *
//      * @param int $post_id Project ID
//      *
//      * @return bool|WP_Error
//      */
//     function ph_user_validate_access( $post_id = 0, $add_member = true, $signature = '', $email = '' ) {
//         if ( ! $post_id ) {
//             global $post;
//             $post_id = $post->ID;
//         }

//         $signature    = isset( $_GET['_signature'] ) ? $_GET['_signature'] : '';
//         $access_token = isset( $_GET['access_token'] ) ? $_GET['access_token'] : '';
//         $email        = $email ? $email : 'guest';

//         if ( ph_signature_override( $post_id, $signature, $email, $add_member ) ) {
//             return true;
//         }

//         // store access token in cookie if set in url
//         if ( isset( $access_token ) && $access_token ) {
//             PH()->session->set(
//                 'project_access',
//                 array(
//                     (int) $post_id => $access_token,
//                 )
//             );
//             // otherwise check for project access session
//         } else {
//             $access       = PH()->session->get( 'project_access' );
//             $access_token = isset( $access[ $post_id ] ) ? $access[ $post_id ] : false;
//         }

//         // does the visitor have access
//         $has_access = ph_visitor_can_access( $access_token, (int) $post_id, $signature, $email );

//         // add the project member if they have access
//         if ( $add_member ) {
//             if ( ! is_wp_error( $has_access ) && $has_access && $current_user = wp_get_current_user() ) {
//                 ph_add_project_member( (int) $post_id, $current_user );
//             }
//         }

//         return $has_access;
//     }
// endif;

if (!function_exists('ph_is_token_valid')) :
	/**
	 * Checks if an access token is valid
	 *
	 * @param string  $token   Access Token
	 * @param integer $post_id Project ID
	 *
	 * @return boolean
	 */
	function ph_is_token_valid($token = '', $post_id = 0)
	{
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}

		return $token === get_post_meta($post_id, 'access_token', true);
	}
endif;

/**
 * Verifies a valid signature
 *
 * @param  integer $post_id
 * @param  string  $signature
 * @param  string  $email
 * @return boolean
 */
function ph_verify_security_signature($post_id, $signature = '', $email = '')
{
	// get security signature
	$key = get_post_meta($post_id, 'security-signature', true);

	// verify signature is correct
	return hash_equals(hash_hmac('sha256', $email, $key), $signature);
}

/**
 * Gerenerate a secret signature for the post
 *
 * @param  integer $post_id
 * @param  boolean $regenerate
 * @return string
 */
function ph_post_signature_key($post_id, $regenerate = false)
{

	if (!$regenerate) {
		if ($signature = get_post_meta($post_id, 'security-signature', true)) {
			return $signature;
		}
	}

	$signature = wp_generate_password(64, true, false); // we don't want spaces
	update_post_meta($post_id, 'security-signature', $signature);

	return $signature;
}
add_action('publish_ph-project', 'ph_post_signature_key');
add_action('publish_ph-website', 'ph_post_signature_key');


if (!function_exists('ph_get_access_token')) :
	/**
	 * Get the access token for a website project
	 *
	 * @param $post mixed Post
	 *
	 * @return string|WP_Error
	 */
	function ph_get_access_token(array $post)
	{
		// newup validatation controller
		$validator = new PH_Permissions_Controller($post['id']);
		// bail if visitor cannot access
		if (!$validator->visitor_can_access()) {
			return '';
		}

		return ph_get_post_access_token($post['id']);
	}
endif;

if (!function_exists('ph_get_project_access')) :
	/**
	 * ph_get_project_access
	 */
	function ph_get_project_access($post_id = 0)
	{
		if (!$post_id) {
			global $post;
			$post_id = is_object($post) ? $post->ID : false;
		}

		if (!$post_id) {
			return false;
		}

		// fallback if meta data doesn't exist.
		if (!metadata_exists('post', $post_id, 'project_access')) {
			return 'link';
		}

		return get_post_meta($post_id, 'project_access', true);
	}
endif;

if (!function_exists('ph_verify_project_signature')) :
	function ph_verify_project_signature( $signature, $email, $post_id = 0, $strict = false)
	{
		$data              = new PH_Permissions_Data($post_id, 'signature', $signature, '', $email);
		$signature_checker = new PH_Signature_Checker($data);
		return $signature_checker->check($strict);
	}
endif;

if (!function_exists('ph_should_script_load')) :
	/**
	 * ph_should_script_load
	 */
	function ph_should_script_load()
	{
		global $post, $wp;

		if (!$post || !($post instanceof WP_Post)) {
			return new WP_Error('invalid_post', __('This post doens\'t exist.', 'project-huddle'), array('status' => 403));
		}

		// check api matches post
		if (!ph_public_api_matches($wp->query_vars['ph_apikey'])) {
			return new WP_Error('wrong_api_key', __('Wrong API Key. Please check the code you pasted on the site to make sure it\'s correct.', 'project-huddle'), array('status' => 403));
		}

		// get comment and url
		$url = get_post_meta($post->ID, 'ph_website_url', true);

		// double check website domain, just to prevent user from adding project install code to multiple domains accidentatlly
		$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
		if ($referrer && !ph_domain_matches($referrer, array($url))) {
			return new WP_Error('wrong_website_domain', __('Website url is wrong in settings. Please double check the url to make sure it exactly matches the site!', 'project-huddle') . ' Live site is: ' . ($referrer ?: 'not detected') . '. You set: ' . esc_url($url) . '.', array('status' => 403));
		}

		// installed
		update_post_meta(get_the_ID(), 'ph_installed', true);
		delete_post_meta(get_the_ID(), 'ph_installation_error');

		// true
		return true;
	}
endif;

/**
 * Add member to project after they are created
 *
 * @param WP_User         $user    Inserted user object.
 * @param WP_REST_Request $request Request object.
 */
function ph_add_member_after_creating($user, $request)
{
	if (!isset($request['project_id']) || !isset($user->user_email)) {
		return;
	}

	// add member to project.
	ph_add_project_member((int) $request['project_id'], $user, false);
}

add_action('ph_rest_insert_user', 'ph_add_member_after_creating', 10, 2);

/**
 * Get all registered PH roles
 *
 * @return array
 */
function ph_get_roles()
{
	return apply_filters(
		'ph_roles',
		array(
			'project_admin',
			'project_editor',
			'project_collaborator',
			'project_client',
		)
	);
}

/**
 * Limit media library access for specific roles
 *
 * This will show only a current user's media library uploads if they can't edit others projects
 * AKA client role
 *
 * @param $query
 *
 * @return mixed
 */
function ph_maybe_show_only_current_users_attachments($query)
{
	// should only run this on backend media queries
	if (!is_admin()) {
		return $query;
	}

	$limit            = false;
	$user_id          = (int) get_current_user_id();
	$capability_types = (array) ph_get_post_types();

	// get roles and add subscriber
	$roles   = ph_get_roles();
	$roles[] = 'subscriber';

	// only limit based on specific roles, not caps
	foreach ($roles as $role) {
		if (current_user_can($role)) {
			$limit = true;
			break;
		}
	}

	if ($limit) {
		foreach ($capability_types as $capability_type) {
			if (!current_user_can("edit_others_{$capability_type}s")) {
				if (apply_filters('ph_restrict_media_gallery', true, $user_id)) {
					$query['author'] = $user_id;
				}
			}
		}
	}

	return $query;
}

add_filter('ajax_query_attachments_args', 'ph_maybe_show_only_current_users_attachments', 999999);


function ph_website_access_check($id = 0, $redirect_url = "", $query_args = [])
{
	if (!$redirect_url) {
		return false;
	}


	if (!$id) {
		global $post;
		if (is_a($post, 'WP_Post')) {
			$id = $post->ID;
		}
	}

	if (!$id) {
		return;
	}

	$website_type = get_post_meta($id, 'website_type', true);

	if ('WordPress' !== $website_type) {
		// should we force login?
		$force_login = filter_var(get_post_meta($id, 'force_login', true), FILTER_VALIDATE_BOOLEAN);
		if (!is_user_logged_in() && apply_filters('ph_require_login', $force_login, $id)) {
			ph_get_template_part('content', 'login');
			exit;
		}
	}

	// store access token
	$access_token = ph_store_access_token_from_url($id);
	// newup validatation controller
	$validator = new PH_Permissions_Controller($id, '', $access_token);
	// check if visitor can access
	$access = $validator->visitor_can_access();

	// show login form if need login
	if ('WordPress' !== $website_type) {
		if (!is_user_logged_in() && !$access) {
			ph_get_template_part('content', 'login');
			exit;
		}
	}

	header('X-Robots-Tag: noindex, nofollow', true);

	$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : '';
	if ($access_token) {
		$redirect_url = add_query_arg(
			array(
				'ph_access_token' => $access_token,
			),
			$redirect_url
		);
	}

	// add query args
	foreach ($query_args as $key => $arg) {
		$redirect_url = add_query_arg(
			array(
				$key => $arg,
			),
			$redirect_url
		);
	}

	wp_redirect(esc_url_raw($redirect_url));

	exit;
}

function ph_store_access_token_from_url($id = 0)
{
	global $post;
	if (!$id && is_a($post, 'WP_Post')) {
		$id = $post->ID;
	}

	// store access token in cookie if set in url
	$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : '';

	if (isset($access_token) && $access_token) {
		PH()->session->set(
			'project_access',
			array(
				$id => $access_token,
			)
		);
	}
	return $access_token;
}

/**
 * Update permalink on website page without hitting save
 *
 * @param string  $return    Sample permalink HTML markup.
 * @param int     $post_id   Post ID.
 * @param string  $new_title New sample permalink title.
 * @param string  $new_slug  New sample permalink slug.
 * @param WP_Post $post      Post object.
 *
 * @return string
 */
function ph_projects_auto_save_permalinks($return, $post_id, $new_title, $new_slug, $post)
{
	if (!defined('DOING_AJAX') || !DOING_AJAX) {
		return $return;
	}

	// if not one of our post types, return.
	if (!in_array(get_post_type($post_id), ph_get_post_types())) {
		return $return;
	}

	// save slug.
	wp_update_post(
		array(
			'ID'        => (int) $post_id,
			'post_name' => $new_slug,
		)
	);

	return $return;
}

add_filter('get_sample_permalink_html', 'ph_projects_auto_save_permalinks', 10, 5);

/**
 * Rename permalink to Magic Share Link
 * Also adds tootip to explain.
 *
 * @param $html
 *
 * @return string
 */
function ph_project_rename_permalink($html, $post_id, $new_title, $new_slug, $post)
{
	// if not one of our post types, return.
	if (!in_array(get_post_type($post_id), ph_get_post_types())) {
		return $html;
	}

	wp_enqueue_script('clipboard.js');

	$html = '<div class="ph-magic-link-wrap">
        <strong class="ph-permalink-label">'
		. __('Project Access Link', 'project-huddle')
		. '<span class="ph-tooltip-wrap">
            <i class="ion-help-circled"></i>
                <span class="ph-tooltip">
                    ' . __('Share this link with someone to give them access to the project.', 'project-huddle') . '
                </span>
            </span>'
		. '</strong>'
		. $html . '<button type="button" class="ph-copy-link button button-primary button-small hide-if-no-js" aria-label="Copy Access Link" data-clipboard-text="' . esc_attr(get_permalink($post->ID)) . '">' . __('Copy', 'project-huddle') . '</button><span class="clipboard-confirm hidden">' . __('Copied!', 'project-huddle') . '</span>
    </div>
    <script>
    jQuery(document).ready(function(){
    	var selector = ".ph-copy-link",
		anchortag = ".ph-magic-link-wrap",
    		phClipboard = new Clipboard(selector);
			$link_dom = jQuery( anchortag ).find("#sample-permalink a");
			$link_dom.attr("target","_blank");
    	phClipboard.on("success", function(e) {
    		var confirm = jQuery(selector).next(".clipboard-confirm");
		    confirm.show();
			setTimeout(function(){
				 confirm.hide();
			}, 1500);
		});
    });
    </script>';

	return $html;
}

add_filter('get_sample_permalink_html', 'ph_project_rename_permalink', 10, 5);


/**
 * Get stored access token
 *
 * @param  integer $post_id
 * @return string
 */
function ph_get_post_access_token($post_id = 0)
{
	if (!$token = get_post_meta($post_id, 'access_token', true)) {
		// generate api key based on post id, url and time
		$token = md5(uniqid($post_id, true));
		update_post_meta($post_id, 'access_token', esc_sql($token));

		return $token;
	}

	return esc_html($token);
}
add_action('publish_ph-project', 'ph_get_post_access_token');
add_action('publish_ph-website', 'ph_get_post_access_token');

/**
 * Add access token to website project links
 *
 * @param $permalink
 * @param $post
 *
 * @return string
 */
function ph_add_access_token($permalink, $post)
{
	global $access_token_override;

	// if not one of our post types, return
	if (!in_array(get_post_type($post->ID), ph_get_post_types()) && !in_array(get_post_type($post->ID), ph_get_child_post_types())) {
		return $permalink;
	}

	if (in_array(get_post_type($post->ID), ph_get_child_post_types())) {
		$parents = ph_get_parents_ids($post);
		$token   = ph_get_post_access_token($parents['project']);
	} else {
		$token = ph_get_post_access_token($post->ID);
	}

	if (is_admin() || $access_token_override) {
		$permalink = add_query_arg(
			array(
				'access_token' => esc_attr($token),
			),
			$permalink
		);
	}

	return $permalink;
}

add_filter('post_type_link', 'ph_add_access_token', 10, 2);
add_filter('preview_post_link', 'ph_add_access_token', 10, 2);

/**
 * Redirect to existing page when login fails
 *
 * @param $username
 */
function ph_login_failed()
{
	$referrer = wp_get_referer();

	// bail for ajax requests
	if (wp_doing_ajax()) {
		return;
	}

	// bail for rest requests
	if (defined('REST_REQUEST') && REST_REQUEST) {
		return;
	}

	if ($referrer && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
		wp_redirect(add_query_arg('login', 'failed', $referrer));
		exit;
	}
}
add_action('wp_login_failed', 'ph_login_failed');

function ph_auto_login($project_id, $signature, $email)
{
	// set error notice 
	$error = new WP_Error('not_allowed', __('User cannot automatically login.', 'project-huddle'));

	// get current user
	$user = wp_get_current_user();

	// if the user is logged in already
	if (is_user_logged_in()) {
		// only if they are trying to change
		if ($user->user_email == $email) {
			return true;
		}
		// if they are not a project client, let's use their credentials already
		// otherwise let's switch accounts
		if (!apply_filters('ph_current_user_can_auto_login', current_user_can('project_client'))) {
			return true;
		}
	}

	// verify identity signature
	if (!$verify_identity = ph_verify_project_signature($signature, $email, $project_id )) {
		return $verify_identity;
	}

	// if we have an error
	if (is_wp_error($verify_identity)) {
		return $verify_identity;
	}

	// get user by email
	$user = get_user_by('email', sanitize_email($email));

	// make sure it's a valid user
	if (!is_a($user, 'WP_User')) {
		return $user;
	}

	// bail for potentially dangerous operations, just in case
	if (user_can($user, 'manage_options') || user_can($user, 'promote_users') || user_can($user, 'edit_posts')) {
		return $error;
	}

	// user must be able to login using the access token permission
	if (!user_can($user, 'login_with_access_token')) {
		return $error;
	}

	// Log user in.
	$_set_cookies = true; // for the closures

	// Set the (secure) auth cookie immediately. We need only the first and last
	// arguments; hence I renamed the other three, namely `$a`, `$b`, and `$c`.
	add_action(
		'set_auth_cookie',
		function ($auth_cookie, $a, $b, $c, $scheme) use ($_set_cookies) {
			if ($_set_cookies) {
				$_COOKIE['secure_auth' === $scheme ? SECURE_AUTH_COOKIE : AUTH_COOKIE] = $auth_cookie;
			}
		},
		10,
		5
	);

	// Set the logged-in cookie immediately. `wp_create_nonce()` relies upon this
	// cookie; hence, we must also set it.
	add_action(
		'set_logged_in_cookie',
		function ($logged_in_cookie) use ($_set_cookies) {
			if ($_set_cookies) {
				$_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
			}
		}
	);

	// Set cookies.
	clean_user_cache($user->ID);
	wp_clear_auth_cookie();
	wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID, true, false);
	update_user_caches($user);

	$_set_cookies = false;

	// add member to project
	ph_add_member_to_project(
		array(
			'user_id' => $user->ID,
			'project_id' =>  $project_id
		)
	);

	return $user;
}

/**
 * Checks to make sure user is authenticated and password is correct
 *
 * @param $user
 * @param $username
 * @param $password
 *
 * @return WP_Error
 */
function ph_authenticate_username_passowrd($user, $username, $password)
{
	if (is_a($user, 'WP_User')) {
		return $user;
	}

	if (empty($username) || empty($password)) {
		if (is_wp_error($user)) {
			return $user;
		}
		$user = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.', 'project-huddle'));
	}

	return $user;
}

add_filter('authenticate', 'ph_authenticate_username_passowrd', 30, 3);

/**
 * Can the current visitor
 *
 * @param  integer $id
 * @param  string  $signature
 * @param  string  $email
 * @param  boolean $strict
 * @return void
 */
function ph_project_allow_login_without_password($id = 0, $signature = '', $email = 'guest', $strict = false)
{
	if (ph_project_users_can_login_without_password($id)) {
		return true;
	}

	// get signature
	$signature = isset($_GET['ph_signature']) ? $_GET['ph_signature'] : $signature;

	// is signature valid?
	return ph_verify_project_signature( $signature, $email, $id, $strict);
}
/**
 * If the user can login via access link
 *
 * @param int $id
 *
 * @return bool
 */
function ph_project_users_can_login_without_password($id = 0)
{
	global $post;
	$access = false;

	if (!$id) {
		if (isset($post) && is_a($post, 'WP_Post')) {
			$id = $post->ID;
		}
	}

	if (!$id) {
		return false;
	}

	$access_link_login = get_post_meta($id, 'access_link_login', true);

	// set to true if doesn't exist
	if (!metadata_exists('post', $id, 'access_link_login')) {
		$access_link_login = true;
	}

	// if access link login is set
	if (filter_var($access_link_login, FILTER_VALIDATE_BOOLEAN)) :
		// if we have the correct project access
		if (in_array(ph_get_project_access($id), array('link', 'public', 'password'))) {
			$access = true;
		}
	endif;

	return apply_filters('ph_allow_login_via_access_link', $access, $id);
}
