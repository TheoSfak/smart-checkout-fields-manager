<?php
/**
 * Admin Settings Handler
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Settings class.
 */
class SCFM_Admin_Settings {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Admin_Settings
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Admin_Settings
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'wp_ajax_scfm_get_fields', array( $this, 'ajax_get_fields' ) );
        add_action( 'wp_ajax_scfm_save_field', array( $this, 'ajax_save_field' ) );
        add_action( 'wp_ajax_scfm_delete_field', array( $this, 'ajax_delete_field' ) );
        add_action( 'wp_ajax_scfm_toggle_field', array( $this, 'ajax_toggle_field' ) );
        add_action( 'wp_ajax_scfm_update_positions', array( $this, 'ajax_update_positions' ) );
        add_action( 'wp_ajax_scfm_reset_fields', array( $this, 'ajax_reset_fields' ) );
    }
    
    /**
     * AJAX: Get fields for a section.
     */
    public function ajax_get_fields() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields' ) ) );
        }
        
        $section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : 'billing';
        
        // TODO: Implement in Phase 2
        $fields = array();
        
        wp_send_json_success( array( 'fields' => $fields ) );
    }
    
    /**
     * AJAX: Save field.
     */
    public function ajax_save_field() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields' ) ) );
        }
        
        // TODO: Implement in Phase 2
        
        wp_send_json_success( array( 'message' => __( 'Field saved successfully.', 'smart-checkout-fields' ) ) );
    }
    
    /**
     * AJAX: Delete field.
     */
    public function ajax_delete_field() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields' ) ) );
        }
        
        // TODO: Implement in Phase 2
        
        wp_send_json_success( array( 'message' => __( 'Field deleted successfully.', 'smart-checkout-fields' ) ) );
    }
    
    /**
     * AJAX: Toggle field enabled status.
     */
    public function ajax_toggle_field() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields' ) ) );
        }
        
        // TODO: Implement in Phase 2
        
        wp_send_json_success( array( 'message' => __( 'Field status updated.', 'smart-checkout-fields' ) ) );
    }
    
    /**
     * AJAX: Update field positions after drag-and-drop.
     */
    public function ajax_update_positions() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields' ) ) );
        }
        
        // TODO: Implement in Phase 2
        
        wp_send_json_success( array( 'message' => __( 'Field positions updated.', 'smart-checkout-fields' ) ) );
    }
    
    /**
     * AJAX: Reset fields to defaults.
     */
    public function ajax_reset_fields() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields' ) ) );
        }
        
        // TODO: Implement in Phase 2
        
        wp_send_json_success( array( 'message' => __( 'Fields reset to defaults successfully.', 'smart-checkout-fields' ) ) );
    }
}
