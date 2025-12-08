<?php
/**
 * Field Renderer - Handles rendering of custom field types
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Field Renderer class.
 */
class SCFM_Field_Renderer {
    
    /**
     * Get available field types.
     *
     * @return array
     */
    public static function get_field_types() {
        return array(
            'text'           => __( 'Text', 'smart-checkout-fields' ),
            'number'         => __( 'Number', 'smart-checkout-fields' ),
            'hidden'         => __( 'Hidden', 'smart-checkout-fields' ),
            'password'       => __( 'Password', 'smart-checkout-fields' ),
            'email'          => __( 'Email', 'smart-checkout-fields' ),
            'tel'            => __( 'Phone', 'smart-checkout-fields' ),
            'radio'          => __( 'Radio', 'smart-checkout-fields' ),
            'textarea'       => __( 'Textarea', 'smart-checkout-fields' ),
            'select'         => __( 'Select', 'smart-checkout-fields' ),
            'multiselect'    => __( 'Multi Select', 'smart-checkout-fields' ),
            'checkbox'       => __( 'Checkbox', 'smart-checkout-fields' ),
            'checkboxgroup'  => __( 'Checkbox Group', 'smart-checkout-fields' ),
            'datetime-local' => __( 'DateTime Local', 'smart-checkout-fields' ),
            'date'           => __( 'Date', 'smart-checkout-fields' ),
            'month'          => __( 'Month', 'smart-checkout-fields' ),
            'time'           => __( 'Time', 'smart-checkout-fields' ),
            'week'           => __( 'Week', 'smart-checkout-fields' ),
            'url'            => __( 'URL', 'smart-checkout-fields' ),
            'heading'        => __( 'Heading', 'smart-checkout-fields' ),
            'paragraph'      => __( 'Paragraph', 'smart-checkout-fields' ),
        );
    }
    
    /**
     * Check if field type is supported in block checkout.
     *
     * @param string $type Field type.
     * @return bool
     */
    public static function is_block_compatible( $type ) {
        $block_types = array( 'text', 'select', 'radio', 'checkbox' );
        return in_array( $type, $block_types, true );
    }
}
