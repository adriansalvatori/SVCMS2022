<?php
// phpcs:ignoreFile

/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-page.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

namespace AutomateWoo\Referrals;

use AutomateWoo\Error;

defined( 'ABSPATH' ) || exit;

/**
 * @var Advocate|false $advocate
 * @var bool $enable_email_share
 */

$button_count = Social_Integrations::get_count();

?>

<div class="woocommerce">
	<div class="aw-referrals-share-container aw-referrals-share-page">

		<?php wc_print_notices(); ?>

		<?php if ( $advocate ) : ?>
			<?php $can_share = $advocate->can_share(); ?>

			<?php if ( $can_share instanceof Error ) : ?>

				<div class="aw-referrals-well">
					<p><?php echo esc_attr( $can_share->get_message() ); ?></p>
				</div>

			<?php else : ?>

				<?php if ( $button_count ) : ?>

					<div class="aw-referrals-share-buttons button-count-<?php echo esc_attr( $button_count ); ?>">
						<?php foreach ( Social_Integrations::get_all() as $integration ) : ?>
							<a href="<?php echo esc_url( $integration->get_redirect_to_share_url() ); // advocate is logged so don't pass $advocate arg ?>"
							   class="btn js-automatewoo-open-share-box <?php echo esc_attr( $integration->get_button_class() ); ?>"
							   style="background-color: <?php echo esc_attr( $integration->get_button_color() ); ?>;"
							><?php echo wp_kses_post( $integration->get_button_text() ); ?></a>
						<?php endforeach; ?>
					</div>

				<?php endif; ?>

				<?php if ( $enable_email_share ) : ?>

					<?php if ( $button_count ) : ?>
						<div class="aw-referrals-share-or"><?php esc_attr_e( 'Or', 'automatewoo-referrals' ); ?></div>
					<?php endif; ?>

					<?php AW_Referrals()->get_template( 'share-page-form.php' ); ?>

				<?php endif; ?>

			<?php endif; ?>

		<?php else : ?>

			<?php AW_Referrals()->get_template( 'share-page-login.php' ); ?>

		<?php endif; ?>

	</div>
</div>
