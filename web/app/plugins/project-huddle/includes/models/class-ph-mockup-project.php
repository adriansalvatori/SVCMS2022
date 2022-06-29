<?php

/**
 * Mockup Project Data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.6.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Project Class
 *
 * This class handles the project data
 *
 * @since 2.6.0
 */
class PH_Mockup_Project extends PH_Project
{
	protected $rest_base = 'mockup';

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = \PH\Models\Mockup::class;

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'ph-project';

	/**
	 * Slug for actions
	 *
	 * @var string
	 */
	public $action_slug = 'project';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'mockup';

	/**
	 * Register actions
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// force boolean on strings.
		add_filter("ph_mockup_rest_get_{$this->action_slug}_attribute", array($this, 'force_boolean'), 10, 3);
	}

	/**
	 * Schema for post meta
	 *
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_website_meta',
			array(
				'images'             => array(
					'description' => esc_html__('An array of image post objects belonging to project.', 'project-huddle'),
					'type'        => 'array',
					'default'     => array(),
					'items'       => array(
						'description' => esc_html__('Image post object.', 'project-huddle'),
						'type'        => 'object',
						'readonly'    => true,
					),
					'readonly'    => true,
					'custom'      => true,
				),
				'project_access'     => array(
					'description' => esc_html__('Access options for the project', 'project-huddle'),
					'type'        => 'string',
					'default'     => 'login',
				),
				'thread_subscribers'     => array(
					'description' => esc_html__('Option for new thread subscribers', 'project-huddle'),
					'type'        => 'string',
					'default'     => 'all',
				),
				'retina'             => array(
					'description' => esc_html__('Whether to serve the images as retina', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'sharing'            => array(
					'description' => esc_html__('Is the project sharing UI enabled?', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => true,
				),
				'zoom'               => array(
					'description' => esc_html__('Is the project zoom functionality enabled', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => true,
				),
				'tooltip'            => array(
					'description' => esc_html__('Show the Leave A Comment tooltip.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => true,
				),
				'allow_guests'  => array( 
					'description' => esc_html__('Allow anyone to access the project and leave comments.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'force_login'  => array(
					'description' => esc_html__('Force users to login instead of using an access link.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'project_download'  => array(
					'description' => esc_html__('Allow the user to download project images', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'project_comments'   => array(
					'description' => esc_html__('Allow non-users to make comments', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'project_approval'   => array(
					'description' => esc_html__('Approvals allowed?', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => true,
				),
				'project_unapproval' => array(
					'description' => esc_html__('Unapprovals allowed?', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => true,
				),
			)
		);
	}

	public function collection_params()
	{
		return apply_filters(
			'ph_mockup_collection_parameters',
			array(
				'project_member' => array(
					'description' => esc_html__('Limit results to only those you are a member of.', 'project-huddle'),
					'type'        => 'boolean',
					'meta'        => false,
				),
			)
		);
	}

	/**
	 * Force boolean for "on", "off" on REST requests
	 *
	 * @param string  $data Attribute value.
	 * @param string  $attr Attribute.
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	public function force_boolean($data, $attr, $post)
	{
		if (in_array(
			$attr,
			array(
				'retina',
				'sharing',
				'zoom',
				'tooltip',
				'project_download',
				'project_comments',
				'project_approval',
				'project_unapproval',
			)
		)) {
			$data = filter_var($data, FILTER_VALIDATE_BOOLEAN);
		}
		return $data;
	}
}
