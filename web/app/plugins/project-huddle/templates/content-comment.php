<?php
global $post;

// use a session
PH()->session->set('ph_comment_id', (int) $post->ID);
$_COOKIE['ph_comment_id'] = (int) $post->ID;

$parents   = ph_get_parents_ids($post->ID);
$image_url = get_the_permalink($parents['item']);
$image_url = add_query_arg(['ph_comment' => $post->ID], $image_url);

// access token
$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : '';
if ($access_token) {
    PH()->session->set(
        'project_access',
        array(
            $parents['project'] => $access_token,
        )
    );
}

// make sure we have a website url
if ($image_url) {
    header('X-Robots-Tag: noindex, nofollow', true);
    wp_redirect(esc_url_raw($image_url));
    exit;
} ?>
<html>

<head>
    <!-- make sure it's not indexed -->
    <meta name="robots" content="noindex, nofollow">
</head>

<body>
    Redirecting... please wait...
</body>

</html>