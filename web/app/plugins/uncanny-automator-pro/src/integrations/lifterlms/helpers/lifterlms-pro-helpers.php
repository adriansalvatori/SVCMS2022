<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Lifterlms_Helpers;
use function Symfony\Component\Translation\t;

/**
 * Class Lifterlms_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Lifterlms_Pro_Helpers extends Lifterlms_Helpers {
	/**
	 * Lifterlms_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Lifterlms_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		// add all memberships option
		add_filter( 'uap_option_all_lf_memberships', array( $this, 'add_all_memberships_option' ), 99, 3 );

	}

	/**
	 * @param Lifterlms_Pro_Helpers $pro
	 */
	public function setPro( Lifterlms_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param bool $any_option
	 *
	 * @return mixed
	 */
	public function all_lf_groups( $label = null, $option_code = 'LFGROUPS', $any_option = true ) {
		if ( ! $this->load_options ) {
			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Group', 'uncanny-automator-pro' );
		}

		$args = array(
			'post_type'      => 'llms_group',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$options = Automator()->helpers->recipe->options->wp_query( $args, $any_option, esc_attr__( 'Any group', 'uncanny-automator' ) );

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                => esc_attr__( 'Group title', 'uncanny-automator' ),
				$option_code . '_ID'        => esc_attr__( 'Group ID', 'uncanny-automator' ),
				$option_code . '_URL'       => esc_attr__( 'Group URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'  => esc_attr__( 'Group featured image ID', 'uncanny-automator' ),
				$option_code . '_THUMB_URL' => esc_attr__( 'Group featured image URL', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_all_lf_groups', $option );
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public function add_all_memberships_option( $options, $is_all_label ) {
		if ( empty( $options ) ) {
			return $options;
		}

		if ( 'LFMEMBERSHIP' !== $options['option_code'] ) {
			return $options;
		}

		if ( true === $is_all_label ) {
			$all_groups         = array( '-1' => esc_attr__( 'All memberships', 'uncanny-automator-pro' ) );
			$options['options'] = $all_groups + $options['options'];
		}

		return $options;
	}
}
