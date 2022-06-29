<?php

namespace Objectiv\Plugins\Checkout;

class FormFieldAugmenter extends SingletonAbstract {
	protected $checkbox_like_field_types = array( 'checkbox', 'radio' );
	protected $filters_added             = false;

	public function add_hooks() {
		if ( $this->filters_added ) {
			return;
		}

		$this->filters_added = true;

		add_filter( 'cfw_pre_output_fieldset_field_args', array( $this, 'calculate_columns' ), 100000 - 1000, 1 );
		add_filter( 'woocommerce_form_field_args', array( $this, 'calculate_columns' ), 100000, 1 );
		add_filter( 'woocommerce_form_field_args', array( $this, 'cfw_form_field_args' ), 100000, 2 );
		add_filter( 'woocommerce_form_field', array( $this, 'remove_extraneous_field_classes' ), 100000, 1 );
		add_filter( 'woocommerce_form_field', array( $this, 'add_select_container_class' ), 200000, 1 );
		add_filter( 'woocommerce_form_field', array( $this, 'cleanup_space_between_checkbox_input_and_text' ), 200000, 3 );
		add_filter( 'woocommerce_form_field', array( $this, 'add_before_html' ), 200000, 3 );
		add_filter( 'woocommerce_form_field_password', array( $this, 'password_field_toggle' ), 200000, 4 );
	}

	public function remove_hooks() {
		if ( ! $this->filters_added ) {
			return;
		}

		$this->filters_added = false;

		remove_filter( 'cfw_pre_output_fieldset_field_args', array( $this, 'calculate_columns' ), 100000 - 1000, 1 );
		remove_filter( 'woocommerce_form_field_args', array( $this, 'calculate_columns' ), 100000 );
		remove_filter( 'woocommerce_form_field_args', array( $this, 'cfw_form_field_args' ), 100000 );
		remove_filter( 'woocommerce_form_field', array( $this, 'remove_extraneous_field_classes' ), 100000 );
		remove_filter( 'woocommerce_form_field', array( $this, 'add_select_container_class' ), 200000 );
		remove_filter( 'woocommerce_form_field', array( $this, 'cleanup_space_between_checkbox_input_and_text' ), 200000, 3 );
		remove_filter( 'woocommerce_form_field', array( $this, 'add_before_html' ), 200000 );
		remove_filter( 'woocommerce_form_field_password', array( $this, 'password_field_toggle' ), 200000, 3 );
	}

	public function calculate_columns( $args ): array {
		if ( ! isset( $args['class'] ) || ! is_array( $args['class'] ) ) {
			$args['class'] = ! empty( $args['class'] ) ? array( $args['class'] ) : array();
		}

		// Calculate columns
		if ( ! isset( $args['columns'] ) ) {
			$args['columns'] = 12;

			if ( in_array( 'form-row-first', $args['class'], true ) || in_array( 'form-row-last', $args['class'], true ) ) {
				$args['columns'] = 6;
			}

			if ( in_array( 'col-lg-3', $args['class'], true ) ) {
				$args['columns'] = 3;
			}

			if ( in_array( 'col-lg-4', $args['class'], true ) ) {
				$args['columns'] = 4;
			}

			if ( in_array( 'col-lg-6', $args['class'], true ) ) {
				$args['columns'] = 6;
			}

			if ( in_array( 'col-lg-8', $args['class'], true ) ) {
				$args['columns'] = 8;
			}

			if ( in_array( 'col-lg-9', $args['class'], true ) ) {
				$args['columns'] = 9;
			}
		}

		// Add column class
		$args['class'][] = 'col-lg-' . $args['columns'];

		return $args;
	}

	/**
	 * Pre-process form field arguments for our pages
	 *
	 * @param mixed $args
	 * @param string $key
	 * @return array
	 */
	public function cfw_form_field_args( $args, string $key ): array {
		// Handle input classes
		if ( is_string( $args['input_class'] ) ) {
			$args['input_class'] = array( $args['input_class'] );
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

		// Add field type class
		$args['class'][] = 'cfw-' . $args['type'] . '-input';

		// Part of operation stop sorting our fields!
		$args['priority'] = '';

		if ( in_array( $args['type'], $this->get_checkbox_like_field_types(), true ) ) {
			$args['class'][] = 'cfw-check-input';
		}

		if ( ! in_array( $args['type'], apply_filters( 'cfw_non_floating_label_field_types', array( 'checkbox', 'radio' ) ), true ) ) {
			$args['label_class'][] = 'cfw-floatable-label';
		}

		// Add generic wrap
		$args['class'][] = 'cfw-input-wrap';

		$value = WC()->checkout()->get_value( $key );

		if ( ! is_null( $value ) ) {
			$args['custom_attributes']['data-persist'] = 'false';
		}

		// Set saved value
		$args['custom_attributes']['data-saved-value'] = $value ?? 'CFW_EMPTY';

		$args['placeholder'] = ! empty( $args['placeholder'] ) ? $args['placeholder'] : strip_tags( $args['label'] );

		if ( ! $args['required'] && false === stripos( $args['placeholder'], cfw__( 'optional', 'woocommerce' ) ) && ! apply_filters( 'cfw_form_field_append_optional_to_placeholder', isset( $args['suppress_optional_suffix'] ), $key ) ) {
			$args['placeholder'] .= ' (' . cfw__( 'optional', 'woocommerce' ) . ')';
		}

		// Make sure we have a default option
		if ( 'select' === $args['type'] && isset( $args['options'] ) && is_array( $args['options'] ) && ! empty( $args['options'] ) ) {
			// Reset options array to first element.
			reset( $args['options'] );

			if ( key( $args['options'] ) !== '' ) {
				$args['options'] = array_merge( array( '' => cfw__( 'Choose an option', 'woocommerce' ) ), $args['options'] );
			}
		}

		if ( 'select' === $args['type'] || ! empty( $args['value'] ) ) {
			$args['class'][] = 'cfw-label-is-floated';
		}

		return $this->maybe_add_parsley_attributes( $args );
	}

	/**
	 * Strip classes that we don't want on our fields from woocommerce_form_field output
	 *
	 * @param $field
	 * @return array|mixed|string|string[]
	 */
	public function remove_extraneous_field_classes( $field ) {
		$classes_to_remove = array( 'form-row-first', 'form-row-last', 'form-row-wide' );

		foreach ( $classes_to_remove as $class ) {
			if ( strpos( $field, $class ) !== false ) {
				// Cleanup <class><space> and <class>
				$field = str_replace( $class . ' ', '', $field );
				$field = str_replace( $class, '', $field );
			}
		}

		return $field;
	}

	/**
	 * Add cfw-select-input to the wrap for select fields
	 * @param $field
	 * @return array|mixed|string|string[]
	 */
	public function add_select_container_class( $field ) {
		if ( stripos( $field, '<select' ) !== false ) {
			$field = str_replace( 'form-row', 'form-row cfw-select-input', $field );
		}

		return $field;
	}

	/**
	 * @param string $field
	 * @param string $key
	 * @param array $args
	 * @return array|string|string[]
	 */
	public function cleanup_space_between_checkbox_input_and_text( string $field, string $key, array $args ): string {
		if ( ! in_array( $args['type'], $this->get_checkbox_like_field_types(), true ) ) {
			return $field;
		}

		$field = preg_replace( '@(<input.+type="checkbox".+/>)\s@', '$1', $field );

		return preg_replace( '@(</span>)\s@', '$1', $field );
	}

	public function add_before_html( $field, $key, $args ) {
		if ( $args['before_html'] ?? false ) {
			$field = $args['before_html'] . $field;
		}

		return $field;
	}

	public function maybe_add_parsley_attributes( array $args ) : array {
		if ( ! $args['required'] ) {
			return $args;
		}

		if ( 'hidden' === $args['type'] ) {
			return $args;
		}

		$args['custom_attributes']['data-parsley-required'] = 'true';

		$current_tab = cfw_get_current_tab();

		// Set parsley group
		if ( ! empty( $current_tab ) && empty( $args['custom_attributes']['data-parsley-group'] ) ) {
			$args['custom_attributes']['data-parsley-group'] = $current_tab;
		}

		return $args;
	}

	/**
	 * @return string[]
	 */
	public function get_checkbox_like_field_types(): array {
		return apply_filters( 'cfw_checkbox_like_field_types', $this->checkbox_like_field_types );
	}

	/**
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	function password_field_toggle( $field, $key, $args, $value ): string {
		$eye_open = '<svg xmlns="http://www.w3.org/2000/svg" class="cfw-eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
		$eye_shut = '<svg xmlns="http://www.w3.org/2000/svg" class="cfw-eye-shut" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>';

		$wrap = '<a class="cfw-password-toggle cfw-password-eye-open" tabindex="-1" href="javascript:">' . $eye_open . $eye_shut . '</a>';

		return str_replace( '<input', "{$wrap}<input", $field );
	}
}
