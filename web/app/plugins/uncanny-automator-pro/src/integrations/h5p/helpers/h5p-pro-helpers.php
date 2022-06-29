<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\H5p_Helpers;

/**
 * Class H5p_Helpers
 * @package Uncanny_Automator_Pro
 */
class H5p_Pro_Helpers extends H5p_Helpers {
	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * @param H5p_Pro_Helpers $pro
	 */
	public function setPro( H5p_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * H5p_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( method_exists( '\Uncanny_Automator\Automator_Helpers_Recipe', 'maybe_load_trigger_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		} else {
			$this->load_options = true;
		}

	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_h5p_contents( $label = null, $option_code = 'H5P_CONTENT', $any_option = true ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = __( 'Content', 'uncanny-automator-pro' );
		}

		global $wpdb;
		$options = [];
		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			// Get the library content
			$contents = $wpdb->get_results(
				"SELECT c.id,c.title FROM {$wpdb->prefix}h5p_contents c"
			);

			if ( $any_option ) {
				$options['-1'] = __( 'Any content', 'uncanny-automator-pro' );
			}
			if ( ! empty( $contents ) ) {
				foreach ( $contents as $content ) {
					$options[ $content->id ] = $content->title;
				}
			}
		}
		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
		];

		return apply_filters( 'uap_option_all_h5p_contents', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_h5p_content_types( $label = null, $option_code = 'H5P_CONTENTTYPE', $any_option = true ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}
		if ( ! $label ) {
			$label = __( 'Type', 'uncanny-automator' );
		}

		global $wpdb;
		$options = [];
		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			// Get the library content
			$types = $wpdb->get_results(
				"SELECT t.id,t.title FROM {$wpdb->prefix}h5p_libraries t WHERE t.runnable = 1 "
			);

			if ( $any_option ) {
				$options['-1'] = __( 'Any type', 'uncanny-automator' );
			}
			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					$options[ $type->id ] = $type->title;
				}
			}
		}
		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
		];

		return apply_filters( 'uap_option_all_h5p_content_types', $option );
	}
}