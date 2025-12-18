<?php
/**
 * Import/Export Handler - Handles field configuration import/export
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Import/Export Handler class.
 */
class SCFM_Import_Export {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Import_Export
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Import_Export
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
        add_action( 'wp_ajax_scfm_export_fields', array( $this, 'ajax_export_fields' ) );
        add_action( 'wp_ajax_scfm_import_fields', array( $this, 'ajax_import_fields' ) );
    }
    
    /**
     * AJAX: Export fields as JSON.
     */
    public function ajax_export_fields() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        
        if ( empty( $section ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid section.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $export_data = $this->prepare_export_data( $section );
        
        if ( empty( $export_data['fields'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No custom fields exist to export.', 'smart-checkout-fields-manager' ) ) );
        }
        
        wp_send_json_success( array(
            'data'     => $export_data,
            'filename' => $this->generate_filename( $section ),
        ) );
    }
    
    /**
     * AJAX: Import fields from JSON.
     */
    public function ajax_import_fields() {
        check_ajax_referer( 'scfm_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'smart-checkout-fields-manager' ) ) );
        }
        
        $section    = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $import_data = isset( $_POST['import_data'] ) ? $_POST['import_data'] : '';
        
        if ( empty( $section ) || empty( $import_data ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid import data.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Decode JSON
        $data = json_decode( stripslashes( $import_data ), true );
        
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => __( 'Invalid JSON format.', 'smart-checkout-fields-manager' ) ) );
        }
        
        // Validate import data
        $validation = $this->validate_import_data( $data, $section );
        
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
        }
        
        // Create backup before import
        $backup = $this->create_backup( $section );
        
        // Import fields
        $result = $this->import_fields( $data, $section );
        
        if ( is_wp_error( $result ) ) {
            // Restore backup on error
            $this->restore_backup( $backup, $section );
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        
        if ( $result === 0 ) {
            wp_send_json_success( array(
                'message' => __( 'No new fields to import. All fields already exist.', 'smart-checkout-fields-manager' ),
                'backup'  => $backup,
            ) );
        }

        wp_send_json_success( array(
            'message' => sprintf(
                /* translators: %d: number of fields imported */
                __( 'Successfully imported %d field(s).', 'smart-checkout-fields-manager' ),
                $result
            ),
            'backup'  => $backup,
        ) );
    }

    /**
     * Prepare export data.
     *
     * @param string $section Section name.
     * @return array
     */
    private function prepare_export_data( $section ) {
        // Get all fields for this section (custom + default)
        $fields = SCFM_Field_Manager::get_all_fields( $section );
        
        // Filter out default WC fields if they haven't been modified
        $custom_fields = array();
        foreach ( $fields as $field_id => $field ) {
            // Always include custom fields
            if ( ! isset( $field['default_wc'] ) || ! $field['default_wc'] ) {
                $custom_fields[ $field_id ] = $field;
            } elseif ( isset( $field['modified'] ) && $field['modified'] ) {
                // Include modified default fields
                $custom_fields[ $field_id ] = $field;
            }
        }
        
        return array(
            'version'    => SCFM_VERSION,
            'plugin'     => 'Smart Checkout Fields Manager',
            'section'    => $section,
            'exported'   => current_time( 'mysql' ),
            'fields'     => $custom_fields,
            'field_count' => count( $custom_fields ),
        );
    }
    
    /**
     * Generate export filename.
     *
     * @param string $section Section name.
     * @return string
     */
    private function generate_filename( $section ) {
        $site_name = sanitize_title( get_bloginfo( 'name' ) );
        $date      = date( 'Y-m-d-His' );
        
        return sprintf(
            'scfm-%s-%s-%s.json',
            $site_name,
            $section,
            $date
        );
    }
    
    /**
     * Validate import data.
     *
     * @param array  $data    Import data.
     * @param string $section Section name.
     * @return true|WP_Error
     */
    private function validate_import_data( $data, $section ) {
        // Check required keys
        if ( ! isset( $data['plugin'] ) || $data['plugin'] !== 'Smart Checkout Fields Manager' ) {
            return new WP_Error( 'invalid_plugin', __( 'Invalid plugin identifier.', 'smart-checkout-fields-manager' ) );
        }
        
        if ( ! isset( $data['section'] ) ) {
            return new WP_Error( 'missing_section', __( 'Section information missing.', 'smart-checkout-fields-manager' ) );
        }
        
        if ( $data['section'] !== $section ) {
            return new WP_Error(
                'section_mismatch',
                sprintf(
                    /* translators: 1: imported section, 2: current section */
                    __( 'Section mismatch. File contains %1$s fields, but you are importing to %2$s.', 'smart-checkout-fields-manager' ),
                    $data['section'],
                    $section
                )
            );
        }
        
        if ( ! isset( $data['fields'] ) || ! is_array( $data['fields'] ) ) {
            return new WP_Error( 'invalid_fields', __( 'Invalid fields data.', 'smart-checkout-fields-manager' ) );
        }
        
        if ( empty( $data['fields'] ) ) {
            return new WP_Error( 'empty_fields', __( 'No fields to import.', 'smart-checkout-fields-manager' ) );
        }
        
        // Validate each field structure
        foreach ( $data['fields'] as $field_id => $field ) {
            if ( ! isset( $field['type'] ) || ! isset( $field['label'] ) ) {
                return new WP_Error(
                    'invalid_field_structure',
                    sprintf(
                        /* translators: %s: field ID */
                        __( 'Invalid field structure for field: %s', 'smart-checkout-fields-manager' ),
                        $field_id
                    )
                );
            }
        }
        
        return true;
    }
    
    /**
     * Create backup before import.
     *
     * @param string $section Section name.
     * @return array
     */
    private function create_backup( $section ) {
        // Get all fields for this section
        $fields = SCFM_Field_Manager::get_all_fields( $section );
        
        return array(
            'section'   => $section,
            'timestamp' => current_time( 'mysql' ),
            'fields'    => $fields,
        );
    }
    
    /**
     * Restore backup.
     *
     * @param array  $backup  Backup data.
     * @param string $section Section name.
     * @return bool
     */
    private function restore_backup( $backup, $section ) {
        if ( empty( $backup ) || $backup['section'] !== $section ) {
            return false;
        }
        
        $option_key = 'scfm_custom_fields_' . $section;
        update_option( $option_key, $backup['fields'] );
        
        return true;
    }
    
    /**
     * Import fields.
     *
     * @param array  $data    Import data.
     * @param string $section Section name.
     * @return int|WP_Error Number of fields imported or error.
     */
    private function import_fields( $data, $section ) {
        $fields = $data['fields'];
        $count  = 0;
        
        foreach ( $fields as $field_id => $field ) {
            // Sanitize field data
            $sanitized_field = SCFM_Admin_Settings::sanitize_field_data( $field );
            
            // Save field
            $result = SCFM_Field_Manager::save_field( $section, $field_id, $sanitized_field );
            
            if ( $result ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Export all sections.
     *
     * @return array
     */
    public function export_all_sections() {
        $export_data = array(
            'version'  => SCFM_VERSION,
            'plugin'   => 'Smart Checkout Fields Manager',
            'exported' => current_time( 'mysql' ),
            'sections' => array(),
        );
        
        $sections = array( 'billing', 'shipping', 'order' );
        
        foreach ( $sections as $section ) {
            $export_data['sections'][ $section ] = $this->prepare_export_data( $section );
        }
        
        return $export_data;
    }
}

