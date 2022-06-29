<?php
/**
 * The Template for displaying the app top bar
 *
 * Override this template by copying it to your theme or child theme
 *
 * @package     ProjectHuddle
 * @subpackage  templates
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php
	// @hooked projecthuddle_before_top_bar
	do_action( 'projecthuddle_before_top_bar' );
?>

<header id="top-bar">
	<div id="top-bar-inner">
		<?php
			// @hooked projecthuddle_before_logo
			do_action( 'projecthuddle_before_logo' );
		?>

		<div id="logo">
			ProjectHuddle
		</div>

		<?php
			// @hooked projecthuddle_after_logo
			do_action( 'projecthuddle_after_logo' );
		?>

		<div id="controls" class="controls">
			<div class="loading"></div>
		</div>

		<?php
			// @hooked projecthuddle_after_controls
			do_action( 'projecthuddle_after_controls' );
		?>
	</div>
</header>

<?php
	// @hooked projecthuddle_after_top_bar
	do_action( 'projecthuddle_after_top_bar' );
?>
