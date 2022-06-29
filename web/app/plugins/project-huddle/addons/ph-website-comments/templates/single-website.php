<?php

/**
 * Redirects to actual website on Single Website page view
 */

// make sure we have a website url
ph_website_access_check(get_the_ID(), get_post_meta(get_the_ID(), 'ph_website_url', true));
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