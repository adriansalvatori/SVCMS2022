<?php
// phpcs:ignoreFile

/**
 * @var string $link
 * @var string $text
 * @var string $background_color
 * @var string $text_color
 * @var string $class
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $text_color ) ) {
	$text_color = '#ffffff';
}

if ( ! isset( $class ) ) {
	$class = '#ffffff';
}

?>

<div class="aw-referrals-share-btn-wrap"><!--[if mso]>
	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo esc_url( $link ); ?>" style="height:50px;v-text-anchor:middle;width:300px;" arcsize="8%" stroke="f" fillcolor="<?php echo esc_attr( $background_color ); ?>">
		<w:anchorlock/>
		<center>&nbsp;&nbsp;<![endif]--><a href="<?php echo esc_url( $link ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		style="background-color:<?php echo esc_attr( $background_color ); ?>;border-radius:4px;color:<?php echo esc_attr( $text_color ); ?>;display:inline-block;font-family:Helvetica, Roboto, Arial, sans-serif;font-size:14px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;"
	><?php echo esc_attr( $text ); ?></a><!--[if mso]>&nbsp;&nbsp;</center>
	</v:roundrect>
	<![endif]--><!--[if mso]><br><![endif]-->
</div>