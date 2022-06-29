<?php
/**
 * Customizer Control: dimensions.
 *
 * @package     Xirki
 * @subpackage  Controls
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dimensions control.
 * multiple fields with CSS units validation.
 */
class Xirki_Control_Dimensions extends Xirki_Control_Base {

	/**
	 * The control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'xirki-dimensions';

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @see WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		if ( is_array( $this->choices ) ) {
			foreach ( $this->choices as $choice => $value ) {
				if ( 'labels' !== $choice && true === $value ) {
					$this->json['choices'][ $choice ] = true;
				}
			}
		}
		if ( is_array( $this->json['default'] ) ) {
			foreach ( $this->json['default'] as $key => $value ) {
				if ( isset( $this->json['choices'][ $key ] ) && ! isset( $this->json['value'][ $key ] ) ) {
					$this->json['value'][ $key ] = $value;
				}
			}
		}
	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
	 */
	public function enqueue() {
		wp_enqueue_style( 'xirki-styles', trailingslashit( Xirki::$url ) . 'controls/css/styles.css', array(), XIRKI_VERSION );
		wp_localize_script( 'xirki-script', 'dimensionsxirkiL10n', $this->l10n() );
	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see WP_Customize_Control::print_template()
	 *
	 * @access protected
	 */
	protected function content_template() {
		?>
		<label>
			<# if ( data.label ) { #><span class="customize-control-title">{{{ data.label }}}</span><# } #>
			<# if ( data.description ) { #><span class="description customize-control-description">{{{ data.description }}}</span><# } #>
			<div class="wrapper">
				<div class="control">
					<# for ( choiceKey in data.default ) { #>
						<div class="{{ choiceKey }}">
							<h5>
								<# if ( ! _.isUndefined( data.choices.labels ) && ! _.isUndefined( data.choices.labels[ choiceKey ] ) ) { #>
									{{ data.choices.labels[ choiceKey ] }}
								<# } else if ( ! _.isUndefined( data.l10n[ choiceKey ] ) ) { #>
									{{ data.l10n[ choiceKey ] }}
								<# } else { #>
									{{ choiceKey }}
								<# } #>
							</h5>
							<div class="{{ choiceKey }} input-wrapper">
								<# var val = ( ! _.isUndefined( data.value ) && ! _.isUndefined( data.value[ choiceKey ] ) ) ? data.value[ choiceKey ].toString().replace( '%%', '%' ) : ''; #>
								<input {{{ data.inputAttrs }}} type="text" data-choice="{{ choiceKey }}" value="{{ val }}"/>
							</div>
						</div>
					<# } #>
				</div>
			</div>
		</label>
		<?php
	}

	/**
	 * Returns an array of translation strings.
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return array
	 */
	protected function l10n() {
		return array(
			'left-top'       => esc_html__( 'Left Top', 'xirki' ),
			'left-center'    => esc_html__( 'Left Center', 'xirki' ),
			'left-bottom'    => esc_html__( 'Left Bottom', 'xirki' ),
			'right-top'      => esc_html__( 'Right Top', 'xirki' ),
			'right-center'   => esc_html__( 'Right Center', 'xirki' ),
			'right-bottom'   => esc_html__( 'Right Bottom', 'xirki' ),
			'center-top'     => esc_html__( 'Center Top', 'xirki' ),
			'center-center'  => esc_html__( 'Center Center', 'xirki' ),
			'center-bottom'  => esc_html__( 'Center Bottom', 'xirki' ),
			'font-size'      => esc_html__( 'Font Size', 'xirki' ),
			'font-weight'    => esc_html__( 'Font Weight', 'xirki' ),
			'line-height'    => esc_html__( 'Line Height', 'xirki' ),
			'font-style'     => esc_html__( 'Font Style', 'xirki' ),
			'letter-spacing' => esc_html__( 'Letter Spacing', 'xirki' ),
			'word-spacing'   => esc_html__( 'Word Spacing', 'xirki' ),
			'top'            => esc_html__( 'Top', 'xirki' ),
			'bottom'         => esc_html__( 'Bottom', 'xirki' ),
			'left'           => esc_html__( 'Left', 'xirki' ),
			'right'          => esc_html__( 'Right', 'xirki' ),
			'center'         => esc_html__( 'Center', 'xirki' ),
			'size'           => esc_html__( 'Size', 'xirki' ),
			'spacing'        => esc_html__( 'Spacing', 'xirki' ),
			'width'          => esc_html__( 'Width', 'xirki' ),
			'height'         => esc_html__( 'Height', 'xirki' ),
			'invalid-value'  => esc_html__( 'Invalid Value', 'xirki' ),
		);
	}
}
