<?php
// require generic functions
require_once 'taxonomies/post-approval-functions.php';
require_once 'taxonomies/post-resolve-functions.php';
require_once 'taxonomies/post-workflow-functions.php';

/**
 * Get mockup project approval status
 *
 * @param int $mockup_id
 *
 * @return array
 */
function ph_get_mockup_approval_status($id)
{
	return ph_get_post_approval_status($id, 'project_image');
}

/**
 * Get approval status for items
 *
 * @param integer $id Project id
 * @param string $type Type of project (mockup or website)
 * @return array
 */
function ph_get_items_approval_status($id, $type = 'mockup')
{
	switch ($type) {
		case 'mockup';
			return ph_get_post_approval_status($id, 'project_image');
			break;
		case 'website':
			return ph_get_post_approval_status($id, 'ph-webpage');
			break;
		default:
			return [
				'total'    => 0,
				'approved' => 0,
			];
			break;
	}
}

/**
 * Get mockup project approval status
 *
 * @param int $mockup_id
 *
 * @return array
 */
function ph_get_mockup_resolve_status($id)
{
	return ph_get_project_resolve_status($id, 'ph_comment_location');
}

/**
 * Is the mockup approved?
 *
 * @param $id
 *
 * @return array
 */
function ph_mockup_is_approved($id)
{
	return ph_post_is_approved($id);
}

/**
 * Gets last approver
 *
 * @param integer $id Image Id
 * @return void
 */
function ph_get_image_approval_status($id)
{
	return ph_get_last_approval_comment($id);
}

/**
 * Gets resolve status of image comments
 *
 * @param integer $id
 * @return void
 */
function ph_get_image_resolve_status($id)
{
	return ph_get_item_resolve_status($id, 'ph_comment_location');
}

/**
 * Get mockup project approval status
 *
 * @param int $mockup_id
 *
 * @return array
 */
function ph_get_website_approval_status($id)
{
	return ph_get_items_approval_status($id, 'website');
}

/**
 * Gets resolve status of page comments
 *
 * @param integer $id
 * @return void
 */
function ph_get_page_resolve_status($id)
{
	return ph_get_item_resolve_status($id, 'phw_comment_loc');
}
