<?php

/**
 * Functions for the templating system.
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

function ph_start_mockup()
{
	global $post;

	if (get_post_type($post) !== 'ph-project') {
		return;
	}
?>
	<script>
		jQuery(document).ready(function() {
			ph.start(<?php
						echo json_encode(PH()->mockup->rest->get(
							$post->ID,
							array(
								'_expand' => array(
									'all' => 'all',
								),
								'orderby' => 'menu_order',
							)
						)); ?>);
		});
	</script>
<?php
}
add_action('ph_mockup_head', 'ph_start_mockup');
