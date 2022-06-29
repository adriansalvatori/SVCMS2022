<?php
// phpcs:ignoreFile

/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/email-share-buttons.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

namespace AutomateWoo\Referrals;

defined( 'ABSPATH' ) || exit;

/**
 * @var bool|Advocate $advocate can be false for guests
 */

?>

<?php // fix for windows 10 mail ?>
<!--[if mso]>
<style type="text/css">
	.aw-referrals-share-btn-wrap a {text-decoration: none;}
</style>
<![endif]-->


<div class="aw-referrals-widget__buttons">
	<?php

	if ( AW_Referrals()->options()->enable_email_share ) {
		AW_Referrals()->get_template(
			'email-share-button.php',
			[
				'link'             => AW_Referrals()->get_share_page_url(),
				'text'             => __( 'Share via Email', 'automatewoo-referrals' ),
				'background_color' => '#43454b',
			]
		);
	}

	foreach ( Social_Integrations::get_all() as $integration ) {
		AW_Referrals()->get_template(
			'email-share-button.php',
			[
				'link'             => $advocate ? $integration->get_redirect_to_share_url( $advocate ) : AW_Referrals()->get_share_page_url(),
				'text'             => $integration->get_button_text( 'email' ),
				'background_color' => $integration->get_button_color(),
				'class'            => 'aw-referrals-share-btn-' . $integration->get_id(),
			]
		);
	}
	?>
</div>