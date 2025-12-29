# Smart Checkout Fields Manager

**Version:** 1.0.0  
**Author:** Theo Sfakianakis  
**License:** GPL-2.0+  
**Requires at least:** WordPress 5.8  
**Tested up to:** WordPress 6.7  
**Requires WooCommerce:** 7.0+  
**Tested up to WooCommerce:** 9.5  
**Stable tag:** 1.0.0

## Description

A complete solution for customizing WooCommerce checkout fields. Add, edit, remove, and rearrange fields with 20+ field types. Manage default WooCommerce fields, add custom validation, control visibility, and integrate seamlessly with orders and emails.

**Note:** This plugin currently supports Classic WooCommerce checkout only. Block checkout support is planned for a future release.

## Features

### ✅ Complete Checkout Field Customization
- Add custom fields to Billing, Shipping, or Additional Information sections
- Edit all default WooCommerce fields (label, placeholder, required status, etc.)
- Delete protection for required WooCommerce fields
- Drag-and-drop field reordering with real-time priority updates
- Enable/disable fields with toggle switches
- Visual distinction between default and custom fields

### ✅ 20 Field Types for Classic Checkout
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

### ✅ Comprehensive Field Validation
**Built-in Type Validation:**
- **Number**: Numeric-only input
- **Email**: Valid email format
- **Phone**: 7-15 digit phone validation
- **URL**: Valid URL format

**Custom Validation Rules:**
- **Postcode**: Postal code format (3-10 alphanumeric)
- **State**: Valid WooCommerce state code
- **Phone (strict)**: Strict international phone format

**Features:**
- Multiple validation rules per field
- Custom error messages with field labels
- Server-side validation on checkout
- Developer hook for custom validation rules

### ✅ Field Visibility Controls
Granular control over where fields appear:
- **Order Details Page** - Show on customer order details and thank you page
- **Admin Emails** - Include in admin notification emails
- **Customer Emails** - Include in customer order emails

All visibility settings are respected across:
- Admin order edit page
- Customer My Account > Order Details
- Thank You page
- New Order emails (admin)
- Processing Order emails (customer)
- Completed Order emails (customer)

### ✅ Default WooCommerce Fields Management
**Editable Properties:**
- Label text
- Placeholder text
- Required status
- Enabled/Disabled status
- Field priority (ordering)
- CSS classes
- Width (full/half)
- Validation rules

**Protected Fields:**
- Cannot delete required WooCommerce fields
- Visual indication when editing default fields
- Type change prevented for default fields

### ✅ Order Integration
**Checkout to Order:**
- Automatic field value saving on checkout
- Type-based sanitization (email, tel, number, url, textarea, arrays)
- Field label storage for proper display
- Support for array values (multi-select, checkbox group)

**Order Display:**
- Display in admin order page (billing & shipping sections)
- Display in customer order details page
- Display in WooCommerce emails
- Proper formatting for arrays, dates, URLs, multi-line text

### ✅ One-Click Reset Feature
- Reset individual sections (Billing, Shipping, or Additional)
- Delete all custom fields in selected section
- Restore default WooCommerce field configurations
- Confirmation dialog with safety check
- Success notifications

### ✅ Translation Ready (i18n)
- All strings wrapped in translation functions
- Text domain: `smart-checkout-fields`
- Ready for .pot file generation
- Compatible with WPML and Polylang
- RTL language support

### ✅ Developer-Friendly Hooks

**Actions:**
```php
// Fires when plugin is initialized
do_action( 'scfm_init' );

// Fires after a field is deleted
do_action( 'scfm_field_deleted', $section, $field_id );

// Fires after field value is saved to order
do_action( 'scfm_after_field_save', $field_id, $value, $order_id );

// Custom validation hook
do_action( 'scfm_validate_field', $field_id, $field, $value, $errors );
```

**Filters:**
```php
// Modify field data after sanitization
apply_filters( 'scfm_sanitize_field_data', $sanitized, $data );

// Modify default WooCommerce fields
apply_filters( 'scfm_default_woocommerce_fields', $fields, $section );

// Modify checkout fields before rendering
apply_filters( 'scfm_checkout_fields', $fields );

// Modify individual field configuration
apply_filters( 'scfm_field_config', $wc_field, $field_id, $field );

// Modify field value before saving to order
apply_filters( 'scfm_field_value', $value, $field_id, $field, $order_id );
```

**Actions:**
- `scfm_before_field_render` - Before field is rendered
- `scfm_after_field_save` - After field is saved
- `scfm_field_deleted` - After field is deleted

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce → Checkout Fields to start customizing

## Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher


## Installation

1. **Upload the plugin:**
   - Upload the `Smart Checkout Fields Manager` folder to `/wp-content/plugins/` directory
   - Or install directly through WordPress admin: Plugins > Add New > Upload Plugin

2. **Activate the plugin:**
   - Navigate to 'Plugins' menu in WordPress
   - Find "Smart Checkout Fields Manager" and click 'Activate'

3. **Configure fields:**
   - Go to WooCommerce > Checkout Fields
   - Select a tab: Billing Fields, Shipping Fields, or Additional Fields
   - Click "Add New Field" to create custom fields
   - Drag and drop to reorder fields
   - Click edit icon to modify existing fields
   - Use toggle switches to enable/disable fields

## Usage

### Adding a Custom Field

1. Navigate to **WooCommerce > Checkout Fields**
2. Select the appropriate tab (Billing, Shipping, or Additional)
3. Click **"Add New Field"** button
4. In the modal, configure:
   - **Field Type** (20 options available)
   - **Label** (required) - Displayed on checkout
   - **Placeholder** - Hint text inside the field
   - **Default Value** - Pre-filled value
   - **Options** - For select, radio, checkbox group (one per line: `value|Label`)
   - **CSS Class** - Full width, Half width (first), or Half width (last)
   - **Priority** - Lower numbers appear first
   - **Validation Rules** - Check applicable rules (email, phone, postcode, etc.)
   - **Required** checkbox
   - **Enabled** checkbox
   - **Visibility** - Order details, admin emails, customer emails
5. Click **"Save Field"**

### Editing Default WooCommerce Fields

1. Find the default field in the list (marked with "default" badge)
2. Click the **edit icon** (pencil)
3. The modal will show "Edit Default WooCommerce Field"
4. You can change:
   - Label
   - Placeholder
   - Required status
   - Enabled/Disabled status
   - Priority (for reordering)
   - Validation rules
   - Visibility settings
5. Note: Field type cannot be changed for default fields
6. Click **"Save Field"**

### Reordering Fields

- **Drag and drop** fields in the table to reorder
- Priority is automatically updated
- Changes are saved immediately

### Deleting Custom Fields

- Click the **trash icon** next to a custom field
- Confirm deletion in the dialog
- Default WooCommerce fields cannot be deleted (no trash icon shown)

### Resetting to Defaults

1. Click **"Reset to Defaults"** button at the top of any tab
2. Confirm the action in the dialog
3. All custom fields in that section will be deleted
4. Default WooCommerce field configurations will be restored

## Frequently Asked Questions

### Can I edit default WooCommerce fields?

Yes! You can edit the label, placeholder, required status, priority, and visibility of all default fields. However, you cannot delete required default fields or change their field type.

### How many field types are supported?

Currently, 20 field types are supported for the classic checkout page:
- Standard inputs: text, number, email, tel, url, password, hidden
- Selection: select, multiselect, radio, checkbox, checkbox group
- Date/time: date, datetime-local, time, month, week
- Text: textarea
- Display-only: heading, paragraph

### Will custom fields appear in order emails?

Yes! You can control visibility for each field:
- Order Details Page (customer)
- Admin Emails
- Customer Emails

Configure these in the field editor modal.

### Can I add validation to fields?

Yes! Select from built-in validation rules:
- Email format
- Phone format (basic and strict)
- Number validation
- URL format
- Postcode format
- State validation

Or use the `scfm_validate_field` action hook to add custom validation.

### Will this work with WooCommerce Blocks?

Not at this time. The plugin is designed for Classic WooCommerce checkout. Block checkout support is planned for a future release.

### Is this plugin translation-ready?

Yes! All strings are wrapped in translation functions. The plugin is ready for translation using WPML, Polylang, Loco Translate, or .po/.mo files (text domain: `smart-checkout-fields`).

### Can developers extend this plugin?

Absolutely! The plugin provides multiple hooks for actions and filters. See the Developer Documentation section below.

### Does this affect site performance?

No. The plugin uses WordPress options API for efficient storage, loads assets only on relevant pages, implements AJAX for smooth interactions, and follows WordPress and WooCommerce coding standards.

## Developer Documentation

### Hook Examples

**Add custom validation:**
```php
add_action( 'scfm_validate_field', 'my_custom_validation', 10, 4 );
function my_custom_validation( $field_id, $field, $value, $errors ) {
    if ( $field_id === 'custom_tax_id' ) {
        if ( ! preg_match( '/^[0-9]{9}$/', $value ) ) {
            $errors->add( $field_id, 'Tax ID must be exactly 9 digits.' );
        }
    }
}
```

**Modify field value before saving:**
```php
add_filter( 'scfm_field_value', 'my_custom_field_value', 10, 4 );
function my_custom_field_value( $value, $field_id, $field, $order_id ) {
    if ( $field_id === 'custom_reference' ) {
        return 'REF-' . $value; // Add prefix
    }
    return $value;
}
```

**Modify checkout fields:**
```php
add_filter( 'scfm_checkout_fields', 'my_custom_checkout_fields', 10, 1 );
function my_custom_checkout_fields( $fields ) {
    // Add a custom class to all billing fields
    if ( isset( $fields['billing'] ) ) {
        foreach ( $fields['billing'] as $key => $field ) {
            $fields['billing'][ $key ]['class'][] = 'my-custom-class';
        }
    }
    return $fields;
}
```

### Field Structure

Custom fields are stored as JSON in `wp_options`:
```php
array(
    'type'        => 'text',
    'label'       => 'Custom Field',
    'placeholder' => 'Enter value',
    'default'     => '',
    'required'    => false,
    'enabled'     => true,
    'priority'    => 100,
    'class'       => array( 'form-row-wide' ),
    'options'     => array( 'key' => 'Label' ),
    'validation'  => array( 'email' ),
    'visibility'  => array(
        'order_details'    => true,
        'admin_emails'     => true,
        'customer_emails'  => true
    ),
    'custom'      => true,
    'default_wc'  => false
)
```

## Changelog

### 1.0.0 - 2025-12-08
- Initial release
- Core plugin structure
- Admin interface foundation
- Field management system
- Ready for Phase 2 implementation

## Support

For support, please open an issue on [GitHub](https://github.com/TheoSfak/smart-checkout-fields-manager)

## License

This plugin is licensed under the GPL-2.0+ license.
