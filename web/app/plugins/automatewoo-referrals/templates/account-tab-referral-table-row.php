<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/account-tab-referral-table-row.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 *
 * @var AutomateWoo\Referrals\Referral $referral
 */

?>


<tr>
	<?php if ( AW_Referrals()->is_using_store_credit() ) : ?>
		<td data-title="<?php esc_attr_e( 'Credit', 'automatewoo-referrals' ); ?>"><strong><?php echo wc_price( $referral->get_reward_amount() ); ?></strong></td>
		<td data-title="<?php esc_attr_e( 'Remaining balance', 'automatewoo-referrals' ); ?>"><?php echo wc_price( $referral->get_reward_amount_remaining() ); ?></td>
	<?php endif; ?>
	<?php if ( false === AW_Referrals()->options()->hide_referred_customer_data_from_advocates ) : ?>
		<td data-title="<?php esc_attr_e( 'Customer', 'automatewoo-referrals' ); ?>"><?php echo $referral ? esc_html( $referral->get_customer_name() ) : '-'; ?></td>
	<?php endif; ?>
	<td data-title="<?php esc_attr_e( 'Date', 'automatewoo-referrals' ); ?>"><?php echo esc_html( AutomateWoo\Format::date( $referral->get_date() ) ); ?></td>
</tr>
