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
        add_action( 'wp_ajax_scfm_save_stylish', array( $this, 'ajax_save_stylish' ) );
        add_action( 'wp_ajax_scfm_reset_stylish', array( $this, 'ajax_reset_stylish' ) );
    }
    
    /**
     * AJAX: Get fields for a section.
     */
    public function ajax_get_fields() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : 'billing';
        
        $fields = SCFM_Field_Manager::get_all_fields( $section );
        
        wp_send_json_success( array( 'fields' => $fields ) );
    }
    
    /**
     * AJAX: Save field.
     */
    public function ajax_save_field() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section   = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $field_id  = isset( $_POST['field_id'] ) ? sanitize_key( $_POST['field_id'] ) : '';
        $field_data = isset( $_POST['field_data'] ) ? wp_unslash( $_POST['field_data'] ) : array();
        
        if ( empty( $section ) || empty( $field_data ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid field data.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Sanitize field data
        $sanitized_data = $this->sanitize_field_data( $field_data );
        
        // Generate field ID if not provided (new field)
        if ( empty( $field_id ) ) {
            $field_id = SCFM_Field_Manager::generate_field_id( $section, $sanitized_data['label'] );
        }
        
        // Save field
        $result = SCFM_Field_Manager::save_field( $section, $field_id, $sanitized_data );
        
        if ( $result ) {
            wp_send_json_success( array( 
                'message' => __( 'Field saved successfully.', 'smart-checkout-fields-manager' ),
                'field_id' => $field_id,
                'field_data' => $sanitized_data
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to save field.', 'smart-checkout-fields-manager' ) ) );
        }
    }
    
    /**
     * AJAX: Delete field.
     */
    public function ajax_delete_field() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section  = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $field_id = isset( $_POST['field_id'] ) ? sanitize_key( $_POST['field_id'] ) : '';
        
        if ( empty( $section ) || empty( $field_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid field ID.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Check if it's a default WooCommerce field
        $field = SCFM_Field_Manager::get_field( $section, $field_id );
        if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
            wp_send_json_error( array( 'message' => __( 'Cannot delete default WooCommerce fields.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Delete field
        $result = SCFM_Field_Manager::delete_field( $section, $field_id );
        
        if ( $result ) {
            do_action( 'scfm_field_deleted', $section, $field_id );
            wp_send_json_success( array( 'message' => __( 'Field deleted successfully.', 'smart-checkout-fields-manager' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to delete field.', 'smart-checkout-fields-manager' ) ) );
        }
    }
    
    /**
     * AJAX: Toggle field enabled status.
     */
    public function ajax_toggle_field() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section  = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $field_id = isset( $_POST['field_id'] ) ? sanitize_key( $_POST['field_id'] ) : '';
        $enabled  = isset( $_POST['enabled'] ) && $_POST['enabled'] === 'true';
        
        if ( empty( $section ) || empty( $field_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid field ID.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Get field
        $field = SCFM_Field_Manager::get_field( $section, $field_id );
        if ( ! $field ) {
            wp_send_json_error( array( 'message' => __( 'Field not found.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Update enabled status
        $field['enabled'] = $enabled;
        $result = SCFM_Field_Manager::save_field( $section, $field_id, $field );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Field status updated.', 'smart-checkout-fields-manager' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to update field status.', 'smart-checkout-fields-manager' ) ) );
        }
    }
    
    /**
     * AJAX: Update field positions after drag-and-drop.
     */
    public function ajax_update_positions() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section   = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $positions = isset( $_POST['positions'] ) ? wp_unslash( $_POST['positions'] ) : array();
        
        if ( empty( $section ) || empty( $positions ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid position data.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Get all fields including defaults
        $all_fields = SCFM_Field_Manager::get_all_fields( $section );
        
        // Update priorities based on positions
        foreach ( $positions as $position_data ) {
            $field_id = sanitize_key( $position_data['field_id'] );
            $priority = intval( $position_data['position'] ) * 10 + 10;
            
            // Check if field exists in all fields
            if ( isset( $all_fields[ $field_id ] ) ) {
                $field = $all_fields[ $field_id ];
                $field['priority'] = $priority;
                SCFM_Field_Manager::save_field( $section, $field_id, $field );
            }
        }
        
        wp_send_json_success( array( 'message' => __( 'Field positions updated.', 'smart-checkout-fields-manager' ) ) );
    }
    
    /**
     * AJAX: Reset fields to defaults.
     */
    public function ajax_reset_fields() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        
        if ( empty( $section ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid section.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Reset fields
        $result = SCFM_Field_Manager::reset_fields( $section );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Fields reset to defaults successfully.', 'smart-checkout-fields-manager' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to reset fields.', 'smart-checkout-fields-manager' ) ) );
        }
    }
    
    /**
     * Sanitize field data.
     *
     * @param array $data Field data.
     * @return array
     */
    public static function sanitize_field_data( $data ) {
        $sanitized = SCFM_Field_Manager::get_default_field_structure();
        
        if ( isset( $data['type'] ) ) {
            $sanitized['type'] = sanitize_key( $data['type'] );
        }
        
        if ( isset( $data['label'] ) ) {
            $sanitized['label'] = sanitize_text_field( $data['label'] );
        }
        
        if ( isset( $data['placeholder'] ) ) {
            $sanitized['placeholder'] = sanitize_text_field( $data['placeholder'] );
        }
        
        if ( isset( $data['default'] ) ) {
            $sanitized['default'] = sanitize_text_field( $data['default'] );
        }
        
        if ( isset( $data['required'] ) ) {
            $sanitized['required'] = (bool) $data['required'];
        }
        
        if ( isset( $data['enabled'] ) ) {
            $sanitized['enabled'] = (bool) $data['enabled'];
        }
        
        if ( isset( $data['class'] ) && is_array( $data['class'] ) ) {
            $sanitized['class'] = array_map( 'sanitize_html_class', $data['class'] );
        }
        
        if ( isset( $data['validate'] ) && is_array( $data['validate'] ) ) {
            $sanitized['validate'] = array_map( 'sanitize_key', $data['validate'] );
        }
        
        if ( isset( $data['priority'] ) ) {
            $sanitized['priority'] = intval( $data['priority'] );
        }
        
        if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
            $sanitized['options'] = array_map( 'sanitize_text_field', $data['options'] );
        }
        
        if ( isset( $data['visibility'] ) && is_array( $data['visibility'] ) ) {
            $sanitized['visibility'] = array(
                'order_details'   => isset( $data['visibility']['order_details'] ) ? (bool) $data['visibility']['order_details'] : true,
                'admin_emails'    => isset( $data['visibility']['admin_emails'] ) ? (bool) $data['visibility']['admin_emails'] : true,
                'customer_emails' => isset( $data['visibility']['customer_emails'] ) ? (bool) $data['visibility']['customer_emails'] : true,
            );
        }
        
        // Block checkout visibility settings
        if ( isset( $data['block_checkout_visible'] ) ) {
            $sanitized['block_checkout_visible'] = (bool) $data['block_checkout_visible'];
        }
        
        if ( isset( $data['block_checkout_location'] ) ) {
            $valid_locations = array( 'contact', 'address', 'order' );
            $location = sanitize_key( $data['block_checkout_location'] );
            if ( in_array( $location, $valid_locations, true ) || empty( $location ) ) {
                $sanitized['block_checkout_location'] = $location;
            }
        }
        
        // Address format settings
        if ( isset( $data['show_in_address_format'] ) ) {
            $sanitized['show_in_address_format'] = (bool) $data['show_in_address_format'];
        }
        
        if ( isset( $data['address_format_position'] ) ) {
            $sanitized['address_format_position'] = max( 0, intval( $data['address_format_position'] ) );
        }
        
        return apply_filters( 'scfm_sanitize_field_data', $sanitized, $data );
    }
    
    /**
     * AJAX: Save stylish settings.
     */
    public function ajax_save_stylish() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();
        
        // Sanitize options
        $sanitized_options = array(
            'power_beautify'       => isset( $options['power_beautify'] ) ? (bool) $options['power_beautify'] : false,
            'primary_color'        => isset( $options['primary_color'] ) ? sanitize_hex_color( $options['primary_color'] ) : '#4f46e5',
            'background_color'     => isset( $options['background_color'] ) ? sanitize_hex_color( $options['background_color'] ) : '#f8fafc',
            'text_color'           => isset( $options['text_color'] ) ? sanitize_hex_color( $options['text_color'] ) : '#1e293b',
            'label_color'          => isset( $options['label_color'] ) ? sanitize_hex_color( $options['label_color'] ) : '#334155',
            'border_radius'        => isset( $options['border_radius'] ) ? max( 0, min( 30, intval( $options['border_radius'] ) ) ) : 8,
            'shadow'               => isset( $options['shadow'] ) ? sanitize_key( $options['shadow'] ) : 'medium',
            'hover_effect'         => isset( $options['hover_effect'] ) ? (bool) $options['hover_effect'] : true,
            'focus_effect'         => isset( $options['focus_effect'] ) ? sanitize_key( $options['focus_effect'] ) : 'glow',
            'font_family'          => isset( $options['font_family'] ) ? sanitize_key( $options['font_family'] ) : 'default',
            'font_size'            => isset( $options['font_size'] ) ? max( 12, min( 20, intval( $options['font_size'] ) ) ) : 14,
            'font_weight'          => isset( $options['font_weight'] ) ? sanitize_key( $options['font_weight'] ) : '400',
            'placeholder_color'    => isset( $options['placeholder_color'] ) ? sanitize_hex_color( $options['placeholder_color'] ) : '#94a3b8',
            'placeholder_italic'   => isset( $options['placeholder_italic'] ) ? (bool) $options['placeholder_italic'] : true,
            'button_style'         => isset( $options['button_style'] ) ? (bool) $options['button_style'] : false,
            'button_accent'        => isset( $options['button_accent'] ) ? sanitize_hex_color( $options['button_accent'] ) : '#10b981',
            'entrance_animation'   => isset( $options['entrance_animation'] ) ? sanitize_key( $options['entrance_animation'] ) : 'fadein',
            'transition_speed'     => isset( $options['transition_speed'] ) ? sanitize_key( $options['transition_speed'] ) : 'normal',
        );
        
        update_option( 'scfm_stylish_options', $sanitized_options );
        
        wp_send_json_success( array( 'message' => __( 'Stylish settings saved successfully!', 'smart-checkout-fields-manager' ) ) );
    }
    
    /**
     * AJAX: Reset stylish settings.
     */
    public function ajax_reset_stylish() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        delete_option( 'scfm_stylish_options' );
        
        wp_send_json_success( array( 'message' => __( 'Stylish settings reset to defaults.', 'smart-checkout-fields-manager' ) ) );
    }
}
