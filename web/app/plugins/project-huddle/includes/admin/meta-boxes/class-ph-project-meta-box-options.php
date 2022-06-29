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
class PH_Meta_Box_Project_Options
{

	public static $fields = array();

	public static function meta_fields()
	{
		$fields = apply_filters(
			'ph_project_meta_box_options',
			array(
				array(
					'id'          => 'allow_guests',
					'label'       => __('Allow Guests', 'project-huddle'),
					'description' => __('Allow anyone who visits this project to register and leave comments.', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => '',
				),
				array(
					'id'          => 'force_login',
					'label'       => __('Force Login', 'project-huddle'),
					'description' => __('Force users to login when visiting a Project Access link.', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => '',
				),
				array(
					'id'          => 'project_sharing',
					'label'       => __('Email Sharing', 'project-huddle'),
					'description' => __('Enable email sharing for this project', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => 'on',
				),
				array(
					'id'          => 'retina',
					'label'       => __('Retina Images', 'project-huddle'),
					'description' => __('Scale down images to half their size for retina screens', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => '',
				),
				array(
					'id'          => 'project_download',
					'label'       => __('Image Download', 'project-huddle'),
					'description' => __('Adds a link to the dropdown menu that lets users download the image', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => '',
				),
				array(
					'id'          => 'zoom',
					'label'       => __('Zoom Controls', 'project-huddle'),
					'description' => __('Adds controls for the project to zoom in/out of the image.', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => 'on',
				),
				array(
					'id'          => 'project_approval',
					'label'       => __('Approvals', 'project-huddle'),
					'description' => __('Enable approvals for this project', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => 'on',
				),
				array(
					'id'          => 'project_unapproval',
					'label'       => __('Unapprovals', 'project-huddle'),
					'description' => __('Enable unapproval of approved images for this project', 'project-huddle'),
					'type'        => 'checkbox',
					'prefix'      => false,
					'default'     => 'on',
				),
				array(
					'id'      => 'thread_subscribers',
					'label'   => __('New Task Subscribers', 'project-huddle'),
					'type'    => 'radio',
					'prefix'  => false,
					'options' => array(
						'all'    => __('All project members', 'project-huddle'),
						'author' => __('The project author and the person making the comment', 'project-huddle'),
						'none'     => __('Only the person who makes the comment', 'project-huddle'),
					),
					'default' => 'all',
				),
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

		$fields = self::meta_fields();

		foreach ($fields as $field) {
			$value = self::sanitize_field($field);
			update_post_meta($post_id, $field['id'], esc_attr($value));
		}

		// if password is empty, make public
		// if ('ph-project' === get_post_type($post_id)) {
		// 	if (empty($post->post_password) && $_POST['project_access'] == 'password') {
		// 		update_post_meta($post_id, 'project_access', 'link');
		// 	}
		// }
	}

	public static function sanitize_field($field)
	{

		$value = isset($_POST[$field['id']]) ? $_POST[$field['id']] : false;

		switch ($field['type']) {
			case 'checkbox':
				$value = $value ? esc_html($value) : 'off';
				break;
		}

		return $value;
	}
}
