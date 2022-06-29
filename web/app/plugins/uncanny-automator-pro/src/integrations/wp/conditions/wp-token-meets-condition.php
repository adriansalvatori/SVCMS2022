<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_TOKEN_MEETS_CONDITION
 *
 * @package Uncanny_Automator_Pro
 */
class WP_TOKEN_MEETS_CONDITION extends Action_Condition {

	/**
	 * define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration = 'WP';
		/*translators: Token */
		$this->name         = __( '{{A token}} meets a condition', 'uncanny-automator-pro' );
		$this->code         = 'TOKEN_MEETS_CONDITION';
		$this->dynamic_name = sprintf(
		/*translators: A token matches a value */
			esc_html__( '{{A token:%1$s}} {{matches:%2$s}} {{a value:%3$s}}', 'uncanny-automator-pro' ),
			'TOKEN',
			'CRITERIA',
			'VALUE'
		);
		$this->is_pro        = true;
		$this->requires_user = false;
		$this->deprecated    = true;
	}

	/**
	 * fields
	 *
	 * @return array
	 */
	public function fields() {
		$conditions = array(
			'option_code'           => 'CRITERIA',
			'label'                 => esc_html__( 'Criteria', 'uncanny-automator-pro' ),
			'required'              => true,
			'options'               => apply_filters(
				'automator_pro_wp_token_meets_condition_criteria_options',
				array(
					array(
						'value' => 'is',
						'text'  => esc_html__( 'is', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'is_not',
						'text'  => esc_html__( 'is not', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'contains',
						'text'  => esc_html__( 'contains', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'does_not_contain',
						'text'  => esc_html__( 'does not contain', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'is_greater_than',
						'text'  => esc_html__( 'is greater than', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'is_greater_than_or_equal_to',
						'text'  => esc_html__( 'is greater than or equal to', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'is_less_than',
						'text'  => esc_html__( 'is less than', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'is_less_than_or_equal_to',
						'text'  => esc_html__( 'is less than or equal to', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'starts_with',
						'text'  => esc_html__( 'starts with', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'does_not_start_with',
						'text'  => esc_html__( 'does not start with', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'ends_with',
						'text'  => esc_html__( 'ends with', 'uncanny-automator-pro' ),
					),
					array(
						'value' => 'does_not_end_with',
						'text'  => esc_html__( 'does not end with', 'uncanny-automator-pro' ),
					),

				)
			),
			'supports_custom_value' => false,
		);

		return array(
			// Token field
			$this->field->text(
				array(
					'option_code' => 'TOKEN',
					'label'       => esc_html__( 'Condition', 'uncanny-automator-pro' ),
					'placeholder' => esc_html__( 'Token', 'uncanny-automator-pro' ),
					'input_type'  => 'text',
					'required'    => true,
					'description' => sprintf(
					/* translators: 1. Learn more */
						_x( '%1$s about conditions', 'Learn more about conditions', 'uncanny-automator-pro' ),
						// Anchor
						sprintf(
							'<a href="%1$s" target="_blank">%2$s <uo-icon id="external-link"></uo-icon></a>',
							'https://automatorplugin.com/knowledge-base/action-filters-conditions/?utm_source=uncanny_automator_pro&utm_medium=action_conditions&utm_content=options_modal-learn_more_link',
							_x( 'Learn more', 'Learn more about conditions', 'uncanny-automator-pro' )
						)
					),
				)
			),
			// Criteria field
			$this->field->select_field_args( $conditions ),
			// Value field
			$this->field->text(
				array(
					'option_code' => 'VALUE',
					'label'       => esc_html__( 'Value', 'uncanny-automator-pro' ),
					'placeholder' => esc_html__( 'Value', 'uncanny-automator-pro' ),
					'input_type'  => 'text',
					'required'    => false,
				)
			),
		);
	}

	/**
	 * Evaluate_condition
	 *
	 * Has to use the $this->condition_failed( $message ); method if the condition is not met.
	 *
	 * @return void
	 */
	public function evaluate_condition() {

		$parsed_token = $this->get_parsed_option( 'TOKEN' );
		$parsed_value = $this->get_parsed_option( 'VALUE' );

		$case_sensitive = apply_filters( 'automator_pro_wp_token_meets_condition_case_sensitive', false );

		if ( false === $case_sensitive ) {
			$parsed_token = mb_strtolower( $parsed_token );
			$parsed_value = mb_strtolower( $parsed_value );
		}

		$criteria = $this->get_option( 'CRITERIA' );

		$condition_met = $this->check_logic( $parsed_token, $criteria, $parsed_value );

		// If the conditions is not met, send an error message and mark the condition as failed.
		if ( false === $condition_met ) {

			$message = $this->generate_error_message( $parsed_token, $parsed_value );

			$this->condition_failed( $message );

		}

		// If the condition is met, do nothing.

	}

	/**
	 * Check_logic
	 *
	 * This function will check the values against the logic selected
	 *
	 * @param mixed $parsed_token
	 * @param mixed $criteria
	 * @param mixed $parsed_value
	 *
	 * @return bool
	 */
	public function check_logic( $parsed_token, $criteria, $parsed_value ) {

		switch ( $criteria ) {
			case 'is':
				$result = $parsed_token == $parsed_value; //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				break;
			case 'is_not':
				$result = $parsed_token != $parsed_value; //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				break;
			case 'contains':
				if ( is_array( $parsed_value ) || is_object( $parsed_value ) ) {
					$parsed_value = join( ' ', $parsed_value );
				}
				$result = stripos( $parsed_token, $parsed_value ) !== false;
				break;
			case 'does_not_contain':
				if ( is_array( $parsed_value ) || is_object( $parsed_value ) ) {
					$parsed_value = join( ' ', $parsed_value );
				}
				$result = stripos( $parsed_token, $parsed_value ) === false;
				break;
			case 'is_greater_than':
				$result = floatval( $parsed_token ) > floatval( $parsed_value );
				break;
			case 'is_greater_than_or_equal_to':
				$result = floatval( $parsed_token ) >= floatval( $parsed_value );
				break;
			case 'is_less_than':
				$result = floatval( $parsed_token ) < floatval( $parsed_value );
				break;
			case 'is_less_than_or_equal_to':
				$result = floatval( $parsed_token ) <= floatval( $parsed_value );
				break;
			case 'starts_with':
				$result = stripos( $parsed_token, $parsed_value ) === 0;
				break;
			case 'does_not_start_with':
				$result = stripos( $parsed_token, $parsed_value ) !== 0;
				break;
			case 'ends_with':
				$result = stripos( strrev( $parsed_token ), strrev( $parsed_value ) ) === 0;
				break;
			case 'does_not_end_with':
				$result = stripos( strrev( $parsed_token ), strrev( $parsed_value ) ) !== 0;
				break;
			default:
				$result = true;
				break;
		}

		return $result;
	}

	/**
	 * Generate_error_message
	 *
	 * @param string $parsed_token
	 * @param string $parsed_value
	 *
	 * @return string
	 */
	public function generate_error_message( $parsed_token, $parsed_value ) {

		$readable_criteria = $this->get_option( 'CRITERIA_readable' );

		$condition_sentence = $parsed_token . '" ' . $readable_criteria . ' "' . $parsed_value . '"';

		return __( 'Failed condition: "', 'uncanny-automator-pro' ) . $condition_sentence;
	}
}
