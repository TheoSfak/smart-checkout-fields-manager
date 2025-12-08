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
        // TODO: Add checkout field hooks in later phases
    }
}
