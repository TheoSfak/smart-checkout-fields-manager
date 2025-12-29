<?php
/**
 * Uninstall Fieldora Checkout for WooCommerce
 *
 * Removes all plugin data from the database when the plugin is deleted.
 *
 * @package Smart_Checkout_Fields_Manager
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if user wants to delete data on uninstall
$scfm_delete_data = get_option( 'scfm_delete_data_on_uninstall', 'no' );

if ( $scfm_delete_data !== 'yes' ) {
    // User chose to keep the data, exit without deleting
    exit;
}

// Delete all plugin options
delete_option( 'scfm_custom_fields' );
delete_option( 'scfm_version' );
delete_option( 'scfm_stylish_options' );
delete_option( 'scfm_delete_data_on_uninstall' );
delete_option( 'scfm_required_indicator' );
delete_option( 'scfm_label_position' );
delete_option( 'scfm_error_position' );
delete_option( 'scfm_custom_css' );

// For multisite installations
if ( is_multisite() ) {
    global $wpdb;
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query acceptable in uninstall for multisite
    $scfm_blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    
    if ( ! empty( $scfm_blog_ids ) && is_array( $scfm_blog_ids ) ) {
        $scfm_original_blog_id = get_current_blog_id();
        
        foreach ( $scfm_blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            
            delete_option( 'scfm_custom_fields' );
            delete_option( 'scfm_version' );
            delete_option( 'scfm_stylish_options' );
            delete_option( 'scfm_delete_data_on_uninstall' );
            delete_option( 'scfm_required_indicator' );
            delete_option( 'scfm_label_position' );
            delete_option( 'scfm_error_position' );
            delete_option( 'scfm_custom_css' );
        }
        
        switch_to_blog( $scfm_original_blog_id );
    }
}

// Clear any cached data
wp_cache_flush();
