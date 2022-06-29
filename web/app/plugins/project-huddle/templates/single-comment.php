<?php

/**
 * Redirects to actual website comment on single page view
 *
 * Sets a cookie
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

ph_template_access('content', 'comment');
