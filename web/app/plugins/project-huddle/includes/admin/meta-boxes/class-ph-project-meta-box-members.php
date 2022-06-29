<?php
/**
 * Project Options Meta Box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PH_Meta_Box_Project_Emails Class
 *
 * @since 1.0
 */
class PH_Meta_Box_Project_Members {

	// store meta box fields
	public static $fields = array();

	// store emails
	public static $member_ids = array();

	public static function meta_fields() {
		global $post;
		global $wp_roles;

		// get emails
		self::$member_ids = ph_get_project_member_ids( $post->ID );

		$options = array();
		foreach ( self::$member_ids as $key => $id ) :
           $user = isset( $id) ? get_user_by( 'id', $id ) : '';

            if ( $user && $id ) :
                // $user_role = current( $user->roles );
                $user_role = translate_user_role( $wp_roles->roles[ $user->roles[0] ]['name'] ); 
                    $options[ $id ] = array(
                        'text' => get_avatar( $id, 22 ) . ' ' . esc_html( $user->display_name ),
                        'attributes' => array(
                            'data-avatar' => get_avatar_url( $id, 22 ),
                            'data-role' => $user_role
                        )
                    ); 
            endif;
        endforeach;

		$fields = array(
			array(
				'id'      => 'project_members',
				'type'    => 'select_multi',
				'class'   => 'ph-select2 ph-get-users ph-multiple',
				'options' => $options,
				'default' => ''
			)
		);

		return $fields;
	}

	/**
	 * Output the metabox
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function output( $post ) {

		// create nonce field
		wp_nonce_field( 'project_huddle_save_data', 'project_huddle_meta_nonce' );

		$fields = self::meta_fields(); ?>

        <div id="project_emails_container" class="ph_meta_box">

			<?php
			foreach ( $fields as $field ) {
				PH()->meta->display_field( $field, $post );
			}
			// $is_updated = get_post_meta( $post->ID, 'custom_ph_members_list', true );

			// if( '' == $is_updated ) {
				self::save_project_client( $post->ID );
			// }
			?>

        </div>

		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function save( $post_id, $post ) {

		$fields = self::meta_fields();

		foreach ( $fields as $field ) {
			$value = self::sanitize_field( $field );

			if ( ! $value ) {
				$value = array();
			}

			if ( $field['id'] = 'project_members' ) {
				$original_members = ph_get_project_member_ids( $post_id );
				ph_update_project_members( $post_id, $value );
				$user = wp_get_current_user();

				/**
				 * allow actions on new email saves
				 *
				 * @returns array of new email addresses added
				 */
				$new_ids = array_diff( $value, $original_members );

				$new_emails = array();
				foreach( $new_ids as $new_id ) {
					$user = get_user_by('id', $new_id);
				    $new_emails[] = $user->user_email;
                }

				do_action( 'ph_new_email_admin_subscribe', $new_emails, $user, $post_id );
			}
		}

		self::save_project_client( $post_id );
	}

	/**
	 * Save custom meta box data
	 *
	 * @access public
	 * @since  4.2.0
	 * @return void
	 */
	public static function save_project_client( $post_id ) {

		$list_mem = get_post_meta( $post_id, 'project_members', true );
		$modified_list = array();

		foreach ( $list_mem as $member => $value ) {
            $user = isset( $value ) ? get_user_by( 'id', $value ) : '';
            if ( $user ) {
                $modified_list[] = $user->user_login;
            }
        }

		update_post_meta( $post_id, 'custom_ph_members_list', serialize( $modified_list ) );

	}

	public static function sanitize_field( $field ) {

		$value = isset( $_POST[ 'ph_' . $field['id'] ] ) ? $_POST[ 'ph_' . $field['id'] ] : false;

		switch ( $field['type'] ) {
			case 'checkbox':
				$value = $value ? esc_html( $value ) : 'off';
				break;
		}

		if ( is_array( $value ) && ! empty( $value ) ) {
			foreach ( $value as $key => $id ) {
			    $value[$key] = (int) $id; // force int
			}
		}

		return $value;
	}
}