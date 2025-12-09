# Changelog

All notable changes to Smart Checkout Fields Manager will be documented in this file.

## [Unreleased]

### Added (Block Visibility Controls)
- Granular visibility control for Block Checkout fields
- "Show in Block Checkout" toggle for each field
- Custom location selector for Block Checkout (Contact, Address, Additional Info)
- Auto-location based on field section
- Block checkout visibility settings saved per field
- UI controls in field editor modal
- Validation for block checkout locations
- Respects field visibility settings in block registration

### Added (Phase 9 - Block Checkout Support)
- WooCommerce Block Checkout integration
- Support for 4 field types in Block Checkout:
  * Text fields
  * Textarea fields
  * Checkbox fields
  * Select/dropdown fields
- Automatic field registration for Block Checkout
- Custom styling for block checkout fields
- Field location mapping (contact, address, order sections)
- Block checkout validation support
- Visual indicator (● blue dot) for block-compatible field types in admin
- Seamless integration with WooCommerce Blocks 11.0+
- Custom CSS and JavaScript for enhanced block checkout experience
- Responsive design for mobile block checkout

### Fixed (Tab Switching)
- Fixed fields not loading when switching to Shipping or Additional Fields tabs
- Added automatic field loading on tab change

### Added (Third-Party Plugin Support)
- Automatically detects and displays fields from other plugins
- Shows fields from plugins like "Ελληνικά Τιμολόγια" (Greek Invoices)
- Visual indicator (yellow badge) for third-party plugin fields
- Can enable/disable third-party plugin fields
- Proper integration with WooCommerce checkout fields filter
- Support for all field types added by external plugins

### Fixed (Recursion Prevention)
- Fixed infinite recursion when loading order/additional fields
- Added static flag to prevent circular dependency
- Temporarily removes filter when detecting third-party fields

### Fixed (WooCommerce Compatibility)
- Added HPOS (High-Performance Order Storage) compatibility declaration
- Declared compatibility with custom_order_tables feature
- Declared compatibility with orders_cache feature
- Added before_woocommerce_init hook for feature declaration
- Ensures full compatibility with WooCommerce 8.0+ features
- Resolves "incompatible plugin" warnings in WooCommerce

### Added (Phase 8 - Import/Export System)
- Complete Import/Export functionality for field configurations
- Export fields as JSON with metadata (version, timestamp, field count)
- Import fields with automatic validation and backup
- Section-specific export (billing, shipping, additional)
- JSON structure validation on import
- Automatic backup creation before import
- Backup restoration on import failure
- Section mismatch detection and warning
- Custom field filtering (exclude unmodified default fields)
- Filename generation with site name and timestamp
- Export/Import buttons in admin interface with icons
- File type validation (JSON only)
- Confirmation dialogs for import operations
- Success/error notifications for import/export
- Developer-friendly JSON format
- SCFM_Import_Export class (300+ lines)
- AJAX endpoints: scfm_export_fields, scfm_import_fields
- Client-side file handling with FileReader API
- Blob creation and download for exports

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
