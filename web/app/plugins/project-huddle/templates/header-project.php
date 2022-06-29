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
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="robots" content="noindex, nofollow">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<?php wp_head(); ?>
	<?php do_action('ph_mockup_head'); ?>
</head>

<body <?php body_class('ph-sans'); ?> style="overscroll-behavior-x: none;">