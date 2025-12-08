<?php
/**
 * Admin Menu Handler
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Menu class.
 */
class SCFM_Admin_Menu {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Admin_Menu
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Admin_Menu
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
        add_action( 'admin_menu', array( $this, 'register_menu' ), 60 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    
    /**
     * Register admin menu.
     */
    public function register_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Checkout Fields', 'smart-checkout-fields' ),
            __( 'Checkout Fields', 'smart-checkout-fields' ),
            'manage_woocommerce',
            'smart-checkout-fields',
            array( $this, 'render_page' )
        );
    }
    
    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_scripts( $hook ) {
        // Only load on our admin page
        if ( 'woocommerce_page_smart-checkout-fields' !== $hook ) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'scfm-admin',
            SCFM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SCFM_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'scfm-admin',
            SCFM_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'jquery-ui-sortable' ),
            SCFM_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'scfm-admin',
            'scfmAdmin',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'scfm_admin_nonce' ),
                'strings'  => array(
                    'confirm_delete' => __( 'Are you sure you want to delete this field?', 'smart-checkout-fields' ),
                    'confirm_reset'  => __( 'Are you sure you want to reset all fields to defaults? This action cannot be undone.', 'smart-checkout-fields' ),
                    'saving'         => __( 'Saving...', 'smart-checkout-fields' ),
                    'saved'          => __( 'Saved successfully!', 'smart-checkout-fields' ),
                    'error'          => __( 'An error occurred. Please try again.', 'smart-checkout-fields' ),
                ),
            )
        );
    }
    
    /**
     * Render admin page.
     */
    public function render_page() {
        ?>
        <div class="wrap scfm-admin-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="scfm-admin-notice" style="display: none;"></div>
            
            <h2 class="nav-tab-wrapper">
                <a href="#billing-fields" class="nav-tab nav-tab-active" data-tab="billing-fields">
                    <?php esc_html_e( 'Billing Fields', 'smart-checkout-fields' ); ?>
                </a>
                <a href="#shipping-fields" class="nav-tab" data-tab="shipping-fields">
                    <?php esc_html_e( 'Shipping Fields', 'smart-checkout-fields' ); ?>
                </a>
                <a href="#additional-fields" class="nav-tab" data-tab="additional-fields">
                    <?php esc_html_e( 'Additional Fields', 'smart-checkout-fields' ); ?>
                </a>
            </h2>
            
            <div class="scfm-tab-content" id="billing-fields" style="display: block;">
                <?php $this->render_fields_table( 'billing' ); ?>
            </div>
            
            <div class="scfm-tab-content" id="shipping-fields" style="display: none;">
                <?php $this->render_fields_table( 'shipping' ); ?>
            </div>
            
            <div class="scfm-tab-content" id="additional-fields" style="display: none;">
                <?php $this->render_fields_table( 'order' ); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render fields table for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     */
    private function render_fields_table( $section ) {
        ?>
        <div class="scfm-fields-section">
            <div class="scfm-section-header">
                <button type="button" class="button button-primary scfm-add-field" data-section="<?php echo esc_attr( $section ); ?>">
                    <?php esc_html_e( 'Add Custom Field', 'smart-checkout-fields' ); ?>
                </button>
                
                <button type="button" class="button scfm-reset-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <?php esc_html_e( 'Reset to Defaults', 'smart-checkout-fields' ); ?>
                </button>
            </div>
            
            <table class="wp-list-table widefat fixed striped scfm-fields-table">
                <thead>
                    <tr>
                        <th class="scfm-drag-handle" style="width: 40px;"></th>
                        <th style="width: 25%;"><?php esc_html_e( 'Field Name', 'smart-checkout-fields' ); ?></th>
                        <th style="width: 15%;"><?php esc_html_e( 'Type', 'smart-checkout-fields' ); ?></th>
                        <th style="width: 30%;"><?php esc_html_e( 'Label', 'smart-checkout-fields' ); ?></th>
                        <th style="width: 10%;" class="scfm-text-center"><?php esc_html_e( 'Required', 'smart-checkout-fields' ); ?></th>
                        <th style="width: 10%;" class="scfm-text-center"><?php esc_html_e( 'Enabled', 'smart-checkout-fields' ); ?></th>
                        <th style="width: 10%;" class="scfm-text-center"><?php esc_html_e( 'Actions', 'smart-checkout-fields' ); ?></th>
                    </tr>
                </thead>
                <tbody class="scfm-sortable-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <tr>
                        <td colspan="7" class="scfm-no-fields">
                            <?php esc_html_e( 'Loading fields...', 'smart-checkout-fields' ); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
}
