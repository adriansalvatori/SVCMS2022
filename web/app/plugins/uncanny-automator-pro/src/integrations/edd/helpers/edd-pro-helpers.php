<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Edd_Helpers;

/**
 * Class Edd_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Edd_Pro_Helpers extends Edd_Helpers {

	/**
	 * Set pro.
	 *
	 * @param Edd_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Edd_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * Edd_Pro_Helpers constructor.
	 */
	public function __construct() {
	}

	/**
	 * All EDD discount codes.
	 *
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_edd_discount_codes( $label = null, $option_code = 'EDDDISCOUNTCODES', $any_option = true ) {
		if ( ! $label ) {
			$label = esc_attr__( 'Discount Code', 'uncanny-automator' );
		}

		if ( true === $any_option ) {
			$options['-1'] = esc_attr__( 'Any discount code', 'uncanny-automator' );
		}

		$discounts = edd_get_discounts();

		if ( isset( $discounts ) && ! empty( $discounts ) ) {
			foreach ( $discounts as $discount ) {
				$title = $discount->post_title;
				// set up a descriptive title for posts with no title.
				if ( empty( $title ) ) {
					/* translators: ID of recipe, trigger or action  */
					$title = sprintf( esc_attr__( 'ID: %1$s (no title)', 'uncanny-automator' ), $discount->ID );
				}
				// add post as an option.
				$options[ $discount->ID ] = $title;
			}
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
			'relevant_tokens' => array(),
		);

		return apply_filters( 'uap_option_all_edd_discount_codes', $option );
	}

}
