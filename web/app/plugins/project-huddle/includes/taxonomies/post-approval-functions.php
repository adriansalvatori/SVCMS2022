<?php

/**
 * Approval Taxonomy Functions
 */

function ph_all_project_items_approved($project_id)
{
	// get items
	$items = new WP_Query(
		array(
			'post_type'      => ph_get_child_post_types(),
			'posts_per_page' => -1,
			'meta_value'     => $project_id,
			'meta_key'       => 'parent_id',
			'fields'         => 'ids',
		)
	);

	$item_ids = $items->posts;
	$total    = count($items->posts);
	$approved = 0;

	if (!empty($item_ids)) {
		// count approved
		foreach ($item_ids as $item_id) {
			if (ph_post_is_approved($item_id)) {
				$approved++;
			}
		}
	}

	return $total === $approved;
}

/**
 * Get post approval status
 *
 * @param integer $id ID of post
 * @param string $post_type Type of approved post in sub collection
 * @return array
 */
function ph_get_post_approval_status($id = 0, $post_type = 'project_image')
{
	$defaults = array(
		'total'    => 0,
		'approved' => 0,
	);

	// check for global post id
	if (!$id) {
		global $post;
		$id = isset($post->ID) ? $post->ID : false;
	}

	// must have id
	if (!$id) {
		return $defaults;
	}

	// get transient
	$approval_status = get_transient("ph_approved_status_" . $id);

	// this code runs when there is no valid transient set.
	if (false === $approval_status) {
		// get pages.
		$items = new WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'meta_value'     => $id,
				'meta_key'       => 'parent_id',
				'fields'         => 'ids',
			)
		);

		$item_ids          = $items->posts;
		$approval_comments = array();
		$approved          = 0;

		if (!empty($item_ids)) {
			// count approved
			foreach ($item_ids as $item_id) {
				if (ph_post_is_approved($item_id)) {
					$approved++;
				}
			}

			// get last approval comment
			$approval_comments = ph_get_comments(
				array(
					'type'     => ph_approval_term_taxonomy(),
					'post__in' => $item_ids,
					'number'   => 1,
				)
			);
		}

		$by = false;
		$on = false;

		if (!empty($approval_comments) && is_array($approval_comments)) {
			$comment = $approval_comments[0];

			if (is_a($comment, 'WP_Comment')) {
				$by = $comment->user_id ? get_userdata($comment->user_id)->display_name : $comment->comment_author;
				$on = $comment->comment_date_gmt; // use gmt and normalize to users timezone later
			}
		}

		$approval_status = array(
			'total'    => count($item_ids),
			'approved' => $approved,
			'by'       => $by,
			'on'       => $on,
		);

		set_transient("ph_approved_status_" . $id, $approval_status, 30 * DAY_IN_SECONDS); // expires in 1 month
	}

	return wp_parse_args($approval_status, $defaults);
}

/**
 * Gets the last approval comment for a given post
 *
 * @param integer $id
 * @return array
 */
function ph_get_last_approval_comment($id = 0)
{
	$defaults = array(
		'by' => '',
		'on' => '',
	);

	if (!$id) {
		global $post;
		$id = isset($post->ID) ? $post->ID : false;
	}

	if (!$id) {
		return $defaults;
	}

	// get last approval comment
	$approval_comments = ph_get_comments(
		array(
			'type'    => ph_approval_term_taxonomy(),
			'post_id' => $id,
			'number'  => 1,
		)
	);

	$by = false;
	$on = false;

	if (!empty($approval_comments) && is_array($approval_comments)) {
		$comment = $approval_comments[0];

		if (is_a($comment, 'WP_Comment')) {
			$by = $comment->user_id ? get_userdata($comment->user_id)->display_name : $comment->comment_author;
			$on = $comment->comment_date;
		}
	}

	$approval_status = array(
		'by' => $by,
		'on' => $on,
	);

	return wp_parse_args($approval_status, $defaults);
}

/**
 * Update project approval when image approval changes
 *
 * @param int    $object_id Object ID.
 * @param array  $terms     An array of object terms.
 * @param array  $tt_ids    An array of term taxonomy IDs.
 * @param string $taxonomy  Taxonomy slug.
 */
function ph_item_approval_relationships($object_id, $terms, $tt_ids, $taxonomy)
{

	if( ! function_exists('ph_get_parents_ids') ) {
		return;
	}
	$parents_ids = ph_get_parents_ids($object_id);

	// must have a project, must not be a project
	if (!$parents_ids['project'] || in_array(get_post_type($object_id), ph_get_project_post_types())) {
		return;
	}

	// clear transient
	$post_type = get_post_type($parents_ids['project']);
	delete_transient("ph_approval_status_" . $parents_ids['project']);

	// update project approval
	ph_post_set_approval(ph_all_project_items_approved($parents_ids['project']), $parents_ids['project']);
}

// update project approval when image approval changes
add_action('set_object_terms', 'ph_item_approval_relationships', 10, 4);
add_action('updated_post_meta', 'ph_item_approval_relationships', 10, 4);

/**
 * Update project approval when image approval changes
 *
 * @param int    $object_id Object ID.
 * @param array  $terms     An array of object terms.
 * @param array  $tt_ids    An array of term taxonomy IDs.
 * @param string $taxonomy  Taxonomy slug.
 */
function ph_project_approval_relationships($object_id, $terms, $tt_ids, $taxonomy)
{
	if (!in_array(get_post_type($object_id), ph_get_post_types())) {
		return;
	}

	// get items
	$items = new WP_Query(
		array(
			'post_type'      => ph_get_child_post_types(),
			'posts_per_page' => -1,
			'meta_value'     => $object_id,
			'meta_key'       => 'parent_id',
			'fields'         => 'ids',
		)
	);

	$item_ids = $items->posts;
	if (!empty($item_ids)) {
		foreach ($item_ids as $item_id) {
			ph_post_set_approval(ph_post_is_approved($object_id), $item_id);
		}
	}
}

// update image approvals when mockup approval changes
// add_action('set_object_terms', 'ph_project_approval_relationships', 10, 4);

/**
 * Define default approval status on new posts
 */
function ph_set_default_object_approval($post_id, $post)
{
	if ('publish' === $post->post_status) {
		// post type must support approvals.
		if (!post_type_supports($post->post_type, 'approvals')) {
			return;
		}

		// set defaults.
		$defaults = array(
			ph_approval_term_taxonomy() => array(ph_get_default_approval_status()),
		);

		$taxonomies = get_object_taxonomies($post->post_type);
		foreach ((array) $taxonomies as $taxonomy) {
			$terms = wp_get_post_terms($post_id, $taxonomy);
			if (empty($terms) && array_key_exists($taxonomy, $defaults)) {
				wp_set_object_terms($post_id, $defaults[$taxonomy], $taxonomy);
			} elseif (ph_approval_term_taxonomy() === $taxonomy && count($terms) > 1) {
				return new WP_Error('You can only add one approval status to this post', 'project-huddle');
			}
		}
	}
}
// add_action( 'save_post', 'ph_set_default_object_approval', 100, 2 );

/**
 * Abstract the term taxonomy
 *
 * @return string
 */
function ph_approval_term_taxonomy()
{
	return apply_filters('ph_approval_term_taxonomy', 'ph_approval');
}

/**
 * Default Approval Status
 *
 * @return string
 */
function ph_get_default_approval_status($post_id = 0)
{
	if (!$post_id) {
		global $post;
		$post_id = $post ? $post->ID : 0;
	}

	return apply_filters('ph_default_approval_status', 'unapproved', $post_id);
}

/**
 * Completed status
 *
 * @return string
 */
function ph_approval_completed_status()
{
	return apply_filters('ph_approval_completed_status', 'approved');
}

/**
 * Approval Meta Box Select
 */
function ph_approval_meta_box_select($post)
{
	// get the terms.
	$terms = get_terms(ph_approval_term_taxonomy(), array('hide_empty' => false));

	// get the post.
	$post = get_post();

	// get approval terms.
	$approval = wp_get_object_terms(
		$post->ID,
		ph_approval_term_taxonomy(),
		array(
			'orderby' => 'term_id',
			'order'   => 'ASC',
		)
	);

	$id = 0;
	if (!is_wp_error($approval)) {
		if (isset($approval[0]) && isset($approval[0]->ID)) {
			$id = $approval[0]->ID;
		} else {
			$slug    = ph_get_default_approval_status();
			$default = get_term_by('slug', $slug, ph_approval_term_taxonomy());
			$id      = $default->ID;
		}
	}

	foreach ($terms as $term) { ?>
		<label title='<?php esc_attr_e($term->ID); ?>'>
			<input type="radio" name="ph_approval" value="<?php esc_attr_e($term->ID); ?>" <?php checked($term->ID, $id); ?>>
			<span><?php esc_html_e($term->name); ?></span>
		</label><br>
	<?php

	}
	?>
	<p><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=ph_approval')); ?>"><?php esc_html_e('Edit Approval Statuses', 'project-huddle'); ?></a></p>
<?php

}

/**
 * Save the approval meta box
 *
 * @param int $post_id The ID of the post that's being saved.
 */
function save_ph_approval_meta_box($post_id)
{
	// bail on autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// post must be set
	if (!isset($_POST[ph_approval_term_taxonomy()])) {
		return;
	}

	// get approval
	$approval = sanitize_text_field($_POST[ph_approval_term_taxonomy()]);

	// A valid approval is required, so don't let this get published without one.
	if (!empty($approval)) {
		$term = get_term_by('name', $approval, ph_approval_term_taxonomy());
		if (!empty($term) && !is_wp_error($term)) {
			wp_set_object_terms($post_id, $term->term_id, ph_approval_term_taxonomy(), false);
		}
	}
}
add_action('save_post', 'save_ph_approval_meta_box');

/**
 * Is the post approved
 *
 * @param WP_Post|int $post_id Post id or post object.
 *
 * @return bool
 */
function ph_post_is_approved($post_id = 0)
{
	if (!is_int($post_id) && is_a($post_id, 'WP_Post')) {
		$post_id = $post_id->ID;
	}

	if (metadata_exists('post', $post_id, 'approved')) {
		return (bool) get_post_meta($post_id, 'approved', true);
	}

	return has_term(ph_approval_completed_status(), ph_approval_term_taxonomy(), $post_id);
}


/**
 * Set Item approval status
 *
 * @param boolean $approved Approved or not.
 * @param WP_Post $post     Post object to approve.
 *
 * @return array|WP_Error Term taxonomy IDs of the affected terms.
 */
function ph_post_set_approval($approved, $post, $trigger = false)
{
	if (!$post) {
		global $post;
	}
	$post_object = get_post($post);

	update_post_meta($post_object->ID, 'approved', (bool) $approved);

	$status = $approved ? ph_approval_completed_status() : ph_get_default_approval_status();

	wp_delete_object_term_relationships($post_object->ID, ph_approval_term_taxonomy());
	$approval_terms = wp_set_object_terms($post_object->ID, $status, ph_approval_term_taxonomy());

	// trigger email if not a new post
	if ($trigger) {
		do_action('ph_rest_set_approval', $post_object, $approved);
	}

	return $approval_terms;
}

/**
 * Store approval comment
 *
 * @param $approval_term WP_Term Status approval term
 * @param $post_id       Integer Post ID
 * @param $value         Boolean Approved or not
 */
// function ph_store_approval_info($approval_term, $post_id, $value)
// {
// 	// get current user
// 	$user = wp_get_current_user();

// 	// if we did a project vs item
// 	if (did_action('ph_rest_project_approval')) {
// 		$text = sprintf(__('%1$s %2$s the project "%3$s"', 'project-huddle'), $user->display_name, strtolower($approval_term->name), ph_get_the_title($post_id));
// 	} else {
// 		$text = sprintf(__('%1$s %2$s %3$s', 'project-huddle'), $user->display_name, strtolower($approval_term->name), ph_get_the_title($post_id));
// 	}

// 	// Insert new comment and get the comment ID
// 	wp_insert_comment(
// 		array(
// 			'comment_post_ID'      => (int) $post_id,
// 			'comment_author'       => $user->display_name,
// 			'comment_author_email' => $user->user_email,
// 			'user_id'              => $user->ID,
// 			'comment_content'      => wp_kses_post($text),
// 			'comment_type'         => ph_approval_term_taxonomy(),
// 			'comment_approved'     => 1, // force approval
// 			'comment_meta'         => array(
// 				'approval' => (bool) $value,
// 			),
// 		)
// 	);
// }

// // Add approval info comment
// add_action('ph_rest_project_approval', 'ph_store_approval_info', 10, 3);

/**
 * Logic to control whether to trigger a project or item approval event
 *
 * @param $post  WP_Post Item post that was approved
 * @param $value Boolean Approval value
 */
function ph_trigger_project_or_item_approval($post, $value)
{
	global $is_ph_batch;

	$status        = $value ? ph_approval_completed_status() : ph_get_default_approval_status();
	$approval_term = get_term_by('slug', $status, ph_approval_term_taxonomy());

	if( ! function_exists('ph_get_parents_ids') ) {
		return;
	}
	// get parents
	$parents = ph_get_parents_ids($post);

	// need project
	if (!$parents['project']) {
		return;
	}

	// make sure we're not running other batches
	static $batch_running;
	if ($batch_running) {
		return;
	}

	// if we're doing batch, short circuit and send project email
	if ($is_ph_batch) {
		$batch_running = true;

		// if we're batch approving items, trigger project approval action
		if (in_array($post->post_type, ph_get_item_post_types())) {
			do_action('ph_rest_project_approval', $approval_term, $parents['project'], $value);
		} elseif (in_array($post->post_type, ph_get_thread_post_types())) {
			do_action('ph_rest_item_comments_resolved', $parents['item'], $value);
		}
	} else {
		// if we have a parent, we are approving, and
		if ($parents['project'] && $value && ph_all_project_items_approved($parents['project'])) {
			do_action('ph_rest_project_approval', $approval_term, $parents['project'], $value);
		} else {
			do_action('ph_rest_item_approval', $approval_term, $post->ID, $value);
		}
	}
}

add_action('ph_rest_set_approval', 'ph_trigger_project_or_item_approval', 10, 2);
