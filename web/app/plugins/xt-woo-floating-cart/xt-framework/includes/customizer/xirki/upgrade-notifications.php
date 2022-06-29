<?php
/**
 * Adds upgrade notifications.
 *
 * @package     Xirki
 * @category    Core
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2020, XplodedThemes
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

if ( ! function_exists( 'xirki_show_upgrade_notification' ) ) :
	/**
	 * Fires at the end of the update message container in each
	 * row of the plugins list table.
	 * Allows us to add important notices about updates should they be needed.
	 * Notices should be added using "== Upgrade Notice ==" in readme.txt.
	 *
	 * @since 2.3.8
	 * @param array $plugin_data An array of plugin metadata.
	 * @param array $response    An array of metadata about the available plugin update.
	 */
	function xirki_show_upgrade_notification( $plugin_data, $response ) {

		// Check "upgrade_notice".
		if ( isset( $response->upgrade_notice ) && strlen( trim( $response->upgrade_notice ) ) > 0 ) : ?>
			<style>.xirki-upgrade-notification {background-color:#d54e21;padding:10px;color:#f9f9f9;margin-top:10px;margin-bottom:10px;}.xirki-upgrade-notification + p {display:none;}</style>
			<div class="xirki-upgrade-notification">
				<strong><?php esc_html_e( 'Important Upgrade Notice:', 'xirki' ); ?></strong>
				<?php $upgrade_notice = wp_strip_all_tags( $response->upgrade_notice ); ?>
				<?php echo esc_html( $upgrade_notice ); ?>
			</div>
			<?php
		endif;
	}
endif;
add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), 'xirki_show_upgrade_notification', 10, 2 );
