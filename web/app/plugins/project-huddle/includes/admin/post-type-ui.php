<?php

include_once 'quick-edit-fields.php';

/**
 * Mockup Post Type Functions
 *
 * @package    project-huddle
 * @subpackage ph-image-approvals
 */

/**
 * Add new column
 *
 * @param $defaults
 *
 * @return mixed
 */
function ph_project_columns_head($defaults)
{
	$defaults['progress'] = __('Progress', 'project-huddle');
	$defaults['approval'] = __('Status', 'project-huddle');

	return $defaults;
}

add_filter('manage_ph-project_posts_columns', 'ph_project_columns_head', 9);

if (is_admin()) {

	add_action('restrict_manage_posts', 'ph_filter_admin_projects');
	function ph_filter_admin_projects() {
		// get post type
		$post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';

		// only run for our post types
		if (!in_array($post_type, ph_get_project_post_types())) {
			return;
		}

		$val = isset($_GET['admin_filter_approval']) ? $_GET['admin_filter_approval'] : '';
		?>
			<select name="admin_filter_approval">
				<option value=""><?php _e('All Statuses', 'project-huddle'); ?></option>
				<option value="approved" <?php echo $val === 'approved' ? 'selected="selected"' : ''; ?>><?php _e('Approved', 'project-huddle'); ?></option>
				<option value="unapproved" <?php echo $val === 'unapproved' ? 'selected="selected"' : ''; ?>><?php _e('Unapproved', 'project-huddle'); ?></option>
			</select>
		<?php

		$params = array(
			'name' => 'client', // this is the "name" attribute for filter <select>
			'show_option_all' => __('All Project Members', 'project-huddle') // label for all authors (display posts without filter)
		);

		if ( isset($_GET['client'] ) ) {
			$params['selected'] = $_GET['client']; // choose selected user by $_GET variable
		}

		wp_dropdown_users( $params );
	}

	//this hook will alter the main query according to the user's selection of the custom filter we created above:
	add_filter('parse_query', 'ph_parse_admin_project_filters');
	function ph_parse_admin_project_filters($query) {
		global $pagenow;
		$post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';

		if (!in_array($post_type, ph_get_project_post_types()) || $pagenow !== 'edit.php') {
			return $query;
		}

		// filter approval
		if (isset($_GET['admin_filter_approval']) && !empty($_GET['admin_filter_approval'])) {
			// approved only
			if ($_GET['admin_filter_approval'] == 'approved') {
				$query->query_vars['meta_key'] = 'approved';
				$query->query_vars['meta_value'] = true;
			}
			// if unapproved or not exists
			if ($_GET['admin_filter_approval'] == 'unapproved') {
				$query->query_vars['meta_query'] = [
					'relation' => 'OR',
					[
						'key' => 'approved',
						'compare' => 'NOT EXISTS'
					],
					[
						'key' => 'approved',
						'value' => false
					],
				];
			}
		}

		return $query;
	}

	add_filter( 'parse_query', 'ph_client_filter' );
	//This hook will alter the main query according to the user's selection of the custom filter we created above:
	function ph_client_filter( $query ) {

		if ( isset( $_GET['client'] ) && ! empty( $_GET['client'] ) ) {
			$value = sanitize_text_field( wp_unslash( $_GET['client'] ) );
			
			$meta_query = $query->get( 'meta_query' );

			// If there is no meta query when this filter runs, it should be initialized as an empty array.
			if ( ! $meta_query ) {
				$meta_query = [];
			}

			$user = get_user_by( 'id', $value );

			$user_name = $user->user_login;			

			// Append our meta query
			$meta_query[] = [
				'key' => 'custom_ph_members_list',
				'value' => sprintf( ':"%s";', $user_name ),
				'compare' => 'LIKE',
			];
			$query->set( 'meta_query', $meta_query );

		}
		return $query;
	}

}

/**
 * Add new column
 *
 * @param $defaults
 *
 * @return mixed
 */
function ph_website_threads_head($defaults)
{
	$defaults['page'] = __('Page', 'project-huddle');
	$defaults['url'] = __('URL', 'project-huddle');
	$defaults['project'] = __('Project', 'project-huddle');
	$defaults['author'] = __('Reporter', 'project-huddle');

	return $defaults;
}

add_filter('manage_phw_comment_loc_posts_columns', 'ph_website_threads_head', 9);

function ph_mockup_threads_head($defaults)
{
	$defaults['image']   = __('Image', 'project-huddle');
	$defaults['project'] = __('Project', 'project-huddle');
	$defaults['author']  = __('Reporter', 'project-huddle');

	return $defaults;
}

add_filter('manage_ph_comment_location_posts_columns', 'ph_mockup_threads_head', 9);

function ph_mockup_threads_sortable($columns)
{
	$columns['image'] = 'Image';

	return $columns;
}

add_action('manage_edit-ph_comment_location_sortable_columns', 'ph_mockup_threads_sortable');

function ph_website_threads_sortable($columns)
{
	$columns['page'] = 'Page';
	$columns['url'] = 'URL';

	return $columns;
}

add_action('manage_edit-phw_comment_loc_sortable_columns', 'ph_website_threads_sortable');

function ph_website_threads_columns($columns)
{
	$columns['project'] = 'Project';

	return $columns;
}

add_action('manage_edit-phw_comment_loc_columns', 'ph_website_threads_columns');


function ph_orderby_page_query($query)
{
	if (!is_admin())
		return;

	$orderby = $query->get('orderby');

	if ('Page' == $orderby) {
		$query->set('meta_key', 'parent_id');
		$query->set('orderby', 'meta_value_num');
	}
	if ('URL' == $orderby) {
		$query->set('meta_key', 'page_url');
		$query->set('orderby', 'meta_value');
	}
}
add_action('pre_get_posts', 'ph_orderby_page_query');


function ph_project_query($query)
{
	if (!is_admin()) {
		return;
	}

	$project = $query->get('project');

	if ($project) {
		$query->set('meta_key', 'project_id');
		$query->set('meta_value', $project);
	}
}

add_action('pre_get_posts', 'ph_project_query');

/**
 * Add project query var
 * @param $vars
 *
 * @return array
 */
function ph_project_query_var($vars)
{
	$vars[] .= 'project';
	$vars[] .= 'ph_comments';
	return $vars;
}

add_filter('query_vars', 'ph_project_query_var');

/**
 * if submitted filter by post meta
 *
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @author Ohad Raz
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function wpse45436_posts_filter($query)
{
	global $pagenow;
	$type = 'post';
	if (isset($_GET['post_type'])) {
		$type = $_GET['post_type'];
	}
	if ('POST_TYPE' == $type && is_admin() && $pagenow == 'edit.php' && isset($_GET['ph_comment_type']) && $_GET['ph_comment_type'] != '') {
		$query->query_vars['meta_key'] = 'META_KEY';
		$query->query_vars['meta_value'] = $_GET['ph_comment_type'];
	}
}


/**
 * Show column value
 *
 * @param $column_name
 * @param $post_ID
 */
function ph_project_columns_content($column_name, $post_ID)
{
	if ($column_name == 'page') {
		$page = get_post_meta($post_ID, 'parent_id', true);

		if ($page) {
			$id       = (int) $page;
			$edit_url = esc_url(admin_url("post.php?post=$id&action=edit"));
			$page_url = esc_url(get_post_meta($page, 'page_url', true));
			echo "<a href='$edit_url' data-id='$page'>$page_url</a>";
		}
	}

	if ($column_name == 'url') {
		echo esc_url(get_post_meta($post_ID, 'page_url', true));
	}

	if ($column_name == 'image') {
		$image = get_post_meta($post_ID, 'parent_id', true);

		if ($image) {
			$id       = (int) $image;
			$edit_url = esc_url(admin_url("post.php?post=$id&action=edit"));
			$image_url = ph_get_the_title($image) ? sanitize_text_field(ph_get_the_title($image)) : __('(No Title)', 'project-huddle');
			echo "<a href='$edit_url'>$image_url</a>";
		}
	}

	if ($column_name == 'project') {
		$parents = ph_get_parents_ids($post_ID);
		if ($parents['project']) {
			$edit_url     = esc_url(add_query_arg('project', (int) $parents['project']));
			$project_name = ph_get_the_title($parents['project']) ? sanitize_text_field(ph_get_the_title($parents['project'])) : __('(No Title)', 'project-huddle');
			echo "<a href='$edit_url'>$project_name</a>";
		}
	}
}

add_action('manage_phw_comment_loc_posts_custom_column', 'ph_project_columns_content', 10, 2);
add_action('manage_ph_comment_location_posts_custom_column', 'ph_project_columns_content', 10, 2);


/**
 * Show column value
 *
 * @param $column_name
 * @param $post_ID
 */
function ph_listing_columns_content($column_name, $post_ID)
{
	if ($column_name == 'approval') {
		ph_approval_badge($post_ID);
	}
	if ($column_name == 'progress') {
		ph_approval_progress_bar($post_ID);
	}

	$is_updated = get_post_meta( $post_ID, 'custom_ph_members_list', true );

	if( '' == $is_updated ) {
		PH_Meta_Box_Project_Members::save_project_client( $post_ID );
	}
}

add_action('manage_ph-project_posts_custom_column', 'ph_listing_columns_content', 10, 2);

if (!function_exists('ph_approval_progress_bar')) :
	/**
	 * Displays a progress bar for the project
	 *
	 * @param int $id Project ID
	 *
	 * @return void
	 */
	function ph_approval_progress_bar($id = 0)
	{
		global $post;
		if (!$id) {
			$id = is_object($post) ? $post->ID : false;
		}
		if (!$id) {
			return;
		}

		$approval = ph_get_mockup_approval_status($id);

		$total    = isset($approval['total']) ? $approval['total'] : 0;
		$approved = isset($approval['approved']) ? $approval['approved'] : 0;

		if (!$total) {
			return;
		}

		$percentage      = $total ? $approved / $total * 100 : 0;
		$approval_class  = $total && $approved >= $total ? 'approved' : 'unapproved';
		$progress_string = sprintf(
			__('%1$s of %2$s images approved.', 'project-huddle'),
			$approved,
			$total
		);

		echo '<div class="progress-wrap ' . $approval_class . '" data-tooltip="' . esc_attr($progress_string) . '">';
		echo '<div class="ph-approval-bar"><div class="ph-approval-amount" style="width: ' . $percentage . '%;"></div></div>';
		echo '</div>';
	}
endif;

if (!function_exists('ph_approval_badge')) :
	/**
	 * Displays an approval badge for the project
	 *
	 * @param int $id Project ID
	 *
	 * @return void
	 */
	function ph_approval_badge($id = 0)
	{
		global $post;
		if ( !$id ) {
			$id = is_object($post) ? $post->ID : false;
		}
		if (!$id) {
			return;
		}

		$approval = ph_get_mockup_approval_status($id);

		if ($approval && is_array($approval) && isset($approval['approved'])) {
			if ($approval['approved'] >= $approval['total']) {

				if (isset($approval['on']) && isset($approval['by'])) {

					if ($approval['on'] && $approval['by']) {
						$date = new DateTime($approval['on']);
						$date_string = sprintf(
							__('By %1$s on %2$s at %3$s', 'project-huddle'),
							$approval['by'],
							date_format($date, get_option('date_format')),
							date_format($date, get_option('time_format'))
						);
					} else {
						$date_string = __('Approved', 'project-huddle');
					}

					if ( 0 == $approval['on'] && 0 == $approval['total'] ) {
						// Show unapproved status in case of new empty mockup.
						echo '<span class="approval-badge">' . __('UnApproved', 'project-huddle') . '</span>';
					} else {
						echo '<span class="approval-badge approved" data-tooltip="' . esc_attr($date_string) . '">' . __('Approved', 'project-huddle') . '</span>';
					}
				} else {
					echo '<span class="approval-badge approved">' . __('Approved', 'project-huddle') . '</span>';
				}
			} else {
				echo '<span class="approval-badge">' . __('UnApproved', 'project-huddle') . '</span>';
			}
		} else {
			echo '<span class="approval-badge">' . __('UnApproved', 'project-huddle') . '</span>';
		}
	}
endif;
