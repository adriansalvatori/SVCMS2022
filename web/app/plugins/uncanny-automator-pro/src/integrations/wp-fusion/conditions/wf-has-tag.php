<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WF_HAS_TAG
 *
 * @package Uncanny_Automator_Pro
 */
class WF_HAS_TAG extends Action_Condition {

	/**
	 * Method define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration = 'WF';
		/*translators: Token */
		$this->name         = __( 'The user has {{a specific}} tag', 'uncanny-automator-pro' );
		$this->code         = 'HAS_TAG';
		$this->dynamic_name = sprintf(
			/* translators: Tag name */
			esc_html__( 'The user has {{a specific:%1$s}} tag', 'uncanny-automator-pro' ),
			'TAG'
		);
		$this->is_pro        = true;
		$this->requires_user = true;
	}

	/**
	 * Method fields
	 *
	 * @return array
	 */
	public function fields() {

		$tag_field_args = array(
			'option_code'           => 'TAG',
			'label'                 => esc_html__( 'Tag', 'uncanny-automator-pro' ),
			'required'              => true,
			'options'               => $this->get_tag_options(),
			'supports_custom_value' => false,
		);

		return array(
			$this->field->select_field_args( $tag_field_args ),
		);
	}

	/**
	 * Method get_tag_options
	 *
	 * @return array
	 */
	public function get_tag_options() {

		$available_tags = wp_fusion()->settings->get( 'available_tags' );

		$options = array();

		foreach ( $available_tags as $tag_id => $tag ) {
			$options[] = array(
				'value' => $tag_id,
				'text'  => $this->get_tag_label( $tag ),
			);
		}

		return $options;
	}

	/**
	 * Method get_tag_label
	 *
	 * @param mixed $tag the tag id.
	 * @return string
	 */
	public function get_tag_label( $tag ) {

		if ( isset( $tag['label'] ) ) {
			return $tag['label'];
		}

		return $tag;
	}

	/**
	 * Method evaluate_condition
	 *
	 * Has to use the $this->condition_failed( $message ); method if the condition is not met.
	 *
	 * @return void
	 */
	public function evaluate_condition() {

		$tag_id = $this->get_parsed_option( 'TAG' );

		$has_tag = wp_fusion()->user->has_tag( $tag_id, $this->user_id );

		if ( false === $has_tag ) {

			$message = __( 'User does not have the tag ', 'uncanny-automator-pro' );

			$message .= $this->get_option( 'TAG_readable' );

			$this->condition_failed( $message );

		}
	}

}
