<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Fields;
use AutomateWoo\Format;

defined( 'ABSPATH' ) || exit;

/**
 * @view Edit Referral Page
 *
 * @var $controller Admin\Controllers\Referrals
 * @var $referral Referral
 * @var $status_field Fields\Field
 * @var $reward_amount_field Fields\Price
 * @var $reward_amount_remaining_field Fields\Price
 */

$advocate = $referral->get_advocate();
$customer = $referral->get_customer();
$order    = $referral->get_order();

?>

<div class="wrap automatewoo-referral-page automatewoo-page automatewoo-page--referrals">

	<h1><?php esc_html_e( 'Referral Details', 'automatewoo-referrals' ); ?></h1>

	<?php $controller->output_messages(); ?>

	<form method="post" action="<?php echo esc_url( $controller->get_route_url( 'save', $referral ) ); ?>" id="aw_referrals_edit_referral">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div id="postbox-container-1">

					<div class="postbox automatewoo-metabox no-drag">

						<table class="automatewoo-table">

							<tr class="automatewoo-table__row">
								<td class="automatewoo-table__col">

									<label class="automatewoo-label"><?php echo esc_html( $status_field->get_title() ); ?></label>

									<?php $status_field->render( $referral->get_status() ); ?>

									<?php if ( $status_field->get_description() ) : ?>
										<p class="aw-field-description"><?php echo esc_html( $status_field->get_description() ); ?></p>
									<?php endif; ?>

								</td>
							</tr>

						</table>

						<div class="automatewoo-metabox-footer submitbox">
							<div id="delete-action"><a class="submitdelete deletion" href="<?php echo esc_url( $controller->get_route_url( 'delete', $referral ) ); ?>"><?php esc_html_e( 'Delete permanently', 'automatewoo-referrals' ); ?></a></div>
							<input type="submit" class="button button-primary" name="save" value="<?php esc_attr_e( 'Save', 'automatewoo-referrals' ); ?>">
						</div>

					</div>
				</div>


				<div id="postbox-container-2">

					<?php if ( $referral->ip_addresses_match() ) : ?>
						<div class="aw-referral-info-boxes">

							<div class="automatewoo-info-box">
								<span class="dashicons dashicons-shield-alt"></span> <strong><?php esc_html_e( 'Potential Fraud Detected', 'automatewoo-referrals' ); ?></strong> -
								<?php esc_html_e( "The IP addresses of the advocate and referred customer were identical. The advocate may have referred themselves.", 'automatewoo-referrals' ); ?>
							</div>

						</div>
					<?php endif ?>


					<div class="postbox automatewoo-metabox no-drag">
						<div class="inside">

							<table class="automatewoo-table automatewoo-table--two-column automatewoo-edit-referral-table">

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Referral ID', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col">#<?php echo esc_html( $referral->get_id() ); ?></td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Date created', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col"><?php echo esc_html( Format::datetime( $referral->get_date() ) ); ?></td>
								</tr>


								<?php if ( $order ) : ?>
									<tr>
										<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Order', 'automatewoo-referrals' ); ?></td>
										<td class="automatewoo-table__col">
											<a href="<?php echo esc_url( get_edit_post_link( $order->get_id() ) ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a>
											<?php echo esc_html( sprintf( __( '(Status:  %s)', 'automatewoo-referrals' ), wc_get_order_status_name( $order->get_status() ) ) ); ?>
										</td>
									</tr>
								<?php endif; ?>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Advocate', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col">
										<a href="<?php echo esc_url( get_edit_user_link( $referral->get_advocate_id() ) ); ?>"><?php echo esc_html( $referral->get_advocate_name() ); ?></a>
										<a href="mailto:<?php echo esc_attr( $advocate->get_email() ); ?>"><?php echo esc_html( $advocate->get_email() ); ?></a>
									</td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Advocate IP address', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col"><?php echo esc_html( $referral->get_advocate_ip_address() ); ?></td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Advocate reward type', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col"><?php echo esc_html( AW_Referrals()->get_reward_types()[ $referral->get_reward_type() ] ); ?></td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Advocate reward amount', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col automatewoo-table__col--field">
										<?php echo esc_html( get_woocommerce_currency_symbol() ); ?><?php $reward_amount_field->render( Format::decimal( $referral->get_reward_amount() ) ); ?>
									</td>
								</tr>


								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Advocate reward amount remaining', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col automatewoo-table__col--field">
										<?php echo esc_html( get_woocommerce_currency_symbol() ); ?><?php $reward_amount_remaining_field->render( Format::decimal( $referral->get_reward_amount_remaining() ) ); ?>
									</td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Customer', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col">
										<?php if ( $customer ) : ?>
											<a href="<?php echo esc_url( get_edit_user_link( $customer->ID ) ); ?>"><?php echo esc_html( AW_Referrals()->admin->get_formatted_customer_name( $customer ) ); ?></a>
										<?php else : ?>
											<?php echo esc_html( AW_Referrals()->admin->get_formatted_customer_name_from_order( $order ) ); ?>
										<?php endif; ?>

										<?php if ( $order ) : ?>
											<?php $email = $order->get_billing_email(); ?>
											<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
										<?php endif; ?>
									</td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Customer IP address', 'automatewoo-referrals' ); ?></td>
									<td class="automatewoo-table__col"><?php echo esc_html( $referral->get_customer_ip_address() ); ?></td>
								</tr>


								<?php if ( $offer_type = $referral->get_offer_type() ) : ?>

									<tr>
										<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Customer discount type', 'automatewoo-referrals' ); ?></td>
										<td class="automatewoo-table__col"><?php echo esc_html( AW_Referrals()->get_offer_types()[ $offer_type ] ); ?></td>
									</tr>

									<tr>
										<td class="automatewoo-table__col automatewoo-table__col--label"><?php esc_html_e( 'Customer discount amount', 'automatewoo-referrals' ); ?></td>
										<td class="automatewoo-table__col">
											<?php
											if ( $offer_type == 'coupon_percentage_discount' ) {
												echo wc_price( $referral->get_discounted_amount() ) . ' (' . esc_html( $referral->get_offer_amount() ) . '%)';
											} elseif ( $offer_type == 'coupon_discount') {
												echo wc_price( $referral->get_offer_amount() );
											}
											?>
										</td>

									</tr>
								<?php endif ?>


							</table>

						</div>

					</div>

				</div>

			</div>
		</div>
	</form>

</div>
