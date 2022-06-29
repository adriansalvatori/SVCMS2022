<?php
/**
 * The Template for displaying the app front end header.
 *
 * Override this template by copying it to your theme or child theme
 *
 * @package     ProjectHuddle
 * @subpackage  templates
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0
 */
?>
<?php
if ( ! is_user_logged_in() ) {
	ph_get_template_part( 'content', 'login' );
} else {
	// before user header
	do_action( 'ph_before_user_header');

	// get custom header
	ph_get_template_part( 'header', 'user' );

	// @hooked ph_before_user_front_settings
	do_action( 'ph_before_user_front_settings' );

	// get main content
	ph_get_template_part( 'content', 'user' );

	// @hooked ph_after_user_front_settings
	do_action( 'ph_after_user_front_settings' );

	// get custom footer
	ph_get_template_part( 'footer', 'user' ); // get footer
}