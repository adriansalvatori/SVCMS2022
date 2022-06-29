<?php
// phpcs:ignoreFile

/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-page-login.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="aw-referrals-well">
    <h4><?php esc_attr_e( 'Please Login', 'automatewoo-referrals' ); ?></h4>
    <p><?php esc_attr_e( 'You must have an account to refer a friend.', 'automatewoo-referrals' ); ?></p>
    <p><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="woocommerce-Button button"><?php esc_attr_e( 'Login or register', 'automatewoo-referrals' ); ?></a></p>
</div>
