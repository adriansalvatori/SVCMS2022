<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Buddypress_Helpers;

/**
 * Class Buddypress_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Buddypress_Pro_Helpers extends Buddypress_Helpers {
	/**
	 * Buddypress_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Buddypress_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}
		add_action( 'wp_ajax_select_bp_member_types', array( $this, 'select_bp_member_types' ) );
	}

	/**
	 * @param Buddypress_Pro_Helpers $pro
	 */
	public function setPro( Buddypress_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function list_base_profile_fields( $label = null, $option_code = 'BPFIELD', $args = array() ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Field', 'uncanny-automator' );
		}

		$args = wp_parse_args( $args,
			array(
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any field', 'uncanny-automator' ),
			)
		);

		$options = [];
		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			if ( $args['uo_include_any'] ) {
				$options[ - 1 ] = $args['uo_any_label'];
			}
			$base_group_id = 1;
			if ( function_exists( 'bp_xprofile_base_group_id' ) ) {
				$base_group_id = bp_xprofile_base_group_id();
			}

			global $wpdb;
			$fields_table    = $wpdb->prefix . "bp_xprofile_fields";
			$xprofile_fields = $wpdb->get_results( "SELECT * FROM {$fields_table} WHERE parent_id = 0 AND group_id = '{$base_group_id}' ORDER BY field_order ASC" );
			if ( ! empty( $xprofile_fields ) ) {
				foreach ( $xprofile_fields as $xprofile_field ) {
					$options[ $xprofile_field->id ] = $xprofile_field->name;
				}
			}
		}

		$option = [
			'option_code'              => $option_code,
			'label'                    => $label,
			'input_type'               => 'select',
			'required'                 => true,
			'options'                  => $options,
			'custom_value_description' => esc_attr__( 'User ID', 'uncanny-automator' ),
		];

		return apply_filters( 'uap_option_list_base_profile_fields', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function get_profile_types( $label = null, $option_code = 'BPPROFILETYPE', $args = array() ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		$args = wp_parse_args( $args, [
			'uo_include_any' => false,
			'uo_any_label'   => esc_attr__( 'Any profile type', 'uncanny-automator' ),
		] );

		if ( ! $label ) {
			$label = esc_attr__( 'Profile type', 'uncanny-automator' );
		}

		$options = [];
		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			if ( $args['uo_include_any'] ) {
				$options[ - 1 ] = $args['uo_any_label'];
			}
			if ( function_exists( 'bp_get_member_types' ) ) {
				$types = bp_get_member_types( [] );

				if ( $types ) {
					foreach ( $types as $type ) {
						$options[ $type->ID ] = $type->post_title;
					}
				}
			}
		}

		$option = [
			'option_code'              => $option_code,
			'label'                    => $label,
			'input_type'               => 'select',
			'required'                 => true,
			'options'                  => $options,
			'is_ajax'                  => true,
			'custom_value_description' => _x( 'Profile Type ID', 'BuddyBoss', 'uncanny-automator' ),
			'endpoint'                 => 'select_bp_member_types',
		];

		return apply_filters( 'uap_option_get_profile_types', $option );
	}

	public function select_bp_member_types() {
		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );
		$fields = [];
		if ( isset( $_POST ) && key_exists( 'value', $_POST ) ) {
			$post_type = sanitize_text_field( $_POST['value'] );
			if ( function_exists( 'bp_get_member_types' ) ) {
				$member_types = bp_get_member_types();

				if ( $member_types ) {
					foreach ( $member_types as $ID => $type ) {
						$fields[] = [
							'value' => $ID,
							'text'  => __( $type, 'uncanny-automator' ),
						];
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function list_all_profile_fields( $label = null, $option_code = 'BPFIELD', $args = array() ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Field', 'uncanny-automator' );
		}

		$args = wp_parse_args( $args,
			[
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any field', 'uncanny-automator' ),
				'is_repeater'    => false,
			]
		);

		$options = [];
		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			if ( $args['uo_include_any'] ) {
				$options[ - 1 ] = $args['uo_any_label'];
			}

			global $wpdb;
			$fields_table    = $wpdb->prefix . "bp_xprofile_fields";
			$xprofile_fields = $wpdb->get_results( "SELECT * FROM {$fields_table} WHERE parent_id = 0 ORDER BY field_order ASC" );
			if ( ! empty( $xprofile_fields ) ) {
				foreach ( $xprofile_fields as $xprofile_field ) {
					if ( $args['is_repeater'] ) {
						$options[] = [
							'value' => $xprofile_field->id,
							'text'  => $xprofile_field->name,
						];
					} else {
						$options[ $xprofile_field->id ] = $xprofile_field->name;
					}
				}
			}
		}

		$option = [
			'option_code'              => $option_code,
			'label'                    => $label,
			'input_type'               => 'select',
			'required'                 => true,
			'options'                  => $options,
			'custom_value_description' => esc_attr__( 'User ID', 'uncanny-automator' ),
		];


		return apply_filters( 'uap_option_list_all_profile_fields', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function list_buddypress_forums( $label = null, $option_code = 'BPFORUMS', $args = array() ) {
		if ( ! $this->load_options ) {
			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! function_exists( 'bbp_get_forum_post_type' ) ) {
			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		$args = wp_parse_args( $args,
			array(
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any forum', 'uncanny-automator' ),
			)
		);
		if ( ! $label ) {
			$label = esc_attr__( 'Forum', 'uncanny-automator' );
		}

		$options    = array();
		$forum_args = [
			'post_type'      => bbp_get_forum_post_type(),
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
		];

		if ( $args['uo_include_any'] ) {
			$options[ - 1 ] = $args['uo_any_label'];
		}

		$forums = Automator()->helpers->recipe->options->wp_query( $forum_args );
		if ( ! empty( $forums ) ) {
			foreach ( $forums as $key => $forum ) {
				$options[ $key ] = $forum;
			}
		}

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code          => esc_attr__( 'Forum title', 'uncanny-automator' ),
				$option_code . '_ID'  => esc_attr__( 'Forum ID', 'uncanny-automator' ),
				$option_code . '_URL' => esc_attr__( 'Forum URL', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_list_buddypress_forums', $option );
	}
}
