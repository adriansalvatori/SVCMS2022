<?php
/**
 * Handles postMessage events and runs ajax to communicate with server
 *
 * @since 1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp;

// get project ID
$project_id = $wp->query_vars['ph_handler'];

// set default headers for security purposes
header_remove( 'X-Frame-Options' );
header_remove( 'Content-Security-Policy' );
header( 'X-Frame-Options: SAMEORIGIN' );
header( "Content-Security-Policy: 'self'" );

// get url
$url = get_post_meta( (int) $project_id, 'ph_website_url', true );

// get website url
if ( ! $url ) {
	$url = get_site_url();
} else {
	header_remove( 'X-Frame-Options' );
	header_remove( 'Content-Security-Policy' );
}

// get components
$url    = parse_url( $url );
$parsed = $url;

// do port
$port = isset( $url['port'] ) ? ':' . $url['port'] : '';

// allow ssl and non ssl urls
$url_ssl = 'https://' . $url['host'] . $port;
$url     = 'http://' . $url['host'] . $port;

$debug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'debug="true"' : false;

// CORS request header for matching url
header( "Content-Security-Policy: frame-ancestors $url_ssl $url" ); // we'll use frame ancestors and make IE people update for security
?>
<head>
	<script type="text/javascript" <?php echo $debug; ?> src="<?php echo esc_url( PH_WEBSITE_PLUGIN_URL . 'assets/js/includes/xdomain.min.js?ver=1.7' ); ?>"></script>
	<script>
	  xdomain.masters({
		'<?php echo esc_url( $url ); ?>'    : '*',
		'<?php echo esc_url( $url_ssl ); ?>': '*',
	  })
	</script>
</head>
