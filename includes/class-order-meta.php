<?php
/**
 * Order Meta Handler - Saves and displays custom field data in orders
 *
 * @package Smart_Checkout_Fields_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Order Meta class.
 */
class SCFM_Order_Meta {

    /**
     * Single instance of the class.
     *
     * @var SCFM_Order_Meta
     */
    private static $instance = null;

    /**
     * Get single instance of the class.
     *
     * @return SCFM_Order_Meta
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
        // Save custom field values
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_field_values' ), 10, 2 );

        // Display in admin order page
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_billing_fields_admin' ), 10, 1 );
        add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'display_shipping_fields_admin' ), 10, 1 );

        // Display in customer order details
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_fields_customer' ), 10, 1 );

        // Display in emails
        add_action( 'woocommerce_email_after_order_table', array( $this, 'display_fields_email' ), 10, 4 );
    }

    /**
     * Save custom field values to order meta.
     *
     * @param int   $order_id Order ID.
     * @param array $posted   Posted data.
     */
    public function save_custom_field_values( $order_id, $posted ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Get all custom fields
        $sections = array( 'billing', 'shipping', 'order' );

        foreach ( $sections as $section ) {
            $fields = SCFM_Field_Manager::get_fields( $section );

            foreach ( $fields as $field_id => $field ) {
                // Skip default WooCommerce fields
                if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
                    continue;
                }

                // Skip disabled fields
                if ( isset( $field['enabled'] ) && ! $field['enabled'] ) {
                    continue;
                }

                // Get posted value
                // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified by WooCommerce checkout process, sanitized via sanitize_field_value() below
                $value = isset( $_POST[ $field_id ] ) ? wp_unslash( $_POST[ $field_id ] ) : '';

                // Sanitize based on field type
                $value = $this->sanitize_field_value( $value, $field['type'] );

                // Apply filter
                $value = apply_filters( 'scfm_field_value', $value, $field_id, $field, $order_id );

                // Save to order meta
                if ( ! empty( $value ) || $value === '0' ) {
                    $order->update_meta_data( $field_id, $value );

                    // Also save the field label for display
                    $order->update_meta_data( '_scfm_' . $field_id . '_label', $field['label'] );
                }

                // Hook after saving
                do_action( 'scfm_after_field_save', $field_id, $value, $order_id );
            }
        }

        $order->save();
    }

    /**
     * Sanitize field value based on type.
     *
     * @param mixed  $value Field value.
     * @param string $type  Field type.
     * @return mixed
     */
    private function sanitize_field_value( $value, $type ) {
        // Handle array values (checkboxgroup, multiselect)
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }

        switch ( $type ) {
            case 'email':
                return sanitize_email( $value );

            case 'tel':
            case 'phone':
                return sanitize_text_field( $value );

            case 'number':
                return is_numeric( $value ) ? floatval( $value ) : '';

            case 'url':
                return esc_url_raw( $value );

            case 'textarea':
                return sanitize_textarea_field( $value );

            case 'checkboxgroup':
                return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();

            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Display billing custom fields in admin order page.
     *
     * @param WC_Order $order Order object.
     */
    public function display_billing_fields_admin( $order ) {
        $this->display_fields_admin( $order, 'billing', __( 'Billing Custom Fields', 'fieldora-checkout-for-woo' ) );
    }

    /**
     * Display shipping custom fields in admin order page.
     *
     * @param WC_Order $order Order object.
     */
    public function display_shipping_fields_admin( $order ) {
        $this->display_fields_admin( $order, 'shipping', __( 'Shipping Custom Fields', 'fieldora-checkout-for-woo' ) );
    }

    /**
     * Display custom fields in admin order page.
     *
     * @param WC_Order $order   Order object.
     * @param string   $section Section name.
     * @param string   $title   Section title.
     */
    private function display_fields_admin( $order, $section, $title ) {
        $fields = SCFM_Field_Manager::get_fields( $section );
        $has_data = false;

        // Check if we have any data
        foreach ( $fields as $field_id => $field ) {
            // Skip default WooCommerce fields
            if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
                continue;
            }

            $value = $order->get_meta( $field_id );
            if ( ! empty( $value ) || $value === '0' ) {
                $has_data = true;
                break;
            }
        }

        if ( ! $has_data ) {
            return;
        }

        echo '<div class="scfm-admin-order-fields">';
        echo '<h3>' . esc_html( $title ) . '</h3>';

        foreach ( $fields as $field_id => $field ) {
            // Skip default WooCommerce fields
            if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
                continue;
            }

            $value = $order->get_meta( $field_id );

            // Skip if empty and not zero
            if ( empty( $value ) && $value !== '0' ) {
                continue;
            }

            // Check visibility
            if ( isset( $field['visibility']['order_details'] ) && ! $field['visibility']['order_details'] ) {
                continue;
            }

            $label = $order->get_meta( '_scfm_' . $field_id . '_label' );
            if ( empty( $label ) ) {
                $label = $field['label'];
            }

            // Format value
            $formatted_value = $this->format_field_value( $value, $field['type'] );
            echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . wp_kses_post( $formatted_value ) . '</p>';
        }

        echo '</div>';
    }

    /**
     * Display custom fields in customer order details.
     *
     * @param WC_Order $order Order object.
     */
    public function display_fields_customer( $order ) {
        $sections = array( 'billing', 'shipping', 'order' );
        $output = '';

        foreach ( $sections as $section ) {
            $fields = SCFM_Field_Manager::get_fields( $section );

            foreach ( $fields as $field_id => $field ) {
                // Skip default WooCommerce fields
                if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
                    continue;
                }

                $value = $order->get_meta( $field_id );

                if ( empty( $value ) && $value !== '0' ) {
                    continue;
                }

                // Check visibility
                if ( isset( $field['visibility']['order_details'] ) && ! $field['visibility']['order_details'] ) {
                    continue;
                }

                $label = $order->get_meta( '_scfm_' . $field_id . '_label' );
                if ( empty( $label ) ) {
                    $label = $field['label'];
                }

                $formatted_value = $this->format_field_value( $value, $field['type'] );
                $output .= '<tr><th>' . esc_html( $label ) . ':</th><td>' . wp_kses_post( $formatted_value ) . '</td></tr>';
            }
        }

        if ( ! empty( $output ) ) {
            echo '<h2>' . esc_html__( 'Additional Information', 'fieldora-checkout-for-woo' ) . '</h2>';
            echo '<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">';
            echo wp_kses_post( $output );
            echo '</table>';
        }
    }

    /**
     * Display custom fields in emails.
     *
     * @param WC_Order $order         Order object.
     * @param bool     $sent_to_admin Sent to admin.
     * @param bool     $plain_text    Plain text email.
     * @param WC_Email $email         Email object.
     */
    public function display_fields_email( $order, $sent_to_admin, $plain_text, $email ) {
        $sections = array( 'billing', 'shipping', 'order' );
        $output = '';

        foreach ( $sections as $section ) {
            $fields = SCFM_Field_Manager::get_fields( $section );

            foreach ( $fields as $field_id => $field ) {
                // Skip default WooCommerce fields
                if ( isset( $field['default_wc'] ) && $field['default_wc'] ) {
                    continue;
                }

                $value = $order->get_meta( $field_id );

                if ( empty( $value ) && $value !== '0' ) {
                    continue;
                }

                // Check visibility based on email type
                $show = false;
                if ( $sent_to_admin && isset( $field['visibility']['admin_emails'] ) && $field['visibility']['admin_emails'] ) {
                    $show = true;
                } elseif ( ! $sent_to_admin && isset( $field['visibility']['customer_emails'] ) && $field['visibility']['customer_emails'] ) {
                    $show = true;
                }

                if ( ! $show ) {
                    continue;
                }

                $label = $order->get_meta( '_scfm_' . $field_id . '_label' );
                if ( empty( $label ) ) {
                    $label = $field['label'];
                }

                $formatted_value = $this->format_field_value( $value, $field['type'] );
                if ( $plain_text ) {
                    $output .= $label . ': ' . $formatted_value . "\n";
                } else {
                    $output .= '<p><strong>' . esc_html( $label ) . ':</strong> ' . wp_kses_post( $formatted_value ) . '</p>';
                }
            }
        }

        if ( ! empty( $output ) ) {
            if ( $plain_text ) {
                echo "\n" . esc_html__( 'Additional Information:', 'fieldora-checkout-for-woo' ) . "\n";
                echo wp_kses_post( $output ) . "\n";
            } else {
                echo '<h2>' . esc_html__( 'Additional Information', 'fieldora-checkout-for-woo' ) . '</h2>';
                echo wp_kses_post( $output );
            }
        }
    }

    /**
     * Format field value for display.
     *
     * @param mixed  $value Field value.
     * @param string $type  Field type.
     * @return string
     */
    private function format_field_value( $value, $type ) {
        switch ( $type ) {
            case 'checkboxgroup':
            case 'multiselect':
                if ( is_array( $value ) ) {
                    return implode( ', ', array_map( 'esc_html', $value ) );
                }
                return esc_html( $value );

            case 'textarea':
                return nl2br( esc_html( $value ) );

            case 'date':
            case 'datetime-local':
                if ( ! empty( $value ) ) {
                    return date_i18n( get_option( 'date_format' ), strtotime( $value ) );
                }
                return esc_html( $value );

            case 'url':
                return '<a href="' . esc_url( $value ) . '" target="_blank">' . esc_html( $value ) . '</a>';

            default:
                return esc_html( $value );
        }
    }
}
