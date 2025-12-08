<?php
/**
 * Order Meta Handler - Saves and displays custom field data in orders
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Order Meta class.
 */
class SCFM_Order_Meta {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Order_Meta
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Order_Meta
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
        // TODO: Add order meta hooks in later phases
    }
}
