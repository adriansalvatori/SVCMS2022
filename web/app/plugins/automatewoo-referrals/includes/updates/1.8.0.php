<?php
// phpcs:ignoreFile

/**
 * Migrate 1.8.0
 *
 * 1. Default value for expiry changes from 4 weeks to unlimited so preserve the default for existing stores
 */

defined( 'ABSPATH' ) || exit;


if ( AW_Referrals()->options()->type === 'coupon' ) {
	$option = 'aw_referrals_offer_coupon_expiry';
} else {
	$option = 'aw_referrals_share_link_expiry';
}

if ( get_option( $option ) === '' ) {
	update_option( $option, '4', false );
}
