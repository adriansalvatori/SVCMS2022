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
// Create a helper function for easy SDK access.
function ph_licensing()
{
    global  $ph_licensing ;
    
    if ( !isset( $ph_licensing ) ) {
        // Activate multisite network integration.
        if ( !defined( 'WP_FS__PRODUCT_5368_MULTISITE' ) ) {
            define( 'WP_FS__PRODUCT_5368_MULTISITE', true );
        }
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . '/../includes/freemius/start.php';
        $ph_licensing = fs_dynamic_init( array(
            'id'                             => '5368',
            'slug'                           => 'project-huddle',
            'premium_slug'                   => 'project-huddle',
            'type'                           => 'plugin',
            'public_key'                     => 'pk_e696bf90f1c97c0fa370a8e826a67',
            'is_premium'                     => true,
            'is_premium_only'                => ( defined( 'PH_TEST_INSTALL' ) ? false : true ),
            'has_addons'                     => true,
            'has_paid_plans'                 => true,
            'is_org_compliant'               => false,
            'trial'                          => array(
            'days'               => 7,
            'is_require_payment' => true,
        ),
            'menu'                           => array(
            'slug'       => ( defined( 'PH_TEST_INSTALL' ) ? 'project-huddle-license' : 'project-huddle' ),
            'first-path' => ( !get_option( 'ph_setup_completed', false ) && !is_multisite() ? 'admin.php?page=ph-setup#/welcome' : false ),
            'support'    => false,
            'contact'    => false,
        ),
            'bundle_id'                      => '5371',
            'bundle_public_key'              => 'pk_58797b7d505ca750cf8d9f1e2d8bc',
            'bundle_license_auto_activation' => true,
            'enable_anonymous'               => ( defined( 'PH_TEST_INSTALL' ) ? true : false ),
            'anonymous_mode'                 => ( defined( 'PH_TEST_INSTALL' ) ? true : false ),
            'is_live'                        => true,
        ) );
    }
    
    fs_override_i18n( array(
        'pricing'            => __( "Upgrade", 'project-huddle' ),
        'symbol_arrow-right' => '',
    ), 'project-huddle' );
    return $ph_licensing;
}

// Init Freemius.
ph_licensing();
// Signal that SDK was initiated.
do_action( 'ph_licensing_loaded' );
// disable permission extensions by default
ph_licensing()->add_filter( 'permission_extensions_default', '__return_false' );
// disable extensions tracking for automatically migrated licenses
ph_licensing()->add_filter( 'is_extensions_tracking_allowed', function ( $is_extension_tracking_allowed ) {
    // if tracking is allow and has been registered
    
    if ( $is_extension_tracking_allowed && ph_licensing()->is_registered() ) {
        // get license
        $license = ph_licensing()->_get_license();
        // make sure it's not a freemius license.
        if ( is_object( $license ) && !empty($license->secret_key) && 0 !== strpos( $license->secret_key, 'sk_' ) ) {
            // Migrated license, disable extensions tracking.
            return false;
        }
    }
    
    return $is_extension_tracking_allowed;
} );
ph_licensing()->add_filter( 'freemius_pricing_js_path', function () {
    return PH_PLUGIN_DIR . 'assets/js/includes/pricing/freemius-pricing.js';
} );
// ph_licensing()->add_filter('templates/account.php', 'ph_licensing_remove_account_pricing_links');
function ph_licensing_remove_account_pricing_links( $template )
{
    $template .= '<script type="text/javascript">
            jQuery(function($) {
                $(\'.fs-header-actions a[href*="project-huddle-pricing"], #fs_account_details a[href*="project-huddle-pricing"]\').each(function() {
                    var $this   = $(this),
                        $parent = $this.parent();

                    if ("li" === $parent[0].nodeName.toLowerCase()) {
                        $parent.next().remove();
                        $parent.remove();
                    } else {
                        $this.remove();

                        var $lastButton = $parent.find(".button").last();
                        $lastButton.css("border-top-right-radius", "3px");
                        $lastButton.css("border-bottom-right-radius", "3px");
                    }
                });
            });
        </script>';
    return $template;
}

ph_licensing()->add_action( 'after_uninstall', 'ph_licensing_uninstall_cleanup' );
function ph_licensing_uninstall_cleanup()
{
    // Load project-huddle.php file
    include_once 'project-huddle.php';
    global  $wpdb, $wp_roles ;
    
    if ( get_option( 'ph_uninstall_data_on_delete', false ) ) {
        /** Delete Capabilities */
        PH()->roles->remove_caps();
        /** Delete the Roles */
        $ph_roles = array(
            'project_admin',
            'project_editor',
            'project_collaborator',
            'project_client'
        );
        foreach ( $ph_roles as $role ) {
            remove_role( $role );
        }
        /** Delete Tables */
        global  $wpdb ;
        $tableArray = array( "ph_members", "ph_thread_members" );
        foreach ( $tableArray as $tablename ) {
            $result = $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . $tablename );
            delete_site_option( "{$tablename}_db_version" );
            delete_option( "{$tablename}_db_version" );
        }
        // delete all posts
        $types = ph_get_all_post_types();
        $types[] = 'project-huddle';
        $all_post_ids = get_posts( array(
            'numberposts' => -1,
            'post_type'   => $types,
            'post_status' => 'any',
            'fields'      => 'ids',
        ) );
        // make sure we remove attachments too
        add_action( 'before_delete_post', function ( $post_id ) {
            global  $post_type ;
            if ( !in_array( $post_type, ph_get_all_post_types() ) ) {
                return;
            }
            global  $wpdb ;
            $attachment_ids = get_posts( array(
                'post_type'      => 'attachment',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'post_parent'    => $post_id,
                'fields'         => 'ids',
            ) );
            wp_reset_postdata();
            
            if ( !empty($attachment_ids) ) {
                $delete_attachments_query = $wpdb->prepare( 'DELETE FROM %1$s WHERE %1$s.ID IN (%2$s)', $wpdb->posts, join( ',', $attachment_ids ) );
                $wpdb->query( $delete_attachments_query );
            }
        
        } );
        // delete posts
        if ( !empty($all_post_ids) ) {
            foreach ( $all_post_ids as $id ) {
                wp_delete_post( $id, true );
                // force delete, also deletes comments and meta
            }
        }
        // remove transients
        ph_delete_transients_from_keys( ph_search_database_for_transients_by_prefix( 'ph' ) );
        // notices
        delete_option( 'dismissed-dismissed-ph-updating-305' );
        delete_option( 'dismissed-ismissed-ph-updating-3.0.5' );
        delete_option( 'dismissed-ph_error_reporting' );
        delete_option( 'dismissed-ph-caching-plugins-detection' );
        delete_option( 'dismissed-ph-error-reporting' );
        delete_option( 'dismissed-ph-php-version' );
        delete_option( 'dismissed-ph-updating-305' );
        // options
        $options = ph_search_database_for_options_by_prefix( 'ph' );
        foreach ( $options as $option ) {
            if ( isset( $option['option_name'] ) ) {
                delete_option( $option['option_name'] );
            }
        }
        // delete taxonomy
        ph_delete_custom_terms( 'ph_approval' );
        ph_delete_custom_terms( 'ph_status' );
        ph_delete_custom_terms( 'plugin-messages' );
    }

}

function ph_delete_custom_terms( $taxonomy )
{
    global  $wpdb ;
    $query = 'SELECT t.name, t.term_id
    FROM ' . $wpdb->terms . ' AS t
    INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
    ON t.term_id = tt.term_id
    WHERE tt.taxonomy = "' . $taxonomy . '"';
    $terms = $wpdb->get_results( $query );
    foreach ( $terms as $term ) {
        if ( is_a( $term, 'WP_Term' ) ) {
            wp_delete_term( $term->term_id, $taxonomy );
        }
    }
}

add_action( 'plugins_loaded', 'ph_include_client_migration' );
function ph_include_client_migration()
{
    if ( get_option( 'ph_license_key', false ) ) {
        require_once dirname( __FILE__ ) . '/client-migration/edd.php';
    }
}
