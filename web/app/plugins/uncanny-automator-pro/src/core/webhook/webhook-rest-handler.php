<?php

namespace Uncanny_Automator_Pro;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Webhook_Rest_Handler class. One class to catch all webhook triggers (Automator, Zapier and Integromat)
 */
class Webhook_Rest_Handler {
	/**
	 * Store all webhook trigger codes
	 *
	 * @var array
	 */
	private static $trigger_codes = array();

	/**
	 * Array key joiner in token "KEY"
	 *
	 * @var mixed|void
	 */
	private static $array_key_separator;

	/**
	 * Webhook_Rest_Handler construct
	 */
	public function __construct() {
		self::$array_key_separator = apply_filters( 'automator_pro_webhook_array_key_in_token_separator', '/' );
		add_action( 'rest_api_init', array( __CLASS__, 'automator_webhook_init_rest_api' ) );
	}

	/**
	 * Return $trigger_codes
	 *
	 * @return array
	 */
	public static function get_trigger_codes() {
		return self::$trigger_codes;
	}

	/**
	 * Store $trigger_codes
	 *
	 * @param string $trigger_codes
	 */
	public static function set_trigger_codes( $trigger_codes ) {
		if ( ! in_array( $trigger_codes, self::$trigger_codes, true ) ) {
			self::$trigger_codes[] = $trigger_codes;
		}
	}

	/**
	 * Get recipes matching webhook trigger codes
	 *
	 * @return array
	 */
	public static function get_recipes() {
		$recipes = array();
		foreach ( self::get_trigger_codes() as $trigger_code ) {
			$recipes = $recipes + Automator()->get->recipes_from_trigger_code( $trigger_code );
		}

		return $recipes;
	}

	/**
	 * Webhooks Rest API inits
	 *
	 * @return void
	 */
	public static function automator_webhook_init_rest_api() {
		$available_hooks = self::get_available_hooks();
		if ( empty( $available_hooks ) ) {
			return;
		}

		foreach ( $available_hooks as $hook ) {
			$args = array();
			if ( ! empty( $hook['params'] ) ) {
				foreach ( $hook['params'] as $param ) {
					$args[ $param['key'] ] = array(
						'type'     => $param['type'],
						'format'   => $param['format'],
						'required' => true,
						'items'    => array(
							'type' => 'string',
						),
					);
				}
			}
			$_route = '/' . $hook['WEBHOOKID'];
			self::register_rest_route( $_route, $hook );
		}
	}

	/**
	 * Get all available webhook end-points
	 *
	 * @return array
	 */
	public static function get_available_hooks() {
		$recipes = self::get_recipes();
		if ( empty( $recipes ) ) {
			return array();
		}
		$available_hooks = array();
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {

				$option_name = 'uap-' . $recipe['ID'] . '-' . $trigger['ID'];
				$saved_hook  = get_transient( $option_name );

				if ( ! empty( $saved_hook ) ) {
					continue;
				}

				if ( ! array_key_exists( 'WEBHOOK_URL', $trigger['meta'] ) ) {
					continue;
				}
				if ( empty( $trigger['meta']['WEBHOOK_URL'] ) ) {
					continue;
				}
				$_hooks                   = array();
				$_hooks['WEBHOOKID']      = sprintf( 'uap-%s-%s', $recipe['ID'], $trigger['ID'] );
				$_hooks['params']         = self::setup_hook_params( $trigger );
				$_hooks['custom_headers'] = self::setup_custom_headers( $trigger );
				$available_hooks[]        = $_hooks;
			}
		}

		return $available_hooks;
	}

	/**
	 * Setup params
	 *
	 * @param $trigger
	 *
	 * @return array
	 */
	public static function setup_hook_params( $trigger ) {
		if ( empty( $trigger['meta']['WEBHOOK_FIELDS'] ) ) {
			return array();
		}
		$_fields = ( json_decode( $trigger['meta']['WEBHOOK_FIELDS'] ) );
		if ( empty( $_fields ) ) {
			return array();
		}
		$params = array();
		foreach ( $_fields as $_field ) {
			$params[] = array(
				//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'key'    => $_field->KEY,
				//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'type'   => $_field->VALUE_TYPE,
				'format' => '',
				'items'  => array(
					'type' => 'string',
				),
			);
		}

		return $params;
	}

	/**
	 * Setup custom header
	 *
	 * @param $trigger
	 *
	 * @return array
	 */
	public static function setup_custom_headers( $trigger ) {
		if ( ! isset( $trigger['meta']['WEBHOOK_HEADERS'] ) ) {
			return array();
		}
		$headers     = array();
		$header_meta = json_decode( $trigger['meta']['WEBHOOK_HEADERS'], true );
		if ( ! empty( $header_meta ) ) {
			foreach ( $header_meta as $head ) {
				$key = strtolower( $head['NAME'] );
				// remove colon if user added in NAME
				$key   = str_replace( ':', '', $key );
				$value = $head['VALUE'];
				if ( ! is_null( $value ) ) {
					if ( 'authorization' === $key || 'authentication' === $key ) {
						$headers[ $key ] = self::parse_basic_authentication( $value );
					} else {
						$headers[ $key ] = $value;
					}
				}
			}
		}

		return $headers;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public static function parse_basic_authentication( $value ) {
		$data   = sanitize_text_field( str_ireplace( 'Basic ', '', $value ) );
		$string = base64_encode( $data ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return "Basic $string";
	}

	/**
	 * Registering all routes
	 *
	 * @param $_route
	 * @param array $hook
	 *
	 * @return void
	 */
	public static function register_rest_route( $_route, $hook = array() ) {
		register_rest_route(
			AUTOMATOR_REST_API_END_POINT,
			$_route,
			array(
				'methods'             => apply_filters(
					'automator_pro_webhook_rest_route_methods',
					array(
						'POST',
						'GET',
						'PUT',
					),
					$_route
				),
				'callback'            => array( __CLASS__, 'automator_webhook_rest_api_callback' ),
				'custom_headers'      => isset( $hook['custom_headers'] ) ? $hook['custom_headers'] : null,
				'permission_callback' => function ( $request ) {
					$attributes = $request->get_attributes();
					if ( empty( $attributes ) || ! isset( $attributes['custom_headers'] ) ) {
						return apply_filters( 'automator_pro_webhook_rest_route_permission_callback', true, $attributes, $request );
					}

					$request_headers = $request->get_headers();
					foreach ( $attributes['custom_headers'] as $header_name => $header_value ) {
						if ( ! isset( $request_headers[ $header_name ] ) ) {
							return apply_filters( 'automator_pro_webhook_rest_route_permission_callback', false, $attributes, $request );
						}
						if ( ! array_intersect( array( $header_value ), $request_headers[ $header_name ] ) ) {
							return apply_filters( 'automator_pro_webhook_rest_route_permission_callback', false, $attributes, $request );
						}
					}

					return apply_filters( 'automator_pro_webhook_rest_route_permission_callback', true, $attributes, $request );
				},
			)
		);
	}

	/**
	 * Catch all "Live" Webhook recipe calls
	 *
	 * @param WP_REST_Request $data
	 *
	 * @return WP_REST_Response
	 */
	public static function automator_webhook_rest_api_callback( WP_REST_Request $data ) {
		$route = $data->get_route();
		if ( empty( $route ) ) {
			return new WP_REST_Response(
				array(
					'status' => 'failed',
					'route'  => '(empty)',
				),
				200
			);
		}

		$recipes = self::get_recipes();
		if ( empty( $recipes ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'success',
					'recipes' => '(empty)',
				),
				200
			);
		}
		$params = $data->get_params();
		$body   = $data->get_body();
		if ( empty( $params ) && empty( $body ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'success',
					'recipes' => '(empty)',
				),
				200
			);
		}

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( ! array_key_exists( 'WEBHOOK_URL', $trigger['meta'] ) ) {
					continue;
				}
				if ( false === strpos( $trigger['meta']['WEBHOOK_URL'], $route ) ) {
					continue;
				}
				$_hooks = array(
					'WEBHOOKID'    => $trigger['ID'],
					'WEBHOOK_BODY' => $body,
				);
				if ( empty( $trigger['meta']['WEBHOOK_FIELDS'] ) ) {
					continue;
				}
				$_fields = ( json_decode( $trigger['meta']['WEBHOOK_FIELDS'] ) );
				if ( empty( $_fields ) ) {
					continue;
				}
				$data_type = $trigger['meta']['DATA_FORMAT'];
				if ( ! empty( $body ) && empty( $params ) ) {
					$params = self::handle_non_json_type_format( $body, $data_type );
				}
				$parsed_fields = self::handle_params( $params );
				foreach ( $_fields as $_field ) {
					if ( isset( $parsed_fields[ $_field->KEY ] ) ) {//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$value = $parsed_fields[ $_field->KEY ];//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					} else {
						$value = apply_filters( 'automator_pro_webhook_field_key_not_found', __( 'Key not found in data', 'uncanny-automator-pro' ), $_field, $parsed_fields, $_fields, $data );
					}
					if ( is_array( $value ) ) {
						$value = serialize( $value ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					}
					$_hooks['params'][] = array(
						//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'key'        => $_field->KEY,
						//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'type'       => $_field->VALUE_TYPE,
						'format'     => '',
						//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'meta_key'   => $_field->KEY,
						'meta_value' => $value,
					);
				}
				$_hooks = apply_filters( 'automator_pro_webhook_hooks_data', $_hooks, $recipe, $data );
				do_action_deprecated(
					'uncanny_automator_pro_wp_webhook',
					array(
						$_hooks,
						$recipe,
					),
					'3.6',
					'automator_pro_run_webhook'
				);
				do_action( 'automator_pro_run_webhook', $_hooks, $recipe );
			}
		}

		return new WP_REST_Response(
			array(
				'status'     => 'success',
				'class'      => __CLASS__,
				'route_path' => $route,
				'is_live'    => 'yes',
			),
			200
		);
	}

	/**
	 * Covert param values to KEY => VALUE pair
	 *
	 * @param $params
	 * @param bool $is_sample
	 *
	 * @return array
	 */
	public static function handle_params( $params, $is_sample = false ) {
		$fields = self::get_leafs( $params );
		if ( $is_sample ) {
			return $fields;
		}

		return self::switch_to_key_value_pairs( $fields );
	}

	/**
	 * @param $array
	 *
	 * @return array
	 */
	public static function get_leafs( $array ) {

		$leafs = array();

		if ( ! is_array( $array ) ) {
			return $leafs;
		}

		$array_iterator    = new \RecursiveArrayIterator( $array );
		$iterator_iterator = new \RecursiveIteratorIterator( $array_iterator, \RecursiveIteratorIterator::LEAVES_ONLY );
		foreach ( $iterator_iterator as $key => $value ) {
			$keys = array();
			for ( $i = 0; $i < $iterator_iterator->getDepth(); $i ++ ) { //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed
				$keys[] = $iterator_iterator->getSubIterator( $i )->key();
			}
			$keys[]   = $key;
			$leaf_key = implode( self::$array_key_separator, $keys );

			//$leafs[ $leaf_key ] = $value;
			$leafs[] = array(
				'key'  => $leaf_key,
				'type' => Webhook_Common_Options::value_maybe_of_type( $key, $value ),
				'data' => $value,
			);
		}

		return $leafs;
	}

	/**
	 * @param $fields
	 *
	 * @return array|mixed
	 */
	public static function switch_to_key_value_pairs( $fields ) {
		if ( empty( $fields ) ) {
			return $fields;
		}
		$data = array();
		foreach ( $fields as $field ) {
			$data[ $field['key'] ] = $field['data'];
		}

		return $data;
	}

	/**
	 * Convert non-json formatted data to arrays for parsing
	 *
	 * @param $data
	 * @param $type
	 *
	 * @return array|array[]
	 */
	public static function handle_non_json_type_format( $data, $type ) {
		if ( 'auto' === $type ) {
			if ( ! is_array( $data ) && preg_match( '/\<\?xml/', $data ) ) {
				$type = 'xml';
			}
		}
		switch ( $type ) {
			case 'xml':
				$xml   = simplexml_load_string( $data );
				$array = self::xml_to_array( $xml );
				$value = array( $xml->getName() => $array );
				break;
			case 'csv':
				$value = self::csvstring_to_array( $data );
				break;
			case 'form-data':
				parse_str( $data, $value );
				break;
			default:
				$value = json_decode( $data );
				break;
		}

		return $value;
	}

	/**
	 * Convert nest XML to array
	 *
	 * @param \SimpleXMLElement $parent
	 *
	 * @return array
	 */
	private static function xml_to_array( \SimpleXMLElement $parent ) {
		$array = array();

		foreach ( $parent as $name => $element ) {
			( $node = &$array[ $name ] ) //phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			&& ( 1 === count( $node ) ? $node = array( $node ) : 1 )
			&& $node = &$node[]; //phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

			$node = $element->count() ? self::xml_to_array( $element ) : trim( $element );
		}

		return $array;
	}

	/**
	 * @param $string
	 * @param $separator_char
	 * @param $enclosure_char
	 * @param $newline_char
	 *
	 * @return array
	 */
	private static function csvstring_to_array( $string, $separator_char = ',', $enclosure_char = '"', $newline_char = "\n" ) {
		// @author: Klemen Nagode
		$array         = array();
		$size          = strlen( $string );
		$column_index  = 0;
		$row_index     = 0;
		$field_value   = '';
		$is_enclosured = false;
		for ( $i = 0; $i < $size; $i ++ ) {
			$char     = $string[ $i ];
			$add_char = '';
			if ( $is_enclosured ) {
				if ( $char === $enclosure_char ) {

					if ( $i + 1 < $size && $string[ $i + 1 ] === $enclosure_char ) {
						// escaped char
						$add_char = $char;
						$i ++; // dont check next char
					} else {
						$is_enclosured = false;
					}
				} else {
					$add_char = $char;
				}
			} else {
				if ( $char === $enclosure_char ) {
					$is_enclosured = true;
				} else {

					if ( $char === $separator_char ) {

						$array[ $row_index ][ $column_index ] = $field_value;
						$field_value                          = '';

						$column_index ++;
					} elseif ( $char === $newline_char ) {
						echo $char; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$array[ $row_index ][ $column_index ] = $field_value;
						$field_value                          = '';
						$column_index                         = 0;
						$row_index ++;
					} else {
						$add_char = $char;
					}
				}
			}
			if ( '' !== $add_char ) {
				$field_value .= $add_char;
			}
		}

		if ( $field_value ) { // save last field
			$array[ $row_index ][ $column_index ] = $field_value;
		}

		return $array;
	}
}
