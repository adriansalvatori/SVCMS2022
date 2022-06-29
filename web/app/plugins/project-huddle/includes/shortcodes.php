<?php
/**
 * Shortcodes
 *
 * @package     Project-Huddle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the Project Gallery shortcode output.
 *
 * This is based of the WordPress gallery shortcode functionality to match styling to the theme.
 *
 * @since 1.0.0
 *
 * @param array $attr {
 *     Attributes of the gallery shortcode.
 *
 *     @type int    $id         Project ID.
 *     @type bool   $titles     Show/Hide the project image titles
 *     @type string $itemtag    HTML tag to use for each image in the gallery.
 *                              Default 'dl', or 'figure' when the theme registers HTML5 gallery support.
 *     @type string $icontag    HTML tag to use for each image's icon.
 *                              Default 'dt', or 'div' when the theme registers HTML5 gallery support.
 *     @type string $captiontag HTML tag to use for each image's caption.
 *                              Default 'dd', or 'figcaption' when the theme registers HTML5 gallery support.
 *     @type int    $columns    Number of columns of images to display. Default 3.
 *     @type string $size       Size of the images to display. Default 'thumbnail'.
 * }
 * @return string HTML content to display gallery.
 */
function ph_project_shortcode( $attr ) {
	// keep track of instances on page.
	static $instance = 0;
	$instance ++;

	/**
	 * Filter the default project gallery shortcode output.
	 *
	 * If the filtered output isn't empty, it will be used instead of generating
	 * the default gallery template.
	 *
	 * @since 1.0.0
	 *
	 * @see ph_project_shortcode()
	 *
	 * @param string $output   The gallery output. Default empty.
	 * @param array  $attr     Attributes of the gallery shortcode.
	 * @param int    $instance Unique numeric ID of this gallery shortcode instance.
	 */
	$output = apply_filters( 'ph_project_thumbnails_shortcode', '', $attr, $instance );
	if ( '' !== $output ) {
		return $output;
	}

	// find html5 support.
	$html5 = current_theme_supports( 'html5', 'gallery' );

	// get attributes.
	$atts = shortcode_atts(
		array(
			'id'       => 0,
			'columns'  => 3,
			'per_page' => 9,
		),
		$attr,
		'project_huddle'
	);

	if ( ! $type = get_post_type( $atts['id'] ) ) {
		return;
	}

	// post type
	$atts['type'] = $type == 'ph-project' ? 'mockups' : 'websites';

	$output = '
	<style>:root{
		--ph-accent-color: ' . esc_html( get_option( 'ph_highlight_color', '#4353ff' ) ) . '
	}
	@font-face {
		font-family: "element-icons";
		src: url("' . PH_PLUGIN_URL . 'assets/fonts/element-icons.woff") format("woff"), /* chrome, firefox */
			 url("' . PH_PLUGIN_URL . 'assets/fonts/element-icons.ttf") format("truetype"); /* chrome, firefox, opera, Safari, Android, iOS 4.2+*/
		font-weight: normal;
		font-style: normal
	  }
	</style>';

	// add shortcodes and scripts.
	wp_enqueue_script( 'project-huddle-shortcodes' );

	do_action( 'ph_shortcodes_enqueue_script' );

	if ( ! is_user_logged_in() ) {
		return wp_login_form();
	}

	return $output . ph_shortcode_output( 'ph-project-shortcode', $atts );
}
add_shortcode( 'project_huddle', 'ph_project_shortcode' );
add_shortcode( 'ph_project', 'ph_project_shortcode' );


/**
 * User projects shortcode
 *
 * @param array $attr Shortcode attributes.
 *
 * @return bool|string
 */
function ph_dashboard_shortcode( $attr ) {
	// only once per page
	static $ph_user_project_already_run = false;
	if ( $ph_user_project_already_run === true ) {
		return;
	}
	$ph_user_project_already_run = true;

	$atts = shortcode_atts(
		array(
			'columns'            => 3,
			'websites'           => true,
			'mockups'            => true,
			'activity'           => true,
			'filter'             => false,
			'subscribe_controls' => (bool) ( current_user_can( 'publish_ph-projects' ) || current_user_can( 'publish_ph-websites' ) ),
			'per_page'           => 9,
		),
		$attr,
		'ph_dashboard'
	);

	if ( ! is_user_logged_in() ) { 
		$css_dir = PH_PLUGIN_URL . 'assets/css/';
		wp_enqueue_style('project-huddle-shortcodes_css', $css_dir . 'dist/project-huddle-shortcodes.css', array(), PH_VERSION); ?>
		<div class="ph-login-form">
			<?php return wp_login_form(); ?>
		</div>
	<?php }

	$output = '
	<style>:root{
		--ph-accent-color: ' . esc_html( get_option( 'ph_highlight_color', '#4353ff' ) ) . '
	}
	@font-face {
		font-family: "element-icons";
		src: url("' . PH_PLUGIN_URL . 'assets/fonts/element-icons.woff") format("woff"), /* chrome, firefox */
			 url("' . PH_PLUGIN_URL . 'assets/fonts/element-icons.ttf") format("truetype"); /* chrome, firefox, opera, Safari, Android, iOS 4.2+*/
		font-weight: normal;
		font-style: normal
	  }
	</style>';

	// add shortcodes and scripts.
	wp_enqueue_script( 'project-huddle-shortcodes' );
	
do_action( 'ph_shortcodes_enqueue_script' );

	return $output . ph_shortcode_output( 'ph-dashboard-shortcode', $atts );
}

add_shortcode( 'ph_dashboard', 'ph_dashboard_shortcode' );
add_shortcode( 'ph_subscribed_projects', 'ph_dashboard_shortcode' );

function ph_shortcode_output( $class, $atts ) {
	$output = '<div class="' . sanitize_html_class( $class ) . '"';

	foreach ( $atts as $att => $value ) {
		$output .= ' data-' . esc_attr( str_replace( '_', '-', $att ) ) . '="' . esc_attr( $value ) . '"';
	}

	$output .= '></div>';

	return $output;
}
