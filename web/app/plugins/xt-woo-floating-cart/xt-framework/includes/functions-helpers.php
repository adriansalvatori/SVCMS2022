<?php

/**
 * Define a constant if it is not already defined.
 *
 * @since  1.6.5
 * @param  string $name
 * @param  string $value
 */
function xtfw_maybe_define_constant( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

/**
 * Return the html selected attribute if stringified $value is found in array of stringified $options
 * or if stringified $value is the same as scalar stringified $options.
 *
 * @param string|int       $value   Value to find within options.
 * @param string|int|array $options Options to go through when looking for value.
 * @return string
 */
function xtfw_selected( $value, $options ) {
	if ( is_array( $options ) ) {
		$options = array_map( 'strval', $options );
		return selected( in_array( (string) $value, $options, true ), true, false );
	}

	return selected( $value, $options, false );
}

/**
 * Display a help tip.
 *
 * @since  1.0.0
 *
 * @param  string $tip        Help tip text.
 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
 * @return string
 */
function xtfw_help_tip( $tip, $allow_html = false ) {

    if(empty($tip)) {
        return '';
    }

	if ( $allow_html ) {

        $allowed_html = array(
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'small' => array(),
            'span' => array(),
            'ul' => array(),
            'li' => array(),
            'ol' => array(),
            'p' => array(),
        );
        ?>

		<span class="xtfw-help-tip" data-tip="<?php echo wp_kses($tip, $allowed_html); ?>"></span>

        <?php

    } else {
        ?>

        <span class="xtfw-help-tip" data-tip="<?php echo esc_attr( $tip );?>"></span>

        <?php
	}
}

/**
 * Display a changelog from a readme file
 *
 * @since  1.3.7
 *
 * @param  string $readme_file  readme file path
 * @return string changelog html
 */
function xtfw_changelog_html($readme_file) {

	require_once XTFW_DIR_INCLUDES . '/class-parsedown.php';

	$parsedown = new XT_Framework_Parsedown();

	$changelog = '';

	$data = file_get_contents( $readme_file );

	if ( ! empty( $data ) ) {
		$data = explode( '== Changelog ==', $data );
		if ( ! empty( $data[1] ) ) {

			$changelog = $data[1];
			$changelog = preg_replace(
				array(
					'/\[\/\/\]\: \# fs_.+?_only_begin/',
					'/\[\/\/\]\: \# fs_.+?_only_end/',
				),
				'',
				$changelog
			);

			$changelog = $parsedown->text( $changelog );

			$changelog = preg_replace(
				array(
					'/\<strong\>(.+?)\<\/strong>\:(.+?)\n/i',
					'/\<p\>/',
					'/\<\/p\>/'
				),
				array(
					'<span class="update-type $1">$1</span><span class="update-txt">$2</span>',
					'',
					''
				),
				$changelog
			);

		}
	}

	return '<div class="xtfw-changelog">' . wp_kses( $changelog, wp_kses_allowed_html( 'post' ) ) . '</div>';
}

/**
 * Check if doing ajax
 *
 * @return    bool
 * @since     1.0.0
 */
function xtfw_doing_ajax() {

    return ((defined('DOING_AJAX') && DOING_AJAX)) || ((defined('WC_DOING_AJAX') && WC_DOING_AJAX)) || ((defined('XTFW_DOING_AJAX') && XTFW_DOING_AJAX));
}

/**
 * Check if is REST request
 *
 * @return    bool
 * @since     1.0.0
 */
function xtfw_is_rest_request() {

    if(defined('REST_REQUEST') && REST_REQUEST) {
        return true;
    }

    if ( empty( $_SERVER['REQUEST_URI'] ) ) {
        // Probably a CLI request
        return false;
    }

    $rest_prefix         = trailingslashit( rest_get_url_prefix() );
    $is_rest_request = strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) !== false;

    return apply_filters( 'xtfw_is_rest_request', $is_rest_request );
}

/**
 * Check if in debug mode
 *
 * @return    bool
 * @since     1.0.0
 */
function xtfw_debug_mode() {

    return (defined('WP_DEBUG') && WP_DEBUG);
}

/**
 * Call a function and get it's output result
 *
 * @return    mixed
 * @since     1.7.0
 */
function xtfw_ob_get_clean(callable $function) {

    ob_start();
    call_user_func($function);
    return ob_get_clean();
}

/**
 * Same as array_merge_recursive, however, without duplicate keys
 *
 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('new value'));
 *
 * @param array $array1
 * @param array $array2
 * @return array
 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
 */
function xtfw_array_merge_recursive_distinct(array $array1, array $array2)
{
    $merged = $array1;
    foreach ($array2 as $key => $value)
    {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
        {
            $merged[$key] = xtfw_array_merge_recursive_distinct($merged[$key], $value);
        }
        else
        {
            $merged[$key] = $value;
        }
    }
    return $merged;
}

/**
 * Applies the callback to the elements of the given arrays, then merge all results to one array
 *
 * @param callable $callback Callback function to run for each element in each array.
 * @param array $array,... Variable list of array arguments to run through the callback function.
 * @param array ...$arrays
 * @return array Returns an array containing all the elements of array1 after
 * applying the callback function to each one, casting them to arrays and merging together.
 */
function xtfw_array_flatmap(callable $callback, array ...$arrays) {
    $args = func_get_args();
    $mapped = array_map(function ($a) {
        return (array)$a;
    }, call_user_func_array('array_map', $args));

    return count($mapped) === 0 ? array() : call_user_func_array('array_merge', $mapped);
}

/**
 * Extract array variables
 *
 * Usage example:
 * ```
 * list ( $type, $class, $value ) = xtfw_extract( $field, 'type', 'class', 'value' );
 * ```
 *
 * @param array  $array   The array.
 * @param string ...$keys The keys.
 *
 * @return array
 * @since 1.9.4
 */
function xtfw_extract( $array, ...$keys ) {
	return array_map(
		function ( $key ) use ( $array ) {
			return isset( $array[ $key ] ) ? $array[ $key ] : null;
		},
		$keys
	);
}

/**
 * Sort Pro files, load them after free files, so they can extend them.
 *
 * @param $a
 *
 * @return int
 */
function xtfw_sort_pro_files($file) {

    $hasPro = (strpos($file, '-pro') !== false);

    if ($hasPro) {
        return 2;
    }else{
        return 1;
    }
}