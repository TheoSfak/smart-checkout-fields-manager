<?php
/**
 * Field Manager - Core functionality for managing custom fields
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Field Manager class.
 */
class SCFM_Field_Manager {
    
    /**
     * Option name for storing custom fields.
     */
    const OPTION_NAME = 'scfm_custom_fields';
    
    /**
     * Get all custom fields for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     * @return array
     */
    public static function get_fields( $section = '' ) {
        $all_fields = get_option( self::OPTION_NAME, array() );
        
        if ( empty( $section ) ) {
            return $all_fields;
        }
        
        return isset( $all_fields[ $section ] ) ? $all_fields[ $section ] : array();
    }
    
    /**
     * Save fields for a section.
     *
     * @param string $section Section name (billing, shipping, order).
     * @param array  $fields  Fields data.
     * @return bool
     */
    public static function save_fields( $section, $fields ) {
        $all_fields = get_option( self::OPTION_NAME, array() );
        $all_fields[ $section ] = $fields;
        
        return update_option( self::OPTION_NAME, $all_fields );
    }
    
    /**
     * Get a single field by ID.
     *
     * @param string $section  Section name (billing, shipping, order).
     * @param string $field_id Field ID.
     * @return array|null
     */
    public static function get_field( $section, $field_id ) {
        $fields = self::get_fields( $section );
        
        return isset( $fields[ $field_id ] ) ? $fields[ $field_id ] : null;
    }
    
    /**
     * Add or update a field.
     *
     * @param string $section    Section name (billing, shipping, order).
     * @param string $field_id   Field ID.
     * @param array  $field_data Field data.
     * @return bool
     */
    public static function save_field( $section, $field_id, $field_data ) {
        $fields = self::get_fields( $section );
        $fields[ $field_id ] = $field_data;
        
        return self::save_fields( $section, $fields );
    }
    
    /**
     * Delete a field.
     *
     * @param string $section  Section name (billing, shipping, order).
     * @param string $field_id Field ID.
     * @return bool
     */
    public static function delete_field( $section, $field_id ) {
        $fields = self::get_fields( $section );
        
        if ( isset( $fields[ $field_id ] ) ) {
            unset( $fields[ $field_id ] );
            return self::save_fields( $section, $fields );
        }
        
        return false;
    }
    
    /**
     * Reset all fields for a section to defaults.
     *
     * @param string $section Section name (billing, shipping, order).
     * @return bool
     */
    public static function reset_fields( $section ) {
        return self::save_fields( $section, array() );
    }
    
    /**
     * Get default field structure.
     *
     * @return array
     */
    public static function get_default_field_structure() {
        return array(
            'type'               => 'text',
            'label'              => '',
            'placeholder'        => '',
            'default'            => '',
            'required'           => false,
            'enabled'            => true,
            'class'              => array(),
            'validate'           => array(),
            'priority'           => 100,
            'custom'             => true,
            'visibility'         => array(
                'order_details'   => true,
                'admin_emails'    => true,
                'customer_emails' => true,
            ),
        );
    }
}
