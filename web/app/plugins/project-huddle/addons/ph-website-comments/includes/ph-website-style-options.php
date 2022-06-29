<?php

/**
 * Custom Styles from Options
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

function ph_website_style_options()
{
	ob_start();
	do_action('ph_website_style_options_output');
?>

	:root{
	--ph-accent-color: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>;
	}

<?php

	$out = ob_get_contents();

	ob_end_clean();

	return $out;
}

function ph_parent_website_style_options()
{

	ob_start();

	do_action('ph_website_style_options_output');
?>
	:root{
	--ph-accent-color: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>;
	--ph-accent-color-10: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>1A;
	--ph-accent-color-20: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>33;
	--ph-accent-color-30: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>4D;
	--ph-accent-color-40: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>66;
	--ph-accent-color-50: <?php echo esc_html(get_option('ph_highlight_color', '#4353ff')); ?>80;
	}

	.ph-comment-container .ph-annotation-dot {
	background: <?php echo get_option('ph_highlight_color', '#4353ff'); ?> !important;
	}
<?php

	$out = ob_get_contents();

	ob_end_clean();

	return str_replace(array("\r\n", "\n\r", "\n", "\r"), null, $out);
}
