<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Signature_Field
 *
 * @since 1.13
 */
class Forminator_Signature_Field extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'signature';

	/**
	 * @var int
	 */
	public $position = 25;

	/**
	 * @var string
	 */
	public $type = 'signature';

	/**
	 * @var string
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-pencil';

	/**
	 * For loading signature library only once when Loading form using AJAX
	 *
	 * @var bool
	 */
	private static $is_library_loaded;

	/**
	 * Uniq ID
	 *
	 * @var bool
	 */
	public $uniq = '';

	/**
	 * Forminator_Signature constructor.
	 *
	 * @since 1.13
	 */
	public function __construct() {
		parent::__construct();
		$this->name = __( 'E-Signature', 'forminator' );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.13
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_type' => 'signature',
			'field_label' => __( 'Signature', 'forminator' ),
			'placeholder' => __( 'Start signing your signature here', 'forminator' ),
			'filetype' => 'png',
			'height' => 180,
			'thickness' => 2,
			'icon' => "true",
		);
	}


	public function get_pro_field() {
		return [
			'field_type' => 'signature',
			'name' => $this->name,
			'icon' => $this->icon,
		];
	}

	/**
	 * Get border width of signature field
	 *
	 * @param string $design
	 * @return int
	 */
	private static function get_border_width( $design ) {
		switch ( $design ) {
			case 'bold':
				$width = 3;
				break;
			case 'flat':
				$width = 0;
				break;
			default:
				$width = 1;
				break;
		}

		return $width;
	}

	/**
	 * Get background width of signature field
	 *
	 * @param string $design
	 * @return string
	 */
	private static function get_back_color( $design, $settings ) {
		switch ( $design ) {
			case 'material':
				$color = 'transparent';
				break;
			default:
				$color = self::get_property( 'input-bg', $settings, '#EDEDED' );
				break;
		}

		return $color;
	}

	/**
	 * Get html-ID of signature field
	 *
	 * @param string $field_id
	 * @return string
	 */
	private function get_signature_id( $field_id, $uniq = '' ) {
		if( ! empty( $uniq ) ) {
			$field_id = $uniq;
		} else {
			$field_id = $this->uniq;
		}

		$id = 'ctlSignature' . $field_id;

		return $id;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.13
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->uniq = uniqid();
		$this->field = $field;
		$field_name = $this->get_id( $field );
		$id = self::get_signature_id( $field_name ); // Add prefix because there is already existed div with the id on the form.
		$height = self::get_property( 'height', $field, 180 );
		$thickness = self::get_property( 'thickness', $field, 2 );
		$required = self::get_property( 'required', $field, false );
		$design = $this->get_form_style( $settings );
		$label = $this->sanitize_value( self::get_property( 'field_label', $field ) );
		$description = $this->sanitize_value( self::get_property( 'description', $field ) );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$color_settings = self::get_property( 'cform-color-settings', $settings, false );

		if ( empty( $color_settings ) || ! $color_settings ) {
			$color = '#000000';
			$border_color = '#777771';
			$reset_color_default = '#888888';
			$reset_color_hover = '#17A8E3';
		} else {
			$color = self::get_property( 'signature-color', $settings, '#000000' );
			$border_color = self::get_property( 'input-border', $settings, '#777771' );
			$reset_color_default = self::get_property( 'signature-reset-icon-default', $settings, '#888888' );
			$reset_color_hover = self::get_property( 'signature-reset-icon-hover', $settings, '#17A8E3' );
		}

		$signature_script = '
			var args_' . $id . ' = {
				SignObject:"' . $id . '",
				PenColor: "' . $color . '",
				PenSize: "' . $thickness . '",
				SignHeight: "' . $height . '",
				ClearImage:"' . forminator_plugin_url() . 'addons/pro/e-signature/lib/Icons - clear.svg",
				TransparentSign:"true",
				IeModalFix: false,
				BorderStyle: "solid",
				BorderWidth: "0",
				BackColor: "transparent",
				BorderColor: "transparent",
				RequiredPoints: "15",
				StartMessage: "",
				SuccessMessage: "",
				ErrorMessage: "",
				SignzIndex: 0,
				Visible: "true",
				forceMouseEvent: true,
				Enabled: "true"
			};

			window.obj' . $id . ' = new SuperSignature( args_' . $id . ' );

			function loadSignField_' . $id . '() {
				if ( jQuery( "#' . $id . '" ).length === 0 || typeof SuperSignature === "undefined" || typeof window.obj' . $id . ' === "undefined" ) {
					setTimeout( function () {
						loadSignField_' . $id . '();
					}, 200 );
				} else {
	           	obj' . $id . '.Init();

					jQuery( "#' . $id . '_data" ).addClass( "do-validate" );

					/**
					 * Change reset button colors on default and hover states.
					 */
					reset_color = function() {

						var button = jQuery( "#' . $id . '_resetbutton" );

						button.each( function () {

							var $e = jQuery( this );
							var imgURL = $e.prop( "src" );

							function change_svg_color( $color ) {

								$.get( imgURL, function( data ) {

									// Get SVG tag.
									var $svg = jQuery( data ).find( "svg" );

									// Set default color.
									$svg.find( "path" ).attr( "fill", $color );

									$e.prop(
										"src",
										"data:image/svg+xml;base64," + window.btoa(
											unescape( encodeURIComponent(
												$svg.prop( "outerHTML" )
											) )
										)
									);
								});
							}

							// Set default color on load.
							change_svg_color( "' . $reset_color_default . '" );

							jQuery( this ).on( "mouseover", function() {
								change_svg_color( "' . $reset_color_hover . '" );
							}).on( "mouseleave", function() {
								change_svg_color( "' . $reset_color_default . '" );
							});

							jQuery( this ).on( "click", function() {
								$( this ).closest( ".forminator-field" ).removeClass( "forminator-is_filled" );
							});
						});
					}

					/**
					 * When form is material design, we need to make label float.
					 */
					floating_signature = function() {

						var canvas     = jQuery( "#' . $id . '" ),
							form        = canvas.closest( ".forminator-ui" ),
							field       = canvas.closest( ".forminator-field" ),
							label       = field.find( ".forminator-label" ),
							placeholder = jQuery( "#' . $id . '_placeholder" )
							;

						var isMaterial = ( "material" === form.attr( "data-design" ) ) || ( form.hasClass( "forminator-design--material" ) ),
							hasLabel   = label.length
							;

						function material_label() {

							var labelHeight  = label.height(),
								labelPadding = 10,
								labelMath    = labelHeight + labelPadding
								;

							if ( ! field.hasClass( "forminator-is_hover" ) || ! field.hasClass( "forminator-is_filled" ) ) {
								label.css( "top", labelMath + "px" );
								placeholder.css( "top", ( labelMath - 1 ) + "px" );
							}
						}

						function init() {

							if ( isMaterial && hasLabel ) {
								material_label();
							}
						}

						init();

						return this;
					}

					reset_color();

					jQuery( "#' . $id . '_Container" ).on( "click", debounce( function() {
						jQuery( this ).closest( ".forminator-custom-form" ).trigger( "forminator.validate.signature" );
					}, 500 ));

					jQuery( "#' . $id . '_Container" ).on( "click", function() {
						jQuery( this ).closest( ".forminator-field-signature" ).trigger( "change" );
					});

					// Check if signature field finished loading.
					jQuery( "#' . $id . '_toolbar" ).on( "load", function() {
						floating_signature();
					});

					// show/hide placeholder.
					jQuery( "#' . $id . '_Container" ).on( "mouseenter", function() {
						jQuery( "#' . $id . '_placeholder" ).css( "visibility", "hidden" );
						jQuery( "#' . $id . '_Container canvas" ).focus();
					});

					jQuery( "#' . $id . '_Container" ).on( "mouseleave", function() {
						if ( "" === $( "#' . $id . '_data" ).val() ) {
							jQuery( "#' . $id . '_placeholder" ).css( "visibility", "visible" );
						}
					});

					jQuery( ".forminator-signature" ).on( "click", "#' . $id . '_resetbutton", function() {
						jQuery( "#' . $id . '_placeholder" ).css( "visibility", "visible" );
						jQuery( this ).closest( ".forminator-field-signature" ).trigger( "change" );
					});
				}
			}
		 ';

		 add_filter(
 			'forminator_enqueue_form_script',
 			function( $script, $is_preview, $is_ajax_load ) use ( $signature_script ) {

 				if ( $is_ajax_load ) {
 					// Load form using AJAX.
 					$signature_library = '';

 					if ( ! self::$is_library_loaded ) {
 						self::$is_library_loaded = true;
 						ob_start();
 						require dirname( dirname( __FILE__ ) ) . '/lib/ss.js';
						require dirname( dirname( __FILE__ ) ) . '/js/scripts.js';
 						$signature_library = ob_get_clean();
 					}

 					$script .= "<script type=\"text/javascript\" id=\"forminator-field-signature-scripts\">$signature_library$signature_script</script>";

 				} else {
 					$src = forminator_plugin_url() . 'addons/pro/e-signature/lib/ss.js';
 					wp_enqueue_script( 'forminator-field-signature', $src, [ 'jquery' ], FORMINATOR_VERSION, true );

					$src_scripts = forminator_plugin_url() . 'addons/pro/e-signature/js/scripts.js';
 					wp_enqueue_script( 'forminator-field-signature-scripts', $src_scripts, [ 'jquery' ], FORMINATOR_VERSION, true );

 					wp_add_inline_script( 'forminator-field-signature', $signature_script );
 				}

 				return $script;
 			},
 			10,
 			3
 		);

		$html = '<div class="forminator-field forminator-field-signature">';

			$required_icon = $required ? ' ' . forminator_get_required_icon() : '';

			$html .= sprintf(
				'<label class="forminator-label">%s%s</label>',
				$label,
				$required_icon
			);

			$html .= '<div class="forminator-signature" data-elementheight="' . $height . '">';

				$html .= '<span id="' . $id . '_placeholder" class="forminator-signature--placeholder" aria-hidden="true">' . $placeholder . '</span>';

				$html .= '<div id="' . $id . '_Container" class="forminator-signature--container">';

					$html .= '<canvas id="' . $id . '" class="forminator-signature-canvas" height="' . $height . '" tabindex="-1">';
						$html .= '<p>' . esc_html__( 'Your browser does not support e-Signature field.', 'forminator' ) . '</p>';
					$html .= '</canvas>';

				$html .= '</div>';

			$html .= '</div>';

			$html .= '<input type="hidden" name="field-' . $field_name . '" value="' . $this->uniq . '" class="signature-prefix">';

			$html .= self::get_description( $description, 'forminator-field-' . $id );

		$html .= '</div>';

		return apply_filters( 'forminator_field_signature_markup', $html, $field, $this );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.13
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;
		$id = $this->get_id( $field );
		$is_required = $this->is_required( $field );
		$rules = '';

		if ( $is_required ) {
			$rules = '"' . self::get_signature_id( $id ) . '_data": {';
			$rules .= '"required": true,';
			$rules .= '},';
		}

		return apply_filters( 'forminator_field_file_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.13
	 * @return string
	 */
	public function get_validation_messages() {
		$field = $this->field;
		$id = self::get_signature_id( $this->get_id( $field ) );
		$is_required = $this->is_required( $field );
		$messages = '"' . self::get_signature_id( $this->get_id( $field ) ) . '_data": {' . "\n";

		if ( $is_required ) {
			$settings_required_message = self::get_property( 'required_message', $field, '' );
			$required_message = apply_filters(
				'forminator_signature_field_required_validation_message',
				( ! empty( $settings_required_message ) ? $settings_required_message : __( 'This field is required. Please sign.', 'forminator' ) ),
				$id,
				$field
			);

			$messages = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}
		$messages .= '},' . "\n";

		return $messages;
	}


	/**
	 * Field back-end validation
	 *
	 * @since 1.13
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id = self::get_signature_id( $this->get_id( $field ) );
			$required_message = self::get_property( 'required_message', $field, '' );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_signature_field_required_validation_message',
					( ! empty( $required_message ) ? $required_message : __( 'This field is required. Please sign.', 'forminator' ) ),
					$id,
					$field
				);
			} elseif ( isset( $data['success'] ) && false === $data['success'] ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_signature_field_upload_failed_message',
					__( 'Error saving signature. Upload error.', 'forminator' ),
					$id,
					$field
				);
			}
		}
	}

	/**
	 * Handle sign upload
	 *
	 * @since 1.13
	 *
	 * @param array field settings
	 *
	 * @return bool|array
	 */
	public function handle_sign_upload( $field ) {
		$this->field = $field;
		$id          = $this->get_id( $field );
		$sign_field  = Forminator_Core::sanitize_text_field( 'field-' . $id );
		$name        = self::get_signature_id( $id, $sign_field );

		// the data that comes from client side.
		$sign_data = Forminator_Core::sanitize_text_field( $name . '_data' );
		// the smooth data that comes from client side.
		$sign_data_smooth = Forminator_Core::sanitize_text_field( $name . '_data_canvas' );

		if ( ! empty( $sign_data ) || ! empty( $sign_data_smooth ) ) {
			$form_id = filter_input( INPUT_POST, 'form_id' );
			$filetype = self::get_property( 'filetype', $field, 'png' );

			$file_name = md5( $form_id . time() ) . ( 'jpg' === $filetype ? '.jpg' : '.png' );

			require_once forminator_plugin_dir() . 'addons/pro/e-signature/lib/license.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( strlen( $sign_data_smooth ) > 0 ) {
				$im = GetSignatureImageSmooth( $sign_data_smooth );
			} else if ( strlen( $sign_data ) > 0 ) {
				$im = GetSignatureImage( $sign_data );
			}
			if ( empty( $im ) ) {
				return false;
			}

			WP_Filesystem();
			/** @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			$upload_dir = wp_upload_dir(); // Set upload folder.
			$sign_dir = $upload_dir['basedir'] . '/forminator/signatures';
			$sign_url = $upload_dir['baseurl'] . '/forminator/signatures';
			$unique_file_name = wp_unique_filename( $sign_dir, $file_name );
			$exploded_name = explode( '/', $unique_file_name );
			$filename = end( $exploded_name ); // Create base file name.

			if ( ! $wp_filesystem->is_dir( $sign_dir ) ) {
				wp_mkdir_p( $sign_dir );
			}

			if ( $wp_filesystem->is_writable( $sign_dir ) ) {
				$file_path = $sign_dir . '/' . $filename;
				$file_url = $sign_url . '/' . $filename;
			} else {
				$file_path = $upload_dir['basedir'] . '/' . $filename;
				$file_url = $upload_dir['baseurl'] . '/' . $filename;
			}

			$create_jpg = 'jpg' === $filetype;
			$transparent_image = self::setTransparency( $im, $create_jpg );
			if ( $create_jpg ) {
				$result = imagejpeg( $transparent_image, $file_path, 100 );
			} else {
				$result = imagepng( $transparent_image, $file_path, 0, null );
			}

			ImageDestroy( $im );
			ImageDestroy( $transparent_image );

			if ( false !== $result ) {
				return array(
					'success' => true,
					'file_url' => $file_url,
					'file_path' => $file_path,
				);
			} else {
				return array(
					'success' => false,
					'message' => __( 'Error saving signature. Upload error. ', 'forminator' ),
				);
			}
		}

		return false;
	}

	private static function setTransparency( $picture, $set_white ) {
		$img_w = imagesx( $picture );
		$img_h = imagesy( $picture );

		$new_picture = imagecreatetruecolor( $img_w, $img_h );
		imagesavealpha( $new_picture, true );
		$rgb = imagecolorallocatealpha( $new_picture, 0, 0, 0, 127 );
		imagefill( $new_picture, 0, 0, $rgb );

		$color = imagecolorat( $picture, $img_w - 1, 1 );
		$white = imagecolorallocate( $new_picture, 255, 255, 255 );

		for ( $x = 0; $x < $img_w; $x++ ) {
			for ( $y = 0; $y < $img_h; $y++ ) {
				$c = imagecolorat( $picture, $x, $y );
				if ( $color !== $c ) {
					imagesetpixel( $new_picture, $x, $y, $c );
				} else if ( $set_white ) {
					imagesetpixel( $new_picture, $x, $y, $white );
				}
			}
		}

		return $new_picture;
	}


}