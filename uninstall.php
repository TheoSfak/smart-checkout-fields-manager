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

// Delete all plugin options
delete_option( 'scfm_custom_fields' );
delete_option( 'scfm_version' );
delete_option( 'scfm_stylish_options' );
delete_option( 'scfm_delete_data_on_uninstall' );
delete_option( 'scfm_required_indicator' );
delete_option( 'scfm_label_position' );
delete_option( 'scfm_error_position' );
delete_option( 'scfm_custom_css' );

// For multisite
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    $original_blog_id = get_current_blog_id();
    
    foreach ( $blog_ids as $blog_id ) {
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
    
    switch_to_blog( $original_blog_id );
}

// Clear cache
if ( function_exists( 'wp_cache_flush' ) ) {
    wp_cache_flush();
}
