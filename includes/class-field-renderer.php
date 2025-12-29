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
            'text'           => __( 'Text', 'fieldora-checkout-for-woo' ),
            'number'         => __( 'Number', 'fieldora-checkout-for-woo' ),
            'hidden'         => __( 'Hidden', 'fieldora-checkout-for-woo' ),
            'password'       => __( 'Password', 'fieldora-checkout-for-woo' ),
            'email'          => __( 'Email', 'fieldora-checkout-for-woo' ),
            'tel'            => __( 'Phone', 'fieldora-checkout-for-woo' ),
            'radio'          => __( 'Radio', 'fieldora-checkout-for-woo' ),
            'textarea'       => __( 'Textarea', 'fieldora-checkout-for-woo' ),
            'select'         => __( 'Select', 'fieldora-checkout-for-woo' ),
            'checkbox'       => __( 'Checkbox', 'fieldora-checkout-for-woo' ),
            'checkboxgroup'  => __( 'Checkbox Group', 'fieldora-checkout-for-woo' ),
            'datetime-local' => __( 'DateTime Local', 'fieldora-checkout-for-woo' ),
            'date'           => __( 'Date', 'fieldora-checkout-for-woo' ),
            'month'          => __( 'Month', 'fieldora-checkout-for-woo' ),
            'time'           => __( 'Time', 'fieldora-checkout-for-woo' ),
            'week'           => __( 'Week', 'fieldora-checkout-for-woo' ),
            'url'            => __( 'URL', 'fieldora-checkout-for-woo' ),
            'multiselect'    => __( 'Multi Select', 'fieldora-checkout-for-woo' ),
            'heading'        => __( 'Heading', 'fieldora-checkout-for-woo' ),
            'paragraph'      => __( 'Paragraph', 'fieldora-checkout-for-woo' ),
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
        add_filter( 'woocommerce_form_field_multiselect', array( __CLASS__, 'render_multiselect_field' ), 10, 4 );
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
            $args['label'] .= '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'fieldora-checkout-for-woo' ) . '">*</abbr>';
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
    
    /**
     * Render multiselect field.
     *
     * @param string $field      Field HTML.
     * @param string $key        Field key.
     * @param array  $args       Field arguments.
     * @param string $value      Field value.
     * @return string
     */
    public static function render_multiselect_field( $field, $key, $args, $value ) {
        $selected = is_array( $value ) ? $value : array();
        
        $field  = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $key ) . '_field">';
        
        if ( $args['required'] ) {
            $args['label'] .= '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'fieldora-checkout-for-woo' ) . '">*</abbr>';
        }
        
        $field .= '<label for="' . esc_attr( $key ) . '">' . wp_kses_post( $args['label'] ) . '</label>';
        $field .= '<span class="woocommerce-input-wrapper">';
        
        // Calculate size based on number of options (min 6, max 12 for better visibility)
        $option_count = ! empty( $args['options'] ) ? count( $args['options'] ) : 6;
        $size = min( max( $option_count, 6 ), 12 );
        
        // Inline styles to force visibility
        $inline_style = 'height: auto !important; min-height: 120px !important; max-height: 200px !important; overflow-y: auto !important; background: white !important; color: black !important; font-size: 15px !important; padding: 8px !important; border: 2px solid #333 !important;';
        
        $field .= '<select name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" class="input-text scfm-multiselect" multiple="multiple" size="' . esc_attr( $size ) . '" aria-label="' . esc_attr( $args['label'] ) . '" style="' . esc_attr( $inline_style ) . '">';
        
        if ( ! empty( $args['options'] ) ) {
            foreach ( $args['options'] as $option_key => $option_text ) {
                $field .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( in_array( $option_key, $selected ), true, false ) . '>';
                $field .= esc_html( $option_text );
                $field .= '</option>';
            }
        }
        
        $field .= '</select>';
        $field .= '</span>';
        $field .= '</p>';
        
        return $field;
    }
}

// Initialize custom field rendering
add_action( 'init', array( 'SCFM_Field_Renderer', 'init' ) );
