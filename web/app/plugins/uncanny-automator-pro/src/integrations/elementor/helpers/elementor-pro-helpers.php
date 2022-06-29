<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Elementor_Helpers;

/**
 * Class Elementor_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Elementor_Pro_Helpers extends Elementor_Helpers {

	/**
	 * Elementor_Pro_Helpers constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_select_form_fields_ANONELEMFORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_select_form_fields_ELEMFORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Elementor_Pro_Helpers $pro
	 */
	public function setPro( Elementor_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST ) ) {
			$form_id = $_POST['value'];

			global $wpdb;
			$query      = "SELECT ms.meta_value  FROM {$wpdb->postmeta} ms JOIN {$wpdb->posts} p on p.ID = ms.post_id WHERE ms.meta_key LIKE '_elementor_data' AND ms.meta_value LIKE '%form_fields%' AND p.post_status = 'publish' ";
			$post_metas = $wpdb->get_results( $query );

			if ( ! empty( $post_metas ) ) {
				foreach ( $post_metas as $post_meta ) {
					$inner_forms = Elementor_Helpers::get_all_inner_forms( json_decode( $post_meta->meta_value ) );
					if ( ! empty( $inner_forms ) ) {
						foreach ( $inner_forms as $form ) {
							if ( $form->id == $form_id ) {
								if ( ! empty( $form->settings->form_fields ) ) {
									foreach ( $form->settings->form_fields as $field ) {
										$fields[] = [
											'value' => $field->custom_id,
											'text'  => ! empty( $field->field_label ) ? $field->field_label : 'unknown',
										];
									}
								}
							}
						}
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Match condition for form field and value.
	 *
	 * @param array $metas .
	 * @param $form_id
	 * @param null|array $recipes .
	 * @param null|string $trigger_meta .
	 * @param null|string $trigger_code .
	 * @param null|string $trigger_second_code .
	 *
	 * @return array|bool
	 */
	public function match_condition( $metas, $form_id, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {
		if ( null === $recipes ) {
			return false;
		}

		$matches    = [];
		$recipe_ids = [];
		$data       = $metas->get( 'sent_data' );
		//Limiting to specific recipe IDs
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && absint( $trigger['meta'][ $trigger_meta ] ) === absint( $form_id ) ) {
					if ( isset( $trigger['meta'][ $trigger_code ] ) && isset( $trigger['meta'][ $trigger_second_code ] ) ) {
						$matches[ $trigger['ID'] ]    = [
							'field' => $trigger['meta'][ $trigger_code ],
							'value' => $trigger['meta'][ $trigger_second_code ],
						];
						$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
					}
				}
			}
		}

		//Try to match value with submitted to isolate recipe ids matched
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $recipe_id => $match ) {
				if ( $metas ) {
					foreach ( $data as $meta_key => $meta ) {

						if ( $match['field'] !== $meta_key ) {
							continue;
						}
						if ( is_array( $meta ) ) {
							$trigger_match = explode( ',', $match['value'] );
							// if input count is less then match then it does not match
							if ( count( $trigger_match ) > count( $meta ) ) {
								unset( $recipe_ids[ $recipe_id ] );
							} elseif ( ! empty( array_diff( $trigger_match, $meta ) ) ) {
								unset( $recipe_ids[ $recipe_id ] );
							}
						} else {
							if ( $meta !== $match['value'] ) {
								unset( $recipe_ids[ $recipe_id ] );
							}
						}
					}
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return [ 'recipe_ids' => $recipe_ids, 'result' => true ];
		}

		return false;
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_elementor_popups( $label = null, $option_code = 'ELEMPOPUPS', $args = [] ) {

		global $uncanny_automator;
		if ( ! $label ) {
			$label = esc_attr__( 'Popup', 'uncanny-automator-pro' );
		}

		$any_option = key_exists( 'any_option', $args ) ? $args['any_option'] : false;

		$args = [
			'post_type'      => 'elementor_library',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_elementor_template_type',
					'value'   => 'popup',
					'compare' => '=',
				),
			),
		];

		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, esc_attr__( 'Any popup', 'uncanny-automator-pro' ) );

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code => esc_attr__( 'Popup title', 'uncanny-automator-pro' ),
			],
		];

		return apply_filters( 'uap_option_all_elementor_popups', $option );
	}
}
