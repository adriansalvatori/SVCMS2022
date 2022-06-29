<?php

namespace Objectiv\Plugins\Checkout\Admin;

use Objectiv\Plugins\Checkout\Managers\UpdatesManager;

class AdminPluginsPageManager {
	protected $cfw_admin_url;

	public function __construct( string $cfw_admin_url ) {
		$this->cfw_admin_url = $cfw_admin_url;
	}

	public function init() {
		add_action( 'after_plugin_row_' . CFW_PATH_BASE, array( $this, 'add_key_nag' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( CFW_MAIN_FILE ), array( $this, 'add_action_link' ), 10, 1 );
	}

	public function add_action_link( $links ) {
		$settings_link = array(
			'<a href="' . $this->cfw_admin_url . '">' . cfw__( 'Settings', 'checkout-wc' ) . '</a>',
		);

		return array_merge( $settings_link, $links );
	}

	public function add_key_nag() {
		$key_status = UpdatesManager::instance()->get_field_value( 'key_status' );

		if ( empty( $key_status ) ) {
			return;
		}

		if ( 'valid' === $key_status ) {
			return;
		}

		$current = get_site_transient( 'update_plugins' );
		if ( isset( $current->response[ plugin_basename( __FILE__ ) ] ) ) {
			return;
		}

		if ( is_network_admin() || ! is_multisite() ) {
			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
			echo "<span style='color:red'>You're missing out on important updates because your license key is missing, invalid, or expired.</span>";
			echo '</div></td></tr>';
		}
	}
}
