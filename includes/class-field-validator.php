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
        // TODO: Add validation hooks in later phases
    }
    
    /**
     * Get available validation rules.
     *
     * @return array
     */
    public static function get_validation_rules() {
        return array(
            'email'    => __( 'Email Format', 'smart-checkout-fields' ),
            'phone'    => __( 'Phone Format', 'smart-checkout-fields' ),
            'number'   => __( 'Numeric Only', 'smart-checkout-fields' ),
            'url'      => __( 'URL Format', 'smart-checkout-fields' ),
            'postcode' => __( 'Postcode Format', 'smart-checkout-fields' ),
            'state'    => __( 'Valid State', 'smart-checkout-fields' ),
        );
    }
}
