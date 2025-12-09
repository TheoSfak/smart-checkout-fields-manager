<?php
/**
 * Checkout Fields Handler - Manages checkout field display
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Checkout Fields class.
 */
class SCFM_Checkout_Fields {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Checkout_Fields
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Checkout_Fields
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
        // Modify checkout fields - Use very high priority to run after all other plugins
        add_filter( 'woocommerce_checkout_fields', array( $this, 'customize_checkout_fields' ), 999 );
        
        // Enqueue scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    
    /**
     * Enqueue frontend scripts.
     */
    public function enqueue_scripts() {
        if ( is_checkout() || is_account_page() ) {
            // Enqueue frontend CSS
            wp_enqueue_style(
                'scfm-frontend',
                SCFM_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                SCFM_VERSION
            );
            
            // Enqueue frontend JavaScript
            wp_enqueue_script(
                'scfm-frontend',
                SCFM_PLUGIN_URL . 'assets/js/frontend.js',
                array( 'jquery' ),
                SCFM_VERSION,
                true
            );
        }
    }
    
    /**
     * Customize checkout fields.
     *
     * @param array $fields WooCommerce checkout fields.
     * @return array
     */
    public function customize_checkout_fields( $fields ) {
        // Get custom fields for each section
        $billing_fields = SCFM_Field_Manager::get_all_fields( 'billing' );
        $shipping_fields = SCFM_Field_Manager::get_all_fields( 'shipping' );
        $order_fields = SCFM_Field_Manager::get_all_fields( 'order' );
        
        // Apply billing fields (merge with existing)
        if ( ! empty( $billing_fields ) ) {
            $fields['billing'] = $this->apply_custom_fields( $fields['billing'], $billing_fields );
        }
        
        // Apply shipping fields (merge with existing)
        if ( ! empty( $shipping_fields ) ) {
            $fields['shipping'] = $this->apply_custom_fields( $fields['shipping'], $shipping_fields );
        }
        
        // Apply order fields (merge with existing)
        if ( ! empty( $order_fields ) ) {
            $fields['order'] = $this->apply_custom_fields( $fields['order'], $order_fields );
        }
        
        return apply_filters( 'scfm_checkout_fields', $fields );
    }
    
    /**
     * Apply custom fields to a section.
     *
     * @param array $default_fields Default WooCommerce fields.
     * @param array $custom_fields  Custom fields from manager.
     * @return array
     */
    private function apply_custom_fields( $default_fields, $custom_fields ) {
        $result = array();
        
        // STEP 1: First, preserve all third-party plugin fields (like Greek VAT)
        // These fields exist in WooCommerce but are NOT in our custom fields array
        foreach ( $default_fields as $field_id => $field_config ) {
            // If this field is not in our custom fields array, it's from another plugin - keep it!
            if ( ! isset( $custom_fields[ $field_id ] ) ) {
                $result[ $field_id ] = $field_config;
            }
        }
        
        // STEP 2: Then, add/override with our managed fields
        foreach ( $custom_fields as $field_id => $field ) {
            // Skip disabled fields
            if ( isset( $field['enabled'] ) && ! $field['enabled'] ) {
                continue;
            }
            
            // Skip third-party fields - keep their original configuration
            // Third-party fields were already added in STEP 1
            if ( isset( $field['third_party'] ) && $field['third_party'] ) {
                // Preserve the original field config from the third-party plugin
                if ( isset( $default_fields[ $field_id ] ) ) {
                    $result[ $field_id ] = $default_fields[ $field_id ];
                }
                continue;
            }
            
            // Convert our custom field to WooCommerce format
            $wc_field = $this->convert_to_wc_field( $field );
            
            // Apply filter for custom modifications
            $wc_field = apply_filters( 'scfm_field_config', $wc_field, $field_id, $field );
            
            $result[ $field_id ] = $wc_field;
        }
        
        return $result;
    }
    
    /**
     * Convert custom field config to WooCommerce field format.
     *
     * @param array $field Custom field config.
     * @return array
     */
    private function convert_to_wc_field( $field ) {
        $wc_field = array(
            'type'        => $field['type'],
            'label'       => $field['label'],
            'placeholder' => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
            'required'    => isset( $field['required'] ) ? $field['required'] : false,
            'class'       => isset( $field['class'] ) ? $field['class'] : array( 'form-row-wide' ),
            'priority'    => isset( $field['priority'] ) ? $field['priority'] : 100,
        );
        
        // Add default value
        if ( isset( $field['default'] ) && ! empty( $field['default'] ) ) {
            $wc_field['default'] = $field['default'];
        }
        
        // Add validation
        if ( isset( $field['validate'] ) && ! empty( $field['validate'] ) ) {
            $wc_field['validate'] = $field['validate'];
        }
        
        // Add options for select, radio, checkbox group
        if ( in_array( $field['type'], array( 'select', 'radio', 'checkboxgroup' ) ) && isset( $field['options'] ) ) {
            $wc_field['options'] = $field['options'];
        }
        
        // Handle special field types
        switch ( $field['type'] ) {
            case 'multiselect':
                $wc_field['type'] = 'select';
                $wc_field['input_class'] = array( 'scfm-multiselect' );
                if ( isset( $field['options'] ) ) {
                    $wc_field['options'] = $field['options'];
                }
                break;
                
            case 'checkboxgroup':
                $wc_field['type'] = 'checkbox';
                $wc_field['class'][] = 'scfm-checkbox-group';
                break;
                
            case 'heading':
                // Heading is display-only
                $wc_field['type'] = 'heading';
                $wc_field['label'] = $field['label'];
                $wc_field['required'] = false;
                break;
                
            case 'paragraph':
                // Paragraph is display-only
                $wc_field['type'] = 'paragraph';
                $wc_field['label'] = '';
                $wc_field['description'] = $field['label'];
                $wc_field['required'] = false;
                break;
        }
        
        return $wc_field;
    }
}
