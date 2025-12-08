# Smart Checkout Fields Manager

**Version:** 1.0.0  
**Author:** Theo Sfakianakis  
**License:** GPL-2.0+

## Description

Complete solution for customizing WooCommerce checkout fields. Add, edit, remove, and rearrange fields with 20+ field types. Compatible with Classic & Block checkout.

## Features

### ✅ Complete Checkout Field Customization
- Effortlessly add, edit, remove, and rearrange fields
- Drag-and-drop field reordering
- Enable/disable fields with a single toggle

### ✅ 24+ Field Types
Add additional field types to your Checkout page:

**Classic Checkout (20 types):**
1. Text
2. Number
3. Hidden
4. Password
5. Email
6. Phone
7. Radio
8. Textarea
9. Select
10. Multi Select
11. Checkbox
12. Checkbox Group
13. DateTime Local
14. Date
15. Month
16. Time
17. Week
18. URL
19. Heading
20. Paragraph

**Block Checkout (4 types):**
1. Text
2. Select
3. Radio
4. Checkbox

### ✅ Field Validation
- Number: Restricts input to numerical values
- Email: Ensures correct email formatting
- Phone: Validate phone number input
- State & Postcode: Checks location-based details
- URL: Allows only properly formatted web addresses

### ✅ Manage Custom Field Visibility
- Control visibility on Order Details Page
- Control visibility in Admin emails
- Control visibility in Customer emails
- Separate settings for Block checkout

### ✅ Prevent Address Field Overrides
- Prevent WooCommerce from changing address format based on countries
- Use values set in the plugin instead of country-based defaults
- Include custom fields in formatted addresses

### ✅ One-Click Reset to Default Fields
- Restore the original state effortlessly
- Revert to default fields with a single click

### ✅ Translation Ready
- Fully translatable
- Compatible with WPML and Polylang
- Support for RTL languages

### ✅ Developer-Friendly Custom Hooks
**Filters:**
- `scfm_custom_fields` - Modify custom fields array
- `scfm_field_value` - Modify field value before saving
- `scfm_validation_rules` - Add custom validation rules

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
