<?php
/**
 * Address Formatter - Custom address formatting and display
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Address Formatter class.
 */
class SCFM_Address_Formatter {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Address_Formatter
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Address_Formatter
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
        // Override address formats
        add_filter( 'woocommerce_localisation_address_formats', array( $this, 'customize_address_formats' ), 20 );
        
        // Customize formatted address replacements
        add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'add_custom_address_replacements' ), 20, 2 );
        
        // Add custom fields to address data
        add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'add_custom_fields_to_billing_address' ), 20, 2 );
        add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'add_custom_fields_to_shipping_address' ), 20, 2 );
        
        // Customize my account address display
        add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'format_my_account_address' ), 20, 3 );
    }
    
    /**
     * Customize address formats for different countries.
     *
     * @param array $formats Address formats.
     * @return array
     */
    public function customize_address_formats( $formats ) {
        // Get custom fields
        $billing_fields = SCFM_Field_Manager::get_fields( 'billing' );
        $shipping_fields = SCFM_Field_Manager::get_fields( 'shipping' );
        
        // Build format string with custom fields
        $custom_format = $this->build_custom_address_format( $billing_fields, $shipping_fields );
        
        if ( ! empty( $custom_format ) ) {
            // Apply custom format to all countries or specific ones
            $custom_countries = apply_filters( 'scfm_custom_address_format_countries', array() );
            
            if ( empty( $custom_countries ) ) {
                // Apply to default format (used when country-specific format doesn't exist)
                $formats['default'] = $custom_format;
            } else {
                // Apply to specific countries
                foreach ( $custom_countries as $country ) {
                    $formats[ $country ] = $custom_format;
                }
            }
        }
        
        return apply_filters( 'scfm_address_formats', $formats );
    }
    
    /**
     * Build custom address format string.
     *
     * @param array $billing_fields  Billing fields.
     * @param array $shipping_fields Shipping fields.
     * @return string
     */
    private function build_custom_address_format( $billing_fields, $shipping_fields ) {
        // Start with default WooCommerce format
        $format = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state} {postcode}\n{country}";
        
        // Get fields with address_format_position set
        $custom_positions = array();
        
        foreach ( $billing_fields as $field_id => $field_config ) {
            if ( isset( $field_config['address_format_position'] ) && 
                 isset( $field_config['show_in_address_format'] ) && 
                 $field_config['show_in_address_format'] ) {
                $custom_positions[ $field_config['address_format_position'] ] = '{' . $field_id . '}';
            }
        }
        
        foreach ( $shipping_fields as $field_id => $field_config ) {
            if ( isset( $field_config['address_format_position'] ) && 
                 isset( $field_config['show_in_address_format'] ) && 
                 $field_config['show_in_address_format'] ) {
                $custom_positions[ $field_config['address_format_position'] ] = '{' . $field_id . '}';
            }
        }
        
        // If custom positions exist, rebuild format
        if ( ! empty( $custom_positions ) ) {
            $lines = explode( "\n", $format );
            ksort( $custom_positions );
            
            // Insert custom fields at specified positions
            foreach ( $custom_positions as $position => $placeholder ) {
                if ( $position >= 0 && $position <= count( $lines ) ) {
                    array_splice( $lines, $position, 0, $placeholder );
                }
            }
            
            $format = implode( "\n", $lines );
        }
        
        return apply_filters( 'scfm_custom_address_format', $format );
    }
    
    /**
     * Add custom field replacements for address formatting.
     *
     * @param array $replacements Address replacements.
     * @param array $args         Address data.
     * @return array
     */
    public function add_custom_address_replacements( $replacements, $args ) {
        // Get custom fields
        $billing_fields = SCFM_Field_Manager::get_fields( 'billing' );
        $shipping_fields = SCFM_Field_Manager::get_fields( 'shipping' );
        
        // Add billing custom fields
        foreach ( $billing_fields as $field_id => $field_config ) {
            if ( isset( $field_config['show_in_address_format'] ) && $field_config['show_in_address_format'] ) {
                $key = '{' . $field_id . '}';
                $replacements[ $key ] = isset( $args[ $field_id ] ) ? $args[ $field_id ] : '';
            }
        }
        
        // Add shipping custom fields
        foreach ( $shipping_fields as $field_id => $field_config ) {
            if ( isset( $field_config['show_in_address_format'] ) && $field_config['show_in_address_format'] ) {
                $key = '{' . $field_id . '}';
                $replacements[ $key ] = isset( $args[ $field_id ] ) ? $args[ $field_id ] : '';
            }
        }
        
        return apply_filters( 'scfm_address_replacements', $replacements, $args );
    }
    
    /**
     * Add custom fields to billing address data.
     *
     * @param array    $address Address data.
     * @param WC_Order $order   Order object.
     * @return array
     */
    public function add_custom_fields_to_billing_address( $address, $order ) {
        $custom_fields = SCFM_Field_Manager::get_fields( 'billing' );
        
        foreach ( $custom_fields as $field_id => $field_config ) {
            // Only include if marked for address format
            if ( isset( $field_config['show_in_address_format'] ) && $field_config['show_in_address_format'] ) {
                $value = $order->get_meta( $field_id );
                if ( ! empty( $value ) ) {
                    $address[ $field_id ] = $value;
                }
            }
        }
        
        return $address;
    }
    
    /**
     * Add custom fields to shipping address data.
     *
     * @param array    $address Address data.
     * @param WC_Order $order   Order object.
     * @return array
     */
    public function add_custom_fields_to_shipping_address( $address, $order ) {
        $custom_fields = SCFM_Field_Manager::get_fields( 'shipping' );
        
        foreach ( $custom_fields as $field_id => $field_config ) {
            // Only include if marked for address format
            if ( isset( $field_config['show_in_address_format'] ) && $field_config['show_in_address_format'] ) {
                $value = $order->get_meta( $field_id );
                if ( ! empty( $value ) ) {
                    $address[ $field_id ] = $value;
                }
            }
        }
        
        return $address;
    }
    
    /**
     * Format address for My Account page.
     *
     * @param array  $address      Address data.
     * @param int    $customer_id  Customer ID.
     * @param string $address_type Address type (billing or shipping).
     * @return array
     */
    public function format_my_account_address( $address, $customer_id, $address_type ) {
        $section = $address_type === 'billing' ? 'billing' : 'shipping';
        $custom_fields = SCFM_Field_Manager::get_fields( $section );
        
        foreach ( $custom_fields as $field_id => $field_config ) {
            // Only include if marked for address format
            if ( isset( $field_config['show_in_address_format'] ) && $field_config['show_in_address_format'] ) {
                $value = get_user_meta( $customer_id, $field_id, true );
                if ( ! empty( $value ) ) {
                    $address[ $field_id ] = $value;
                }
            }
        }
        
        return $address;
    }
    
    /**
     * Get formatted address with custom fields.
     *
     * @param array  $address      Address data.
     * @param string $address_type Address type (billing or shipping).
     * @return string
     */
    public function get_formatted_address( $address, $address_type = 'billing' ) {
        // Get WooCommerce countries instance
        $countries = WC()->countries;
        
        // Get format for country
        $country = isset( $address['country'] ) ? $address['country'] : '';
        $format = $countries->get_address_formats();
        $format = isset( $format[ $country ] ) ? $format[ $country ] : $format['default'];
        
        // Get replacements
        $replacements = $this->add_custom_address_replacements( array(), $address );
        
        // Replace placeholders
        $formatted = str_replace( array_keys( $replacements ), array_values( $replacements ), $format );
        
        // Clean up empty lines
        $formatted = preg_replace( '/\n+/', "\n", $formatted );
        $formatted = trim( $formatted );
        
        return apply_filters( 'scfm_formatted_address', $formatted, $address, $address_type );
    }
    
    /**
     * Get address format template for a country.
     *
     * @param string $country Country code.
     * @return string
     */
    public function get_address_format_template( $country = '' ) {
        $formats = WC()->countries->get_address_formats();
        $formats = $this->customize_address_formats( $formats );
        
        if ( empty( $country ) ) {
            $country = 'default';
        }
        
        return isset( $formats[ $country ] ) ? $formats[ $country ] : $formats['default'];
    }
    
    /**
     * Get available address format placeholders.
     *
     * @return array
     */
    public function get_available_placeholders() {
        $placeholders = array(
            '{name}'       => __( 'Full Name', 'fieldora-checkout-for-woo' ),
            '{first_name}' => __( 'First Name', 'fieldora-checkout-for-woo' ),
            '{last_name}'  => __( 'Last Name', 'fieldora-checkout-for-woo' ),
            '{company}'    => __( 'Company', 'fieldora-checkout-for-woo' ),
            '{address_1}'  => __( 'Address Line 1', 'fieldora-checkout-for-woo' ),
            '{address_2}'  => __( 'Address Line 2', 'fieldora-checkout-for-woo' ),
            '{city}'       => __( 'City', 'fieldora-checkout-for-woo' ),
            '{state}'      => __( 'State/County', 'fieldora-checkout-for-woo' ),
            '{postcode}'   => __( 'Postcode/ZIP', 'fieldora-checkout-for-woo' ),
            '{country}'    => __( 'Country', 'fieldora-checkout-for-woo' ),
        );
        
        // Add custom fields
        $sections = array( 'billing', 'shipping' );
        foreach ( $sections as $section ) {
            $custom_fields = SCFM_Field_Manager::get_fields( $section );
            
            foreach ( $custom_fields as $field_id => $field_config ) {
                if ( isset( $field_config['show_in_address_format'] ) && $field_config['show_in_address_format'] ) {
                    $placeholders[ '{' . $field_id . '}' ] = $field_config['label'];
                }
            }
        }
        
        return apply_filters( 'scfm_address_format_placeholders', $placeholders );
    }
}
