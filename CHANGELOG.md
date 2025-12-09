# Changelog

All notable changes to Smart Checkout Fields Manager will be documented in this file.

## [Unreleased]

### Added (Phase 7 - Frontend Enhancements)
- Complete frontend CSS stylesheet (frontend.css) with 200+ lines
- Custom styling for heading and paragraph field types
- Enhanced checkbox group styling with proper spacing
- Multi-select dropdown enhancement with visual feedback
- Date/time input styling with consistent appearance
- Number input styling with spinner removal
- Real-time validation feedback with visual indicators
- Required field indicator styling
- Radio button group enhancement
- Textarea enhancement with resize capability
- Loading state animations
- Error shake animation for invalid fields
- Focus states with blue highlight
- Disabled state styling
- Responsive design for mobile devices
- WooCommerce theme compatibility
- Frontend JavaScript (frontend.js) with 250+ lines
- Real-time field validation (email, URL, number)
- Phone number masking and formatting
- Multi-select help text on focus
- Checkbox group value management
- Date picker placeholder enhancement
- Validation error/success visual feedback
- AJAX update compatibility
- Email format validation
- URL format validation
- Required field checking

### Added (Phase 5 - Validation & Default Fields)
- Comprehensive field validation system with WooCommerce integration
- Server-side validation via `woocommerce_after_checkout_validation` hook
- Type-based validation: email, number, url, tel (phone)
- Custom validation rules: postcode, state, phone (strict)
- Field-level validation with custom error messages
- Support for multiple validation rules per field
- Developer hook: `scfm_validate_field` for custom validation
- Validation rules UI in field editor modal (6 rule checkboxes)
- Default WooCommerce fields management system
- Edit capabilities for all default fields (label, placeholder, required, enabled, priority)
- Protection against deleting required default WooCommerce fields
- Visual distinction in modal when editing default fields (disabled type selector)
- Field type change prevention for default WooCommerce fields
- Enhanced admin.js to handle validation rule checkboxes in modal

### Added (Phase 4)
- Frontend checkout field rendering with WooCommerce integration
- Order meta saving for custom fields on checkout
- Custom field display in admin order page (billing & shipping sections)
- Custom field display in customer order details page
- Custom field display in WooCommerce emails (admin and customer)
- Support for all 20 field types on checkout page
- Custom field type renderers (heading, paragraph, checkbox group)
- Field value sanitization based on type
- Field visibility controls implementation
- Proper value formatting for display (arrays, dates, URLs, etc.)
- WooCommerce hooks integration for seamless field injection

## [1.0.0] - 2025-12-08

### Added
- Initial plugin structure
- Core plugin architecture with singleton pattern
- Admin menu integration under WooCommerce
- Three-tab interface: Billing Fields, Shipping Fields, Additional Fields
- Field Manager class for data handling
- Field Renderer class for 20+ field types support
- Field Validator class foundation
- Order Meta handler foundation
- Checkout Fields handler foundation
- Admin CSS with modern styling
- Admin JavaScript with sortable functionality
- AJAX endpoints for field management (structure ready)
- Translation-ready with text domain
- README.md with comprehensive documentation
- .gitignore file
- GPL-2.0+ license

### Structure
- Main plugin file: `smart-checkout-fields-manager.php`
- Includes folder with 7 core classes
- Assets folder with CSS and JS
- Languages folder ready for translations

### Notes
- Phase 1 Complete: Core Structure & Foundation
- Ready for Phase 2: Database Schema & Field Storage
- All TODO markers in place for next phase implementation
