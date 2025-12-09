<?php
/**
 * Field Manager - Core functionality for managing custom fields
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Field Manager class.
 */
class SCFM_Field_Manager {
    
    /**
     * Option name for storing custom fields.
     */
    const OPTION_NAME = 'scfm_custom_fields';
    
    /**
     * Option name for plugin version.
     */
    const VERSION_OPTION = 'scfm_version';
    
    /**
     * Get all custom fields for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     * @return array
     */
    public static function get_fields( $section = '' ) {
        $all_fields = get_option( self::OPTION_NAME, array() );
        
        if ( empty( $section ) ) {
            return $all_fields;
        }
        
        return isset( $all_fields[ $section ] ) ? $all_fields[ $section ] : array();
    }
    
    /**
     * Get all fields (custom + default) for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     * @return array
     */
    public static function get_all_fields( $section ) {
        $custom_fields = self::get_fields( $section );
        $default_fields = self::get_default_woocommerce_fields( $section );
        
        // Get actual checkout fields (including from other plugins)
        $actual_fields = self::get_actual_checkout_fields( $section );
        
        // Merge: actual fields from checkout, then defaults, then custom overrides
        $all_fields = array_merge( $actual_fields, $default_fields, $custom_fields );
        
        // Sort by priority
        uasort( $all_fields, function( $a, $b ) {
            $priority_a = isset( $a['priority'] ) ? $a['priority'] : 100;
            $priority_b = isset( $b['priority'] ) ? $b['priority'] : 100;
            return $priority_a - $priority_b;
        });
        
        return $all_fields;
    }
    
    /**
     * Get actual checkout fields from WooCommerce (including third-party plugin fields).
     *
     * @param string $section Section name (billing, shipping, order).
     * @return array
     */
    public static function get_actual_checkout_fields( $section ) {
        // Get WooCommerce checkout fields (this includes fields from other plugins)
        $wc_fields = WC()->countries->get_address_fields( '', $section . '_' );
        
        // For order section, get it differently
        if ( $section === 'order' ) {
            // Apply the same filter that WooCommerce uses for checkout fields
            $all_checkout_fields = apply_filters( 'woocommerce_checkout_fields', array(
                'billing'  => array(),
                'shipping' => array(),
                'order'    => array(),
            ) );
            
            $wc_fields = isset( $all_checkout_fields['order'] ) ? $all_checkout_fields['order'] : array();
        }
        
        // Convert to our format
        $fields = array();
        foreach ( $wc_fields as $field_key => $field_config ) {
            // Skip if we already have this as a default field
            $default_fields = self::get_default_woocommerce_fields( $section );
            if ( isset( $default_fields[ $field_key ] ) ) {
                continue;
            }
            
            // Convert to our format
            $fields[ $field_key ] = array(
                'type'        => isset( $field_config['type'] ) ? $field_config['type'] : 'text',
                'label'       => isset( $field_config['label'] ) ? $field_config['label'] : $field_key,
                'placeholder' => isset( $field_config['placeholder'] ) ? $field_config['placeholder'] : '',
                'required'    => isset( $field_config['required'] ) ? $field_config['required'] : false,
                'enabled'     => true,
                'priority'    => isset( $field_config['priority'] ) ? $field_config['priority'] : 100,
                'class'       => isset( $field_config['class'] ) ? $field_config['class'] : array( 'form-row-wide' ),
                'custom'      => false,
                'default_wc'  => false,
                'third_party' => true, // Mark as coming from another plugin
            );
            
            // Add options for select fields
            if ( isset( $field_config['options'] ) ) {
                $fields[ $field_key ]['options'] = $field_config['options'];
            }
            
            // Add validation
            if ( isset( $field_config['validate'] ) ) {
                $fields[ $field_key ]['validate'] = $field_config['validate'];
            }
        }
        
        return $fields;
    }
    
    /**
     * Get default WooCommerce fields for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     * @return array
     */
    public static function get_default_woocommerce_fields( $section ) {
        $fields = array();
        
        switch ( $section ) {
            case 'billing':
                $fields = array(
                    'billing_first_name' => array(
                        'type'        => 'text',
                        'label'       => __( 'First name', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 10,
                        'class'       => array( 'form-row-first' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_last_name' => array(
                        'type'        => 'text',
                        'label'       => __( 'Last name', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 20,
                        'class'       => array( 'form-row-last' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_company' => array(
                        'type'        => 'text',
                        'label'       => __( 'Company name', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 30,
                        'class'       => array( 'form-row-wide' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_country' => array(
                        'type'        => 'country',
                        'label'       => __( 'Country / Region', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 40,
                        'class'       => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_address_1' => array(
                        'type'        => 'text',
                        'label'       => __( 'Street address', 'woocommerce' ),
                        'placeholder' => __( 'House number and street name', 'woocommerce' ),
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 50,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_address_2' => array(
                        'type'        => 'text',
                        'label'       => __( 'Apartment, suite, unit, etc.', 'woocommerce' ),
                        'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'woocommerce' ),
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 60,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_city' => array(
                        'type'        => 'text',
                        'label'       => __( 'Town / City', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 70,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_state' => array(
                        'type'        => 'state',
                        'label'       => __( 'State / County', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 80,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'validate'    => array( 'state' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_postcode' => array(
                        'type'        => 'text',
                        'label'       => __( 'Postcode / ZIP', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 90,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'validate'    => array( 'postcode' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_phone' => array(
                        'type'        => 'tel',
                        'label'       => __( 'Phone', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 100,
                        'class'       => array( 'form-row-wide' ),
                        'validate'    => array( 'phone' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_email' => array(
                        'type'        => 'email',
                        'label'       => __( 'Email address', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 110,
                        'class'       => array( 'form-row-wide' ),
                        'validate'    => array( 'email' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                );
                break;
                
            case 'shipping':
                $fields = array(
                    'shipping_first_name' => array(
                        'type'        => 'text',
                        'label'       => __( 'First name', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 10,
                        'class'       => array( 'form-row-first' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_last_name' => array(
                        'type'        => 'text',
                        'label'       => __( 'Last name', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 20,
                        'class'       => array( 'form-row-last' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_company' => array(
                        'type'        => 'text',
                        'label'       => __( 'Company name', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 30,
                        'class'       => array( 'form-row-wide' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_country' => array(
                        'type'        => 'country',
                        'label'       => __( 'Country / Region', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 40,
                        'class'       => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_address_1' => array(
                        'type'        => 'text',
                        'label'       => __( 'Street address', 'woocommerce' ),
                        'placeholder' => __( 'House number and street name', 'woocommerce' ),
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 50,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_address_2' => array(
                        'type'        => 'text',
                        'label'       => __( 'Apartment, suite, unit, etc.', 'woocommerce' ),
                        'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'woocommerce' ),
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 60,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_city' => array(
                        'type'        => 'text',
                        'label'       => __( 'Town / City', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 70,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_state' => array(
                        'type'        => 'state',
                        'label'       => __( 'State / County', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 80,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'validate'    => array( 'state' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_postcode' => array(
                        'type'        => 'text',
                        'label'       => __( 'Postcode / ZIP', 'woocommerce' ),
                        'placeholder' => '',
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 90,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'validate'    => array( 'postcode' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                );
                break;
                
            case 'order':
                $fields = array(
                    'order_comments' => array(
                        'type'        => 'textarea',
                        'label'       => __( 'Order notes', 'woocommerce' ),
                        'placeholder' => __( 'Notes about your order, e.g. special notes for delivery.', 'woocommerce' ),
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 10,
                        'class'       => array( 'form-row-wide' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                );
                break;
        }
        
        return apply_filters( 'scfm_default_woocommerce_fields', $fields, $section );
    }
    
    /**
     * Save fields for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     * @param array  $fields  Fields data.
     * @return bool
     */
    public static function save_fields( $section, $fields ) {
        $all_fields = get_option( self::OPTION_NAME, array() );
        $all_fields[ $section ] = $fields;
        
        return update_option( self::OPTION_NAME, $all_fields );
    }
    
    /**
     * Get a single field by ID.
     *
     * @param string $section  Section name (billing, shipping, order).
     * @param string $field_id Field ID.
     * @return array|null
     */
    public static function get_field( $section, $field_id ) {
        $fields = self::get_fields( $section );
        
        return isset( $fields[ $field_id ] ) ? $fields[ $field_id ] : null;
    }
    
    /**
     * Add or update a field.
     *
     * @param string $section    Section name (billing, shipping, order).
     * @param string $field_id   Field ID.
     * @param array  $field_data Field data.
     * @return bool
     */
    public static function save_field( $section, $field_id, $field_data ) {
        $fields = self::get_fields( $section );
        $fields[ $field_id ] = $field_data;
        
        return self::save_fields( $section, $fields );
    }
    
    /**
     * Delete a field.
     *
     * @param string $section  Section name (billing, shipping, order).
     * @param string $field_id Field ID.
     * @return bool
     */
    public static function delete_field( $section, $field_id ) {
        $fields = self::get_fields( $section );
        
        if ( isset( $fields[ $field_id ] ) ) {
            unset( $fields[ $field_id ] );
            return self::save_fields( $section, $fields );
        }
        
        return false;
    }
    
    /**
     * Reset all fields for a section to defaults.
     *
     * @param string $section Section name (billing, shipping, order).
     * @return bool
     */
    public static function reset_fields( $section ) {
        return self::save_fields( $section, array() );
    }
    
    /**
     * Get default field structure.
     *
     * @return array
     */
    public static function get_default_field_structure() {
        return array(
            'type'               => 'text',
            'label'              => '',
            'placeholder'        => '',
            'default'            => '',
            'required'           => false,
            'enabled'            => true,
            'class'              => array( 'form-row-wide' ),
            'validate'           => array(),
            'priority'           => 100,
            'custom'             => true,
            'options'            => array(), // For select, radio, checkbox group
            'visibility'         => array(
                'order_details'   => true,
                'admin_emails'    => true,
                'customer_emails' => true,
            ),
        );
    }
    
    /**
     * Update a default WooCommerce field settings.
     *
     * @param string $section  Section name (billing, shipping, order).
     * @param string $field_id Field ID.
     * @param array  $updates  Field updates (label, placeholder, required, etc.).
     * @return bool
     */
    public static function update_default_field( $section, $field_id, $updates ) {
        $custom_fields = self::get_fields( $section );
        
        // Store overrides for default fields
        if ( ! isset( $custom_fields[ $field_id ] ) ) {
            $default_fields = self::get_default_woocommerce_fields( $section );
            if ( isset( $default_fields[ $field_id ] ) ) {
                $custom_fields[ $field_id ] = $default_fields[ $field_id ];
            }
        }
        
        // Apply updates
        $custom_fields[ $field_id ] = array_merge(
            $custom_fields[ $field_id ],
            $updates
        );
        
        return self::save_fields( $section, $custom_fields );
    }
    
    /**
     * Check if plugin needs migration/update.
     *
     * @return bool
     */
    public static function needs_migration() {
        $current_version = get_option( self::VERSION_OPTION, '0' );
        return version_compare( $current_version, SCFM_VERSION, '<' );
    }
    
    /**
     * Run migration/update routines.
     */
    public static function migrate() {
        $current_version = get_option( self::VERSION_OPTION, '0' );
        
        // Future migrations can be added here
        // Example:
        // if ( version_compare( $current_version, '1.1.0', '<' ) ) {
        //     self::migrate_to_1_1_0();
        // }
        
        // Update version
        update_option( self::VERSION_OPTION, SCFM_VERSION );
    }
    
    /**
     * Generate unique field ID.
     *
     * @param string $section Section name.
     * @param string $label   Field label.
     * @return string
     */
    public static function generate_field_id( $section, $label ) {
        // Create base ID from label
        $base_id = sanitize_title( $label );
        $base_id = str_replace( '-', '_', $base_id );
        
        // Add section prefix
        $field_id = $section . '_' . $base_id;
        
        // Ensure uniqueness
        $counter = 1;
        $original_id = $field_id;
        $existing_fields = self::get_all_fields( $section );
        
        while ( isset( $existing_fields[ $field_id ] ) ) {
            $field_id = $original_id . '_' . $counter;
            $counter++;
        }
        
        return $field_id;
    }
}
