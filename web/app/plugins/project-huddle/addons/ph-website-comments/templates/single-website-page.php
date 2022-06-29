<?php

/**
 * Redirects to actual website comment on single page view
 *
 * Sets a cookie
 */
global $post;

// get page url from thread
$parents      = ph_get_parents_ids($post);
ph_website_access_check($parents['project'], html_entity_decode(get_post_meta($post->ID, 'page_url', true)));
?>
<html>

<head>
	<!-- make sure it's not indexed -->
	<meta name="robots" content="noindex, nofollow">
</head>

<body>
</body>

</html>