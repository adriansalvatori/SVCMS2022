<?php

/**
 * Project Options Meta Box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Meta_Box_Images Class
 *
 * @since 1.0
 */
class PH_Meta_Box_Images
{
	/**
	 * Stores the image collection
	 *
	 * @var array
	 */
	public static $collection = array();

	/**
	 * Display the metabox that holds our project images
	 * Holds spinner animation for loading
	 *
	 * @since 1.0
	 */
	public static function output()
	{
		global $post;

		// create nonce.
		wp_nonce_field('project_huddle_save_data', 'project_huddle_meta_nonce');

		// add thickbox popup.
		add_thickbox();
		settings_errors();
?>

		<!-- projects wrapper -->
		<div id="ph-project-images" class="ph-projects-wrapper">
			<span class="spinner"></span>
		</div>
		<script>
			jQuery(document).ready(function() {
				ph.start(<?php echo json_encode(
								PH()->mockup->rest->get(
									$post->ID,
									[
										'_expand' => [
											'images' => 'all',
										],
									]
								)
							); ?>);
			});
		</script>
		<!-- ph-projects-wrapper -->

<?php

		// allow adding additional output.
		do_action('ph_image_meta_box_output', $post);
	}

	/**
	 * Save meta box data
	 * Validates data on input
	 *
	 * @param integer $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public static function save($post_id, $post)
	{
		// phpcs:ignore
		$images = isset($_POST['ph-image-model']) ? $_POST['ph-image-model'] :  array();
		if (!empty($images) && is_array($images)) {
			foreach ($images as $image) {
				$object             = json_decode(stripslashes($image));
				self::$collection[] = json_decode(wp_json_encode($object), true);
			}
		}

		// if we have an image, update, otherwise create
		// we're using rest functions here, so sanitization and permissions
		// are handled by controller.
		if (!empty(self::$collection)) {
			$order = 0;

			foreach (self::$collection as $key => $image) {
				$image['menu_order'] = $order++; // set menu order.
				$image['parent_id']  = $post_id; // force mockup id as parent

				if (isset($image['id']) && $image['id'] && isset($image['date']) && $image['date'] && PH()->image->rest->get($image['id'])) {
					$updated = PH()->image->update_item($image['id'], $image);
				} elseif (isset($image['featured_media']) && $image['featured_media']) {
					// unset id just in case
					if (isset($image['id'])) {
						unset($image['id']);
					}

					unset($image['approval']);
					unset($image['approved']);

					// create new
					$created = PH()->image->create_item($image);

					// log error
					if (is_wp_error($created)) {
						ph_log($created);
					}
				}
			}
		}

		// return nothing.
		return false;
	}
}
