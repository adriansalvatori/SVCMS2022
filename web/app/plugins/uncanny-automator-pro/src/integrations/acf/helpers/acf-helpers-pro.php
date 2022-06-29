<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
namespace Uncanny_Automator_Pro;

/**
 * Class Acf_Helpers
 *
 * @package Uncanny_Automator
 */
class Acf_Helpers_Pro {

	/**
	 * Options.
	 *
	 * @var mixed $options The options.
	 */
	public $options;

	/**
	 * Pro.
	 *
	 * @var mixed $pro The pro helper options.
	 */
	public $pro;

	/**
	 * Load options.
	 *
	 * @var bool
	 */
	public $load_options;

	public static $instance;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		$this->load_options = Automator()->helpers->recipe->maybe_load_trigger_options( __CLASS__ );

		add_action( 'wp_ajax_uo_automator_acf_get_fields', array( $this, 'acf_get_fields' ) );

		add_action( 'wp_ajax_uo_automator_acf_get_post_under_post_type', array( $this, 'acf_get_post_under_post_type' ) );

	}

	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set options.
	 *
	 * @param Acf_Helpers_Pro $options
	 */
	public function setOptions( Acf_Helpers_Pro $options ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		$this->options = $options;
	}

	/**
	 * Set pro.
	 *
	 * @param Acf_Pro_Helpers $pro
	 */
	public function setPro( Acf_Helpers_Pro $pro ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		$this->pro = $pro;
	}

	/**
	 * Outputs all the post under the selected post type.
	 *
	 * @return void
	 */
	public function acf_get_post_under_post_type() {

		$list = array();

		$selected_post_type = automator_filter_input( 'value', INPUT_POST );

		$args = array(
			'post_type'      => $selected_post_type,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$obj = get_post_type_object( $selected_post_type );

		$post_type_name = esc_html__( 'post', 'uncanny-automator' );

		if ( isset( $obj->labels->singular_name ) ) {
			$post_type_name = $obj->labels->singular_name;
		}

		$query = new \WP_Query( $args );

		$list[] = array(
			'value' => $selected_post_type,
			/* translators: The post type singular label */
			'text'  => sprintf( esc_html__( 'Any %s', 'uncanny-automator' ), strtolower( $post_type_name ) ),
		);

		if ( $query->have_posts() ) :

			while ( $query->have_posts() ) :
				$query->the_post();

				$list[] = array(
					'value' => get_the_ID(),
					'text'  => get_the_title(),
				);

			endwhile;

			wp_reset_postdata();

		endif;

		wp_send_json( $list );

	}

	/**
	 * Outputs all the field under a selected post.
	 *
	 * @return void.
	 */
	public function acf_get_fields() {

		$post_id = automator_filter_input( 'value', INPUT_POST );

		$args = array(
			'post_id' => $post_id,
		);

		// Treat it as post type if its non-numeric.
		if ( ! is_numeric( $post_id ) ) {
			$args = array(
				'post_type' => $post_id,
			);
		}

		$field_groups_collection = acf_get_field_groups( $args );

		$field_groups = array();

		foreach ( $field_groups_collection as $field_group ) {

			$field_groups[] = acf_get_fields( $field_group['key'] );

		}

		$singular_fields[] = array(
			'value' => '-1',
			'text'  => esc_html__( 'Any field', 'uncanny-automator' ),
		);

		if ( ! empty( $field_groups ) && is_array( $field_groups ) ) {

			foreach ( $field_groups as $field_groups ) {

				foreach ( $field_groups as $field_group ) {

					$singular_fields[] = array(
						'value' => $field_group['name'],
						'text'  => $field_group['label'],
					);

				}
			}
		}

		wp_send_json( $singular_fields );

	}

	/**
	 * Get the ACF field on 'user_form' location.
	 **/

	public function get_user_fields() {

		if ( ! function_exists( 'acf_get_fields' ) ) {
			return array();
		}

		$fields = array(
			'-1' => esc_html__( 'Any field', 'uncanny-automator' ),
		);

		$field_groups = $this->get_user_form_field_groups();

		if ( empty( $field_groups ) ) {

			return array();

		}

		foreach ( $field_groups as $group ) {

			$group_fields = acf_get_fields( $group['key'] );

			if ( ! empty( $group_fields ) ) {

				foreach ( $group_fields as $field ) {

					$fields[ esc_attr( $field['name'] ) ] = esc_html( $field['label'] );

				}
			}
		}

		return $fields;
	}

	/**
	 * Get all field groups where location is equals to 'user_form'.
	 *
	 * @return array The field groups.
	 */
	public function get_user_form_field_groups() {

		if ( ! function_exists( 'acf_get_field_groups' ) ) {

			return array();

		}

		$groups_user_form = array();

		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $group ) {

			if ( ! empty( $group['location'] ) ) {

				foreach ( $group['location'] as $locations ) {

					foreach ( $locations as $location ) {

						if ( 'user_form' === $location['param'] ) {

							$groups_user_form[] = $group;

						}
					}
				}
			}
		}

		return $groups_user_form;

	}

}
