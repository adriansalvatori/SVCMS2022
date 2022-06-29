<?php

namespace Uncanny_Automator_Pro;

/**
 * Auto-prune logs
 * Settings > General > Logs > Auto-prune logs
 *
 * @since   3.7
 * @version 3.7
 * @package Uncanny_Automator
 * @author  Daniela R. & Agustin B.
 *
 * Variables:
 * $is_enabled              TRUE if this setting is enabled
 * $interval_number_of_days Integer with interval, in days
 */

?>

<form method="POST">

	<?php

	// Nonce field
	wp_nonce_field( 'uncanny_automator' );

	?>

	<div class="uap-settings-panel-content-separator"></div>

	<div class="uap-settings-panel-content-subtitle">
		<?php esc_html_e( 'Auto-prune activity logs', 'uncanny-automator-pro' ); ?><uo-pro-tag></uo-pro-tag>
	</div>

	<uo-switch
		id="uap_automator_purge_days_switch"
		<?php echo $is_enabled ? 'checked' : ''; ?>

		status-label="<?php esc_attr_e( 'Enabled', 'uncanny-automator' ); ?>,<?php esc_attr_e( 'Disabled', 'uncanny-automator' ); ?>"

		class="uap-spacing-top"
	></uo-switch>

	<div id="uap-auto-prune-content" style="display: none;">

		<uo-text-field
			id="uap_automator_purge_days"
			value="<?php echo ! empty( $interval_number_of_days ) ? esc_attr( $interval_number_of_days ) : '10'; ?>"

			label="<?php esc_attr_e( 'Interval (in days)', 'uncanny-automator' ); ?>"
			helper="<?php esc_attr_e( 'Enter a number of days to activate automatic daily deletion of recipe log entries older than the specified number of days. Logs will only be deleted for recipes that are not In Progress.', 'uncanny-automator' ); ?>"
			placeholder="<?php esc_attr_e( 'Ex: 10', 'uncanny-automator' ); ?>"

			class="uap-spacing-top"
		></uo-text-field>

	</div>

	<uo-button
		type="submit"
		class="uap-spacing-top"
	>
		<?php esc_html_e( 'Save', 'uncanny-automator' ); ?>
	</uo-button>

</form>

<script>
	
/**
 * We're adding this code here because it's an exception and applies only
 * to the content in this template. If this is used in multiple settings,
 * consider creating a global solution. 
 */

// Get the switch element
const $switch = document.getElementById( 'uap_automator_purge_days_switch' );

// Get the content element
const $content = document.getElementById( 'uap-auto-prune-content' );

/**
 * Sets the visibility of the content
 * 
 * @return {undefined}
 */
const setContentVisibility = () => {
	// Check if it's enabled
	if ( $switch.checked ) {
		// Show
		$content.style.display = 'block';
	} else {
		// Hide
		$content.style.display = 'none';
	}
}

// Evaluate on load
setContentVisibility();

// Evaluate when the value of the switch changes
$switch.addEventListener( 'change', () => {
	// Evaluate the visibility
	setContentVisibility();
} );

</script>