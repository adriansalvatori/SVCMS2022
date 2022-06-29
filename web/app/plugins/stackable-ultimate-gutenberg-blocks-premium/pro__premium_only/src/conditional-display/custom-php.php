<?php
/**
 * Conditional logic of the condition type Custom PHP.
 *
 * @package Stackable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'stackable_conditional_display/custom-php', function( $condition_is_met, $condition, $block_content, $block ) {
    $options = isset( $condition['options'] ) ? $condition['options'] : null;

    if ( $options ) {
        $custom_php = $options['customPHP'];
        // Skip checking if custom PHP is empty.
        if ( empty( $custom_php ) ) {
            return true; // Show the block if there is no custom php entered
        }

        if ( stripos( $custom_php, 'return' ) === false ) {
            $custom_php = 'return ' . $custom_php;
        }
        $code = urldecode( $custom_php );

        if ( ! is_admin() ) {
            try {
                ob_start();
                $condition_is_met = eval( $code . ';' );
                ob_end_clean();
            }
            catch ( Error $e ) {
                trigger_error( $e->getMessage(), E_USER_WARNING );
            }
        }
    }

	return $condition_is_met;
}, 10, 5 );
