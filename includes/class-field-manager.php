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
     * Flag to prevent recursion when getting actual checkout fields.
     *
     * @var bool
     */
    private static $getting_actual_fields = false;
    
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
        // Prevent recursion
        if ( self::$getting_actual_fields ) {
            return array();
        }
        
        self::$getting_actual_fields = true;
        
        $wc_fields = array();
        
        // For billing and shipping, use WooCommerce's address fields
        if ( $section === 'billing' || $section === 'shipping' ) {
            $wc_fields = WC()->countries->get_address_fields( '', $section . '_' );
        } 
        // For order section, get fields from the checkout fields filter
        elseif ( $section === 'order' ) {
            // Remove our own filter temporarily to avoid recursion
            remove_filter( 'woocommerce_checkout_fields', array( SCFM_Checkout_Fields::instance(), 'customize_checkout_fields' ), 20 );
            
            // Get checkout fields with other plugins' modifications
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- This is WooCommerce core hook
            $all_checkout_fields = apply_filters( 'woocommerce_checkout_fields', array(
                'billing'  => array(),
                'shipping' => array(),
                'order'    => array(),
            ) );
            
            // Re-add our filter
            add_filter( 'woocommerce_checkout_fields', array( SCFM_Checkout_Fields::instance(), 'customize_checkout_fields' ), 20 );
            
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
        
        self::$getting_actual_fields = false;
        
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
                        'label'       => __( 'First name', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Last name', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Company name', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Country / Region', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Street address', 'smart-checkout-fields-manager' ),
                        'placeholder' => __( 'House number and street name', 'smart-checkout-fields-manager' ),
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 50,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_address_2' => array(
                        'type'        => 'text',
                        'label'       => __( 'Apartment, suite, unit, etc.', 'smart-checkout-fields-manager' ),
                        'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'smart-checkout-fields-manager' ),
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 60,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'billing_city' => array(
                        'type'        => 'text',
                        'label'       => __( 'Town / City', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'State / County', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Postcode / ZIP', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Phone', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Email address', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'First name', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Last name', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Company name', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Country / Region', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Street address', 'smart-checkout-fields-manager' ),
                        'placeholder' => __( 'House number and street name', 'smart-checkout-fields-manager' ),
                        'required'    => true,
                        'enabled'     => true,
                        'priority'    => 50,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_address_2' => array(
                        'type'        => 'text',
                        'label'       => __( 'Apartment, suite, unit, etc.', 'smart-checkout-fields-manager' ),
                        'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'smart-checkout-fields-manager' ),
                        'required'    => false,
                        'enabled'     => true,
                        'priority'    => 60,
                        'class'       => array( 'form-row-wide', 'address-field' ),
                        'custom'      => false,
                        'default_wc'  => true,
                    ),
                    'shipping_city' => array(
                        'type'        => 'text',
                        'label'       => __( 'Town / City', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'State / County', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Postcode / ZIP', 'smart-checkout-fields-manager' ),
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
                        'label'       => __( 'Order notes', 'smart-checkout-fields-manager' ),
                        'placeholder' => __( 'Notes about your order, e.g. special notes for delivery.', 'smart-checkout-fields-manager' ),
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
        // Transliterate Greek characters to Latin equivalents
        $greek_latin = array(
            'Α' => 'A', 'α' => 'a', 'Β' => 'B', 'β' => 'b', 'Γ' => 'G', 'γ' => 'g',
            'Δ' => 'D', 'δ' => 'd', 'Ε' => 'E', 'ε' => 'e', 'Ζ' => 'Z', 'ζ' => 'z',
            'Η' => 'I', 'η' => 'i', 'Θ' => 'Th', 'θ' => 'th', 'Ι' => 'I', 'ι' => 'i',
            'Κ' => 'K', 'κ' => 'k', 'Λ' => 'L', 'λ' => 'l', 'Μ' => 'M', 'μ' => 'm',
            'Ν' => 'N', 'ν' => 'n', 'Ξ' => 'X', 'ξ' => 'x', 'Ο' => 'O', 'ο' => 'o',
            'Π' => 'P', 'π' => 'p', 'Ρ' => 'R', 'ρ' => 'r', 'Σ' => 'S', 'σ' => 's', 'ς' => 's',
            'Τ' => 'T', 'τ' => 't', 'Υ' => 'Y', 'υ' => 'y', 'Φ' => 'F', 'φ' => 'f',
            'Χ' => 'Ch', 'χ' => 'ch', 'Ψ' => 'Ps', 'ψ' => 'ps', 'Ω' => 'O', 'ω' => 'o',
            // Greek with diacritics
            'Ά' => 'A', 'ά' => 'a', 'Έ' => 'E', 'έ' => 'e', 'Ή' => 'I', 'ή' => 'i',
            'Ί' => 'I', 'ί' => 'i', 'Ό' => 'O', 'ό' => 'o', 'Ύ' => 'Y', 'ύ' => 'y',
            'Ώ' => 'O', 'ώ' => 'o', 'Ϊ' => 'I', 'ϊ' => 'i', 'ΐ' => 'i', 'Ϋ' => 'Y',
            'ϋ' => 'y', 'ΰ' => 'y'
        );
        
        // Transliterate Greek characters first
        $base_id = strtr( $label, $greek_latin );
        
        // Then handle other accents
        $base_id = remove_accents( $base_id );
        
        // Sanitize to create valid field ID
        $base_id = sanitize_title( $base_id );
        $base_id = str_replace( '-', '_', $base_id );
        
        // If after sanitization we have an empty string, use a generic name
        if ( empty( $base_id ) ) {
            $base_id = 'custom_field';
        }
        
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
