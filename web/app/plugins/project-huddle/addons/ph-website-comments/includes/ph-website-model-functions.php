<?php

/**
 * Functions for getting/creating/updating/deleting
 * ProjectHuddle models.
 *
 * Use these instead of direct class references for future compatibility
 */

if (!function_exists('ph_get_website')) :
	/**
	 * Get Website
	 *
	 * @param int   $id Website ID
	 * @param array $params
	 * @param array $query_params
	 */
	function ph_get_website($id = null, $params = array(), $query_params = array())
	{
		return PH()->website->rest->get($id, $params, $query_params);
	}
endif;

if (!function_exists('ph_get_website_page')) :
	/**
	 * Get Website
	 *
	 * @param int   $id Website ID
	 * @param array $params
	 * @param array $query_params
	 */
	function ph_get_website_page($id = null, $params = array(), $query_params = array())
	{
		return PH()->page->rest->get($id, $params, $query_params);
	}
endif;

if (!function_exists('ph_get_website_thread')) :
	/**
	 * Get Website
	 *
	 * @param int   $id Website ID
	 * @param array $params
	 * @param array $query_params
	 */
	function ph_get_website_thread($id = null, $params = array(), $query_params = array())
	{
		return PH()->website_thread->rest->get($id, $params, $query_params);
	}
endif;

if (!function_exists('ph_get_website_comments')) :
	/**
	 * Get Website
	 *
	 * @param int   $id Website ID
	 * @param array $params
	 * @param array $query_params
	 */
	function ph_get_website_comments($id = 0, $params = array(), $query_params = array())
	{
		PH()->website_thread->rest->get($id, $params, $query_params);
	}
endif;

if (!function_exists('ph_get_parents_ids')) :
	/**
	 * ph_get_parents_ids
	 */
	function ph_get_parents_ids($object, $type = 'post')
	{
		$defaults = array(
			'project' => 0,
			'item'    => 0,
			'thread'  => 0,
		);

		if (is_int($object)) {
			if ($type === 'post') {
				$object = get_post($object);
				if (!is_a($object, 'WP_Post')) {
					return $defaults;
				}
			} else {
				$object = get_comment($object);
				if (!is_a($object, 'WP_Comment')) {
					return $defaults;
				}
			}
		}

		$parent_ids = array();
		$object_id  = 0;

		// post or comment
		if (is_a($object, 'WP_Comment') && isset($object->comment_post_ID) && get_post_type($object->comment_post_ID)) {
			$parent_ids[get_post_type_object(get_post_type($object->comment_post_ID))->rest_base] = $object->comment_post_ID;

			$object_id = (int) $object->comment_post_ID;
		} elseif (is_a($object, 'WP_Post')) {
			$object_id = (int) $object->ID;
		}

		if (!$object_id) {
			return $defaults;
		}

		$finished = false;

		// add itself
		$parent_ids[get_post_type_object(get_post_type($object_id))->rest_base] = (int) $object_id;

		while (!$finished) {
			$parent_id = get_post_meta($object_id, 'parent_id', true);
			if (!$parent_id) {
				$finished = true;
			} else {
				$post_object = get_post_type_object(get_post_type($parent_id));

				if ($post_object) {
					$parent_ids[get_post_type_object(get_post_type($parent_id))->rest_base] = (int) $parent_id;
				}
				$object_id = (int) $parent_id;
			}
		}

		// do generic item
		$parent_ids['project'] = 0;
		if (isset($parent_ids['website'])) {
			$parent_ids['project'] = $parent_ids['website'];
		}
		if (isset($parent_ids['mockup'])) {
			$parent_ids['project'] = $parent_ids['mockup'];
		}

		// do generic item
		$parent_ids['item'] = 0;
		if (isset($parent_ids['website-page'])) {
			$parent_ids['item'] = $parent_ids['website-page'];
		}
		if (isset($parent_ids['mockup-image'])) {
			$parent_ids['item'] = $parent_ids['mockup-image'];
		}

		$parent_ids['thread'] = 0;
		if (isset($parent_ids['website-thread'])) {
			$parent_ids['thread'] = $parent_ids['website-thread'];
		}
		if (isset($parent_ids['mockup-thread'])) {
			$parent_ids['thread'] = $parent_ids['mockup-thread'];
		}


		return $parent_ids;
	}
endif;

if (!function_exists('ph_get_mockup_parent_ids')) :
	/**
	 * Get mockup parent ids
	 */
	function ph_get_mockup_parent_ids($post, $type = 'post')
	{
		return wp_parse_args(
			ph_get_parents_ids($post, $type),
			array(
				'mockup'        => 0,
				'mockup-thread' => 0,
				'mockup-image'  => 0,
			)
		);
	}
endif;
