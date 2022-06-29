<?php

/**
 * The Template for displaying the project images
 *
 * Override this template by copying it to your theme or child theme
 *
 * @package     ProjectHuddle
 * @subpackage  templates
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0
 */
// get custom header
ph_get_template_part('header', 'project');

// @hooked ph_before_single_project
do_action('ph_before_single_project');
?>
<div class="project-huddle" id="app">
	<div class="loading"></div>
</div>

<?php
// @hooked ph_after_single_project
do_action('ph_after_single_project');

// get custom footer
ph_get_template_part('footer', 'project'); // get footer

PH()->session->clear('ph_comment_id');
