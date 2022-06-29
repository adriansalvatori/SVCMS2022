<?php
// phpcs:ignoreFile

/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/email-styles.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

defined( 'ABSPATH' ) || exit;

$bg = get_option( 'woocommerce_email_background_color' );

?>

.aw-referrals-share-widget {
	background: <?php echo esc_attr( $bg ); ?>;
	margin: 25px 0;
	padding: 25px 40px 30px;
}

.aw-referrals-share-widget-text h2,
.aw-referrals-share-widget-text p {
	text-align: center;
}

.aw-referrals-share-btn-wrap {
	margin: 0 0 8px;
	text-align: center;
}
