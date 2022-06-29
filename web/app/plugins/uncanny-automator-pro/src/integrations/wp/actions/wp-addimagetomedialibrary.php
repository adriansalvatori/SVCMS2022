<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class WP_ADDIMAGETOMEDIALIBRARY
 *
 * @package Uncanny_Automator_Pro
 */
class WP_ADDIMAGETOMEDIALIBRARY {
	use Recipe\Actions;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->setup_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'WP' );
		$this->set_action_code( 'ADDIMAGE' );
		$this->set_action_meta( 'WPIMAGE' );
		$this->set_requires_user( false );
		$this->set_is_pro( true );

		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( esc_attr__( 'Add {{an image:%1$s}} to the media library', 'uncanny-automator-pro' ), $this->get_action_meta() ) );

		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Add {{an image}} to the media library', 'uncanny-automator-pro' ) );

		$this->set_options_callback( array( $this, 'load_options' ) );
		$this->register_action();
	}

	/**
	 * load_options
	 *
	 * @return array
	 */
	public function load_options() {

		$options = array(
			'options_group' => array(
				$this->get_action_meta() => array(
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'IMAGE_URL',
							/* translators: Email field */
							'label'       => esc_attr__( 'Image URL', 'uncanny-automator-pro' ),
							'placeholder' => esc_attr__( 'https://examplewebsite.com/path/to/image.jpg', 'uncanny-automator-pro' ),
							'input_type'  => 'url',
							'required'    => true,
							'description' => esc_attr__( 'The URL must include a supported image file extension (e.g. .jpg, .png, .svg, etc.). Some sites may block remote image download.', 'uncanny-automator-pro' ),
						)
					),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'IMAGE_TEXT',
							/* translators: Email field */
							'required'    => false,
							'label'       => esc_attr__( 'Alternative text', 'uncanny-automator-pro' ),
							'input_type'  => 'text',
						)
					),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => $this->get_action_meta(),
							/* translators: Email field */
							'required'    => false,
							'label'       => esc_attr__( 'Title', 'uncanny-automator-pro' ),
							'input_type'  => 'text',
						)
					),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'IMAGE_CAPTION',
							/* translators: Email field */
							'required'    => false,
							'label'       => esc_attr__( 'Caption', 'uncanny-automator-pro' ),
							'input_type'  => 'text',
						)
					),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code'      => 'IMAGE_DESCRIPTION',
							/* translators: Email field */
							'required'         => false,
							'label'            => esc_attr__( 'Description', 'uncanny-automator-pro' ),
							'input_type'       => 'textarea',
							'supports_tinymce' => false,
						)
					),
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options );
	}

	/**
	 * Process the action.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$image_url = Automator()->parse->text( $action_data['meta']['IMAGE_URL'], $recipe_id, $user_id, $args );
		$image_url = filter_var( $image_url, FILTER_SANITIZE_URL );
		if ( empty( $image_url ) ) {
			$action_data['complete_with_errors'] = true;
			$error_message                       = esc_html__( 'Invalid image url.', 'uncanny-automator' );
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$description = Automator()->parse->text( $action_data['meta']['IMAGE_DESCRIPTION'], $recipe_id, $user_id, $args );
		$title       = Automator()->parse->text( $action_data['meta'][ $this->get_action_meta() ], $recipe_id, $user_id, $args );
		$caption     = Automator()->parse->text( $action_data['meta']['IMAGE_CAPTION'], $recipe_id, $user_id, $args );
		$alt_text    = Automator()->parse->text( $action_data['meta']['IMAGE_TEXT'], $recipe_id, $user_id, $args );
		$image_id    = media_sideload_image( $image_url, null, null, 'id' );
		if ( is_wp_error( $image_id ) ) {
			$action_data['complete_with_errors'] = true;
			$error_message                       = $image_id->get_error_message();
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$image_uploaded = wp_get_attachment_url( $image_id );
		$filetype       = wp_check_filetype( basename( $image_uploaded ) );
		$image_details  = array(
			'post_title'     => sanitize_text_field( $title ),
			'post_excerpt'   => sanitize_text_field( $caption ),
			'post_content'   => sanitize_text_field( $description ),
			'ID'             => $image_id,
			'file'           => $image_uploaded,
			'post_mime_type' => $filetype['type'],
		);
		$image_updated  = wp_insert_attachment( $image_details );
		if ( is_wp_error( $image_updated ) ) {
			$action_data['complete_with_errors'] = true;
			$error_message                       = $image_updated->get_error_message();
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		// Set the image Alt-Text
		update_post_meta( $image_id, '_wp_attachment_image_alt', $alt_text );
		Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}
}
