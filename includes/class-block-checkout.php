<?php
/**
 * Block Checkout Integration - Handles WooCommerce Block Checkout fields
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Block Checkout class.
 */
class SCFM_Block_Checkout {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Block_Checkout
     */
    private static $instance = null;
    
    /**
     * Supported block field types.
     *
     * @var array
     */
    private $supported_types = array( 'text', 'textarea', 'checkbox', 'select' );
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Block_Checkout
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
        // Check if block checkout is available
        if ( ! $this->is_block_checkout_available() ) {
            return;
        }
        
        // Register additional checkout fields for blocks
        add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'register_block_fields' ) );
        
        // Register field locations
        add_filter( 'woocommerce_blocks_checkout_fields_locations', array( $this, 'add_field_locations' ), 10, 2 );
        
        // Filter field values before rendering
        add_filter( 'woocommerce_blocks_checkout_fields', array( $this, 'modify_block_field_properties' ), 10, 3 );
        
        // Add conditional logic support
        add_filter( 'woocommerce_blocks_checkout_update_order_from_request', array( $this, 'handle_conditional_fields' ), 10, 2 );
        
        // Enqueue block scripts
        add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', array( $this, 'enqueue_block_scripts' ) );
        
        // Add inline script data
        add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', array( $this, 'add_inline_script_data' ) );
    }
    
    /**
     * Check if WooCommerce Block Checkout is available.
     *
     * @return bool
     */
    private function is_block_checkout_available() {
        return class_exists( 'Automattic\WooCommerce\Blocks\Package' ) &&
               version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), '11.0.0', '>=' );
    }
    
    /**
     * Register block checkout fields.
     */
    public function register_block_fields() {
        // Get custom fields from all sections
        $sections = array( 'billing', 'shipping', 'order' );
        
        foreach ( $sections as $section ) {
            $fields = SCFM_Field_Manager::get_fields( $section );
            
            foreach ( $fields as $field_id => $field_config ) {
                // Skip if disabled
                if ( isset( $field_config['enabled'] ) && ! $field_config['enabled'] ) {
                    continue;
                }
                
                // Check block checkout visibility
                if ( ! $this->is_visible_in_block_checkout( $field_config ) ) {
                    continue;
                }
                
                // Skip if not a supported block type
                if ( ! in_array( $field_config['type'], $this->supported_types, true ) ) {
                    continue;
                }
                
                // Register the field
                $this->register_single_field( $field_id, $field_config, $section );
            }
        }
    }
    
    /**
     * Check if field should be visible in block checkout.
     *
     * @param array $field_config Field configuration.
     * @return bool
     */
    private function is_visible_in_block_checkout( $field_config ) {
        // If block_checkout_visible setting exists, respect it
        if ( isset( $field_config['block_checkout_visible'] ) ) {
            return (bool) $field_config['block_checkout_visible'];
        }
        
        // Default: show in block checkout if enabled
        return true;
    }
    
    /**
     * Register a single checkout field for blocks.
     *
     * @param string $field_id     Field ID.
     * @param array  $field_config Field configuration.
     * @param string $section      Section name.
     */
    private function register_single_field( $field_id, $field_config, $section ) {
        // Determine location based on section and custom settings
        $location = $this->get_field_location( $section, $field_config );
        
        // Check if field should be hidden based on visibility rules
        $is_hidden = $this->should_hide_field( $field_config );
        
        // Prepare field arguments
        $args = array(
            'label'    => isset( $field_config['label'] ) ? $field_config['label'] : '',
            'required' => isset( $field_config['required'] ) ? $field_config['required'] : false,
            'hidden'   => $is_hidden,
        );
        
        // Add field-type specific attributes
        switch ( $field_config['type'] ) {
            case 'text':
                $args['type'] = 'text';
                if ( ! empty( $field_config['placeholder'] ) ) {
                    $args['placeholder'] = $field_config['placeholder'];
                }
                break;
                
            case 'textarea':
                $args['type'] = 'textarea';
                if ( ! empty( $field_config['placeholder'] ) ) {
                    $args['placeholder'] = $field_config['placeholder'];
                }
                break;
                
            case 'checkbox':
                $args['type'] = 'checkbox';
                $args['checked'] = isset( $field_config['default'] ) && $field_config['default'];
                break;
                
            case 'select':
                $args['type'] = 'select';
                if ( isset( $field_config['options'] ) && is_array( $field_config['options'] ) ) {
                    $args['options'] = $this->format_select_options( $field_config['options'] );
                }
                break;
        }
        
        // Register with WooCommerce Blocks
        woocommerce_register_additional_checkout_field(
            array(
                'id'         => $field_id,
                'location'   => $location,
                'type'       => $args['type'],
                'label'      => $args['label'],
                'required'   => $args['required'],
                'hidden'     => $args['hidden'],
                'attributes' => $args,
            )
        );
    }
    
    /**
     * Check if field should be hidden in block checkout.
     *
     * @param array $field_config Field configuration.
     * @return bool
     */
    private function should_hide_field( $field_config ) {
        // Check if explicitly set to hidden in block checkout
        if ( isset( $field_config['block_checkout_hidden'] ) ) {
            return (bool) $field_config['block_checkout_hidden'];
        }
        
        return false;
    }
    
    /**
     * Get field location for blocks based on section.
     *
     * @param string $section      Section name.
     * @param array  $field_config Field configuration (optional).
     * @return string
     */
    private function get_field_location( $section, $field_config = array() ) {
        // Check if custom location is specified
        if ( isset( $field_config['block_checkout_location'] ) && ! empty( $field_config['block_checkout_location'] ) ) {
            return $field_config['block_checkout_location'];
        }
        
        // Default location mapping
        $locations = array(
            'billing'  => 'contact',
            'shipping' => 'address',
            'order'    => 'order',
        );
        
        return isset( $locations[ $section ] ) ? $locations[ $section ] : 'contact';
    }
    
    /**
     * Format select options for blocks.
     *
     * @param array $options Options array.
     * @return array
     */
    private function format_select_options( $options ) {
        $formatted = array();
        
        foreach ( $options as $value => $label ) {
            $formatted[] = array(
                'value' => $value,
                'label' => $label,
            );
        }
        
        return $formatted;
    }
    
    /**
     * Add custom field locations.
     *
     * @param array  $locations Current locations.
     * @param string $field_id  Field ID.
     * @return array
     */
    public function add_field_locations( $locations, $field_id ) {
        // Get all custom fields
        $all_fields = array_merge(
            SCFM_Field_Manager::get_fields( 'billing' ),
            SCFM_Field_Manager::get_fields( 'shipping' ),
            SCFM_Field_Manager::get_fields( 'order' )
        );
        
        // Check if this is one of our fields
        if ( isset( $all_fields[ $field_id ] ) ) {
            $field_config = $all_fields[ $field_id ];
            
            // Determine section from field ID prefix
            $section = 'order';
            if ( strpos( $field_id, 'billing_' ) === 0 ) {
                $section = 'billing';
            } elseif ( strpos( $field_id, 'shipping_' ) === 0 ) {
                $section = 'shipping';
            }
            
            $location = $this->get_field_location( $section );
            $locations[ $field_id ] = $location;
        }
        
        return $locations;
    }
    
    /**
     * Enqueue block checkout scripts.
     */
    public function enqueue_block_scripts() {
        // Check if custom CSS file exists
        $css_file = SCFM_PLUGIN_DIR . 'assets/css/block-checkout.css';
        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'scfm-block-checkout',
                SCFM_PLUGIN_URL . 'assets/css/block-checkout.css',
                array(),
                SCFM_VERSION
            );
        }
        
        // Check if custom JS file exists
        $js_file = SCFM_PLUGIN_DIR . 'assets/js/block-checkout.js';
        if ( file_exists( $js_file ) ) {
            wp_enqueue_script(
                'scfm-block-checkout',
                SCFM_PLUGIN_URL . 'assets/js/block-checkout.js',
                array( 'wc-blocks-checkout' ),
                SCFM_VERSION,
                true
            );
        }
    }
    
    /**
     * Get supported field types for blocks.
     *
     * @return array
     */
    public function get_supported_types() {
        return apply_filters( 'scfm_block_supported_types', $this->supported_types );
    }
    
    /**
     * Check if a field type is supported in block checkout.
     *
     * @param string $type Field type.
     * @return bool
     */
    public function is_type_supported( $type ) {
        return in_array( $type, $this->get_supported_types(), true );
    }
    
    /**
     * Modify block field properties before rendering.
     *
     * @param array  $fields   Checkout fields.
     * @param string $context  Context (checkout, editor).
     * @param object $instance Block instance.
     * @return array
     */
    public function modify_block_field_properties( $fields, $context, $instance ) {
        $sections = array( 'billing', 'shipping', 'order' );
        
        foreach ( $sections as $section ) {
            $custom_fields = SCFM_Field_Manager::get_fields( $section );
            
            foreach ( $custom_fields as $field_id => $field_config ) {
                if ( isset( $fields[ $field_id ] ) ) {
                    // Add custom classes
                    if ( ! empty( $field_config['class'] ) ) {
                        $fields[ $field_id ]['class'] = $field_config['class'];
                    }
                    
                    // Add custom attributes
                    if ( ! empty( $field_config['custom_attributes'] ) ) {
                        $fields[ $field_id ]['custom_attributes'] = $field_config['custom_attributes'];
                    }
                    
                    // Apply conditional logic
                    if ( ! empty( $field_config['conditional_logic'] ) ) {
                        $fields[ $field_id ]['conditional_logic'] = $field_config['conditional_logic'];
                    }
                }
            }
        }
        
        return apply_filters( 'scfm_block_checkout_fields', $fields, $context );
    }
    
    /**
     * Handle conditional fields in order processing.
     *
     * @param WC_Order $order   Order object.
     * @param array    $request Request data.
     * @return WC_Order
     */
    public function handle_conditional_fields( $order, $request ) {
        // Process conditional field logic
        $sections = array( 'billing', 'shipping', 'order' );
        
        foreach ( $sections as $section ) {
            $fields = SCFM_Field_Manager::get_fields( $section );
            
            foreach ( $fields as $field_id => $field_config ) {
                // Check if field has conditional logic
                if ( ! empty( $field_config['conditional_logic'] ) ) {
                    $should_process = $this->evaluate_conditional_logic(
                        $field_config['conditional_logic'],
                        $request
                    );
                    
                    // If condition not met, remove field data
                    if ( ! $should_process && isset( $request[ $field_id ] ) ) {
                        $order->delete_meta_data( $field_id );
                    }
                }
            }
        }
        
        return $order;
    }
    
    /**
     * Evaluate conditional logic for a field.
     *
     * @param array $logic   Conditional logic rules.
     * @param array $request Request data.
     * @return bool
     */
    private function evaluate_conditional_logic( $logic, $request ) {
        if ( empty( $logic ) || ! is_array( $logic ) ) {
            return true;
        }
        
        $operator = isset( $logic['operator'] ) ? $logic['operator'] : 'and';
        $rules = isset( $logic['rules'] ) ? $logic['rules'] : array();
        
        $results = array();
        
        foreach ( $rules as $rule ) {
            $field = isset( $rule['field'] ) ? $rule['field'] : '';
            $condition = isset( $rule['condition'] ) ? $rule['condition'] : 'equals';
            $value = isset( $rule['value'] ) ? $rule['value'] : '';
            
            $field_value = isset( $request[ $field ] ) ? $request[ $field ] : '';
            
            switch ( $condition ) {
                case 'equals':
                    $results[] = ( $field_value == $value );
                    break;
                case 'not_equals':
                    $results[] = ( $field_value != $value );
                    break;
                case 'contains':
                    $results[] = ( strpos( $field_value, $value ) !== false );
                    break;
                case 'not_contains':
                    $results[] = ( strpos( $field_value, $value ) === false );
                    break;
                case 'empty':
                    $results[] = empty( $field_value );
                    break;
                case 'not_empty':
                    $results[] = ! empty( $field_value );
                    break;
                default:
                    $results[] = true;
            }
        }
        
        // Evaluate based on operator
        if ( $operator === 'and' ) {
            return ! in_array( false, $results, true );
        } else {
            return in_array( true, $results, true );
        }
    }
    
    /**
     * Add inline script data for block checkout.
     */
    public function add_inline_script_data() {
        $sections = array( 'billing', 'shipping', 'order' );
        $fields_data = array();
        
        foreach ( $sections as $section ) {
            $fields = SCFM_Field_Manager::get_fields( $section );
            
            foreach ( $fields as $field_id => $field_config ) {
                // Only include block-supported fields
                if ( ! $this->is_type_supported( $field_config['type'] ) ) {
                    continue;
                }
                
                // Skip if not visible in block checkout
                if ( ! $this->is_visible_in_block_checkout( $field_config ) ) {
                    continue;
                }
                
                $fields_data[ $field_id ] = array(
                    'type' => $field_config['type'],
                    'label' => $field_config['label'],
                    'conditional_logic' => ! empty( $field_config['conditional_logic'] ) ? $field_config['conditional_logic'] : null,
                    'custom_classes' => ! empty( $field_config['class'] ) ? $field_config['class'] : array(),
                );
            }
        }
        
        wp_localize_script(
            'scfm-block-checkout',
            'scfmBlockCheckout',
            array(
                'fields' => $fields_data,
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'scfm_block_checkout' ),
            )
        );
    }
}
