<?php

/**
 * Redirects to actual website comment on single page view
 *
 * Sets a cookie
 */
global $post;

$image_url = get_the_permalink($post->ID);

// access token
$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : '';
if ($access_token) {
	PH()->session->set(
		'project_access',
		array(
			$post->ID => $access_token,
		)
	);
}

// make sure we have a url
// permissions are handled by project id
if ($image_url) {
	header('X-Robots-Tag: noindex, nofollow', true);
	// wp_redirect( esc_url( $image_url ) );
} ?>
<html>

<head>
	<!-- make sure it's not indexed -->
	<meta name="robots" content="noindex, nofollow">
</head>

<body>
</body>

</html>