<?php

/**
 * Handle licenses for updates and activations
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'fu_fs' ) ) {
    // Create a helper function for easy SDK access.
    function fu_fs()
    {
        global  $fu_fs ;
        
        if ( !isset( $fu_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_5369_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_5369_MULTISITE', true );
            }
            // Include Freemius SDK.
            
            if ( file_exists( dirname( dirname( dirname( __FILE__ ) ) ) . '/project-huddle/includes/freemius/start.php' ) ) {
                // Try to load SDK from parent plugin folder.
                require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/project-huddle/includes/freemius/start.php';
            } else {
                
                if ( file_exists( dirname( dirname( dirname( __FILE__ ) ) ) . '/project-huddle-premium/includes/freemius/start.php' ) ) {
                    // Try to load SDK from premium parent plugin folder.
                    require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/project-huddle-premium/includes/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
                }
            
            }
            
            $fu_fs = fs_dynamic_init( array(
                'id'               => '5369',
                'slug'             => 'ph-file-uploads',
                'premium-slug'     => 'ph-file-uploads',
                'type'             => 'plugin',
                'public_key'       => 'pk_c4e17e82017b6fe32061d80a88f11',
                'is_premium'       => false,
                'is_premium_only'  => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'parent'           => array(
                'id'         => '5368',
                'slug'       => 'project-huddle',
                'public_key' => 'pk_e696bf90f1c97c0fa370a8e826a67',
                'name'       => 'ProjectHuddle Dashboard Plugin',
            ),
                'menu'             => array(
                'first-path' => 'plugins.php',
                'support'    => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $fu_fs;
    }
    
    function fu_fs_is_parent_active_and_loaded()
    {
        // Check if the parent's init SDK method exists.
        return function_exists( 'ph_licensing' );
    }
    
    function fu_fs_is_parent_active()
    {
        $active_plugins = get_option( 'active_plugins', array() );
        
        if ( is_multisite() ) {
            $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
            $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
        }
        
        foreach ( $active_plugins as $basename ) {
            if ( 0 === strpos( $basename, 'project-huddle/' ) || 0 === strpos( $basename, 'project-huddle-premium/' ) ) {
                return true;
            }
        }
        return false;
    }
    
    function fu_fs_init()
    {
        
        if ( fu_fs_is_parent_active_and_loaded() ) {
            // Init Freemius.
            fu_fs();
            // Signal that the add-on's SDK was initiated.
            do_action( 'fu_fs_loaded' );
            // Parent is active, add your init code here.
        } else {
            // Parent is inactive, add your error handling here.
        }
    
    }
    
    
    if ( fu_fs_is_parent_active_and_loaded() ) {
        // If parent already included, init add-on.
        fu_fs_init();
    } else {
        
        if ( fu_fs_is_parent_active() ) {
            // Init add-on only after the parent is loaded.
            add_action( 'ph_licensing_loaded', 'fu_fs_init' );
        } else {
            // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
            fu_fs_init();
        }
    
    }

}
