<?php
/**
 * Conditional logic of the condition type User role.
 *
 * @package Stackable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'stackable_conditional_display/role', function( $condition_is_met, $condition, $block_content, $block ) {
    $options = isset( $condition['options'] ) ? $condition['options'] : null;

    if ( $options ) {
        $selected_values = $options['selectedValues'];

        if ( $selected_values ) {
            if ( is_user_logged_in() ) {
                // Get the roles of the current user
                $current_user = wp_get_current_user();
                $user_roles = $current_user->roles;

                // Convert selectedValues to lowercase to compare with the user role
                $values = array_map( 'strtolower', $selected_values );
                $matched_roles = array_intersect( $user_roles, $values );

                $condition_is_met = ! empty( $matched_roles );
            } else {
                // For non logged in users
				return false;
            }
        }
    }

	return $condition_is_met;
}, 10, 5 );
