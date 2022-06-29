<?php

/**
 * Redirects to actual website comment on single page view
 *
 * Sets a cookie
 */
global $post;

// use a session
PH()->session->set('ph_comment_id', (int) $post->ID);
$parents          = ph_get_parents_ids($post); // get parents
ph_website_access_check($parents['project'], html_entity_decode(get_post_meta($parents['item'], 'page_url', true)), ['ph_comment' => $post->ID]);
?>
<html>

<head>
	<!-- make sure it's not indexed -->
	<meta name="robots" content="noindex, nofollow">
</head>

<body>
	<?php _e('Website URL is incorrect or not set.', 'project-huddle'); ?>
</body>

</html>