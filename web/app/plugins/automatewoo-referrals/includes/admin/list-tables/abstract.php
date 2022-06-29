<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Admin_List_Table
 */
abstract class Admin_List_Table extends AutomateWoo\Admin_List_Table {


	function output_advocate_filter() {

		$advocate_string = '';
		$advocate_id     = absint( aw_request( '_advocate_user' ) );

		if ( $advocate_id ) {
			$advocate        = get_user_by( 'id', $advocate_id );
			$advocate_string = esc_html( $advocate->display_name ) . ' (#' . absint( $advocate->ID ) . ' &ndash; ' . esc_html( $advocate->user_email );
		}

		?>
		<select class="wc-customer-search" style="width:203px;" name="_advocate_user"
				data-placeholder="<?php esc_attr_e( 'Search for an advocate&hellip;', 'automatewoo-referrals' ); ?>"
				data-allow_clear="true">
			<?php if ( $advocate_id ) : ?>
				<?php echo '<option value="' . absint( $advocate_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $advocate_string ) . '</option>'; ?>
			<?php endif; ?>
		</select>
		<?php
	}


}