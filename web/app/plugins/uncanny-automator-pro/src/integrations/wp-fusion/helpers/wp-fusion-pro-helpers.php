<?php


namespace Uncanny_Automator_Pro;


/**
 * Class Wp_Fusion_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wp_Fusion_Pro_Helpers {

	/**
	 * @var bool
	 */
	public $load_options = true;
	/**
	 * @var
	 */
	public $pro;
	/**
	 * @var
	 */
	public $options;

	/**
	 * Wp_Fusion_Pro_Helpers constructor.
	 */
	public function __construct() {
	}

	/**
	 * @param Wp_Fusion_Pro_Helpers $options
	 */
	public function setOptions( Wp_Fusion_Pro_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param Wp_Fusion_Pro_Helpers $pro
	 */
	public function setPro( Wp_Fusion_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}


	/**
	 * @param string $label
	 * @param string $trigger_meta
	 *
	 * @return mixed
	 */
	public static function fusion_tags( $label = '', $trigger_meta = '' ) {

		if ( empty( $label ) ) {
			$label = __( 'Tag', 'uncanny-automator' );
		}

		$tags    = wp_fusion()->settings->get( 'available_tags' );
		$options = array();
		if ( $tags ) {
			foreach ( $tags as $t_id => $tag ) {
				if ( is_array( $tag ) && isset( $tag['label'] ) ) {
					$options[ $t_id ] = $tag['label'];
				} else {
					$options[ $t_id ] = $tag;
				}
			}
		}

		$option = array(
			'option_code' => $trigger_meta,
			'label'       => $label,
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $options,
		);

		return apply_filters( 'uap_option_wp_fusion_tags', $option );
	}

}