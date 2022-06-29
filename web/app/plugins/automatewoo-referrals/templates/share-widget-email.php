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
 * @var $advocate Advocate
 * @var $widget_heading string
 * @var $widget_text string
 */

?>

<div class="aw-referrals-share-widget">

    <div class="aw-referrals-share-widget-text">
        <h2><?php echo esc_attr( $widget_heading ); ?></h2>
		<?php echo wp_kses_post( wpautop( $widget_text ) ); ?>
    </div>

	<?php
	AW_Referrals()->get_template(
		'email-share-buttons.php',
		[
			'advocate' => $advocate,
		]
	);
	?>

</div>
