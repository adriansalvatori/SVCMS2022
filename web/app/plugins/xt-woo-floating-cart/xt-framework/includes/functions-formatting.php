<?php

if ( ! function_exists( 'xtfw_parse_relative_date_option' ) ) {

    /**
     * Parse a relative date option from the settings API into a standard format.
     *
     * @param mixed $raw_value Value stored in DB.
     * @return array Nicely formatted array with number and unit values.
     * @since 1.0.0
     */
    function xtfw_parse_relative_date_option($raw_value)
    {
        $periods = array(
            'days' => esc_html__('Day(s)', 'xt-framework'),
            'weeks' => esc_html__('Week(s)', 'xt-framework'),
            'months' => esc_html__('Month(s)', 'xt-framework'),
            'years' => esc_html__('Year(s)', 'xt-framework'),
        );

        $value = wp_parse_args(
            (array)$raw_value,
            array(
                'number' => '',
                'unit' => 'days',
            )
        );

        $value['number'] = !empty($value['number']) ? absint($value['number']) : '';

        if (!in_array($value['unit'], array_keys($periods), true)) {
            $value['unit'] = 'days';
        }

        return $value;
    }
}

if ( ! function_exists( 'xtfw_float_to_string' ) ) {

    /**
     * Convert a float to a string without locale formatting which PHP adds when changing floats to strings.
     *
     * @param float $float Float value to format.
     * @return string
     * @since 1.0.0
     */
    function xtfw_float_to_string($float)
    {

        if (function_exists('xtfw_float_to_string')) {
            return xtfw_float_to_string($float);
        }

        if (!is_float($float)) {
            return $float;
        }

        $locale = localeconv();
        $string = strval($float);
        $string = str_replace($locale['decimal_point'], '.', $string);

        return $string;
    }
}

if ( ! function_exists( 'xtfw_string_to_bool' ) ) {

    /**
     * Convert boolean string to boolean value
     *
     * @param string $string
     * @return bool
     * @since 1.0.0
     */
    function xtfw_string_to_bool($string)
    {
        return is_bool($string) ? $string : ('yes' === $string || 1 === $string || 'true' === $string || '1' === $string);
    }
}

if ( ! function_exists( 'xtfw_implode_html_attributes' ) ) {

	/**
	 * Transform attributes array to HTML attributes string.
	 * If using a string, the attributes will be escaped.
	 * Prefer using arrays.
	 *
	 * @param array|string $attributes The attributes.
	 * @param bool         $echo       Set to true to print it directly; false otherwise.
	 *
	 * @return string
	 * @since 3.7.0
	 * @since 3.8.0 Escaping attributes when using strings; allow value-less attributes by setting value to null.
	 */
    function xtfw_implode_html_attributes( $attributes = array(), $echo = false )
    {
	    $html_attributes = '';

	    if ( ! ! $attributes ) {
		    if ( is_string( $attributes ) ) {
			    $parsed_attrs = wp_kses_hair( $attributes, wp_allowed_protocols() );
			    $attributes   = array();
			    foreach ( $parsed_attrs as $attr ) {
				    $attributes[ $attr['name'] ] = 'n' === $attr['vless'] ? $attr['value'] : null;
			    }
		    }

		    if ( is_array( $attributes ) ) {
			    $html_attributes = array();
			    foreach ( $attributes as $key => $value ) {
				    if ( ! is_null( $value ) ) {
					    $html_attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
				    } else {
					    $html_attributes[] = esc_attr( $key );
				    }
			    }
			    $html_attributes = implode( ' ', $html_attributes );
		    }
	    }

	    if ( $echo ) {
		    // Already escaped above.
		    echo $html_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	    }

	    return $html_attributes;
    }
}

if ( ! function_exists( 'xtfw_html_data_to_string' ) ) {
	/**
	 * Transform data array to HTML data.
	 *
	 * @param array $data The array of data.
	 * @param false $echo Set to true to print it directly; false otherwise.
	 *
	 * @return string
	 */
	function xtfw_html_data_to_string( $data = array(), $echo = false ) {

		$html_data = '';

		if ( ! ! $data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data_attribute = "data-{$key}";
					$data_value     = ! is_array( $value ) ? $value : implode( ',', $value );

					$html_data .= ' ' . esc_attr( $data_attribute ) . '="' . esc_attr( $data_value ) . '"';
				}
				$html_data .= ' ';
			}
		}

		if ( $echo ) {
			echo $html_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html_data;
	}
}

if ( ! function_exists( 'xtfw_format_localized_decimal' ) ) {
    /**
     * Format a decimal with PHP Locale settings.
     *
     * @param string $value Decimal to localize.
     * @return string
     * @since 1.0.0
     */
    function xtfw_format_localized_decimal($value)
    {

        if (function_exists('woocommerce_format_localized_decimal')) {
            return xtfw_format_localized_decimal($value);
        }

        $locale = localeconv();
        return apply_filters('xtfw_format_localized_decimal', str_replace('.', $locale['decimal_point'], strval($value)), $value);
    }
}

if ( ! function_exists( 'xtfw_clean' ) ) {

    /**
     * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
     * Non-scalar values are ignored.
     *
     * @param string|array $var Data to sanitize.
     * @return string|array
     * @since 1.0.0
     */
    function xtfw_clean($var)
    {
        if (is_array($var)) {
            return array_map('xtfw_clean', $var);
        } else {
            return is_scalar($var) ? sanitize_text_field($var) : $var;
        }
    }
}

if ( ! function_exists( 'xtfw_rgb_from_hex' ) ) {

    /**
     * Convert RGB to HEX.
     *
     * @param string $hex Color.
     *
     * @return array
     */
    function xtfw_rgb_from_hex( $hex ) {

        $hex = xtfw_format_hex( $hex );

        $rgb      = array();
	    $rgb['R'] = hexdec( $hex[1] . $hex[2] );
	    $rgb['G'] = hexdec( $hex[3] . $hex[4] );
	    $rgb['B'] = hexdec( $hex[5] . $hex[6] );

        return $rgb;
    }
}

if ( ! function_exists( 'xtfw_hex_from_rgb' ) ) {

    /**
     * Convert RGB to HEX.
     *
     * @param array $rgb Color.
     *
     * @return string
     */
    function xtfw_hex_from_rgb( $rgb ) {

	    $rgb = xtfw_rgb_from_string_rgb($rgb);

        $hex = "#";
        $hex .= str_pad(dechex($rgb['R']), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb['G']), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb['B']), 2, "0", STR_PAD_LEFT);

        return $hex; // returns the hex value including the number sign (#)
    }
}

if ( ! function_exists( 'xtfw_rgb_from_string_rgb' ) ) {

	/**
	 * Convert RGB string to RGB Array.
	 *
	 * @param string $rgb Color.
	 *
	 * @return array
	 */
	function xtfw_rgb_from_string_rgb( $rgb_string ) {

		$rgb = array(
			'R' => 0,
			'G' => 0,
			'B' => 0
		);

		if ( is_string( $rgb_string ) ) {

			if ( strpos( $rgb_string, '#' ) === 0 ) {
				return xtfw_rgb_from_hex($rgb_string);
			}

			preg_match( '/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i', $rgb_string, $match );

            if(!empty($match)) {
                $rgb = array(
                    'R' => $match[1],
                    'G' => $match[2],
                    'B' => $match[3]
                );
            }

        }

		return $rgb;
	}
}

if ( ! function_exists( 'xtfw_is_hex_color' ) ) {

    /**
     * Check if Is Hex Color
     *
     * @param string $hex Color.
     *
     * @return bool
     */
    function xtfw_is_hex_color( $hex ) {

        return is_string($hex) && (
            (strpos($hex, "#") !== false) && (strlen($hex) === 4 || strlen($hex) === 7) ||
            (strpos($hex, "#") === false) && (strlen($hex) === 3 || strlen($hex) === 6)
        );
    }
}

if ( ! function_exists( 'xtfw_hex_darker' ) ) {

    /**
     * Make HEX color darker.
     *
     * @param mixed $hex  Color.
     * @param int   $factor Darker factor.
     *                      Defaults to 30.
     * @return string
     */
    function xtfw_hex_darker( $hex, $factor = 30 ) {
        $base  = xtfw_rgb_from_hex( $hex );
        $color = '#';

        foreach ( $base as $k => $v ) {
            $amount      = $v / 100;
            $amount      = round( $amount * $factor );
            $new_decimal = $v - $amount;

            $new_hex_component = dechex( $new_decimal );
            if ( strlen( $new_hex_component ) < 2 ) {
                $new_hex_component = '0' . $new_hex_component;
            }
            $color .= $new_hex_component;
        }

        return $color;
    }
}

if ( ! function_exists( 'xtfw_hex_lighter' ) ) {

    /**
     * Make HEX color lighter.
     *
     * @param mixed $hex  Color.
     * @param int   $factor Lighter factor.
     *                      Defaults to 30.
     * @return string
     */
    function xtfw_hex_lighter( $hex, $factor = 30 ) {
        $base  = xtfw_rgb_from_hex( $hex );
        $color = '#';

        foreach ( $base as $k => $v ) {
            $amount      = 255 - $v;
            $amount      = $amount / 100;
            $amount      = round( $amount * $factor );
            $new_decimal = $v + $amount;

            $new_hex_component = dechex( $new_decimal );
            if ( strlen( $new_hex_component ) < 2 ) {
                $new_hex_component = '0' . $new_hex_component;
            }
            $color .= $new_hex_component;
        }

        return $color;
    }
}

if ( ! function_exists( 'xtfw_hex_is_light' ) ) {

    /**
     * Determine whether a hex color is light.
     *
     * @param mixed $hex Color.
     * @return bool  True if a light color.
     */
    function xtfw_hex_is_light( $hex ) {

        $hex = xtfw_format_hex( $hex );
        $rgb = xtfw_rgb_from_hex( $hex );

	    $hsp = sqrt(
		    0.299 * ($rgb['R'] * $rgb['R']) +
		    0.587 * ($rgb['G'] * $rgb['G']) +
		    0.114 * ($rgb['B'] * $rgb['B'])
	    );

	    return ($hsp > 127.5);
    }
}

if ( ! function_exists( 'xtfw_light_or_dark' ) ) {

    /**
     * Detect if we should use a light or dark color on a background color.
     *
     * @param mixed  $hex Color.
     * @param string $dark  Darkest reference.
     *                      Defaults to '#000000'.
     * @param string $light Lightest reference.
     *                      Defaults to '#FFFFFF'.
     * @return string
     */
    function xtfw_light_or_dark( $hex, $dark = '#000000', $light = '#FFFFFF' ) {
        return xtfw_hex_is_light( $hex ) ? $dark : $light;
    }
}

if ( ! function_exists( 'xtfw_format_hex' ) ) {

    /**
     * Format string as hex.
     *
     * @param string $hex HEX color.
     * @return string|null
     */
    function xtfw_format_hex( $hex ) {

    	// If not hexm maybe RGB ?
    	if(!xtfw_is_hex_color($hex)) {
    		$hex = xtfw_hex_from_rgb($hex);
	    }

        if(!empty($hex)) {
            $hex = trim(str_replace('#', '', $hex));
            $hex = preg_replace('~^(.)(.)(.)$~', '$1$1$2$2$3$3', $hex);
        }

        return $hex ? '#' . $hex : null;
    }
}

if ( ! function_exists( 'xtfw_heading' ) ) {
	/**
	 * Heading title .
	 *
	 * @param string $title
	 * @param array $classes
	 *
	 * @return string
	 */
	function xtfw_heading( $title, $tag = 'h1', $classes = array() ) {

		$classes = array_merge(array('xtfw-heading'), $classes);
		$classes = implode(" ", $classes);

		return '<'.esc_attr($tag).' class="'.esc_attr($classes).'">'.esc_html($title).'</'.esc_attr($tag).'>';
	}
}



if ( ! function_exists( 'xtfw_dash_to_camel_case' ) ) {

    /**
     * convert dashed string to CamelCase .
     *
     * @param string $title
     * @param bool $capitalizeFirstCharacter
     *
     * @return string
     */
    function xtfw_dash_to_camel_case($string, $capitalizeFirstCharacter = false, $dashToUnderscore = false)
    {
        $replacement = $dashToUnderscore ? '_' : '';

        $str = str_replace('-', $replacement, ucwords($string, '-'));
        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }
        return $str;
    }
}