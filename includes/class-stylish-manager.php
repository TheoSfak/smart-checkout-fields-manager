<?php
/**
 * Stylish Manager - Handles frontend styling
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Stylish Manager class.
 */
class SCFM_Stylish_Manager {
    
    /**
     * Single instance of the class.
     *
     * @var SCFM_Stylish_Manager
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class.
     *
     * @return SCFM_Stylish_Manager
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
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_head', array( $this, 'output_custom_styles' ), 999 );
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
    }
    
    /**
     * Enqueue stylish styles.
     */
    public function enqueue_styles() {
        if ( ! is_checkout() && ! is_account_page() ) {
            return;
        }
        
        wp_enqueue_style(
            'scfm-stylish-frontend',
            SCFM_PLUGIN_URL . 'assets/css/stylish-frontend.css',
            array(),
            SCFM_VERSION
        );
        
        // Enqueue Google Fonts if needed
        $options = get_option( 'scfm_stylish_options', array() );
        $font_family = isset( $options['font_family'] ) ? $options['font_family'] : 'default';
        
        if ( $font_family !== 'default' ) {
            $this->enqueue_google_font( $font_family );
        }
    }
    
    /**
     * Enqueue Google Font.
     *
     * @param string $font Font family name.
     */
    private function enqueue_google_font( $font ) {
        $font_urls = array(
            'inter'      => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
            'roboto'     => 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
            'opensans'   => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap',
            'lato'       => 'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap',
            'montserrat' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap',
            'poppins'    => 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
        );
        
        if ( isset( $font_urls[ $font ] ) ) {
            wp_enqueue_style( 'scfm-google-font-' . $font, $font_urls[ $font ], array(), SCFM_VERSION );
        }
    }
    
    /**
     * Output custom inline styles based on settings.
     */
    public function output_custom_styles() {
        if ( ! is_checkout() && ! is_account_page() ) {
            return;
        }
        
        $options = get_option( 'scfm_stylish_options', array() );
        $power_beautify = isset( $options['power_beautify'] ) && $options['power_beautify'];
        
        // If power beautify is enabled, use preset values
        if ( $power_beautify ) {
            $options = array_merge( $this->get_power_beautify_defaults(), $options );
        }
        
        $css = $this->generate_custom_css( $options );
        
        if ( ! empty( $css ) ) {
            echo '<style id="scfm-stylish-custom">' . esc_html( wp_strip_all_tags( $css ) ) . '</style>';
        }
    }
    
    /**
     * Get Power Beautify default values.
     *
     * @return array
     */
    private function get_power_beautify_defaults() {
        return array(
            'primary_color'        => '#667eea',
            'background_color'     => '#ffffff',
            'text_color'           => '#1a202c',
            'label_color'          => '#667eea',
            'border_radius'        => '16',
            'shadow'               => 'glow',
            'hover_effect'         => true,
            'focus_effect'         => 'glow',
            'font_family'          => 'poppins',
            'font_size'            => '16',
            'font_weight'          => '500',
            'placeholder_color'    => '#94a3b8',
            'placeholder_italic'   => true,
            'button_style'         => true,
            'button_accent'        => '#f093fb',
            'entrance_animation'   => 'bounce',
            'transition_speed'     => 'normal',
        );
    }
    
    /**
     * Generate custom CSS from options.
     *
     * @param array $options Stylish options.
     * @return string CSS code.
     */
    private function generate_custom_css( $options ) {
        $css = '';
        
        // Primary color
        if ( ! empty( $options['primary_color'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text:focus,';
            $css .= '.woocommerce form .form-row textarea:focus,';
            $css .= '.woocommerce form .form-row select:focus {';
            $css .= 'border-color: ' . esc_attr( $options['primary_color'] ) . ' !important;';
            $css .= '}';
        }
        
        // Background color
        if ( ! empty( $options['background_color'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text,';
            $css .= '.woocommerce form .form-row textarea,';
            $css .= '.woocommerce form .form-row select {';
            $css .= 'background-color: ' . esc_attr( $options['background_color'] ) . ' !important;';
            $css .= '}';
        }
        
        // Text color
        if ( ! empty( $options['text_color'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text,';
            $css .= '.woocommerce form .form-row textarea,';
            $css .= '.woocommerce form .form-row select {';
            $css .= 'color: ' . esc_attr( $options['text_color'] ) . ' !important;';
            $css .= '}';
        }
        
        // Label color
        if ( ! empty( $options['label_color'] ) ) {
            $css .= '.woocommerce form .form-row label {';
            $css .= 'color: ' . esc_attr( $options['label_color'] ) . ' !important;';
            $css .= '}';
        }
        
        // Border radius
        if ( isset( $options['border_radius'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text,';
            $css .= '.woocommerce form .form-row textarea,';
            $css .= '.woocommerce form .form-row select {';
            $css .= 'border-radius: ' . intval( $options['border_radius'] ) . 'px !important;';
            $css .= '}';
        }
        
        // Font size
        if ( isset( $options['font_size'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text,';
            $css .= '.woocommerce form .form-row textarea,';
            $css .= '.woocommerce form .form-row select {';
            $css .= 'font-size: ' . intval( $options['font_size'] ) . 'px !important;';
            $css .= '}';
        }
        
        // Font weight
        if ( ! empty( $options['font_weight'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text,';
            $css .= '.woocommerce form .form-row textarea,';
            $css .= '.woocommerce form .form-row select {';
            $css .= 'font-weight: ' . intval( $options['font_weight'] ) . ' !important;';
            $css .= '}';
        }
        
        // Placeholder color
        if ( ! empty( $options['placeholder_color'] ) ) {
            $css .= '.woocommerce form .form-row input.input-text::placeholder,';
            $css .= '.woocommerce form .form-row textarea::placeholder {';
            $css .= 'color: ' . esc_attr( $options['placeholder_color'] ) . ' !important;';
            $css .= '}';
        }
        
        // Placeholder italic
        if ( isset( $options['placeholder_italic'] ) && $options['placeholder_italic'] ) {
            $css .= '.woocommerce form .form-row input.input-text::placeholder,';
            $css .= '.woocommerce form .form-row textarea::placeholder {';
            $css .= 'font-style: italic !important;';
            $css .= '}';
        }
        
        // Button accent color
        if ( ! empty( $options['button_accent'] ) ) {
            $css .= '.scfm-button-style .woocommerce form .form-row input[type="checkbox"]:checked + label,';
            $css .= '.scfm-button-style .woocommerce form .form-row input[type="radio"]:checked + label {';
            $css .= 'background-color: ' . esc_attr( $options['button_accent'] ) . ' !important;';
            $css .= 'border-color: ' . esc_attr( $options['button_accent'] ) . ' !important;';
            $css .= '}';
        }
        
        return $css;
    }
    
    /**
     * Add body classes based on stylish options.
     *
     * @param array $classes Body classes.
     * @return array
     */
    public function add_body_classes( $classes ) {
        if ( ! is_checkout() && ! is_account_page() ) {
            return $classes;
        }
        
        $options = get_option( 'scfm_stylish_options', array() );
        
        // Power beautify
        if ( isset( $options['power_beautify'] ) && $options['power_beautify'] ) {
            $classes[] = 'scfm-power-beautify';
        }
        
        // Shadow
        if ( ! empty( $options['shadow'] ) ) {
            $classes[] = 'scfm-shadow-' . sanitize_html_class( $options['shadow'] );
        }
        
        // Focus effect
        if ( ! empty( $options['focus_effect'] ) ) {
            $classes[] = 'scfm-focus-' . sanitize_html_class( $options['focus_effect'] );
        }
        
        // Font family
        if ( ! empty( $options['font_family'] ) && $options['font_family'] !== 'default' ) {
            $classes[] = 'scfm-font-' . sanitize_html_class( $options['font_family'] );
        }
        
        // Button style
        if ( isset( $options['button_style'] ) && $options['button_style'] ) {
            $classes[] = 'scfm-button-style';
        }
        
        // Entrance animation
        if ( ! empty( $options['entrance_animation'] ) && $options['entrance_animation'] !== 'none' ) {
            $classes[] = 'scfm-entrance-' . sanitize_html_class( $options['entrance_animation'] );
        }
        
        // Transition speed
        if ( ! empty( $options['transition_speed'] ) ) {
            $classes[] = 'scfm-transition-' . sanitize_html_class( $options['transition_speed'] );
        }
        
        return $classes;
    }
}
