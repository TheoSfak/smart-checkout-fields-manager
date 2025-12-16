<?php
/**
 * Uninstall Smart Checkout Fields Manager
 *
 * Removes all plugin data from the database when the plugin is deleted.
 *
 * @package Smart_Checkout_Fields_Manager
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all plugin options
delete_option( 'scfm_custom_fields' );
delete_option( 'scfm_version' );
delete_option( 'scfm_stylish_options' );

// For multisite installations
if ( is_multisite() ) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();
    
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        
        delete_option( 'scfm_custom_fields' );
        delete_option( 'scfm_version' );
        delete_option( 'scfm_stylish_options' );
    }
    
    switch_to_blog( $original_blog_id );
}

// Clear any cached data
wp_cache_flush();
