<?php

/**
 * Project Options Meta Box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Meta_Box_Project_Options Class
 *
 * @since 1.0
 */
class PH_Meta_Box_Website_Options
{

	public static $fields = array();

	public static function meta_fields()
	{
		global $post;
		$fields = apply_filters(
			'ph_website_meta_box_options',
			array(
				array(
					'id'      => 'project_access',
					'label'   => __('Project Access', 'project-huddle'),
					'type'    => 'radio',
					'prefix'  => false,
					'options' => array(
						'login'  => __('Must be logged in', 'project-huddle'),
						'link'   => __('Anyone with the Project Access Link', 'project-huddle'),
						'public' => __('Anyone who visits the site', 'project-huddle'),
					),
					'default' => 'link',
				),
				array(
					'id'          => 'access_link_login',
					'label'       => __('Allow Guests', 'project-huddle'),
					'description' => __('Let visitors comment while only providing a name and email.', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => !metadata_exists('post', $post->ID, 'access_link_login') ? true : '',
				),
				// array(
				// 	'id'      => 'toolbar_location',
				// 	'label'   => __( 'Toolbar Location', 'project-huddle' ),
				// 	'type'    => 'radio',
				// 	'prefix'  => false,
				// 	'options' => array(
				// 		'bottom-right' => __( 'Bottom Right', 'project-huddle' ),
				// 		'bottom-left'  => __( 'Bottom Left', 'project-huddle' ),
				// 	),
				// 	'default' => 'bottom-right',
				// ),
			)
		);

		return $fields;
	}

	/**
	 * Output the metabox
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function output($post)
	{

		// create nonce field
		wp_nonce_field('project_huddle_save_data', 'project_huddle_meta_nonce');

		$fields = self::meta_fields(); ?>

		<div id="project_options_container" class="ph_meta_box">

			<?php
			foreach ($fields as $field) {
				PH()->meta->display_field($field, $post);
			}
			?>

			<input type="submit" name="publish" id="update_options" class="button button-primary button-large" value="Update">
		</div>

<?php
	}

	/**
	 * Save meta box data
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function save($post_id, $post)
	{
	}

	public static function sanitize_field($field)
	{
	}
}
