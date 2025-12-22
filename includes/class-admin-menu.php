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
        // Only load on our plugin page
        if ( 'woocommerce_page_smart-checkout-fields-manager' !== $hook ) {
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
                    'confirm_update' => __( 'This will update the plugin from GitHub. Your current settings will be preserved. Continue?', 'smart-checkout-fields-manager' ),
                    'saving'         => __( 'Saving...', 'smart-checkout-fields-manager' ),
                    'saved'          => __( 'Saved successfully!', 'smart-checkout-fields-manager' ),
                    'updating'       => __( 'Updating from GitHub...', 'smart-checkout-fields-manager' ),
                    'updated'        => __( 'Plugin updated successfully! Please refresh the page.', 'smart-checkout-fields-manager' ),
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
                <a href="#settings" class="nav-tab" data-tab="settings">
                    <?php esc_html_e( 'Settings', 'smart-checkout-fields-manager' ); ?>
                </a>
                <a href="#donate" class="nav-tab" data-tab="donate">
                    <span style="color: #ff6b6b;">❤️</span> <?php esc_html_e( 'Donate', 'smart-checkout-fields-manager' ); ?>
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
            
            <div class="scfm-tab-content" id="settings" style="display: none;">
                <?php $this->render_general_settings(); ?>
            </div>
            
            <div class="scfm-tab-content" id="donate" style="display: none;">
                <?php $this->render_donate_tab(); ?>
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
                                            <option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ) . wp_kses_post( $badge ); ?></option>
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
    
    /**
     * Render general settings page.
     */
    private function render_general_settings() {
        // Handle form submission
        if ( isset( $_POST['scfm_save_settings'] ) && check_admin_referer( 'scfm_save_settings', 'scfm_settings_nonce' ) ) {
            $delete_on_uninstall = isset( $_POST['scfm_delete_data_on_uninstall'] ) ? 'yes' : 'no';
            update_option( 'scfm_delete_data_on_uninstall', $delete_on_uninstall );
            
            // Display & Styling settings
            $required_indicator = sanitize_text_field( wp_unslash( $_POST['scfm_required_indicator'] ?? '*' ) );
            $label_position = sanitize_text_field( wp_unslash( $_POST['scfm_label_position'] ?? 'above' ) );
            $error_position = sanitize_text_field( wp_unslash( $_POST['scfm_error_position'] ?? 'below' ) );
            $custom_css = wp_strip_all_tags( wp_unslash( $_POST['scfm_custom_css'] ?? '' ) );
            
            update_option( 'scfm_required_indicator', $required_indicator );
            update_option( 'scfm_label_position', $label_position );
            update_option( 'scfm_error_position', $error_position );
            update_option( 'scfm_custom_css', $custom_css );
            
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'smart-checkout-fields-manager' ) . '</p></div>';
        }
        
        $delete_on_uninstall = get_option( 'scfm_delete_data_on_uninstall', 'no' );
        $required_indicator = get_option( 'scfm_required_indicator', '*' );
        $label_position = get_option( 'scfm_label_position', 'above' );
        $error_position = get_option( 'scfm_error_position', 'below' );
        $custom_css = get_option( 'scfm_custom_css', '' );
        ?>
        <style>
            .scfm-settings-layout {
                display: flex;
                gap: 30px;
                margin-top: 20px;
            }
            .scfm-settings-left {
                flex: 1;
                min-width: 0;
            }
            .scfm-settings-right {
                flex: 0 0 400px;
                position: sticky;
                top: 32px;
                align-self: flex-start;
                max-height: calc(100vh - 100px);
                overflow-y: auto;
            }
            @media (max-width: 1280px) {
                .scfm-settings-layout {
                    flex-direction: column;
                }
                .scfm-settings-right {
                    flex: 1;
                    position: static;
                    max-height: none;
                }
            }
        </style>
        <div class="scfm-settings-container">
            <h2><?php esc_html_e( 'General Settings', 'smart-checkout-fields-manager' ); ?></h2>
            
            <div class="scfm-settings-layout">
                <!-- Left Column: Settings Form -->
                <div class="scfm-settings-left">
                    <form method="post" action="" class="scfm-settings-form">
                        <?php wp_nonce_field( 'scfm_save_settings', 'scfm_settings_nonce' ); ?>
                        
                        <!-- Display & Styling Section -->
                <h3><?php esc_html_e( 'Display & Styling', 'smart-checkout-fields-manager' ); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="scfm_required_indicator">
                                <?php esc_html_e( 'Required Field Indicator', 'smart-checkout-fields-manager' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="scfm_required_indicator" 
                                   id="scfm_required_indicator" 
                                   value="<?php echo esc_attr( $required_indicator ); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e( 'Symbol or text to display for required fields. Default: *', 'smart-checkout-fields-manager' ); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="scfm_label_position">
                                <?php esc_html_e( 'Field Label Position', 'smart-checkout-fields-manager' ); ?>
                            </label>
                        </th>
                        <td>
                            <select name="scfm_label_position" id="scfm_label_position" class="regular-text">
                                <option value="above" <?php selected( $label_position, 'above' ); ?>><?php esc_html_e( 'Above Field (Default)', 'smart-checkout-fields-manager' ); ?></option>
                                <option value="inline" <?php selected( $label_position, 'inline' ); ?>><?php esc_html_e( 'Inline (Left of Field)', 'smart-checkout-fields-manager' ); ?></option>
                                <option value="floating" <?php selected( $label_position, 'floating' ); ?>><?php esc_html_e( 'Floating (Inside Field)', 'smart-checkout-fields-manager' ); ?></option>
                                <option value="hidden" <?php selected( $label_position, 'hidden' ); ?>><?php esc_html_e( 'Hidden (Placeholder Only)', 'smart-checkout-fields-manager' ); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e( 'Choose how field labels are displayed.', 'smart-checkout-fields-manager' ); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="scfm_error_position">
                                <?php esc_html_e( 'Error Message Position', 'smart-checkout-fields-manager' ); ?>
                            </label>
                        </th>
                        <td>
                            <select name="scfm_error_position" id="scfm_error_position" class="regular-text">
                                <option value="below" <?php selected( $error_position, 'below' ); ?>><?php esc_html_e( 'Below Field (Default)', 'smart-checkout-fields-manager' ); ?></option>
                                <option value="above" <?php selected( $error_position, 'above' ); ?>><?php esc_html_e( 'Above Field', 'smart-checkout-fields-manager' ); ?></option>
                                <option value="tooltip" <?php selected( $error_position, 'tooltip' ); ?>><?php esc_html_e( 'As Tooltip', 'smart-checkout-fields-manager' ); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e( 'Choose where validation error messages appear.', 'smart-checkout-fields-manager' ); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="scfm_custom_css">
                                <?php esc_html_e( 'Custom CSS', 'smart-checkout-fields-manager' ); ?>
                            </label>
                        </th>
                        <td>
                            <textarea name="scfm_custom_css" 
                                      id="scfm_custom_css" 
                                      rows="10" 
                                      class="large-text code"><?php echo esc_textarea( $custom_css ); ?></textarea>
                            <p class="description">
                                <?php esc_html_e( 'Add custom CSS styles for checkout fields. Do not include <style> tags.', 'smart-checkout-fields-manager' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <!-- Plugin Management Section -->
                <h3><?php esc_html_e( 'Plugin Management', 'smart-checkout-fields-manager' ); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="scfm_delete_on_uninstall">
                                <?php esc_html_e( 'Remove Data on Uninstall', 'smart-checkout-fields-manager' ); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="scfm_delete_data_on_uninstall" 
                                       id="scfm_delete_on_uninstall" 
                                       value="yes" 
                                       <?php checked( $delete_on_uninstall, 'yes' ); ?>>
                                <?php esc_html_e( 'Delete all custom fields and plugin data when uninstalling the plugin', 'smart-checkout-fields-manager' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Warning: If enabled, all your custom checkout fields and settings will be permanently deleted when you delete this plugin from WordPress.', 'smart-checkout-fields-manager' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                        <p class="submit">
                            <button type="submit" name="scfm_save_settings" class="button button-primary">
                                <?php esc_html_e( 'Save Settings', 'smart-checkout-fields-manager' ); ?>
                            </button>
                        </p>
                    </form>
                </div>
                
                <!-- Right Column: Live Preview -->
                <div class="scfm-settings-right">
                    <div class="scfm-settings-preview" style="padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                        <h3 style="margin-top: 0;"><?php esc_html_e( 'Live Preview', 'smart-checkout-fields-manager' ); ?></h3>
                        <p class="description"><?php esc_html_e( 'Preview updates in real-time as you change settings.', 'smart-checkout-fields-manager' ); ?></p>
                
                <div class="scfm-preview-wrapper" style="margin-top: 20px; padding: 20px; background: white; border: 1px solid #ddd;">
                    <div class="scfm-preview-field-row" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px;">
                            <?php esc_html_e( 'First Name', 'smart-checkout-fields-manager' ); ?> 
                            <abbr class="required" style="color: #d63638; text-decoration: none;" title="required"><?php echo esc_html( $required_indicator ); ?></abbr>
                        </label>
                        <input type="text" placeholder="<?php esc_attr_e( 'Enter your first name', 'smart-checkout-fields-manager' ); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <span class="scfm-preview-error" style="display: none; color: #d63638; font-size: 13px; margin-top: 5px;">
                            <?php esc_html_e( 'This field is required', 'smart-checkout-fields-manager' ); ?>
                        </span>
                    </div>
                    
                    <div class="scfm-preview-field-row" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px;">
                            <?php esc_html_e( 'Email Address', 'smart-checkout-fields-manager' ); ?> 
                            <abbr class="required" style="color: #d63638; text-decoration: none;" title="required"><?php echo esc_html( $required_indicator ); ?></abbr>
                        </label>
                        <input type="email" placeholder="<?php esc_attr_e( 'your@email.com', 'smart-checkout-fields-manager' ); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; margin-top: 20px;">
                        <strong><?php esc_html_e( 'Current Settings:', 'smart-checkout-fields-manager' ); ?></strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <li><strong><?php esc_html_e( 'Required Indicator:', 'smart-checkout-fields-manager' ); ?></strong> <span id="preview-note-indicator"><?php echo esc_html( $required_indicator ); ?></span></li>
                            <li><strong><?php esc_html_e( 'Label Position:', 'smart-checkout-fields-manager' ); ?></strong> <span id="preview-note-label"><?php echo esc_html( ucfirst( $label_position ) ); ?></span></li>
                            <li><strong><?php esc_html_e( 'Error Position:', 'smart-checkout-fields-manager' ); ?></strong> <span id="preview-note-error"><?php echo esc_html( ucfirst( $error_position ) ); ?></span></li>
                        </ul>
                        <p style="margin-top: 10px; margin-bottom: 0;">
                            <button type="button" class="button button-small" id="scfm-toggle-error-preview">
                                <?php esc_html_e( 'Toggle Error Example', 'smart-checkout-fields-manager' ); ?>
                            </button>
                        </p>
                    </div>
                </div>
                
                <style>
                    /* Preview Label Position Styles */
                    <?php if ( $label_position === 'inline' ) : ?>
                    .scfm-preview-field-row {
                        display: flex;
                        align-items: center;
                        gap: 15px;
                    }
                    .scfm-preview-field-row label {
                        flex: 0 0 150px;
                        margin-bottom: 0 !important;
                    }
                    .scfm-preview-field-row input {
                        flex: 1;
                    }
                    <?php elseif ( $label_position === 'floating' ) : ?>
                    .scfm-preview-field-row {
                        position: relative;
                        padding-top: 10px;
                    }
                    .scfm-preview-field-row label {
                        position: absolute;
                        top: 18px;
                        left: 10px;
                        background: white;
                        padding: 0 5px;
                        transition: all 0.2s;
                        color: #666;
                    }
                    .scfm-preview-field-row input:focus ~ label,
                    .scfm-preview-field-row input:not(:placeholder-shown) ~ label {
                        top: 2px;
                        font-size: 12px;
                        color: #2271b1;
                    }
                    <?php elseif ( $label_position === 'hidden' ) : ?>
                    .scfm-preview-field-row label {
                        position: absolute;
                        width: 1px;
                        height: 1px;
                        margin: -1px;
                        padding: 0;
                        overflow: hidden;
                        clip: rect(0,0,0,0);
                        border: 0;
                    }
                    <?php endif; ?>
                    
                    /* Preview Error Position Styles */
                    <?php if ( $error_position === 'above' ) : ?>
                    .scfm-preview-error {
                        display: block;
                        order: -1;
                        margin-bottom: 5px !important;
                        margin-top: 0 !important;
                    }
                    .scfm-preview-field-row {
                        display: flex;
                        flex-direction: column;
                    }
                    <?php elseif ( $error_position === 'tooltip' ) : ?>
                    .scfm-preview-field-row {
                        position: relative;
                    }
                    .scfm-preview-error {
                        position: absolute;
                        background: #d63638;
                        color: white !important;
                        padding: 8px 12px;
                        border-radius: 4px;
                        top: -45px;
                        left: 0;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                        white-space: nowrap;
                        z-index: 1000;
                    }
                    .scfm-preview-error::after {
                        content: '';
                        position: absolute;
                        bottom: -6px;
                        left: 20px;
                        width: 0;
                        height: 0;
                        border-left: 6px solid transparent;
                        border-right: 6px solid transparent;
                        border-top: 6px solid #d63638;
                    }
                    <?php endif; ?>
                </style>
                
                <script>
                jQuery(document).ready(function($) {
                    // Toggle error preview button
                    $('#scfm-toggle-error-preview').on('click', function() {
                        $('.scfm-preview-error').first().toggle();
                    });
                    
                    // Real-time preview updates
                    function updatePreview() {
                        var requiredIndicator = $('#scfm_required_indicator').val() || '*';
                        var labelPosition = $('#scfm_label_position').val();
                        var errorPosition = $('#scfm_error_position').val();
                        
                        // Update required indicator
                        $('.scfm-preview-field-row .required').text(requiredIndicator);
                        
                        // Update label position note
                        $('.scfm-preview-field-row').removeClass('label-inline label-floating label-hidden label-above');
                        $('.scfm-preview-field-row label').css({
                            'display': '',
                            'position': '',
                            'flex': '',
                            'margin-bottom': '',
                            'top': '',
                            'left': '',
                            'background': '',
                            'padding': '',
                            'font-size': '',
                            'color': '',
                            'width': '',
                            'height': '',
                            'overflow': '',
                            'clip': '',
                            'border': ''
                        });
                        $('.scfm-preview-field-row').css({
                            'display': '',
                            'align-items': '',
                            'gap': '',
                            'position': '',
                            'padding-top': ''
                        });
                        $('.scfm-preview-field-row input').css('flex', '');
                        
                        if (labelPosition === 'inline') {
                            $('.scfm-preview-field-row').css({
                                'display': 'flex',
                                'align-items': 'center',
                                'gap': '15px'
                            });
                            $('.scfm-preview-field-row label').css({
                                'flex': '0 0 150px',
                                'margin-bottom': '0'
                            });
                            $('.scfm-preview-field-row input').css('flex', '1');
                        } else if (labelPosition === 'floating') {
                            $('.scfm-preview-field-row').css({
                                'position': 'relative',
                                'padding-top': '10px'
                            });
                            $('.scfm-preview-field-row label').css({
                                'position': 'absolute',
                                'top': '18px',
                                'left': '10px',
                                'background': 'white',
                                'padding': '0 5px',
                                'color': '#666',
                                'font-size': '14px'
                            });
                        } else if (labelPosition === 'hidden') {
                            $('.scfm-preview-field-row label').css({
                                'position': 'absolute',
                                'width': '1px',
                                'height': '1px',
                                'margin': '-1px',
                                'padding': '0',
                                'overflow': 'hidden',
                                'clip': 'rect(0,0,0,0)',
                                'border': '0'
                            });
                        }
                        
                        // Update error position note
                        $('.scfm-preview-error').css({
                            'display': '',
                            'order': '',
                            'margin-bottom': '',
                            'margin-top': '',
                            'position': '',
                            'background': '',
                            'color': '',
                            'padding': '',
                            'border-radius': '',
                            'top': '',
                            'left': '',
                            'box-shadow': '',
                            'white-space': '',
                            'z-index': ''
                        });
                        $('.scfm-preview-error::after').remove();
                        
                        if (errorPosition === 'above') {
                            $('.scfm-preview-field-row').css({
                                'display': 'flex',
                                'flex-direction': 'column'
                            });
                            $('.scfm-preview-error').css({
                                'order': '-1',
                                'margin-bottom': '5px',
                                'margin-top': '0'
                            });
                        } else if (errorPosition === 'tooltip') {
                            $('.scfm-preview-field-row').css('position', 'relative');
                            $('.scfm-preview-error').css({
                                'position': 'absolute',
                                'background': '#d63638',
                                'color': 'white',
                                'padding': '8px 12px',
                                'border-radius': '4px',
                                'top': '-45px',
                                'left': '0',
                                'box-shadow': '0 2px 8px rgba(0,0,0,0.2)',
                                'white-space': 'nowrap',
                                'z-index': '1000'
                            });
                        }
                        
                        // Update note section
                        $('#preview-note-indicator').text(requiredIndicator);
                        $('#preview-note-label').text(labelPosition.charAt(0).toUpperCase() + labelPosition.slice(1));
                        $('#preview-note-error').text(errorPosition.charAt(0).toUpperCase() + errorPosition.slice(1));
                    }
                    
                    // Bind change events
                    $('#scfm_required_indicator, #scfm_label_position, #scfm_error_position').on('change keyup', updatePreview);
                    
                    // Initial update
                    updatePreview();
                });
                </script>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Donate tab.
     */
    private function render_donate_tab() {
        ?>
        <div class="scfm-donate-container" style="max-width: 800px; margin: 40px auto; text-align: center;">
            
            <!-- Beautiful Quote -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 50px 40px; border-radius: 15px; color: white; margin-bottom: 40px; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
                <div style="font-size: 28px; font-weight: 300; line-height: 1.6; font-style: italic; margin-bottom: 20px;">
                    "The best way to find yourself is to lose yourself in the service of others."
                </div>
                <div style="font-size: 18px; font-weight: 500; opacity: 0.9;">
                    — Mahatma Gandhi
                </div>
            </div>
            
            <!-- Author Info -->
            <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h2 style="color: #667eea; margin-top: 0; font-size: 32px;">
                    ❤️ Support This Plugin
                </h2>
                <p style="font-size: 18px; color: #555; line-height: 1.8; margin: 25px 0;">
                    Hi! I'm <strong>Theodore Sfakianakis</strong>, the developer behind this plugin.<br>
                    I created <strong>Smart Checkout Fields Manager</strong> to help WooCommerce stores create better checkout experiences.
                </p>
                <p style="font-size: 16px; color: #666; line-height: 1.8; margin: 25px 0;">
                    This plugin is <strong>100% free and open source</strong>. If you find it useful and it helps your business,<br>
                    please consider supporting its development with a small donation. ☕
                </p>
            </div>
            
            <!-- Donate Buttons -->
            <div style="background: #f8f9fa; padding: 40px; border-radius: 15px; margin-bottom: 30px;">
                <h3 style="color: #333; font-size: 24px; margin-top: 0;">Choose Your Donation Amount</h3>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; margin: 30px 0;">
                    
                    <form action="https://www.paypal.com/donate" method="post" target="_blank" style="display: inline-block;">
                        <input type="hidden" name="business" value="theodore.sfakianakis@gmail.com" />
                        <input type="hidden" name="amount" value="5" />
                        <input type="hidden" name="currency_code" value="EUR" />
                        <input type="hidden" name="item_name" value="Smart Checkout Fields Manager - Coffee Donation" />
                        <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 35px; font-size: 16px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s;">
                            ☕ €5 - Buy me a coffee
                        </button>
                    </form>
                    
                    <form action="https://www.paypal.com/donate" method="post" target="_blank" style="display: inline-block;">
                        <input type="hidden" name="business" value="theodore.sfakianakis@gmail.com" />
                        <input type="hidden" name="amount" value="10" />
                        <input type="hidden" name="currency_code" value="EUR" />
                        <input type="hidden" name="item_name" value="Smart Checkout Fields Manager - Pizza Donation" />
                        <button type="submit" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; padding: 15px 35px; font-size: 16px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4); transition: all 0.3s;">
                            🍕 €10 - Buy me a pizza
                        </button>
                    </form>
                    
                    <form action="https://www.paypal.com/donate" method="post" target="_blank" style="display: inline-block;">
                        <input type="hidden" name="business" value="theodore.sfakianakis@gmail.com" />
                        <input type="hidden" name="amount" value="25" />
                        <input type="hidden" name="currency_code" value="EUR" />
                        <input type="hidden" name="item_name" value="Smart Checkout Fields Manager - Generous Donation" />
                        <button type="submit" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none; padding: 15px 35px; font-size: 16px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.4); transition: all 0.3s;">
                            🎉 €25 - Generous support
                        </button>
                    </form>
                    
                    <form action="https://www.paypal.com/donate" method="post" target="_blank" style="display: inline-block;">
                        <input type="hidden" name="business" value="theodore.sfakianakis@gmail.com" />
                        <input type="hidden" name="currency_code" value="EUR" />
                        <input type="hidden" name="item_name" value="Smart Checkout Fields Manager - Custom Donation" />
                        <button type="submit" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; padding: 15px 35px; font-size: 16px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4); transition: all 0.3s;">
                            💝 Custom Amount
                        </button>
                    </form>
                    
                </div>
            </div>
            
            <!-- Thank You Message -->
            <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 30px; border-radius: 15px; color: #333;">
                <p style="font-size: 18px; font-weight: 600; margin: 0;">
                    🙏 Thank you for your support!
                </p>
                <p style="font-size: 14px; margin: 10px 0 0 0; opacity: 0.8;">
                    Your contribution helps me maintain and improve this plugin for everyone.
                </p>
            </div>
            
            <!-- Contact & GitHub -->
            <div style="margin-top: 30px; padding: 20px; font-size: 14px; color: #666;">
                <p>
                    <strong>Contact:</strong> theodore.sfakianakis@gmail.com<br>
                    <strong>GitHub:</strong> <a href="https://github.com/TheoSfk" target="_blank" style="color: #667eea;">@TheoSfk</a>
                </p>
            </div>
            
        </div>
        <?php
    }
}
