<?php

/**
 * Comment Thread Data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2017, Andre Gagnon
 * @since       2.6.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Website Thread Class
 * @since 2.6.0
 */
class PH_Website_Thread extends PH_Thread
{
	protected $rest_base = 'website-thread';

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\WebsiteThread';

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'phw_comment_loc';

	/**
	 * Parent collection post type
	 *
	 * @var string
	 */
	public $parent_post_type = 'ph-webpage';

	/**
	 * Collection name
	 *
	 * @var string
	 */
	public $collection_name = 'threads';

	/**
	 * Slug for actions
	 *
	 * @var string
	 */
	public $action_slug = 'thread';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'website';

	/**
	 * Add object meta and filters
	 */
	public function __construct()
	{
		parent::__construct();

		// screenshot attached to thread
		add_filter('rest_api_init', array($this, 'screenshot'), 8, 2);
	}

	/**
	 * Schema for post meta
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_website_thread_meta',
			array(
				'path'           => array(
					'description' => esc_html__('CSS path to the clicked element.', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
				),
				'xPath'          => array(
					'description' => esc_html__('xPath to the element.', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
				),
				'relativeX'      => array(
					'description' => esc_html__('Relative horizontal click position of the element.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'relativeY'      => array(
					'description' => esc_html__('Relative vertical click position of the element.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'pageX'          => array(
					'description' => esc_html__('Page relative horizontal click position of the element.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'pageY'          => array(
					'description' => esc_html__('Page relative vertical click position of the element.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'html'           => array(
					'description' => esc_html__('Inner html of the clicked element.', 'project-huddle'),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'wp_kses_post',
					),
					'default'     => '',
				),
				'screenPosition' => array(
					'description' => esc_html__('X, Y percentage array of screen comment position.', 'project-huddle'),
					'type'        => 'array',
					'default'     => array(0, 0),
					'items'       => array(
						'description' => esc_html__('Percentage screen comment position.', 'project-huddle'),
						'type'        => 'number',
					),
				),
				'resX'           => array(
					'description' => esc_html__('Horizontal resolution of user who reported issue.', 'project-huddle'),
					'type'        => 'number',
					'default'     => '',
				),
				'resY'           => array(
					'description' => esc_html__('Vertical resolution of user who reported issue.', 'project-huddle'),
					'type'        => 'number',
					'default'     => '',
				),
				'resolved'       => array(
					'description' => esc_html__('Issue resolve status.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'browser'        => array(
					'description' => esc_html__('Browser information.', 'project-huddle'),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'default'     => '',
				),
				'browserVersion' => array(
					'description' => esc_html__('Browser version information.', 'project-huddle'),
					'type'        => 'number',
					'default'     => '',
				),
				'browserOS'      => array(
					'description' => esc_html__('Operating system information.', 'project-huddle'),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'default'     => '',
				),
				'page_url'       => array(
					'description' => esc_html__('Url for the page.', 'project-huddle'),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
				),
				'page_title'     => array(
					'description' => esc_html__('Title for the new page.', 'project-huddle'),
					'type'        => 'string',
				),
				'website_id'     => array(
					'description' => esc_html__('Website project id for the thread.', 'project-huddle'),
					'type'        => 'integer',
				),
				'total_comments' => array(
					'description' => esc_html__('Total comments on the thread.', 'project-huddle'),
					'type'        => 'integer',
					'readonly'    => true,
				),
				'assigned'       => array(
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
			'ph_website_thread_collection_parameters',
			array(
				'resolved'   => array(
					'description' => esc_html__('Limit results by resolved status.', 'project-huddle'),
					'type'        => 'boolean',
					'meta'        => true,
				),
				'assigned'   => array(
					'description' => esc_html__('Limit by ID of user who is assigned.', 'project-huddle'),
					'type'        => 'integer',
					'meta'        => true,
				),
				'project_id' => array(
					'description' => esc_html__('Limit results by project id status.', 'project-huddle'),
					'type'        => 'integer',
					'meta'        => true,
				),
			)
		);
	}

	/**
	 * Add comment data as rest field
	 */
	public function add_comment_data()
	{
		// comments
		register_rest_field(
			$this->post_type,
			'comments',
			array(
				'update_callback' => function ($comments, $post, $attr, $request, $object_type) {
					if (empty($comments)) {
						return new WP_Error('rest_missing_comment', __('You must provide at least one comment to create a thread.', 'project-huddle'), array('status' => '400'));
					}

					// save comments to post
					foreach ($comments as $comment) {
						$comment['post'] = $post->ID; // set the post id

						// new item
						$comments_request = new WP_REST_Request('POST', '/' . PH()->comment->namespace . '/' . PH()->comment->rest_base);

						// set new comment params
						$comments_request->set_body_params(apply_filters('ph_new_website_thread_comment', $comment, $post, $comments_request));

						// do request
						$created = rest_do_request($comments_request);
					}

					if (is_wp_error($created)) {
						return $created;
					}

					return $comments;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					// should ignore new posts
					if ($request->get_method() != 'POST' && $request->get_method() != 'PUT') {
						$expanded = (array) $request['_expand'];

						if (empty($expanded)) {
							return array();
						}

						if (!(array_key_exists('comments', $expanded) || array_key_exists('all', $expanded))) {
							return array();
						}
					}

					$url = '/' . PH()->comment->namespace . '/' . PH()->comment->rest_base;

					$comments_request = new WP_REST_Request('GET', $url);

					$comments_request->set_query_params(
						array(
							'post'       => array($post['id']),
							'per_page'   => apply_filters('ph_comments_per_page', 15),
							'order'      => 'desc',
							'_signature' => $request['_signature'],
						)
					);

					$response = rest_do_request($comments_request);

					return $response->get_data();
				},
				'schema'          => array(
					'description' => esc_html__('Array of comment objects in thread.', 'project-huddle'),
					'type'        => 'array',
					'items'       => array(
						'description' => esc_html__('Comment object.', 'project-huddle'),
						'type'        => 'object',
					),
				),
			)
		);
	}
	/**
	 * Override the default upload path.
	 *
	 * @param   array   $dir
	 * @return  array
	 */
	function screenshot_upload_dir($dir)
	{
		return array(
			'path'   => $dir['basedir'] . '/ph-screenshots',
			'url'    => $dir['baseurl'] . '/ph-screenshots',
			'subdir' => '/ph-screenshots',
		) + $dir;
	}

	/**
	 * Add/remove thread members
	 *
	 * @return void
	 */
	function members()
	{
		register_rest_field(
			$this->post_type,
			'members',
			array(
				'update_callback' => function ($members = array(), $post = '', $attr = '', $request = '', $object_type = '') {
					return ph_update_thread_members($post->ID, $members);
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return ph_get_thread_member_ids($post['id']);
				},
				'schema'          => array(
					'description' => esc_html__('Array of user ids who are subscribed to the thread.', 'project-huddle'),
					'type'        => 'array',
					'default'     => array(),
					'items'       => array(
						'description' => esc_html__('User ID.', 'project-huddle'),
						'type'        => 'integer',
					),
				),
			)
		);
	}

	function screenshot()
	{
		register_rest_field(
			$this->post_type,
			'screenshot',
			array(
				'update_callback' => function ($image, $post, $attr, $request, $object_type) {
					try {
						if (!$image) {
							return;
						}

						// set custom folder for screenshots
						$upload_dir = wp_upload_dir();
						$location = $upload_dir['basedir'] . '/ph-screenshots/';
						if (!file_exists($location)) {
							wp_mkdir_p($location);
						}

						// set filename and type
						$filename = 'screenshot_' . $post->ID . '.jpg';
						$type = 'image/jpeg';

						/** Include admin functions to get access to wp_tempnam() and wp_handle_sideload() */
						require_once ABSPATH . 'wp-admin/includes/admin.php';

						// Save the file.
						$tmpfname = wp_tempnam($filename);

						$fp = fopen($tmpfname, 'w+');

						if (!$fp) {
							return new WP_Error('rest_upload_file_error', __('Could not open file handle.'), array('status' => 500));
						}

						// get and decode base64 image
						$image_parts = explode('base64,', $image);
						$image_decoded = base64_decode($image_parts[1]);

						// write to temp
						fwrite($fp, $image_decoded);
						fclose($fp);

						// Now, sideload it in.
						$file_data = array(
							'error'    => null,
							'tmp_name' => $tmpfname,
							'name'     => $filename,
							'type'     => $type,
						);

						$overrides = array(
							'test_form' => false,
						);

						// Register our path override.
						add_filter('upload_dir', array($this, 'screenshot_upload_dir'));

						// move file to directory, safely
						$sideloaded = wp_handle_sideload($file_data, $overrides);

						// Set everything back to normal.
						remove_filter('upload_dir', array($this, 'screenshot_upload_dir'));

						// handle error
						if (isset($sideloaded['error'])) {
							@unlink($tmpfname);

							return new WP_Error('rest_upload_sideload_error', $sideloaded['error'], array('status' => 500));
						}

						// save url in meta
						update_post_meta($post->ID, 'screenshot', $sideloaded['url']);
					} catch (Exception $e) {
						ph_log($e->getMessage());
					}

					// return url to file
					return esc_url($sideloaded['url']);
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return esc_url(get_post_meta($post['id'], 'screenshot', true));
				},
				'schema'          => array(
					'description' => esc_html__('Array of comment objects in thread.', 'project-huddle'),
					'type'        => 'integer',
					'readonly'    => true,
				),
			)
		);
	}
}
