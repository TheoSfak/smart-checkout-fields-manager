<?php
/**
 * Plugin Name: Fieldora Checkout for WooCommerce
 * Plugin URI: https://github.com/TheoSfak/smart-checkout-fields-manager
 * Description: Complete solution for customizing WooCommerce checkout fields. Add, edit, remove, and rearrange fields with 20+ field types.
 * Version: 1.1.2
 * Author: irmaiden
 * Author URI: https://profiles.wordpress.org/irmaiden/
 * Text Domain: fieldora-checkout-for-woo
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 * Requires Plugins: woocommerce
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'SCFM_VERSION', '1.1.2' );
define( 'SCFM_PLUGIN_FILE', __FILE__ );
define( 'SCFM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCFM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCFM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 */
class SCFM_Checkout_Fields_Manager {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Checkout_Fields_Manager
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Checkout_Fields_Manager
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
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Declare WooCommerce compatibility
        add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
        
        // Check if WooCommerce is active
        add_action( 'plugins_loaded', array( $this, 'check_woocommerce' ) );
        
        // Initialize plugin
        add_action( 'woocommerce_init', array( $this, 'init' ) );
    }
    
    /**
     * Declare compatibility with WooCommerce features.
     */
    public function declare_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                SCFM_PLUGIN_FILE,
                true
            );
            
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'orders_cache',
                SCFM_PLUGIN_FILE,
                true
            );
        }
    }
    
    /**
     * Check if WooCommerce is active.
     */
    public function check_woocommerce() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }
    }
    
    /**
     * Display notice if WooCommerce is not active.
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p>
                <?php
                echo wp_kses_post(
                    sprintf(
                        /* translators: 1: plugin name, 2: WooCommerce link */
                        __( '<strong>%1$s</strong> requires <a href="%2$s" target="_blank">WooCommerce</a> to be installed and activated.', 'fieldora-checkout-for-woo' ),
                        'Fieldora Checkout for WooCommerce',
                        'https://wordpress.org/plugins/woocommerce/'
                    )
                );
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Initialize plugin.
     */
    public function init() {
        // Include required files
        $this->includes();
        
        // Check for migrations
        if ( SCFM_Field_Manager::needs_migration() ) {
            SCFM_Field_Manager::migrate();
        }
        
        // Initialize components
        $this->init_components();
        
        /**
         * Action hook fired after plugin initialization.
         *
         * @since 1.0.0
         */
        do_action( 'scfm_init' );
    }
    
    /**
     * Include required files.
     */
    private function includes() {
        // Admin
        require_once SCFM_PLUGIN_DIR . 'includes/class-admin-menu.php';
        require_once SCFM_PLUGIN_DIR . 'includes/class-admin-settings.php';
        
        // Core functionality
        require_once SCFM_PLUGIN_DIR . 'includes/class-field-manager.php';
        require_once SCFM_PLUGIN_DIR . 'includes/class-field-renderer.php';
        require_once SCFM_PLUGIN_DIR . 'includes/class-field-validator.php';
        
        // Data handling
        require_once SCFM_PLUGIN_DIR . 'includes/class-order-meta.php';
        require_once SCFM_PLUGIN_DIR . 'includes/class-import-export.php';
        
        // Frontend
        require_once SCFM_PLUGIN_DIR . 'includes/class-checkout-fields.php';
        // require_once SCFM_PLUGIN_DIR . 'includes/class-block-checkout.php'; // Removed - block checkout not supported
        require_once SCFM_PLUGIN_DIR . 'includes/class-address-formatter.php';
        require_once SCFM_PLUGIN_DIR . 'includes/class-stylish-manager.php';
    }
    
    /**
     * Initialize plugin components.
     */
    private function init_components() {
        // Admin components (only in admin area)
        if ( is_admin() ) {
            SCFM_Admin_Menu::instance();
            SCFM_Admin_Settings::instance();
            SCFM_Import_Export::instance();
        }
        
        // Frontend components
        SCFM_Checkout_Fields::instance();
        // SCFM_Block_Checkout::instance(); // Removed - block checkout not supported
        SCFM_Address_Formatter::instance();
        SCFM_Stylish_Manager::instance();
        SCFM_Order_Meta::instance();
        SCFM_Field_Validator::instance();
    }
    
    /**
     * Get plugin version.
     *
     * @return string
     */
    public function get_version() {
        return SCFM_VERSION;
    }
    
    /**
     * Get plugin directory path.
     *
     * @return string
     */
    public function get_plugin_dir() {
        return SCFM_PLUGIN_DIR;
    }
    
    /**
     * Get plugin URL.
     *
     * @return string
     */
    public function get_plugin_url() {
        return SCFM_PLUGIN_URL;
    }
}

/**
 * Get main instance of SCFM_Checkout_Fields_Manager.
 *
 * @return SCFM_Checkout_Fields_Manager
 */
function SCFM() {
    return SCFM_Checkout_Fields_Manager::instance();
}

// Initialize plugin.
SCFM();
