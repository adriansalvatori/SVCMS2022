<?php

use Objectiv\Plugins\Checkout\Managers\SettingsManager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<footer id="cfw-footer" class="container">
	<div class="row">
		<div class="col-12">
			<div class="cfw-footer-inner entry-footer">
				<?php
				/**
				 * Fires at the top of footer
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_before_footer' );
				?>
				<?php if ( ! empty( $footer_text = SettingsManager::instance()->get_setting( 'footer_text' ) ) ) : ?>
					<?php echo do_shortcode( $footer_text ); ?>
				<?php else : ?>
					Copyright &copy; <?php echo date( 'Y' ); ?>, <?php echo get_bloginfo( 'name' ); ?>. All rights reserved.
					<?php
				endif;

				/**
				 * Fires at the bottom of footer
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_after_footer' );
				?>
			</div>
		</div>
	</div>
</footer>
