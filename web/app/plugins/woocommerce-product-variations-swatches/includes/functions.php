<?php
/**
 * Function include all files in folder
 *
 * @param $path   Directory address
 * @param $ext    array file extension what will include
 * @param $prefix string Class prefix
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'villatheme_include_folder' ) ) {
	function villatheme_include_folder( $path, $prefix = '', $ext = array( 'php' ) ) {

		/*Include all files in payment folder*/
		if ( ! is_array( $ext ) ) {
			$ext = explode( ',', $ext );
			$ext = array_map( 'trim', $ext );
		}
		$sfiles = scandir( $path );
		foreach ( $sfiles as $sfile ) {
			if ( $sfile != '.' && $sfile != '..' ) {
				if ( is_file( $path . "/" . $sfile ) ) {
					$ext_file  = pathinfo( $path . "/" . $sfile );
					$file_name = $ext_file['filename'];
					if ( $ext_file['extension'] ) {
						if ( in_array( $ext_file['extension'], $ext ) ) {
							$class = preg_replace( '/\W/i', '_', $prefix . ucfirst( $file_name ) );

							if ( ! class_exists( $class ) ) {
								require_once $path . $sfile;
								if ( class_exists( $class ) ) {
									new $class;
								}
							}
						}
					}
				}
			}
		}
	}
}
if ( ! function_exists( 'viwpvs_sanitize_fields' ) ) {
	function viwpvs_sanitize_fields( $data ) {
		if ( ! $data ) {
			return $data;
		} elseif ( is_array( $data ) ) {
			return array_map( 'viwpvs_sanitize_fields', $data );
		} else {
			return sanitize_text_field( wp_unslash( $data ) );
		}
	}
}

if ( ! function_exists( 'villatheme_ctr_time' ) ) {
	function villatheme_ctr_time( $time ) {
		if ( ! $time ) {
			return 0;
		}
		$temp = explode( ":", $time );
		if ( count( $temp ) == 2 ) {
			return ( absint( $temp[0] ) * 3600 + absint( $temp[1] ) * 60 );
		} else {
			return 0;
		}
	}
}
if ( ! function_exists( 'villatheme_ctr_time_revert' ) ) {
	function villatheme_ctr_time_revert( $time ) {
		$hour = floor( $time / 3600 );
		$min  = floor( ( $time - 3600 * $hour ) / 60 );

		return implode( ':', array( zeroise( $hour, 2 ), zeroise( $min, 2 ) ) );
	}
}

if ( ! function_exists( 'viwpvs_rgba2hex' ) ) {
	function viwpvs_rgba2hex( $color ) {
		preg_match( '/^rgba\((.*)\)/im', $color, $matches );
		if ( ! $matches ) {
			preg_match( '/^rgb\((.*)\)/im', $color, $matches );
		}
		if ( count( $matches ) === 2 ) {
			$color_patterns = explode( ',', $matches[1] );
			if ( count( $color_patterns ) === 3 ) {
				$hex = array_map( 'dechex', $color_patterns );

				$color = '#' . implode( '', $hex );
			} elseif ( count( $color_patterns ) === 4 ) {
				$color_patterns[3] = intval( $color_patterns[3] * 255 );
				$hex               = array_map( 'dechex', $color_patterns );
				if ( strlen( $hex[3] ) === 1 ) {
					$hex[3] = "0{$hex[3]}";
				} elseif ( $hex[3] === 'ff' ) {
					$hex[3] = '';
				}

				$color = '#' . implode( '', $hex );
			}
		}

		return $color;
	}
}