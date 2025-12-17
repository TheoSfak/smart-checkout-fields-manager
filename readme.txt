=== Smart Checkout Fields Manager ===
Contributors: irmaiden
Tags: woocommerce, checkout, fields, custom fields, checkout manager
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Requires WooCommerce: 6.0
Tested WooCommerce: 9.5
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Complete solution for customizing WooCommerce checkout fields. Add, edit, remove, and rearrange fields with 20+ field types. Compatible with Classic & Block checkout.

== Description ==

🎯 **The Ultimate WooCommerce Checkout Fields Customization Plugin**

Smart Checkout Fields Manager gives you complete control over your WooCommerce checkout. Add unlimited custom fields, edit default fields, rearrange with drag-and-drop, and choose from 20+ field types. Works seamlessly with both Classic and Block checkout!

= Key Features =

**✅ Complete Checkout Customization**

* Add custom fields to Billing, Shipping, or Additional Information sections
* Edit ALL default WooCommerce fields (label, placeholder, required status, etc.)
* Drag-and-drop field reordering with real-time priority updates
* Enable/disable fields with toggle switches
* Visual distinction between default and custom fields
* Delete protection for required WooCommerce fields

**✅ 20 Field Types for Classic Checkout**

1. **Text** - Single-line text input
2. **Number** - Numeric input with validation
3. **Hidden** - Hidden field for storing data
4. **Password** - Password input field
5. **Email** - Email input with validation
6. **Phone (tel)** - Phone number input with validation
7. **Radio** - Radio button options
8. **Textarea** - Multi-line text area
9. **Select** - Dropdown select menu
10. **Multi-Select** - Multi-selection dropdown
11. **Checkbox** - Single checkbox
12. **Checkbox Group** - Multiple checkboxes returning array
13. **DateTime Local** - Date and time picker
14. **Date** - Date picker
15. **Month** - Month picker
16. **Time** - Time picker
17. **Week** - Week picker
18. **URL** - URL input with validation
19. **Heading** - Display-only section heading (H3)
20. **Paragraph** - Display-only paragraph text

**✅ WooCommerce Block Checkout Support**

* **4 Block-Compatible Field Types:** Text ●, Textarea ●, Checkbox ●, Select ●
* Automatic registration with WooCommerce Blocks API
* Field location mapping (contact, address, order sections)
* Custom styling for block checkout fields
* Validation support
* Requires WooCommerce Blocks 11.0+
* Visual indicator (● blue dot) in admin for block-compatible fields

**✅ Comprehensive Field Validation**

Built-in Type Validation:
* **Number**: Numeric-only input
* **Email**: Valid email format
* **Phone**: 7-15 digit phone validation
* **URL**: Valid URL format

Custom Validation Rules:
* **Postcode**: Postal code format (3-10 alphanumeric)
* **State**: Valid WooCommerce state code
* **Phone (strict)**: Strict international phone format

Features:
* Multiple validation rules per field
* Custom error messages with field labels
* Server-side validation on checkout
* Developer hook for custom validation rules

**✅ Field Visibility Controls**

Granular control over where fields appear:
* **Order Details Page** - Show on customer order details and thank you page
* **Admin Emails** - Include in admin notification emails
* **Customer Emails** - Include in customer order emails

All visibility settings respected across:
* Admin order edit page
* Customer My Account → Order Details
* Thank You page
* New Order emails (admin)
* Processing Order emails (customer)
* Completed Order emails (customer)

**✅ Order Integration**

* Automatic field value saving on checkout
* Type-based sanitization (email, tel, number, url, textarea, arrays)
* Field label storage for proper display
* Support for array values (multi-select, checkbox group)
* Display in admin order page (billing & shipping sections)
* Display in customer order details page
* Display in WooCommerce emails
* Proper formatting for arrays, dates, URLs, multi-line text

**✅ One-Click Reset Feature**

* Reset individual sections (Billing, Shipping, or Additional)
* Delete all custom fields in selected section
* Restore default WooCommerce field configurations
* Confirmation dialog with safety check
* Success notifications

**✅ Translation Ready**

* All strings wrapped in translation functions
* Text domain: `smart-checkout-fields-manager`
* Compatible with Loco Translate, WPML, and Polylang
* RTL language support
* Ready for community translations

**✅ Developer-Friendly**

Powerful hooks for developers:
* Actions: `scfm_init`, `scfm_field_deleted`, `scfm_after_field_save`, `scfm_validate_field`
* Filters: `scfm_sanitize_field_data`, `scfm_checkout_fields`, `scfm_field_config`, `scfm_field_value`
* Full documentation in code
* Easy to extend with custom field types

= Use Cases =

* **E-Commerce Stores**: Collect delivery instructions, gift messages, company VAT numbers
* **B2B Shops**: Add business license fields, tax IDs, purchase order numbers
* **Subscription Services**: Collect subscription preferences, renewal dates
* **Event Tickets**: Add attendee information, dietary requirements
* **Custom Products**: Collect personalization details, custom specifications
* **Multi-Vendor**: Add vendor-specific information fields

= Why Choose Smart Checkout Fields Manager? =

✓ **Both Classic & Block Checkout** - The only plugin supporting both seamlessly
✓ **20+ Field Types** - Most comprehensive field type selection
✓ **Drag & Drop** - Intuitive interface for field management
✓ **No Coding Required** - User-friendly admin interface
✓ **Developer Friendly** - Extensive hooks and filters
✓ **Translation Ready** - Works in any language
✓ **Free Forever** - Core features always free
✓ **Active Development** - Regular updates and improvements

= Premium Features (Coming Soon) =

* Conditional Logic - Show/hide fields based on other field values
* Field Dependencies - Make fields required based on conditions
* Import/Export - Backup and share field configurations
* Field Templates - Pre-built field sets for common scenarios
* Advanced Field Types - Signature, File Upload, Color Picker, Range Slider
* Multi-Step Checkout - Split checkout into multiple steps
* Field Analytics - Track field completion rates
* Priority Support - Fast email support

**Want these features now?** [Support development](https://www.paypal.com/donate?business=theodore.sfakianakis@gmail.com) to help prioritize!

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "Smart Checkout Fields Manager"
4. Click "Install Now" then "Activate"
5. Go to WooCommerce → Checkout Fields to start customizing

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins → Add New → Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. After installation, click "Activate Plugin"
6. Go to WooCommerce → Checkout Fields

= After Activation =

1. Navigate to **WooCommerce → Checkout Fields**
2. Choose a section: Billing, Shipping, or Additional
3. Click "Add New Field" to create custom fields
4. Or edit existing fields by clicking the edit icon
5. Drag fields to reorder them
6. Save your changes

== Frequently Asked Questions ==

= Does this work with WooCommerce Block Checkout? =

Yes! Smart Checkout Fields Manager supports both Classic Checkout (shortcode-based) and Block Checkout (Gutenberg blocks). Currently, 4 field types are compatible with Block Checkout: Text, Textarea, Checkbox, and Select. More block-compatible field types coming soon!

= Can I edit default WooCommerce fields? =

Absolutely! You can edit labels, placeholders, required status, CSS classes, width, and validation rules for all default WooCommerce fields (first name, last name, email, address, etc.). Required fields are protected from deletion to maintain checkout functionality.

= How many custom fields can I add? =

Unlimited! Add as many custom fields as you need to any section (Billing, Shipping, or Additional Information).

= Will custom fields appear in emails? =

Yes! You have full control over visibility. For each field, you can choose whether it appears in:
* Admin order emails
* Customer order emails
* Order details page
* Thank you page

= Can I reorder fields? =

Yes! Use drag-and-drop to reorder fields in the admin interface. Priority numbers update automatically, and changes are reflected immediately on the checkout page.

= What field types are supported? =

**Classic Checkout (20 types):** Text, Number, Email, Phone, URL, Password, Hidden, Textarea, Select, Multi-Select, Radio, Checkbox, Checkbox Group, Date, Time, DateTime, Month, Week, Heading, Paragraph

**Block Checkout (4 types):** Text, Textarea, Checkbox, Select

= Does it support field validation? =

Yes! Built-in validation for email, phone, number, URL, and custom validation rules for postcode, state, and strict phone formats. You can add multiple validation rules per field with custom error messages.

= Is it translation ready? =

Yes! The plugin is fully translation-ready with:
* Text domain: `smart-checkout-fields-manager`
* Compatible with Loco Translate, WPML, and Polylang
* RTL language support
* All strings wrapped in translation functions

= Will it slow down my checkout? =

No! The plugin is optimized for performance with:
* Minimal JavaScript (jQuery-based, no heavy frameworks)
* Efficient database queries
* Cached field configurations
* No external API calls

= Can developers extend it? =

Absolutely! The plugin provides extensive hooks:
* Actions: `scfm_init`, `scfm_field_deleted`, `scfm_after_field_save`
* Filters: `scfm_checkout_fields`, `scfm_field_config`, `scfm_field_value`
* Well-documented code
* Developer-friendly architecture

= Is it compatible with other plugins? =

Smart Checkout Fields Manager is designed to work with:
* All WooCommerce themes
* Popular page builders (Elementor, Divi, etc.)
* Checkout optimization plugins
* Translation plugins (WPML, Polylang)
* Most WooCommerce extensions

If you experience compatibility issues, please report them on our [GitHub repository](https://github.com/TheoSfak/smart-checkout-fields-manager/issues).

= How do I reset fields to default? =

Use the "Reset Section" button at the top of each section (Billing, Shipping, Additional). This will:
* Delete all custom fields in that section
* Restore default WooCommerce field configurations
* Show a confirmation dialog before resetting

= Where is field data stored? =

Custom field configurations are stored in WordPress options:
* `scfm_billing_fields`
* `scfm_shipping_fields`
* `scfm_additional_fields`

Field values from checkout are stored in WooCommerce order meta, just like default fields.

= How do I uninstall the plugin? =

Simply deactivate and delete the plugin from WordPress admin. The `uninstall.php` file will automatically:
* Remove all plugin options
* Clean up database entries
* Remove custom field data

Default WooCommerce fields remain unaffected.

== Screenshots ==

1. Admin interface - Manage all checkout fields with drag-and-drop
2. Add new field - Choose from 20+ field types with comprehensive options
3. Edit field - Configure labels, validation, visibility, and more
4. Billing fields - Customize billing section fields
5. Shipping fields - Manage shipping section fields
6. Additional fields - Add custom fields to additional information section
7. Checkout page - Custom fields displayed on WooCommerce checkout
8. Order details - Custom field values displayed in admin order page
9. Customer email - Custom fields included in order emails

== Changelog ==

= 1.0.1 (2025-01-17) =
* Improved: Code quality and WordPress coding standards compliance
* Improved: Security enhancements for field sanitization
* Improved: Performance optimizations for field rendering
* Fixed: Minor UI inconsistencies in admin interface
* Updated: Documentation and inline comments

= 1.0.0 (2024-12-15) =
* Initial release
* 20 field types for Classic Checkout
* 4 field types for Block Checkout
* Drag-and-drop field reordering
* Comprehensive field validation
* Visibility controls (orders, emails, pages)
* Edit default WooCommerce fields
* One-click section reset
* Translation ready
* Developer hooks and filters

== Upgrade Notice ==

= 1.0.1 =
Recommended update with improved security, performance, and code quality. No breaking changes.

= 1.0.0 =
Initial release - The complete WooCommerce checkout fields customization solution!

== Developer Documentation ==

= Available Hooks =

**Actions:**
```php
// Plugin initialization
do_action( 'scfm_init' );

// After field deletion
do_action( 'scfm_field_deleted', $section, $field_id );

// After field value saved to order
do_action( 'scfm_after_field_save', $field_id, $value, $order_id );

// Custom field validation
do_action( 'scfm_validate_field', $field_id, $field, $value, $errors );
```

**Filters:**
```php
// Modify sanitized field data
$sanitized = apply_filters( 'scfm_sanitize_field_data', $sanitized, $data );

// Modify default WooCommerce fields
$fields = apply_filters( 'scfm_default_woocommerce_fields', $fields, $section );

// Modify checkout fields before rendering
$fields = apply_filters( 'scfm_checkout_fields', $fields );

// Modify individual field configuration
$wc_field = apply_filters( 'scfm_field_config', $wc_field, $field_id, $field );

// Modify field value before saving
$value = apply_filters( 'scfm_field_value', $value, $field_id, $field, $order_id );
```

= Adding Custom Field Types =

Use the `scfm_field_config` filter to add custom field types:

```php
add_filter( 'scfm_field_config', 'my_custom_field_type', 10, 3 );
function my_custom_field_type( $wc_field, $field_id, $field ) {
    if ( $field['type'] === 'my_custom_type' ) {
        $wc_field['type'] = 'text';
        $wc_field['custom_attributes'] = array(
            'data-custom-type' => 'my_custom_type'
        );
    }
    return $wc_field;
}
```

= Custom Validation Rules =

Add custom validation using the `scfm_validate_field` action:

```php
add_action( 'scfm_validate_field', 'my_custom_validation', 10, 4 );
function my_custom_validation( $field_id, $field, $value, $errors ) {
    if ( $field['type'] === 'text' && strlen( $value ) < 5 ) {
        $errors->add( 'validation', 'Minimum 5 characters required!' );
    }
}
```

Full developer documentation available on [GitHub](https://github.com/TheoSfak/smart-checkout-fields-manager).

== Support ==

* 📧 **Email**: theodore.sfakianakis@gmail.com
* 🐛 **Bug Reports**: [GitHub Issues](https://github.com/TheoSfak/smart-checkout-fields-manager/issues)
* 💬 **Questions**: [GitHub Discussions](https://github.com/TheoSfak/smart-checkout-fields-manager/discussions)
* 💰 **Donate**: [PayPal](https://www.paypal.com/donate?business=theodore.sfakianakis@gmail.com)

== Donations ==

If this plugin helped your WooCommerce store, consider supporting its development:

[![Donate with PayPal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/donate?business=theodore.sfakianakis@gmail.com)

**Why donate?**
* ☕ Buy me a coffee
* 🚀 Fund premium features development
* 🐛 Faster bug fixes and support
* 📚 Better documentation
* ❤️ Show appreciation

Every contribution helps keep this plugin free and actively maintained!

---

**Made with ❤️ by Theodore Sfakianakis (irmaiden)**
