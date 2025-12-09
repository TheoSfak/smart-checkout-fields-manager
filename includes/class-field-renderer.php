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
    
    /**
     * Initialize custom field rendering.
     */
    public static function init() {
        // Add custom field type support to WooCommerce
        add_filter( 'woocommerce_form_field_heading', array( __CLASS__, 'render_heading_field' ), 10, 4 );
        add_filter( 'woocommerce_form_field_paragraph', array( __CLASS__, 'render_paragraph_field' ), 10, 4 );
        add_filter( 'woocommerce_form_field_checkboxgroup', array( __CLASS__, 'render_checkbox_group_field' ), 10, 4 );
    }
    
    /**
     * Render heading field.
     *
     * @param string $field      Field HTML.
     * @param string $key        Field key.
     * @param array  $args       Field arguments.
     * @param string $value      Field value.
     * @return string
     */
    public static function render_heading_field( $field, $key, $args, $value ) {
        $field = '<h3 class="scfm-heading ' . esc_attr( implode( ' ', $args['class'] ) ) . '">';
        $field .= esc_html( $args['label'] );
        $field .= '</h3>';
        
        return $field;
    }
    
    /**
     * Render paragraph field.
     *
     * @param string $field      Field HTML.
     * @param string $key        Field key.
     * @param array  $args       Field arguments.
     * @param string $value      Field value.
     * @return string
     */
    public static function render_paragraph_field( $field, $key, $args, $value ) {
        $field = '<div class="scfm-paragraph ' . esc_attr( implode( ' ', $args['class'] ) ) . '">';
        $field .= '<p>' . wp_kses_post( $args['description'] ) . '</p>';
        $field .= '</div>';
        
        return $field;
    }
    
    /**
     * Render checkbox group field.
     *
     * @param string $field      Field HTML.
     * @param string $key        Field key.
     * @param array  $args       Field arguments.
     * @param string $value      Field value.
     * @return string
     */
    public static function render_checkbox_group_field( $field, $key, $args, $value ) {
        $selected = is_array( $value ) ? $value : array();
        
        $field  = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $key ) . '_field">';
        
        if ( $args['required'] ) {
            $args['label'] .= '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
        }
        
        $field .= '<label>' . wp_kses_post( $args['label'] ) . '</label>';
        $field .= '<span class="woocommerce-input-wrapper scfm-checkbox-group">';
        
        if ( ! empty( $args['options'] ) ) {
            foreach ( $args['options'] as $option_key => $option_text ) {
                $checked = in_array( $option_key, $selected ) ? 'checked="checked"' : '';
                $field .= '<label class="checkbox">';
                $field .= '<input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $option_key ) . '" ' . $checked . '> ';
                $field .= esc_html( $option_text );
                $field .= '</label>';
            }
        }
        
        $field .= '</span>';
        $field .= '</p>';
        
        return $field;
    }
}

// Initialize custom field rendering
add_action( 'init', array( 'SCFM_Field_Renderer', 'init' ) );
