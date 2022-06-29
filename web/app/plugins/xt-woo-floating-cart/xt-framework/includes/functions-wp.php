<?php
/**
* XT Framework WP Override functions or define them if they don't exist is older WP versions
*
* @author      XplodedThemss
* @category    Core
* @package     XT_Framework/Admin/Functions
* @version     1.0.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if is json request
 */
if(!function_exists('wp_is_json_request')) {

	function wp_is_json_request() {

		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT'], 'application/json' ) ) {
			return true;
		}

		if ( isset( $_SERVER['CONTENT_TYPE'] ) && 'application/json' === $_SERVER['CONTENT_TYPE'] ) {
			return true;
		}

		return false;

	}
}

/**
 * Returns a normalized list of all currently registered image sub-sizes.
 *
 * @since 5.3.0
 * @uses wp_get_additional_image_sizes()
 * @uses get_intermediate_image_sizes()
 *
 * @return array Associative array of the registered image sub-sizes.
 */
if(!function_exists('wp_get_registered_image_subsizes')) {

    function wp_get_registered_image_subsizes()
    {
        $additional_sizes = wp_get_additional_image_sizes();
        $all_sizes = array();

        foreach (get_intermediate_image_sizes() as $size_name) {
            $size_data = array(
                'width' => 0,
                'height' => 0,
                'crop' => false,
            );

            if (isset($additional_sizes[$size_name]['width'])) {
                // For sizes added by plugins and themes.
                $size_data['width'] = intval($additional_sizes[$size_name]['width']);
            } else {
                // For default sizes set in options.
                $size_data['width'] = intval(get_option("{$size_name}_size_w"));
            }

            if (isset($additional_sizes[$size_name]['height'])) {
                $size_data['height'] = intval($additional_sizes[$size_name]['height']);
            } else {
                $size_data['height'] = intval(get_option("{$size_name}_size_h"));
            }

            if (empty($size_data['width']) && empty($size_data['height'])) {
                // This size isn't set.
                continue;
            }

            if (isset($additional_sizes[$size_name]['crop'])) {
                $size_data['crop'] = $additional_sizes[$size_name]['crop'];
            } else {
                $size_data['crop'] = get_option("{$size_name}_crop");
            }

            if (!is_array($size_data['crop']) || empty($size_data['crop'])) {
                $size_data['crop'] = (bool)$size_data['crop'];
            }

            $all_sizes[$size_name] = $size_data;
        }

        return $all_sizes;
    }
}


/**
 * Retrieve additional image sizes.
 *
 * @since 4.7.0
 *
 * @global array $_wp_additional_image_sizes
 *
 * @return array Additional images size data.
 */
if(!function_exists('wp_get_additional_image_sizes')) {

    function wp_get_additional_image_sizes()
    {
        global $_wp_additional_image_sizes;

        if (!$_wp_additional_image_sizes) {
            $_wp_additional_image_sizes = array();
        }

        return $_wp_additional_image_sizes;
    }
}