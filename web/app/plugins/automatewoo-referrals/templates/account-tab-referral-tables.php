<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * The Template for displaying referral credits in the my account area.
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/account-tab-referral-tables.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 *
 * @var AutomateWoo\Referrals\Referral[] $referrals
 * @var AutomateWoo\Referrals\Referral[] $used_referrals
 */

?>

<?php if ( $referrals ) : ?>

	<div class="referrals-container">

		<table class="shop_table shop_table_responsive my_account_referrals">

			<thead>
			<tr>
				<?php if ( AW_Referrals()->is_using_store_credit() ) : ?>
					<th><?php esc_html_e( 'Credit', 'automatewoo-referrals' ); ?></th>
					<th><?php esc_html_e( 'Remaining balance', 'automatewoo-referrals' ); ?></th>
				<?php endif; ?>
				<?php if ( false === AW_Referrals()->options()->hide_referred_customer_data_from_advocates ) : ?>
					<th><?php esc_html_e( 'Customer', 'automatewoo-referrals' ); ?></th>
				<?php endif; ?>
				<th><?php esc_html_e( 'Date', 'automatewoo-referrals' ); ?></th>
			</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $referrals as $referral ) {
					AW_Referrals()->get_template( 'account-tab-referral-table-row.php', [ 'referral' => $referral, ] );
				}
				?>
			</tbody>
		</table>
	</div>

<?php endif; ?>


<?php if ( $used_referrals ) : ?>

	<div class="used-referrals-container">

		<h3><?php esc_html_e( 'Used Referrals', 'automatewoo-referrals' ); ?></h3>

		<table class="shop_table shop_table_responsive my_account_referrals">

			<thead>
			<tr>
			    <?php if ( AW_Referrals()->is_using_store_credit() ) : ?>
				    <th><?php esc_html_e( 'Credit', 'automatewoo-referrals' ); ?></th>
				    <th><?php esc_html_e( 'Remaining balance', 'automatewoo-referrals' ); ?></th>
			    <?php endif; ?>
				<?php if ( false === AW_Referrals()->options()->hide_referred_customer_data_from_advocates ) : ?>
					<th><?php esc_html_e( 'Customer', 'automatewoo-referrals' ); ?></th>
				<?php endif; ?>
				<th><?php esc_html_e( 'Date', 'automatewoo-referrals' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php
            foreach ( $used_referrals as $referral ) {
                AW_Referrals()->get_template( 'account-tab-referral-table-row.php', [ 'referral' => $referral, ] );
            }
            ?>
			</tbody>
		</table>
	</div>

<?php endif; ?>

