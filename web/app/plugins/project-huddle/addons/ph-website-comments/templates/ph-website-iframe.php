<?php

/**
 * The Template for displaying the website iframe
 * This file is outputted via json to be created on the website
 *
 * Do not put any sensitive information or direct ajax requests in this file
 * All server calls should run through postMessage via the iframe on this page
 *
 * Override this template by copying it to your theme or child theme.
 *
 * This template purposely excludes wp_head as to not interfere with theme styles and functions
 *
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit; ?>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<?php do_action('ph_website_header'); ?>
	<?php if (isset($_GET['ph_query_test'])) wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div class="project-huddle-iframe"></div>
	<?php do_action('ph_website_footer'); ?>
	<?php if (isset($_GET['ph_query_test'])) wp_footer(); ?>
</body>