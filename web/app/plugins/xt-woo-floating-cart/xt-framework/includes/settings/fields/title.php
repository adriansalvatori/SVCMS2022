<?php
/**
 * Template for displaying the title field
 *
 * @var bool $section_has_preview Section has preview flag
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

echo '<div class="xtfw-settings-section">' . "\n\n";

if ( ! empty( $field['desc'] ) ) {
	echo '<div id="' . esc_attr( sanitize_title( $field['id'] ) ) . '-description" class="xtfw-section-description">';
	echo wp_kses_post( wpautop( wptexturize( $field['desc'] ) ) );
	echo '</div>';
}

// if section has a preview
if ( ! empty( $field['has_preview'] ) && !empty($field['has_preview']['callback']) ) :
$section_has_preview = true;
$preview = $field['has_preview'];
$preview['css'] = !empty($preview['css']) ? $preview['css'] : '';
$args = !empty($preview['args']) ? $preview['args'] : array();
?>
<div class="xtfw-settings-preview-section">
    <div class="xtfw-settings-preview-sidebar">
		<?php if(!empty($preview['title'])):?>
            <span class="xtfw-settings-preview-title"><?php echo esc_html($preview['title']);?></span>
		<?php endif; ?>
        <div class="xtfw-settings-preview <?php echo esc_attr( sanitize_title( $preview['id'] ) ); ?>" style="<?php echo esc_attr( $preview['css'] ); ?>" id="<?php echo esc_attr( sanitize_title( $preview['id'] ) ); ?>">
			<?php call_user_func_array($preview['callback'], $args); ?>
			<?php $this->render_spinner(); ?>
        </div>
    </div>
    <div class="xtfw-settings-preview-settings">
<?php endif; ?>

    <table class="form-table">

        <?php
        if ( ! empty( $field['id'] ) ) {
            do_action( $this->core->plugin_prefix('settings_') . sanitize_title( $field['id'] ) );
        }
        ?>
