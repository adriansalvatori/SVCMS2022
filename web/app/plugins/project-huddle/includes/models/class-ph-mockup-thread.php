<?php

/**
 * Comment Thread Data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2017, Andre Gagnon
 * @since       2.6.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Website Thread Class
 * @since 2.6.0
 */
class PH_Mockup_Thread extends PH_Thread
{
	protected $rest_base = 'mockup-thread';

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\MockupThread';

	/**
	 * Post type
	 * @var string
	 */
	public $post_type = 'ph_comment_location';

	/**
	 * Parent collection post type
	 * @var string
	 */
	public $parent_post_type = 'project_image';

	/**
	 * Collection name
	 * @var string
	 */
	public $collection_name = 'threads';

	/*
	 * Slug for actions
	 * @var string
	 */
	public $action_slug = 'thread';

	/**
	 * For mockup commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'mockup';

	/**
	 * Schema for post meta
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_mockup_thread_meta',
			array(
				'relativeX' => array(
					'description' => esc_html__('Relative horizontal click position of the element.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'relativeY' => array(
					'description' => esc_html__('Relative vertical click position of the element.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'resolved'  => array(
					'description' => esc_html__('Issue resolve status.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'assigned'  => array(
					'description' => esc_html__('ID of user who is assigned.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
				),
				'is_status'       => array(
					'description' => esc_html__('Status of the comment.', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
				),
			)
		);
	}

	/**
	 * Collection query params
	 *
	 * Allows querying collections via the following meta data.
	 *
	 * @return array
	 */
	public function collection_params()
	{
		return apply_filters(
			'ph_mockup_thread_collection_parameters',
			array(
				'resolved' => array(
					'description' => esc_html__('Limit results by resolved status.', 'project-huddle'),
					'type'        => 'boolean',
					'meta'        => true,
				),
				'assigned' => array(
					'description' => esc_html__('Limit by ID of user who is assigned.', 'project-huddle'),
					'type'        => 'integer',
					'meta'        => true,
				),
			)
		);
	}
}
