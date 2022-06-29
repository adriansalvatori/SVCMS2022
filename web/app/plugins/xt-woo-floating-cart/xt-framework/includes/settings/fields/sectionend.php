<?php
/**
 * Template for displaying the sectionend field
 *
 * @var bool $section_has_preview Section has preview flag
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! empty( $field['id'] ) ) {
    do_action( $this->core->plugin_prefix('settings_') . sanitize_title( $field['id'] ) . '_end' );
}
?>
</table>

    <?php

    // Close preview if any
    if ( $section_has_preview ):
        $section_has_preview = false;
        ?>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php

if ( ! empty( $field['id'] ) ) {
	do_action( $this->core->plugin_prefix('settings_') . sanitize_title( $field['id'] ) . '_after' );
}