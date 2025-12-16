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
            __( 'Checkout Fields', 'smart-checkout-fields-manager' ),
            __( 'Checkout Fields', 'smart-checkout-fields-manager' ),
            'manage_woocommerce',
            'smart-checkout-fields-manager',
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
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'smart-checkout-fields' ) {
            return;
        }
        
        // Enqueue WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );
        
        // Enqueue CSS
        wp_enqueue_style(
            'scfm-admin',
            SCFM_PLUGIN_URL . 'assets/css/admin.css',
            array( 'wp-color-picker' ),
            SCFM_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'scfm-admin',
            SCFM_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
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
                    'confirm_delete' => __( 'Are you sure you want to delete this field?', 'smart-checkout-fields-manager' ),
                    'confirm_reset'  => __( 'Are you sure you want to reset all fields to defaults? This action cannot be undone.', 'smart-checkout-fields-manager' ),
                    'saving'         => __( 'Saving...', 'smart-checkout-fields-manager' ),
                    'saved'          => __( 'Saved successfully!', 'smart-checkout-fields-manager' ),
                    'error'          => __( 'An error occurred. Please try again.', 'smart-checkout-fields-manager' ),
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
                    <?php esc_html_e( 'Billing Fields', 'smart-checkout-fields-manager' ); ?>
                </a>
                <a href="#shipping-fields" class="nav-tab" data-tab="shipping-fields">
                    <?php esc_html_e( 'Shipping Fields', 'smart-checkout-fields-manager' ); ?>
                </a>
                <a href="#additional-fields" class="nav-tab" data-tab="additional-fields">
                    <?php esc_html_e( 'Additional Fields', 'smart-checkout-fields-manager' ); ?>
                </a>
                <a href="#stylish" class="nav-tab" data-tab="stylish">
                    <span style="color: #ff6b6b;">✨</span> <?php esc_html_e( 'Stylish', 'smart-checkout-fields-manager' ); ?>
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
            
            <div class="scfm-tab-content" id="stylish" style="display: none;">
                <?php $this->render_stylish_settings(); ?>
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
                    <?php esc_html_e( 'Add Custom Field', 'smart-checkout-fields-manager' ); ?>
                </button>
                
                <button type="button" class="button scfm-export-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                    <?php esc_html_e( 'Export', 'smart-checkout-fields-manager' ); ?>
                </button>
                
                <button type="button" class="button scfm-import-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                    <?php esc_html_e( 'Import', 'smart-checkout-fields-manager' ); ?>
                </button>
                
                <button type="button" class="button scfm-reset-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <?php esc_html_e( 'Reset to Defaults', 'smart-checkout-fields-manager' ); ?>
                </button>
            </div>
            
            <table class="wp-list-table widefat fixed striped scfm-fields-table">
                <thead>
                    <tr>
                        <th class="scfm-drag-handle" style="width: 40px;"></th>
                        <th style="width: 25%;"><?php esc_html_e( 'Field Name', 'smart-checkout-fields-manager' ); ?></th>
                        <th style="width: 15%;"><?php esc_html_e( 'Type', 'smart-checkout-fields-manager' ); ?></th>
                        <th style="width: 30%;"><?php esc_html_e( 'Label', 'smart-checkout-fields-manager' ); ?></th>
                        <th style="width: 10%;" class="scfm-text-center"><?php esc_html_e( 'Required', 'smart-checkout-fields-manager' ); ?></th>
                        <th style="width: 10%;" class="scfm-text-center"><?php esc_html_e( 'Enabled', 'smart-checkout-fields-manager' ); ?></th>
                        <th style="width: 10%;" class="scfm-text-center"><?php esc_html_e( 'Actions', 'smart-checkout-fields-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody class="scfm-sortable-fields" data-section="<?php echo esc_attr( $section ); ?>">
                    <tr>
                        <td colspan="7" class="scfm-no-fields">
                            <?php esc_html_e( 'Loading fields...', 'smart-checkout-fields-manager' ); ?>
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
                    <h2 id="scfm-modal-title"><?php esc_html_e( 'Add Custom Field', 'smart-checkout-fields-manager' ); ?></h2>
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
                                    <label for="scfm-field-type"><?php esc_html_e( 'Field Type', 'smart-checkout-fields-manager' ); ?> <span class="required">*</span></label>
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
                                        <?php esc_html_e( 'Select the type of field to add.', 'smart-checkout-fields-manager' ); ?>
                                        <br>
                                        <span style="color: #2271b1;">● = <?php esc_html_e( 'Block Checkout Compatible', 'smart-checkout-fields-manager' ); ?></span>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-label"><?php esc_html_e( 'Label', 'smart-checkout-fields-manager' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="scfm-field-label" name="field_data[label]" class="regular-text" required>
                                    <p class="description"><?php esc_html_e( 'The label displayed on the checkout page.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-placeholder"><?php esc_html_e( 'Placeholder', 'smart-checkout-fields-manager' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="scfm-field-placeholder" name="field_data[placeholder]" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Optional placeholder text shown in the field.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-default"><?php esc_html_e( 'Default Value', 'smart-checkout-fields-manager' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="scfm-field-default" name="field_data[default]" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Default value for this field.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr id="scfm-field-options-row" style="display: none;">
                                <th scope="row">
                                    <label for="scfm-field-options"><?php esc_html_e( 'Options', 'smart-checkout-fields-manager' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="scfm-field-options" name="field_data[options]" class="large-text" rows="4"></textarea>
                                    <p class="description"><?php esc_html_e( 'Enter each option on a new line. Format: value|Label or just value', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-class"><?php esc_html_e( 'CSS Class', 'smart-checkout-fields-manager' ); ?></label>
                                </th>
                                <td>
                                    <select id="scfm-field-class" name="field_data[class]" class="regular-text">
                                        <option value="form-row-wide"><?php esc_html_e( 'Full Width', 'smart-checkout-fields-manager' ); ?></option>
                                        <option value="form-row-first"><?php esc_html_e( 'Half Width (First)', 'smart-checkout-fields-manager' ); ?></option>
                                        <option value="form-row-last"><?php esc_html_e( 'Half Width (Last)', 'smart-checkout-fields-manager' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Field width on checkout page.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-priority"><?php esc_html_e( 'Priority', 'smart-checkout-fields-manager' ); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="scfm-field-priority" name="field_data[priority]" class="small-text" value="100" min="0" step="10">
                                    <p class="description"><?php esc_html_e( 'Lower numbers appear first. Default fields use multiples of 10.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="scfm-field-validation"><?php esc_html_e( 'Validation Rules', 'smart-checkout-fields-manager' ); ?></label>
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
                                    <p class="description"><?php esc_html_e( 'Additional validation rules to apply to this field.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Options', 'smart-checkout-fields-manager' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="scfm-field-required" name="field_data[required]" value="1">
                                        <?php esc_html_e( 'Required field', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" id="scfm-field-enabled" name="field_data[enabled]" value="1" checked>
                                        <?php esc_html_e( 'Enabled', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Visibility', 'smart-checkout-fields-manager' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="field_data[visibility][order_details]" value="1" checked>
                                        <?php esc_html_e( 'Show in Order Details', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="field_data[visibility][admin_emails]" value="1" checked>
                                        <?php esc_html_e( 'Show in Admin Emails', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="field_data[visibility][customer_emails]" value="1" checked>
                                        <?php esc_html_e( 'Show in Customer Emails', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Block Checkout', 'smart-checkout-fields-manager' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="scfm-field-block-visible" name="field_data[block_checkout_visible]" value="1" checked>
                                        <?php esc_html_e( 'Show in Block Checkout', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e( 'Only text, textarea, checkbox, and select fields are supported in Block Checkout.', 'smart-checkout-fields-manager' ); ?>
                                    </p>
                                    <br>
                                    <label for="scfm-field-block-location"><?php esc_html_e( 'Block Location:', 'smart-checkout-fields-manager' ); ?></label>
                                    <select id="scfm-field-block-location" name="field_data[block_checkout_location]" class="regular-text">
                                        <option value=""><?php esc_html_e( 'Auto (based on section)', 'smart-checkout-fields-manager' ); ?></option>
                                        <option value="contact"><?php esc_html_e( 'Contact Information', 'smart-checkout-fields-manager' ); ?></option>
                                        <option value="address"><?php esc_html_e( 'Address', 'smart-checkout-fields-manager' ); ?></option>
                                        <option value="order"><?php esc_html_e( 'Additional Information', 'smart-checkout-fields-manager' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Where to display this field in Block Checkout.', 'smart-checkout-fields-manager' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr id="scfm-address-format-row" style="display: none;">
                                <th scope="row"><?php esc_html_e( 'Address Format', 'smart-checkout-fields-manager' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="scfm-field-show-in-address" name="field_data[show_in_address_format]" value="1">
                                        <?php esc_html_e( 'Show in Address Format', 'smart-checkout-fields-manager' ); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e( 'Include this field when displaying formatted addresses (orders, invoices, emails).', 'smart-checkout-fields-manager' ); ?>
                                    </p>
                                    <br>
                                    <label for="scfm-field-address-position"><?php esc_html_e( 'Position in Address:', 'smart-checkout-fields-manager' ); ?></label>
                                    <input type="number" id="scfm-field-address-position" name="field_data[address_format_position]" class="small-text" value="0" min="0" step="1">
                                    <p class="description">
                                        <?php esc_html_e( 'Line number where this field appears (0 = first line, 1 = second line, etc.).', 'smart-checkout-fields-manager' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
                
                <div class="scfm-modal-footer">
                    <button type="button" class="button scfm-modal-close"><?php esc_html_e( 'Cancel', 'smart-checkout-fields-manager' ); ?></button>
                    <button type="button" class="button button-primary" id="scfm-save-field"><?php esc_html_e( 'Save Field', 'smart-checkout-fields-manager' ); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Stylish Settings tab.
     */
    private function render_stylish_settings() {
        $stylish_options = get_option( 'scfm_stylish_options', array() );
        $power_beautify = isset( $stylish_options['power_beautify'] ) ? $stylish_options['power_beautify'] : false;
        ?>
        <div class="scfm-stylish-container">
            <div class="scfm-stylish-header">
                <h2>✨ <?php esc_html_e( 'Checkout Field Beautification', 'smart-checkout-fields-manager' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Enhance your checkout fields with beautiful styling options. Use Power Beautify for instant professional look!', 'smart-checkout-fields-manager' ); ?>
                </p>
            </div>
            
            <!-- Power Beautify Toggle -->
            <div class="scfm-power-beautify-section">
                <div class="scfm-power-beautify-card">
                    <div class="scfm-power-toggle-header">
                        <span class="scfm-power-icon">⚡</span>
                        <h3><?php esc_html_e( 'Power Beautify Mode', 'smart-checkout-fields-manager' ); ?></h3>
                    </div>
                    <p><?php esc_html_e( 'Enable this to apply all premium styling options automatically with optimal settings.', 'smart-checkout-fields-manager' ); ?></p>
                    <label class="scfm-power-switch">
                        <input type="checkbox" id="scfm-power-beautify" name="stylish[power_beautify]" value="1" <?php checked( $power_beautify, true ); ?>>
                        <span class="scfm-power-slider"></span>
                        <span class="scfm-power-label"><?php esc_html_e( 'Activate Power Mode', 'smart-checkout-fields-manager' ); ?></span>
                    </label>
                </div>
            </div>
            
            <!-- Individual Styling Options -->
            <div class="scfm-stylish-options" id="scfm-individual-options">
                <h3><?php esc_html_e( 'Individual Style Options', 'smart-checkout-fields-manager' ); ?></h3>
                
                <!-- Field Colors -->
                <div class="scfm-style-section">
                    <h4><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Field Colors', 'smart-checkout-fields-manager' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Primary Color', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="text" class="scfm-color-picker" name="stylish[primary_color]" value="<?php echo esc_attr( isset( $stylish_options['primary_color'] ) ? $stylish_options['primary_color'] : '#4f46e5' ); ?>">
                                <p class="description"><?php esc_html_e( 'Main color for borders, focus states, and accents.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Background Color', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="text" class="scfm-color-picker" name="stylish[background_color]" value="<?php echo esc_attr( isset( $stylish_options['background_color'] ) ? $stylish_options['background_color'] : '#f8fafc' ); ?>">
                                <p class="description"><?php esc_html_e( 'Field background color.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Text Color', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="text" class="scfm-color-picker" name="stylish[text_color]" value="<?php echo esc_attr( isset( $stylish_options['text_color'] ) ? $stylish_options['text_color'] : '#1e293b' ); ?>">
                                <p class="description"><?php esc_html_e( 'Text color for field content.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Label Color', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="text" class="scfm-color-picker" name="stylish[label_color]" value="<?php echo esc_attr( isset( $stylish_options['label_color'] ) ? $stylish_options['label_color'] : '#334155' ); ?>">
                                <p class="description"><?php esc_html_e( 'Color for field labels.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Field Effects -->
                <div class="scfm-style-section">
                    <h4><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Field Effects', 'smart-checkout-fields-manager' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Border Radius', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="range" name="stylish[border_radius]" value="<?php echo esc_attr( isset( $stylish_options['border_radius'] ) ? $stylish_options['border_radius'] : '8' ); ?>" min="0" max="30" step="1">
                                <span class="scfm-range-value"></span> px
                                <p class="description"><?php esc_html_e( 'Roundness of field corners.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Shadow Intensity', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <select name="stylish[shadow]">
                                    <option value="none" <?php selected( isset( $stylish_options['shadow'] ) ? $stylish_options['shadow'] : 'medium', 'none' ); ?>><?php esc_html_e( 'None', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="light" <?php selected( isset( $stylish_options['shadow'] ) ? $stylish_options['shadow'] : 'medium', 'light' ); ?>><?php esc_html_e( 'Light', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="medium" <?php selected( isset( $stylish_options['shadow'] ) ? $stylish_options['shadow'] : 'medium', 'medium' ); ?>><?php esc_html_e( 'Medium', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="heavy" <?php selected( isset( $stylish_options['shadow'] ) ? $stylish_options['shadow'] : 'medium', 'heavy' ); ?>><?php esc_html_e( 'Heavy', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="glow" <?php selected( isset( $stylish_options['shadow'] ) ? $stylish_options['shadow'] : 'medium', 'glow' ); ?>><?php esc_html_e( 'Glow Effect', 'smart-checkout-fields-manager' ); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Shadow effect around fields.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Hover Effect', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <label><input type="checkbox" name="stylish[hover_effect]" value="1" <?php checked( isset( $stylish_options['hover_effect'] ) ? $stylish_options['hover_effect'] : true, true ); ?>> <?php esc_html_e( 'Enable hover animations', 'smart-checkout-fields-manager' ); ?></label>
                                <p class="description"><?php esc_html_e( 'Add smooth hover transitions.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Focus Effect', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <select name="stylish[focus_effect]">
                                    <option value="default" <?php selected( isset( $stylish_options['focus_effect'] ) ? $stylish_options['focus_effect'] : 'glow', 'default' ); ?>><?php esc_html_e( 'Default', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="glow" <?php selected( isset( $stylish_options['focus_effect'] ) ? $stylish_options['focus_effect'] : 'glow', 'glow' ); ?>><?php esc_html_e( 'Glow', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="scale" <?php selected( isset( $stylish_options['focus_effect'] ) ? $stylish_options['focus_effect'] : 'glow', 'scale' ); ?>><?php esc_html_e( 'Scale Up', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="lift" <?php selected( isset( $stylish_options['focus_effect'] ) ? $stylish_options['focus_effect'] : 'glow', 'lift' ); ?>><?php esc_html_e( 'Lift', 'smart-checkout-fields-manager' ); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Animation when field is focused.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Typography -->
                <div class="scfm-style-section">
                    <h4><span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e( 'Typography', 'smart-checkout-fields-manager' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Font Family', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <select name="stylish[font_family]">
                                    <option value="default" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'default' ); ?>><?php esc_html_e( 'Default', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="inter" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'inter' ); ?>>Inter</option>
                                    <option value="roboto" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'roboto' ); ?>>Roboto</option>
                                    <option value="opensans" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'opensans' ); ?>>Open Sans</option>
                                    <option value="lato" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'lato' ); ?>>Lato</option>
                                    <option value="montserrat" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'montserrat' ); ?>>Montserrat</option>
                                    <option value="poppins" <?php selected( isset( $stylish_options['font_family'] ) ? $stylish_options['font_family'] : 'default', 'poppins' ); ?>>Poppins</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Font Size', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="range" name="stylish[font_size]" value="<?php echo esc_attr( isset( $stylish_options['font_size'] ) ? $stylish_options['font_size'] : '14' ); ?>" min="12" max="20" step="1">
                                <span class="scfm-range-value"></span> px
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Font Weight', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <select name="stylish[font_weight]">
                                    <option value="300" <?php selected( isset( $stylish_options['font_weight'] ) ? $stylish_options['font_weight'] : '400', '300' ); ?>><?php esc_html_e( 'Light', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="400" <?php selected( isset( $stylish_options['font_weight'] ) ? $stylish_options['font_weight'] : '400', '400' ); ?>><?php esc_html_e( 'Regular', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="500" <?php selected( isset( $stylish_options['font_weight'] ) ? $stylish_options['font_weight'] : '400', '500' ); ?>><?php esc_html_e( 'Medium', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="600" <?php selected( isset( $stylish_options['font_weight'] ) ? $stylish_options['font_weight'] : '400', '600' ); ?>><?php esc_html_e( 'Semi Bold', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="700" <?php selected( isset( $stylish_options['font_weight'] ) ? $stylish_options['font_weight'] : '400', '700' ); ?>><?php esc_html_e( 'Bold', 'smart-checkout-fields-manager' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Placeholder Styling -->
                <div class="scfm-style-section">
                    <h4><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Placeholder Style', 'smart-checkout-fields-manager' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Placeholder Color', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="text" class="scfm-color-picker" name="stylish[placeholder_color]" value="<?php echo esc_attr( isset( $stylish_options['placeholder_color'] ) ? $stylish_options['placeholder_color'] : '#94a3b8' ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Placeholder Style', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <label><input type="checkbox" name="stylish[placeholder_italic]" value="1" <?php checked( isset( $stylish_options['placeholder_italic'] ) ? $stylish_options['placeholder_italic'] : true, true ); ?>> <?php esc_html_e( 'Italic', 'smart-checkout-fields-manager' ); ?></label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Button Style Fields -->
                <div class="scfm-style-section">
                    <h4><span class="dashicons dashicons-button"></span> <?php esc_html_e( 'Button-Style Fields', 'smart-checkout-fields-manager' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Make Checkboxes/Radio as Buttons', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <label><input type="checkbox" name="stylish[button_style]" value="1" <?php checked( isset( $stylish_options['button_style'] ) ? $stylish_options['button_style'] : false, true ); ?>> <?php esc_html_e( 'Enable button-style for checkboxes and radio buttons', 'smart-checkout-fields-manager' ); ?></label>
                                <p class="description"><?php esc_html_e( 'Transform checkboxes and radio buttons into modern clickable buttons.', 'smart-checkout-fields-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Button Accent Color', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <input type="text" class="scfm-color-picker" name="stylish[button_accent]" value="<?php echo esc_attr( isset( $stylish_options['button_accent'] ) ? $stylish_options['button_accent'] : '#10b981' ); ?>">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Animation Options -->
                <div class="scfm-style-section">
                    <h4><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Animations', 'smart-checkout-fields-manager' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Field Entrance Animation', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <select name="stylish[entrance_animation]">
                                    <option value="none" <?php selected( isset( $stylish_options['entrance_animation'] ) ? $stylish_options['entrance_animation'] : 'fadein', 'none' ); ?>><?php esc_html_e( 'None', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="fadein" <?php selected( isset( $stylish_options['entrance_animation'] ) ? $stylish_options['entrance_animation'] : 'fadein', 'fadein' ); ?>><?php esc_html_e( 'Fade In', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="slideup" <?php selected( isset( $stylish_options['entrance_animation'] ) ? $stylish_options['entrance_animation'] : 'fadein', 'slideup' ); ?>><?php esc_html_e( 'Slide Up', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="slidein" <?php selected( isset( $stylish_options['entrance_animation'] ) ? $stylish_options['entrance_animation'] : 'fadein', 'slidein' ); ?>><?php esc_html_e( 'Slide In', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="bounce" <?php selected( isset( $stylish_options['entrance_animation'] ) ? $stylish_options['entrance_animation'] : 'fadein', 'bounce' ); ?>><?php esc_html_e( 'Bounce', 'smart-checkout-fields-manager' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Transition Speed', 'smart-checkout-fields-manager' ); ?></th>
                            <td>
                                <select name="stylish[transition_speed]">
                                    <option value="fast" <?php selected( isset( $stylish_options['transition_speed'] ) ? $stylish_options['transition_speed'] : 'normal', 'fast' ); ?>><?php esc_html_e( 'Fast (0.2s)', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="normal" <?php selected( isset( $stylish_options['transition_speed'] ) ? $stylish_options['transition_speed'] : 'normal', 'normal' ); ?>><?php esc_html_e( 'Normal (0.3s)', 'smart-checkout-fields-manager' ); ?></option>
                                    <option value="slow" <?php selected( isset( $stylish_options['transition_speed'] ) ? $stylish_options['transition_speed'] : 'normal', 'slow' ); ?>><?php esc_html_e( 'Slow (0.5s)', 'smart-checkout-fields-manager' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Save Button -->
            <p class="submit">
                <button type="button" class="button button-primary button-hero" id="scfm-save-stylish">
                    <span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Stylish Settings', 'smart-checkout-fields-manager' ); ?>
                </button>
                <button type="button" class="button button-secondary" id="scfm-reset-stylish">
                    <?php esc_html_e( 'Reset to Defaults', 'smart-checkout-fields-manager' ); ?>
                </button>
            </p>
            
            <!-- Preview Section -->
            <div class="scfm-style-preview">
                <h3><?php esc_html_e( 'Live Preview', 'smart-checkout-fields-manager' ); ?></h3>
                <div class="scfm-preview-container">
                    <div class="scfm-preview-field-wrapper">
                        <label><?php esc_html_e( 'Sample Text Field', 'smart-checkout-fields-manager' ); ?></label>
                        <input type="text" class="scfm-preview-field" placeholder="<?php esc_attr_e( 'Enter your text here...', 'smart-checkout-fields-manager' ); ?>">
                    </div>
                    <div class="scfm-preview-field-wrapper">
                        <label><?php esc_html_e( 'Sample Select Field', 'smart-checkout-fields-manager' ); ?></label>
                        <select class="scfm-preview-field">
                            <option><?php esc_html_e( 'Option 1', 'smart-checkout-fields-manager' ); ?></option>
                            <option><?php esc_html_e( 'Option 2', 'smart-checkout-fields-manager' ); ?></option>
                        </select>
                    </div>
                    <div class="scfm-preview-field-wrapper">
                        <label><input type="checkbox" class="scfm-preview-checkbox"> <?php esc_html_e( 'Sample Checkbox', 'smart-checkout-fields-manager' ); ?></label>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
