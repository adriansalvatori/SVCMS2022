<?php

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
function ph_website_listing_columns_head($defaults)
{
	$defaults['tasks'] = __('Tasks', 'project-huddle');
	$defaults['progress'] = __('Approval', 'project-huddle');
	$defaults['approval'] = __('Status', 'project-huddle');

	return $defaults;
}

add_filter('manage_ph-website_posts_columns', 'ph_website_listing_columns_head', 9);

/**
 * Show column value
 *
 * @param $column_name
 * @param $post_ID
 */
function ph_website_listing_columns_content($column_name, $post_ID)
{
	$website = PH()->website->get($post_ID);

	if ($column_name == 'progress') {
		$status = $website->getItemsApprovalStatus();

		if ($website->isApproved()) {
			$status['approved'] = $status['total'];
		}

		// get percentage
		$percentage = $status['total'] ? $status['approved'] / $status['total'] * 100 : 0;
		// is complete?
		$approval_class = $status['total'] && $status['approved'] >= $status['total'] ? 'approved' : 'unapproved';

		if (!$status['total']) {
			echo __('No Pages', 'project-huddle');
		} else {
			$progress_string = sprintf(
				__('%1$s of %2$s pages approved.', 'project-huddle'),
				$status['approved'],
				$status['total']
			);
			echo '<div class="progress-wrap ' . $approval_class . '" data-tooltip="' . esc_attr($progress_string) . '">';
			echo '<div class="ph-approval-bar"><div class="ph-approval-amount" style="width: ' . $percentage . '%;"></div></div>';
			echo '</div>';
		}
	}

	if ($column_name == 'approval') {
		if ($website->isApproved()) {
			echo '<span class="approval-badge approved">' . __('Project Approved', 'project-huddle') . '</span>';
		} else if ($website->itemsApproved()) {
			echo '<span class="approval-badge approved">' . __('All Pages Approved', 'project-huddle') . '</span>';
		} else {
			echo '<span class="approval-badge">' . __('Unapproved', 'project-huddle') . '</span>';
		}
	}

	if ($column_name == 'tasks') {
		$status = $website->getThreadsResolveStatus();
		// get percentage
		$percentage = $status['total'] ? $status['resolved'] / $status['total'] * 100 : 0;
		// is complete?
		$approval_class = $status['total'] && $status['resolved'] >= $status['total'] ? 'approved' : 'unapproved';

		if (!$status['total']) {
			echo '<p style="opacity:0.65">' . __('No Tasks', 'project-huddle') . '<p>';
		} else {
			$progress_string = sprintf(
				__('%1$s of %2$s tasks resolved.', 'project-huddle'),
				$status['resolved'],
				$status['total']
			);
			echo '<div class="progress-wrap ' . $approval_class . '" data-tooltip="' . esc_attr($progress_string) . '">';
			echo '<div class="ph-approval-bar"><div class="ph-approval-amount" style="width: ' . $percentage . '%;"></div></div>';
			echo '</div>';
		}
	}

	$is_updated = get_post_meta( $post_ID, 'custom_ph_members_list', true );

	if( '' == $is_updated ) {
		PH_Meta_Box_Project_Members::save_project_client( $post_ID );
	}

}

add_action('manage_ph-website_posts_custom_column', 'ph_website_listing_columns_content', 10, 2);

if (!function_exists('ph_website_status_bar')) :
	/**
	 * Displays a progress bar for the project
	 *
	 * @param int $id Project ID
	 *
	 * @return void
	 */
	function ph_website_status_bar($id = 0)
	{
		global $post;
		if (!$id) {
			$id = is_object($post) ? $post->ID : false;
		}
		if (!$id) {
			return;
		}

		// get project status
		$status = ph_get_website_resolve_status($id);

		// get percentage
		$percentage = $status['total'] ? $status['resolved'] / $status['total'] * 100 : 0;

		// is complete?
		$approval_class = $status['total'] && $status['resolved'] >= $status['total'] ? 'approved' : 'unapproved';

		// progress string
		if ($status['total'] == 0) {
			$progress_string = __('No comments yet.', 'project-huddle');
		} else {
			$progress_string = sprintf(
				__('%1$s of %2$s issues resolved.', 'project-huddle'),
				$status['resolved'],
				$status['total']
			);
		}

		echo '<div class="progress-wrap ' . $approval_class . '" data-tooltip="' . esc_attr($progress_string) . '">';
		echo '<div class="ph-approval-bar"><div class="ph-approval-amount" style="width: ' . $percentage . '%;"></div></div>';
		echo '</div>';
	}
endif;
