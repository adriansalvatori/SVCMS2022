<?php
// phpcs:ignoreFile

/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-widget.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

namespace AutomateWoo\Referrals;

defined( 'ABSPATH' ) || exit;

/**
 * @var $advocate|bool Advocate
 * @var $widget_heading string
 * @var $widget_text string
 * @var $enable_email_share bool
 * @var $position string
 */

$button_count = Social_Integrations::get_count();

if ( $enable_email_share ) {
    $button_count++;
}

?>

<div class="aw-referrals-share-widget aw-referrals-well aw-referrals-share-container aw-referrals-share-widget--position-<?php echo esc_attr( $position ); ?>">

	<div class="aw-referrals-share-widget-text">
		<h3><?php echo esc_html( $widget_heading ); ?></h3>
		<?php echo wp_kses_post( wpautop( $widget_text ) ); ?>
	</div>


	<div class="aw-referrals-share-buttons button-count-<?php echo esc_attr( $button_count ); ?>">

		<?php if ( $enable_email_share ) : ?>
			<a href="<?php echo esc_url( AW_Referrals()->get_share_page_url() ); ?>" class="btn btn-email"><?php esc_html_e( 'Share via Email', 'automatewoo-referrals' ); ?></a>
		<?php endif; ?>

		<?php if ( $advocate ) : // user is logged in ?>

           <?php foreach ( Social_Integrations::get_all() as $integration ) : ?>
                <a href="<?php echo esc_url( $integration->get_redirect_to_share_url() ); // advocate is logged so don't pass $advocate arg ?>"
				   class="btn <?php echo $advocate ? 'js-automatewoo-open-share-box' : ''; ?> <?php echo esc_attr( $integration->get_button_class() ); ?>"
				   style="background-color: <?php echo esc_attr( $integration->get_button_color() ); ?>;"
				><?php echo wp_kses_post( $integration->get_button_text() ); ?></a>
           <?php endforeach; ?>

		<?php else : // send to share page if no user account ?>

           <?php foreach ( Social_Integrations::get_all() as $integration ) : ?>
                <a href="<?php echo esc_url( AW_Referrals()->get_share_page_url() ); ?>"
				   class="btn <?php echo $advocate ? 'js-automatewoo-open-share-box' : ''; ?> <?php echo esc_attr( $integration->get_button_class() ); ?>"
				   style="background-color: <?php echo esc_attr( $integration->get_button_color() ); ?>;"
				><?php echo wp_kses_post( $integration->get_button_text() ); ?></a>
           <?php endforeach; ?>

		<?php endif; ?>

	</div>

</div>