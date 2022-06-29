<?php

use PH\Models\Image;

/**
 * Mockup Image
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
 * Mockup Image Model Class
 *
 * This class handles the project data
 *
 * @since 1.0
 */
class PH_Mockup_Image extends PH_Item
{
	protected $rest_base = 'mockup-image';

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\Image';

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'project_image';

	/**
	 * Parent collection post type
	 *
	 * @var string
	 */
	public $parent_post_type = 'ph-project';

	/**
	 * Collection name
	 *
	 * @var string
	 */
	public $collection_name = 'images';

	/**
	 * Slug for actions
	 *
	 * @var string
	 */
	public $action_slug = 'image';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'mockup';

	/**
	 * Stores image options array
	 *
	 * @var array
	 * @since  1.0.0
	 */
	public $options = array(
		'alignment'                 => array(
			'default'  => 'center', // left, right or center.
			'sanitize' => 'esc_html',
		),
		'size'                      => array(
			'default'  => 'normal', // left, right or center.
			'sanitize' => 'esc_html',
		),
		'background_color'          => array(
			'default'  => '#222', // left, right or center.
			'sanitize' => 'sanitize_hex_color',
		),
		'background_image'          => array(
			'default'  => '',
			'sanitize' => 'esc_url',
		),
		'background_image_position' => array(
			'default'  => 'center',
			'sanitize' => 'esc_url',
		)
	);

	/**
	 * Actions and filters
	 */
	public function __construct()
	{
		parent::__construct();

		// register fields.
		add_action('rest_api_init', array($this, 'version_field'));
		add_action('rest_api_init', array($this, 'options_field'));
		add_action('rest_api_init', array($this, 'approval_field'));
		add_action('rest_api_init', array($this, 'approval_data'));
		add_action('rest_api_init', array($this, 'resolve_status'));
		add_action('rest_api_init', array($this, 'resolved_field'));
		add_action('rest_api_init', array($this, 'media_type_field'));
		add_action('rest_api_init', array($this, 'private_comment'));
		add_action('rest_api_init', array($this, 'ph_comment_status') );

		// dynamic comment text for approval comments to make sure user display name and translations are up to date.
		add_filter('comment_text', array($this, 'approval_comment_text'), 8, 2);

		// sort collectino by menu order
		add_filter("ph_{$this->collection_name}_collection_data", array($this, 'sort_by_menu_order'));

		add_action('transition_post_status', array($this, 'clear_parent_transients_on_status_change'), 10, 3);
	}

	/**
	 * Schema for post meta
	 *
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_mockup_image_meta',
			array(
				'menu_order' => array(
					'description' => esc_html__('Order of image in project.', 'project-huddle'),
					'type'        => 'number',
					'default'     => 0,
				),
				'sketch_id'  => array(
					'description' => esc_html__('ID of sketch artboard', 'project-huddle'),
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
			'ph_mockup_image_collection_parameters',
			array(
				'sketch_id' => array(
					'description' => esc_html__('Limit results by sketch id.', 'project-huddle'),
					'type'        => 'string',
					'meta'        => true,
				),
			)
		);
	}

	/**
	 * Clear parent transient on update
	 *
	 * @param string $new
	 * @param string $old
	 * @param WP_Post $post
	 * @return void
	 */
	public function clear_parent_transients_on_status_change($new, $old, $post)
	{
		if ($post->post_type !== $this->post_type) {
			return;
		}
		$parent_id = get_post_meta($post->ID, 'parent_id', true);
		if ($parent_id) {
			delete_transient("ph_{$this->parent_post_type}_approval_status_" . $parent_id);
		}
	}

	/**
	 * Approval field
	 *
	 * @return void
	 */
	public function approval_field()
	{
		// register_rest_field(
		// 	$this->post_type,
		// 	'approval',
		// 	array(
		// 		'get_callback'    => function ($post, $attr, $request, $object_type) {
		// 			return Image::get($post['id'])->isApproved();
		// 		},
		// 		'update_callback' => function ($value, $post, $attr, $request, $object_type) {
		// 			return Image::get($post->ID)->saveApprovalStatus($value);
		// 		},
		// 		'schema'          => array(
		// 			'description' => esc_html__('Approval.', 'project-huddle'),
		// 			'type'        => 'boolean',
		// 			'default'     => false,
		// 		),
		// 	)
		// );
		register_rest_field(
			$this->post_type,
			'approved',
			array(
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return Image::get($post['id'])->isApproved();
				},
				'update_callback' => function ($value, $post, $attr, $request, $object_type) {
					return Image::get($post->ID)->saveApprovalStatus($value);
				},
				'schema'          => array(
					'description' => esc_html__('Approval.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
			)
		);
	}

	public function approval_data()
	{
		register_rest_field(
			$this->post_type,
			'approval_data',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (array) ph_get_image_approval_status($post['id']);
				},
				'schema'          => array(
					'description' => esc_html__('Array of approval data for the image.', 'project-huddle'),
					'type'        => 'array',
					// 'readonly'    => true,
				),
			)
		);
	}

	public function resolve_status()
	{
		register_rest_field(
			$this->post_type,
			'resolve_status',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (array) ph_get_image_resolve_status($post['id']);
				},
				'schema'          => array(
					'description' => esc_html__('Array of comment data for the image.', 'project-huddle'),
					'type'        => 'array',
					// 'readonly'    => true,
					'items'       => array(
						'description' => esc_html__('Associated number.', 'project-huddle'),
						'type'        => 'integer',
					),
				),
			)
		);
	}

	public function media_type_field()
	{
		register_rest_field(
			$this->post_type,
			'type',
			array(
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$type = get_post_meta($post['id'], $attr, true);
					$type = apply_filters("ph_{$this->endpoint_type}_rest_get_{$this->action_slug}_attribute", $type, $attr, $post);

					return $type ? esc_html($type) : 'image';
				},
				'update_callback' => function ($value, $post, $attr, $request, $object_type) {
					$updated = update_post_meta($post->ID, $attr, esc_html($value));

					do_action("ph_{$this->endpoint_type}_rest_update_{$this->action_slug}_attribute", $attr, $value, $post);

					return $updated;
				},
				'schema'          => array(
					'description' => esc_html__('Media type.', 'project-huddle'),
					'type'        => 'string',
					'default'     => 'image',
				),
			)
		);
	}

	public function private_comment()
	{
		register_rest_field(
			$this->post_type,
			'is_private',
			array(
				'update_callback' =>  function ( $value, $comment, $attr, $request, $object_type ) {

					if ( ! apply_filters( "ph_mockup_update_comment_{$attr}_allowed", true, $comment, $value ) ) {
						return new WP_Error( 'rest_forbidden_meta', __( 'Sorry, you are not allowed to do this.', 'project-huddle' ), array( 'status' => rest_authorization_required_code() ) );
					}

					$comment_meta = '';

					if( "true" == $value ) {
						$comment_meta = update_comment_meta( $comment->comment_ID, $attr, $value );
					}
					
					do_action( "ph_new_comment_meta_saved", $comment->comment_ID, $comment );

					return ( "true" == $value ) ? $comment_meta : null;
					
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$comment_meta = get_comment_meta( $post['id'], 'is_private', true);

					return filter_var( $comment_meta, FILTER_VALIDATE_BOOLEAN );
				},
				'schema'          => array(
					'description' => esc_html__( 'Private Comment.', 'project-huddle' ),
					'type'        => 'boolean',
					'default'     => false,
				),
			)
		);
	}

	public function ph_comment_status()
	{
		register_rest_field(
			$this->post_type,
			'is_status',
			array(
				'update_callback' =>  function ( $value, $comment, $attr, $request, $object_type ) {

					if ( ! apply_filters( "ph_mockup_update_comment_{$attr}_allowed", true, $comment, $value ) ) {
						return new WP_Error( 'rest_forbidden_meta', __( 'Sorry, you are not allowed to do this.', 'project-huddle' ), array( 'status' => rest_authorization_required_code() ) );
					}

					$comment_meta = '';
					
					if( isset($value) ) {
						update_comment_meta( $comment->comment_ID, $attr, $value );
					}
					
					do_action( "ph_new_comment_meta_saved", $comment->comment_ID, $comment );

					return;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$comment_meta = get_comment_meta( $post['id'], 'is_status', true);

					return $comment_meta;
				},
				'schema'          => array(
					'description' => esc_html__( 'Comment Status.', 'project-huddle' ),
					'type'        => 'string',
					'default'     => 'active',
				),
			)
		);
		
	}


	/**
	 * Resolved field
	 *
	 * @return void
	 */
	public function resolved_field()
	{
		register_rest_field(
			$this->post_type,
			'resolved',
			array(
				'get_callback'    => null, // don't allow get
				'update_callback' => function ($value, $post, $attr, $request, $object_type) {
					if (!$value) {
						return;
					}
					global $is_ph_batch;
					$is_ph_batch = true;

					// get unresolved threads
					$threads = PH()->mockup_thread->rest->fetch(
						array(
							'parent_id' => $post->ID,
							'per_page'  => 200,
						)
					);

					foreach ($threads as $thread) {
						$request = new WP_REST_Request('PATCH', PH()->mockup_thread->rest->route() . '/' . $thread['id']);
						$request->set_param('resolved', $value);

						$response = rest_do_request($request);

						// handle error and short circuit
						if ($response->is_error()) {
							// Convert to a WP_Error object and bail
							ph_log($response->as_error());
							return $response->as_error();
						}
					}

					// clear transients
					$parents = ph_get_parents_ids($post);
					delete_transient("ph_{$this->post_type}_resolve_status_" . $post->ID);
					delete_transient("ph_{$this->parent_post_type}_resolve_status_" . $parents['project']);

					return true;
				},
				'schema'          => array(
					'description' => esc_html__('Whether to resolve all threads in this image.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
			)
		);
	}

	/**
	 * Register versions field for schema
	 */
	public function version_field()
	{
		register_rest_field(
			$this->post_type,
			'version',
			array(
				'schema' => array(
					'description' => esc_html__('Save previous version history for this change.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
			)
		);
	}

	/**
	 * Image options field
	 *
	 * @return void
	 */
	public function options_field()
	{
		register_rest_field(
			$this->post_type,
			'options',
			array(
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$options = apply_filters('ph_image_options_defaults', $this->options);

					foreach ($options as $key => $option) {
						$option_value = get_post_meta($post['id'], $key, true);

						// get value or default
						$value = $option_value ? $option_value : $options[$key]['default'];

						// call sanitize function
						if (isset($option['sanitize']) && $option['sanitize']) {
							$value = call_user_func($option['sanitize'], $value);
						}

						$options[$key] = $value;
					}

					return $options;
				},
				'update_callback' => function ($options, $post, $attr, $request, $object_type) {
					if (empty($options) || !is_array($options)) {
						return $options;
					}

					foreach ($options as $key => $option) {
						$this->options = apply_filters('ph_image_options_defaults', $this->options);
						if (array_key_exists($key, $this->options)) {
							update_post_meta($post->ID, $key, esc_html($option));
						}
					}

					return $this->options;
				},
				'schema'          => array(
					'type'  => 'object',
					'items' => array(
						'description' => esc_html__('Image display options.', 'project-huddle'),
						'type'        => 'string',
					),
				),
			)
		);
	}

	/**
	 * Fallback filters
	 *
	 * @param WP_Post         $prepared_post Prepared post.
	 * @param WP_Rest_Request $request       Rest request.
	 *
	 * @return object
	 */
	public function filters($prepared_post, $request)
	{
		$method = $this->map_method($request->get_method());

		return apply_filters('ph_' . $method . '_mockup_image', $prepared_post, $request);
	}

	/**
	 * Make approval comment text dynamic
	 *
	 * @param string     $comment_text Comment text.
	 * @param WP_Comment $comment      Comment object.
	 *
	 * @return string Comment text
	 */
	public function approval_comment_text($comment_text = '', $comment = null)
	{
		if (isset( $comment ) && 'ph_approval' !== $comment->comment_type || get_post_type($comment->comment_post_ID) !== $this->post_type) {
			return $comment_text;
		}

		// get dynamic author and approval meta.
		$author   = $comment->user_id ? get_userdata($comment->user_id)->display_name : $comment->comment_author;
		$approval = get_comment_meta($comment->comment_ID, 'approval', true) ? __('approved', 'project-huddle') : __('unapproved', 'project-huddle');

		// translators: {author} {approved} {title}.
		$comment_text = apply_filters('ph_approval_comment_text', sprintf(__('%1$s %2$s %3$s.'), esc_html($author), esc_html($approval), '<strong>' . esc_html(get_the_title($comment->comment_post_ID)) . '</strong>'), $comment);

		return $comment_text;
	}

	/**
	 * Sort images by menu order to keep order correct.
	 *
	 * @param array $args Arguments for get request.
	 *
	 * @return array
	 */
	public function sort_by_menu_order($args)
	{
		$args['order_by'] = 'menu_order';

		return $args;
	}
}
