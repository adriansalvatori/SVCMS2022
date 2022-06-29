<?php

use PH\Models\Post;
use PH\Models\Visitor;

/**
 * REST API: PH_REST_Versions_Controller class
 *
 * @package ProjectHuddle
 * @subpackage REST_API
 * @since 3.0.0
 */

/**
 * Used to access versions via the REST API.
 *
 * @since 3.0.0
 *
 * @see WP_REST_Controller
 */
class PH_REST_Versions_Controller extends WP_REST_Controller
{

	/**
	 * Parent post type.
	 *
	 * @since 4.7.0
	 * @var string
	 */
	private $parent_post_type;

	/**
	 * Parent controller.
	 *
	 * @since 4.7.0
	 * @var WP_REST_Controller
	 */
	private $parent_controller;

	/**
	 * The base of the parent controller's route.
	 *
	 * @since 4.7.0
	 * @var string
	 */
	private $parent_base;

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 *
	 * @param string $parent_post_type Post type of the parent.
	 */
	public function __construct($parent_post_type)
	{
		$this->parent_post_type = $parent_post_type;
		$this->parent_controller = new PH_REST_Posts_Controller($parent_post_type);
		$this->namespace = 'projecthuddle/v2';
		$this->rest_base = 'versions';
		$post_type_object = get_post_type_object($parent_post_type);
		$this->parent_base = !empty($post_type_object->rest_base) ? $post_type_object->rest_base : $post_type_object->name;
	}

	/**
	 * Registers routes for versions based on post types supporting versions.
	 *
	 * @since 4.7.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes()
	{

		register_rest_route($this->namespace, '/' . $this->parent_base . '/(?P<parent>[\d]+)/' . $this->rest_base, array(
			'args' => array(
				'parent' => array(
					'description' => __('The ID for the parent of the object.'),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array($this, 'get_public_item_schema'),
		));

		register_rest_route($this->namespace, '/' . $this->parent_base . '/(?P<parent>[\d]+)/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'parent' => array(
					'description' => __('The ID for the parent of the object.'),
					'type'        => 'integer',
				),
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check'),
				'args'                => array(
					'context' => $this->get_context_param(array('default' => 'view')),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'delete_item_permissions_check'),
				'args'                => array(
					'force' => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __('Required to be true, as versions do not support trashing.'),
					),
				),
			),
			'schema' => array($this, 'get_public_item_schema'),
		));
	}

	/**
	 * Get the parent post, if the ID is valid.
	 *
	 * @since 4.7.2
	 *
	 * @param int $parent Parent ID
	 *
	 * @return WP_Post|WP_Error Post object if ID is valid, WP_Error otherwise.
	 */
	protected function get_parent($parent)
	{
		$error = new WP_Error('rest_post_invalid_parent', __('Invalid post parent ID.'), array('status' => 404));
		if ((int) $parent <= 0) {
			return $error;
		}

		$parent = get_post((int) $parent);
		if (empty($parent) || empty($parent->ID) || $this->parent_post_type !== $parent->post_type) {
			return $error;
		}

		return $parent;
	}

	/**
	 * Checks if a given request has access to get versions.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check($request)
	{
		$parent = $this->get_parent($request['parent']);

		if (is_wp_error($parent)) {
			return $parent;
		}

		// must be able to access the post
		if (!Visitor::current()->canAccess(Post::get($parent->ID))) {
			return new WP_Error('rest_cannot_read', __('Sorry, you are not allowed to view versions.', 'project-huddle'), array('status' => rest_authorization_required_code()));
		}

		return true;
	}

	/**
	 * Get the version, if the ID is valid.
	 *
	 * @since 4.7.2
	 *
	 * @param int $id Supplied ID.
	 * @return WP_Post|WP_Error Revision post object if ID is valid, WP_Error otherwise.
	 */
	protected function get_version($id)
	{
		$error = new WP_Error('rest_post_invalid_id', __('Invalid version ID.'), array('status' => 404));
		if ((int) $id <= 0) {
			return $error;
		}

		$version = get_post((int) $id);
		if (empty($version) || empty($version->ID) || 'ph_version' !== $version->post_type) {
			return $error;
		}

		return $version;
	}

	/**
	 * Gets a collection of versions.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items($request)
	{
		$parent = $this->get_parent($request['parent']);
		if (is_wp_error($parent)) {
			return $parent;
		}

		// get versions
		$versions = ph_get_post_versions($request['parent']);

		// backwards compat
		$legacy_versions = (array) get_post_ancestors($request['parent']);
		if (!empty($legacy_versions)) {
			foreach ($legacy_versions as $key => $legacy_version) {
				$legacy_post = get_post($legacy_version);
				$legacy_post->post_parent = (int) $request['parent'];
				$legacy_versions[$key] = get_post($legacy_version);
			}
		}
		if ($legacy_children = (array) ph_get_all_post_children($request['parent'])) {
			foreach ($legacy_children as $child) {
				$legacy_versions[] = $child;
			}
		}
		$versions = array_merge($versions, $legacy_versions);

		$response = array();
		foreach ($versions as $version) {
			$data = $this->parent_controller->prepare_item_for_response($version, $request);
			$response[] = $this->prepare_response_for_collection($data);
		}
		return rest_ensure_response($response);
	}

	/**
	 * Checks if a given request has access to get a specific version.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check($request)
	{
		return $this->get_items_permissions_check($request);
	}

	/**
	 * Retrieves one version from the collection.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item($request)
	{
		$parent = $this->get_parent($request['parent']);
		if (is_wp_error($parent)) {
			return $parent;
		}

		$version = $this->get_version($request['id']);
		if (is_wp_error($version)) {
			return $version;
		}

		$response = $this->prepare_item_for_response($version, $request);
		return rest_ensure_response($response);
	}

	/**
	 * Checks if a given request has access to delete a version.
	 *
	 * @since 4.7.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check($request)
	{
		$parent = $this->get_parent($request['parent']);
		if (is_wp_error($parent)) {
			return $parent;
		}

		$version = $this->get_version($request['id']);
		if (is_wp_error($version)) {
			return $version;
		}

		$response = $this->get_items_permissions_check($request);
		if (!$response || is_wp_error($response)) {
			return $response;
		}

		$post_type = get_post_type_object('ph_version');
		return current_user_can($post_type->cap->delete_post, $version->ID);
	}

	/**
	 * Deletes a single version.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True on success, or WP_Error object on failure.
	 */
	public function delete_item($request)
	{
		$version = $this->get_version($request['id']);
		if (is_wp_error($version)) {
			return $version;
		}

		$force = isset($request['force']) ? (bool) $request['force'] : false;

		// We don't support trashing for versions.
		if (!$force) {
			/* translators: %s: force=true */
			return new WP_Error('rest_trash_not_supported', sprintf(__("Revisions do not support trashing. Set '%s' to delete."), 'force=true'), array('status' => 501));
		}

		$previous = $this->prepare_item_for_response($version, $request);

		$result = wp_delete_post($request['id'], true);

		/**
		 * Fires after a version is deleted via the REST API.
		 *
		 * @since 4.7.0
		 *
		 * @param (mixed) $result The version object (if it was deleted or moved to the trash successfully)
		 *                        or false (failure). If the version was moved to the trash, $result represents
		 *                        its new state; if it was deleted, $result represents its state before deletion.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		do_action('ph_rest_delete_version', $result, $request);

		if (!$result) {
			return new WP_Error('rest_cannot_delete', __('The post cannot be deleted.'), array('status' => 500));
		}

		$response = new WP_REST_Response();
		$response->set_data(array('deleted' => true, 'previous' => $previous->get_data()));
		return $response;
	}

	/**
	 * Prepares the version for the REST response.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_Post         $post    Post version object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response($post, $request)
	{
		$GLOBALS['post'] = $post;

		setup_postdata($post);

		$schema = $this->get_item_schema();

		$data = array();

		if (!empty($schema['properties']['author'])) {
			$data['author'] = (int) $post->post_author;
		}

		if (!empty($schema['properties']['featured_media'])) {
			$data['featured_media'] = (int) get_post_thumbnail_id($post->ID);
		}

		if (!empty($schema['properties']['date'])) {
			$data['date'] = $this->prepare_date_response($post->post_date_gmt, $post->post_date);
		}

		if (!empty($schema['properties']['date_gmt'])) {
			$data['date_gmt'] = $this->prepare_date_response($post->post_date_gmt);
		}

		if (!empty($schema['properties']['id'])) {
			$data['id'] = $post->ID;
		}

		if (!empty($schema['properties']['modified'])) {
			$data['modified'] = $this->prepare_date_response($post->post_modified_gmt, $post->post_modified);
		}

		if (!empty($schema['properties']['modified_gmt'])) {
			$data['modified_gmt'] = $this->prepare_date_response($post->post_modified_gmt);
		}

		if (!empty($schema['properties']['parent'])) {
			$data['parent'] = (int) $post->post_parent;
		}

		if (!empty($schema['properties']['slug'])) {
			$data['slug'] = $post->post_name;
		}

		// if ( ! empty( $schema['properties']['guid'] ) ) {
		// 	$data['guid'] = array(
		// 		/** This filter is documented in wp-includes/post-template.php */
		// 		'rendered' => apply_filters( 'get_the_guid', $post->guid, $post->ID ),
		// 		'raw'      => $post->guid,
		// 	);
		// }

		if (!empty($schema['properties']['title'])) {
			$data['title'] = array(
				'raw'      => $post->post_title,
				'rendered' => get_the_title($post->ID),
			);
		}

		if (!empty($schema['properties']['content'])) {

			$data['content'] = array(
				'raw'      => $post->post_content,
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters('the_content', $post->post_content),
			);
		}

		if (!empty($schema['properties']['excerpt'])) {
			$data['excerpt'] = array(
				'raw'      => $post->post_excerpt,
				'rendered' => $this->prepare_excerpt_response($post->post_excerpt, $post),
			);
		}

		$context = !empty($request['context']) ? $request['context'] : 'view';
		$data = $this->add_additional_fields_to_object($data, $request);
		$data = $this->filter_response_by_context($data, $context);
		$response = rest_ensure_response($data);

		if (!empty($data['parent'])) {
			$response->add_link('parent', rest_url(sprintf('%s/%s/%d', $this->namespace, $this->parent_base, $data['parent'])));
		}

		/**
		 * Filters a version returned from the API.
		 *
		 * Allows modification of the version right before it is returned.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     The original version object.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters('ph_rest_prepare_version', $response, $post, $request);
	}

	/**
	 * Checks the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @since 4.7.0
	 *
	 * @param string      $date_gmt GMT publication time.
	 * @param string|null $date     Optional. Local publication time. Default null.
	 * @return string|null ISO8601/RFC3339 formatted datetime, otherwise null.
	 */
	protected function prepare_date_response($date_gmt, $date = null)
	{
		if ('0000-00-00 00:00:00' === $date_gmt) {
			return null;
		}

		if (isset($date)) {
			return mysql_to_rfc3339($date);
		}

		return mysql_to_rfc3339($date_gmt);
	}

	/**
	 * Retrieves the version's schema, conforming to JSON Schema.
	 *
	 * @since 4.7.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema()
	{
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => "{$this->parent_post_type}-version",
			'type'       => 'object',
			// Base properties for every Revision.
			'properties' => array(
				'author'          => array(
					'description' => __('The ID for the author of the object.'),
					'type'        => 'integer',
					'context'     => array('view', 'edit', 'embed'),
				),
				'date'            => array(
					'description' => __("The date the object was published, in the site's timezone."),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array('view', 'edit', 'embed'),
				),
				'date_gmt'        => array(
					'description' => __('The date the object was published, as GMT.'),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array('view', 'edit'),
				),
				'guid'            => array(
					'description' => __('GUID for the object, as it exists in the database.'),
					'type'        => 'string',
					'context'     => array('view', 'edit'),
				),
				'id'              => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
					'context'     => array('view', 'edit', 'embed'),
				),
				'modified'        => array(
					'description' => __("The date the object was last modified, in the site's timezone."),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array('view', 'edit'),
				),
				'modified_gmt'    => array(
					'description' => __('The date the object was last modified, as GMT.'),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array('view', 'edit'),
				),
				'parent'          => array(
					'description' => __('The ID for the parent of the object.'),
					'type'        => 'integer',
					'context'     => array('view', 'edit', 'embed'),
				),
				'slug'            => array(
					'description' => __('An alphanumeric identifier for the object unique to its type.'),
					'type'        => 'string',
					'context'     => array('view', 'edit', 'embed'),
				),
			),
		);

		$parent_schema = $this->parent_controller->get_item_schema();

		$schema['properties'] = array_merge($schema['properties'], $parent_schema['properties']);

		if (!empty($parent_schema['properties']['title'])) {
			$schema['properties']['title'] = $parent_schema['properties']['title'];
		}

		if (!empty($parent_schema['properties']['content'])) {
			$schema['properties']['content'] = $parent_schema['properties']['content'];
		}

		if (!empty($parent_schema['properties']['excerpt'])) {
			$schema['properties']['excerpt'] = $parent_schema['properties']['excerpt'];
		}

		if (!empty($parent_schema['properties']['guid'])) {
			$schema['properties']['guid'] = $parent_schema['properties']['guid'];
		}

		return $this->add_additional_fields_schema($schema);
	}

	/**
	 * Retrieves the query params for collections.
	 *
	 * @since 4.7.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params()
	{
		return array(
			'context' => $this->get_context_param(array('default' => 'view')),
		);
	}

	/**
	 * Checks the post excerpt and prepare it for single post output.
	 *
	 * @since 4.7.0
	 *
	 * @param string  $excerpt The post excerpt.
	 * @param WP_Post $post    Post version object.
	 * @return string Prepared excerpt or empty string.
	 */
	protected function prepare_excerpt_response($excerpt, $post)
	{

		/** This filter is documented in wp-includes/post-template.php */
		$excerpt = apply_filters('the_excerpt', $excerpt, $post);

		if (empty($excerpt)) {
			return '';
		}

		return $excerpt;
	}
}

// Function to register our new routes from the controller.
function ph_register_version_routes()
{
	foreach (get_post_types(array('show_in_rest' => true), 'objects') as $post_type) {
		if (post_type_supports($post_type->name, 'versions')) {
			$versions = new PH_REST_Versions_Controller($post_type->name);
			$versions->register_routes();
		}
	}
}

add_action('rest_api_init', 'ph_register_version_routes');
