<?php

/**
 * User Profile Sites List Table
 *
 * @package Plugins/Users/Profiles/SitesList
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-ms-sites-list-table.php';

/**
 * Core class used to implement displaying sites in a list table within a user profile.
 */
class WP_User_Profiles_Sites_List_Table extends WP_MS_Sites_List_Table {

	/**
	 * Gets a list of columns.
	 *
	 * @return string[] Array of column heading names keyed by column ID.
	 */
	public function get_columns() {

		// Default columns
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'blogname' => esc_html__( 'URL',   'wp-user-profiles' ),
			'users'    => esc_html__( 'Users', 'wp-user-profiles' ),
		);

		// Can manage sites
		if ( current_user_can( 'manage_sites' ) ) {
			$columns['roles'] = esc_html__( 'Roles', 'wp-user-profiles' );
		}

		// Return
		return $columns;
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @param array $blog Current site.
	 */
	public function column_cb( $blog = array() ) {

		// Combine and add a trailing slash
		$blogname = untrailingslashit( $blog['domain'] . $blog['path'] ); ?>

		<label class="screen-reader-text" for="blog_<?php echo esc_attr( $blog['blog_id'] ); ?>"><?php

			/* translators: %s: Site URL. */
			printf( esc_html__( 'Select %s', 'wp-user-profiles' ), $blogname );

		?></label>
		<input type="checkbox" id="blog_<?php echo esc_attr( $blog['blog_id'] ); ?>" name="allblogs[]" value="<?php echo esc_attr( $blog['blog_id'] ); ?>" />

		<?php
	}

	/**
	 * Removes all row action links as they're not needed for this context.
	 *
	 * @param object $blog        Site being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string Row actions output for sites.
	 */
	protected function handle_row_actions( $blog, $column_name, $primary ) {
		return '';
	}
}
