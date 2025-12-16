<?php
/**
 * Field Validator - Handles field validation
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Field Validator class.
 */
class SCFM_Field_Validator {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Field_Validator
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Field_Validator
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
        // Hook into WooCommerce checkout validation
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_custom_fields' ), 10, 2 );
    }
    
    /**
     * Get available validation rules.
     *
     * @return array
     */
    public static function get_validation_rules() {
        return array(
            'email'    => __( 'Email Format', 'smart-checkout-fields-manager' ),
            'phone'    => __( 'Phone Format', 'smart-checkout-fields-manager' ),
            'number'   => __( 'Numeric Only', 'smart-checkout-fields-manager' ),
            'url'      => __( 'URL Format', 'smart-checkout-fields-manager' ),
            'postcode' => __( 'Postcode Format', 'smart-checkout-fields-manager' ),
            'state'    => __( 'Valid State', 'smart-checkout-fields-manager' ),
        );
    }
    
    /**
     * Validate custom fields during checkout.
     *
     * @param array    $data   Posted checkout data.
     * @param WP_Error $errors WP_Error object.
     */
    public function validate_custom_fields( $data, $errors ) {
        $all_fields = SCFM_Field_Manager::get_fields();
        
        foreach ( $all_fields as $section => $fields ) {
            foreach ( $fields as $field_id => $field ) {
                // Skip disabled fields
                if ( ! isset( $field['enabled'] ) || ! $field['enabled'] ) {
                    continue;
                }
                
                // Skip default WC fields (they have their own validation)
                if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
                    continue;
                }
                
                $value = isset( $data[ $field_id ] ) ? $data[ $field_id ] : '';
                
                // Validate based on field type and validation rules
                $this->validate_field( $field_id, $field, $value, $errors );
            }
        }
    }
    
    /**
     * Validate individual field.
     *
     * @param string   $field_id Field ID.
     * @param array    $field    Field configuration.
     * @param mixed    $value    Field value.
     * @param WP_Error $errors   WP_Error object.
     */
    private function validate_field( $field_id, $field, $value, $errors ) {
        $field_label = isset( $field['label'] ) ? $field['label'] : $field_id;
        $field_type = isset( $field['type'] ) ? $field['type'] : 'text';
        
        // For multiselect and checkboxgroup, check if array is empty
        $is_empty = $value;
        if ( in_array( $field_type, array( 'multiselect', 'checkboxgroup' ) ) ) {
            $is_empty = empty( $value ) || ( is_array( $value ) && count( $value ) === 0 );
        } else {
            $is_empty = empty( $value );
        }
        
        // Required field validation
        if ( ! empty( $field['required'] ) && $is_empty ) {
            $errors->add(
                $field_id,
                sprintf(
                    /* translators: %s: field label */
                    __( '%s is a required field.', 'smart-checkout-fields-manager' ),
                    '<strong>' . esc_html( $field_label ) . '</strong>'
                )
            );
            return;
        }
        
        // Skip validation if field is empty and not required
        if ( $is_empty ) {
            return;
        }
        
        // Type-based validation
        
        switch ( $field_type ) {
            case 'email':
                if ( ! is_email( $value ) ) {
                    $errors->add(
                        $field_id,
                        sprintf(
                            /* translators: %s: field label */
                            __( '%s must be a valid email address.', 'smart-checkout-fields-manager' ),
                            '<strong>' . esc_html( $field_label ) . '</strong>'
                        )
                    );
                }
                break;
                
            case 'number':
                if ( ! is_numeric( $value ) ) {
                    $errors->add(
                        $field_id,
                        sprintf(
                            /* translators: %s: field label */
                            __( '%s must be a valid number.', 'smart-checkout-fields-manager' ),
                            '<strong>' . esc_html( $field_label ) . '</strong>'
                        )
                    );
                }
                break;
                
            case 'url':
                if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
                    $errors->add(
                        $field_id,
                        sprintf(
                            /* translators: %s: field label */
                            __( '%s must be a valid URL.', 'smart-checkout-fields-manager' ),
                            '<strong>' . esc_html( $field_label ) . '</strong>'
                        )
                    );
                }
                break;
                
            case 'tel':
                // Basic phone validation - at least 7 digits
                $clean_phone = preg_replace( '/[^0-9]/', '', $value );
                if ( strlen( $clean_phone ) < 7 ) {
                    $errors->add(
                        $field_id,
                        sprintf(
                            /* translators: %s: field label */
                            __( '%s must be a valid phone number.', 'smart-checkout-fields-manager' ),
                            '<strong>' . esc_html( $field_label ) . '</strong>'
                        )
                    );
                }
                break;
        }
        
        // Custom validation rules
        if ( ! empty( $field['validation'] ) ) {
            $this->apply_custom_validation( $field_id, $field, $value, $errors );
        }
        
        // Allow developers to add custom validation
        do_action( 'scfm_validate_field', $field_id, $field, $value, $errors );
    }
    
    /**
     * Apply custom validation rules.
     *
     * @param string   $field_id Field ID.
     * @param array    $field    Field configuration.
     * @param mixed    $value    Field value.
     * @param WP_Error $errors   WP_Error object.
     */
    private function apply_custom_validation( $field_id, $field, $value, $errors ) {
        $validation_rules = is_array( $field['validation'] ) ? $field['validation'] : array( $field['validation'] );
        $field_label      = isset( $field['label'] ) ? $field['label'] : $field_id;
        
        foreach ( $validation_rules as $rule ) {
            switch ( $rule ) {
                case 'postcode':
                    // Basic postcode validation
                    if ( ! preg_match( '/^[A-Za-z0-9\s\-]{3,10}$/', $value ) ) {
                        $errors->add(
                            $field_id,
                            sprintf(
                                /* translators: %s: field label */
                                __( '%s must be a valid postcode.', 'smart-checkout-fields-manager' ),
                                '<strong>' . esc_html( $field_label ) . '</strong>'
                            )
                        );
                    }
                    break;
                    
                case 'state':
                    // Validate against WooCommerce states
                    $countries = WC()->countries->get_states();
                    $valid     = false;
                    
                    foreach ( $countries as $states ) {
                        if ( isset( $states[ $value ] ) ) {
                            $valid = true;
                            break;
                        }
                    }
                    
                    if ( ! $valid ) {
                        $errors->add(
                            $field_id,
                            sprintf(
                                /* translators: %s: field label */
                                __( '%s must be a valid state.', 'smart-checkout-fields-manager' ),
                                '<strong>' . esc_html( $field_label ) . '</strong>'
                            )
                        );
                    }
                    break;
                    
                case 'phone':
                    // More strict phone validation
                    $clean_phone = preg_replace( '/[^0-9+]/', '', $value );
                    if ( ! preg_match( '/^[\+]?[0-9]{7,15}$/', $clean_phone ) ) {
                        $errors->add(
                            $field_id,
                            sprintf(
                                /* translators: %s: field label */
                                __( '%s must be a valid phone number (7-15 digits).', 'smart-checkout-fields-manager' ),
                                '<strong>' . esc_html( $field_label ) . '</strong>'
                            )
                        );
                    }
                    break;
            }
        }
    }
    
    /**
     * Validate field value (used for AJAX/programmatic validation).
     *
     * @param string $field_id Field ID.
     * @param array  $field    Field configuration.
     * @param mixed  $value    Field value.
     * @return true|string True if valid, error message if invalid.
     */
    public function validate_field_value( $field_id, $field, $value ) {
        require_once ABSPATH . 'wp-includes/class-wp-error.php';
        
        $errors = new WP_Error();
        $this->validate_field( $field_id, $field, $value, $errors );
        
        if ( $errors->has_errors() ) {
            return $errors->get_error_message();
        }
        
        return true;
    }
}
