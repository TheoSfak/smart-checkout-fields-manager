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
            
            <?php $this->render_field_modal(); ?>
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
                
                <button type="button" class="button scfm-export-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                    <?php esc_html_e( 'Export', 'smart-checkout-fields' ); ?>
                </button>
                
                <button type="button" class="button scfm-import-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                    <?php esc_html_e( 'Import', 'smart-checkout-fields' ); ?>
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
    
    /**
     * Render field editor modal.
     */
    private function render_field_modal() {
        $field_types = SCFM_Field_Renderer::get_field_types();
        ?>
        <div id="scfm-field-modal" class="scfm-modal" style="display: none;">
            <div class="scfm-modal-overlay"></div>
            <div class="scfm-modal-content">
                <div class="scfm-modal-header">
                    <h2 id="scfm-modal-title"><?php esc_html_e( 'Add Custom Field', 'smart-checkout-fields' ); ?></h2>
                    <button type="button" class="scfm-modal-close">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                
                <div class="scfm-modal-body">
                    <form id="scfm-field-form">
                        <input type="hidden" id="scfm-field-id" name="field_id" value="">
                        <input type="hidden" id="scfm-field-section" name="section" value="">
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-type"><?php esc_html_e( 'Field Type', 'smart-checkout-fields' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <select id="scfm-field-type" name="field_data[type]" class="regular-text" required>
                                        <?php 
                                        $block_supported = array( 'text', 'textarea', 'checkbox', 'select' );
                                        foreach ( $field_types as $type => $label ) : 
                                            $is_block_supported = in_array( $type, $block_supported, true );
                                            $badge = $is_block_supported ? ' <span style="color: #2271b1; font-size: 0.85em;">●</span>' : '';
                                        ?>
                                            <option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ) . $badge; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Select the type of field to add.', 'smart-checkout-fields' ); ?>
                                        <br>
                                        <span style="color: #2271b1;">● = <?php esc_html_e( 'Block Checkout Compatible', 'smart-checkout-fields' ); ?></span>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-label"><?php esc_html_e( 'Label', 'smart-checkout-fields' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="scfm-field-label" name="field_data[label]" class="regular-text" required>
                                    <p class="description"><?php esc_html_e( 'The label displayed on the checkout page.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-placeholder"><?php esc_html_e( 'Placeholder', 'smart-checkout-fields' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="scfm-field-placeholder" name="field_data[placeholder]" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Optional placeholder text shown in the field.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-default"><?php esc_html_e( 'Default Value', 'smart-checkout-fields' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="scfm-field-default" name="field_data[default]" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Default value for this field.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr id="scfm-field-options-row" style="display: none;">
                                <th scope="row">
                                    <label for="scfm-field-options"><?php esc_html_e( 'Options', 'smart-checkout-fields' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="scfm-field-options" name="field_data[options]" class="large-text" rows="4"></textarea>
                                    <p class="description"><?php esc_html_e( 'Enter each option on a new line. Format: value|Label or just value', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-class"><?php esc_html_e( 'CSS Class', 'smart-checkout-fields' ); ?></label>
                                </th>
                                <td>
                                    <select id="scfm-field-class" name="field_data[class]" class="regular-text">
                                        <option value="form-row-wide"><?php esc_html_e( 'Full Width', 'smart-checkout-fields' ); ?></option>
                                        <option value="form-row-first"><?php esc_html_e( 'Half Width (First)', 'smart-checkout-fields' ); ?></option>
                                        <option value="form-row-last"><?php esc_html_e( 'Half Width (Last)', 'smart-checkout-fields' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Field width on checkout page.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-priority"><?php esc_html_e( 'Priority', 'smart-checkout-fields' ); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="scfm-field-priority" name="field_data[priority]" class="small-text" value="100" min="0" step="10">
                                    <p class="description"><?php esc_html_e( 'Lower numbers appear first. Default fields use multiples of 10.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-validation"><?php esc_html_e( 'Validation Rules', 'smart-checkout-fields' ); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $validation_rules = SCFM_Field_Validator::get_validation_rules();
                                    foreach ( $validation_rules as $rule => $label ) :
                                        ?>
                                        <label style="display: block; margin-bottom: 5px;">
                                            <input type="checkbox" name="field_data[validation][]" value="<?php echo esc_attr( $rule ); ?>">
                                            <?php echo esc_html( $label ); ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <p class="description"><?php esc_html_e( 'Additional validation rules to apply to this field.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Options', 'smart-checkout-fields' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="scfm-field-required" name="field_data[required]" value="1">
                                        <?php esc_html_e( 'Required field', 'smart-checkout-fields' ); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" id="scfm-field-enabled" name="field_data[enabled]" value="1" checked>
                                        <?php esc_html_e( 'Enabled', 'smart-checkout-fields' ); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Visibility', 'smart-checkout-fields' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="field_data[visibility][order_details]" value="1" checked>
                                        <?php esc_html_e( 'Show in Order Details', 'smart-checkout-fields' ); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="field_data[visibility][admin_emails]" value="1" checked>
                                        <?php esc_html_e( 'Show in Admin Emails', 'smart-checkout-fields' ); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="field_data[visibility][customer_emails]" value="1" checked>
                                        <?php esc_html_e( 'Show in Customer Emails', 'smart-checkout-fields' ); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Block Checkout', 'smart-checkout-fields' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="scfm-field-block-visible" name="field_data[block_checkout_visible]" value="1" checked>
                                        <?php esc_html_e( 'Show in Block Checkout', 'smart-checkout-fields' ); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e( 'Only text, textarea, checkbox, and select fields are supported in Block Checkout.', 'smart-checkout-fields' ); ?>
                                    </p>
                                    <br>
                                    <label for="scfm-field-block-location"><?php esc_html_e( 'Block Location:', 'smart-checkout-fields' ); ?></label>
                                    <select id="scfm-field-block-location" name="field_data[block_checkout_location]" class="regular-text">
                                        <option value=""><?php esc_html_e( 'Auto (based on section)', 'smart-checkout-fields' ); ?></option>
                                        <option value="contact"><?php esc_html_e( 'Contact Information', 'smart-checkout-fields' ); ?></option>
                                        <option value="address"><?php esc_html_e( 'Address', 'smart-checkout-fields' ); ?></option>
                                        <option value="order"><?php esc_html_e( 'Additional Information', 'smart-checkout-fields' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Where to display this field in Block Checkout.', 'smart-checkout-fields' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr id="scfm-address-format-row" style="display: none;">
                                <th scope="row"><?php esc_html_e( 'Address Format', 'smart-checkout-fields' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="scfm-field-show-in-address" name="field_data[show_in_address_format]" value="1">
                                        <?php esc_html_e( 'Show in Address Format', 'smart-checkout-fields' ); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e( 'Include this field when displaying formatted addresses (orders, invoices, emails).', 'smart-checkout-fields' ); ?>
                                    </p>
                                    <br>
                                    <label for="scfm-field-address-position"><?php esc_html_e( 'Position in Address:', 'smart-checkout-fields' ); ?></label>
                                    <input type="number" id="scfm-field-address-position" name="field_data[address_format_position]" class="small-text" value="0" min="0" step="1">
                                    <p class="description">
                                        <?php esc_html_e( 'Line number where this field appears (0 = first line, 1 = second line, etc.).', 'smart-checkout-fields' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
                
                <div class="scfm-modal-footer">
                    <button type="button" class="button scfm-modal-close"><?php esc_html_e( 'Cancel', 'smart-checkout-fields' ); ?></button>
                    <button type="button" class="button button-primary" id="scfm-save-field"><?php esc_html_e( 'Save Field', 'smart-checkout-fields' ); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
}
