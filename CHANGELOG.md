# Changelog

All notable changes to Fieldora Checkout for WooCommerce will be documented in this file.

## [Unreleased]

## [1.1.2] - 2025-12-30

### Security
- Removed arbitrary custom CSS editor functionality (WordPress.org security requirement)
- Added proper CSS escaping using `wp_strip_all_tags()` for all inline styles

### Changed
- Added "Requires Plugins: woocommerce" header for better dependency management
- Renamed main class from `Smart_Checkout_Fields_Manager` to `SCFM_Checkout_Fields_Manager` for consistent naming
- All plugin elements now use consistent `SCFM_` prefix per WordPress.org guidelines

### Fixed
- CSS escaping in `class-checkout-fields.php` - properly escaped before output
- Verified CSS escaping in `class-stylish-manager.php`
- Removed custom CSS functionality from admin settings

## [1.1.1] - 2025-12-29

### Changed
- **Rebranding**: Renamed plugin to "Fieldora Checkout for WooCommerce" per WordPress.org requirements
- Updated text domain from `smart-checkout-fields-manager` to `fieldora-checkout-for-woo`
- Updated plugin slug to `fieldora-checkout-for-woo`
- Removed promotional language ("Ultimate", "Most comprehensive", "Free Forever") from readme

### Fixed
- Moved inline `<style>` and `<script>` tags to proper `wp_add_inline_style()` and `wp_add_inline_script()` functions
- Fixed PHPCS nonce verification warnings in order meta handling
- Updated all 298 text domain instances across 9 PHP files

### Technical
- Renamed main plugin file from `smart-checkout-fields-manager.php` to `fieldora-checkout-for-woo.php`
- Renamed plugin folder from `smart-checkout-fields-manager` to `fieldora-checkout-for-woo`
- All WordPress coding standards compliance issues resolved

## [1.1.0] - 2025-12-22

### Added
- WordPress.org submission preparation with full coding standards compliance
- Translation support with POT file for Loco Translate compatibility
- Stylish tab for comprehensive field styling and customization
- Power Beautify mode for instant professional styling with one click
- Custom color controls (primary, background, text, label, placeholder)
- Border radius adjustment (0-30px)
- Shadow effects (none, light, medium, heavy, glow)
- Hover and focus effects (glow, scale, lift)
- Typography controls with 6 Google Fonts (Inter, Roboto, Open Sans, Lato, Montserrat, Poppins)
- Font size (12-20px) and weight (300-700) customization
- Animation effects (none, fade, slide, bounce, scale)
- Custom CSS editor for advanced styling
- Address Format Manager for customizing checkout layout
- Live preview in settings for real-time field styling

### Enhanced
- Power Beautify with dramatic visual enhancements
- Animated gradient borders with 5-color transitions
- Multiple layered shadows (3-5 layers) for depth and dimension
- Pulsing border animation with color transitions
- Shine effect animation sweeping across fields
- Focus state with 6 shadow layers and glowing aura effect
- Hover state with multi-layered shadows and lift animation
- Gradient text labels with vibrant purple-pink-blue gradients
- Button-style inputs with pink gradient
- Admin card with animated gradient background
- Spectacular toggle switch with gradient and glow effects

### Fixed
- All WordPress coding standards violations for WordPress.org submission
- Text domain consistency (changed to 'smart-checkout-fields-manager')
- Output escaping with wp_kses_post() and esc_html()
- Input sanitization with wp_unslash() and sanitize_text_field()
- Nonce verification in AJAX handlers
- Global variable prefixes with scfm_ namespace
- Translation support with proper translators comments
- Date/time functions using gmdate() instead of date()
- Resource versioning for cache busting

### Changed
- Author updated to 'irmaiden' for WordPress.org submission
- Tested up to WordPress 6.9
- Short description optimized to 114 characters
- Removed debugging features (GitHub update button)
- load_plugin_textdomain() removed (WordPress.org handles automatically)

### Security
- Enhanced input validation and sanitization
- Proper nonce verification in all form submissions
- Output escaping for all user-generated content
- Button-style checkboxes and radio buttons
- Entrance animations (fade in, slide up, slide in, bounce)
- Transition speed controls (fast, normal, slow)
- Live preview of styling changes in admin
- Frontend CSS generation based on settings
- Body class system for modular styling
- Responsive design optimizations
- 15+ customization options with real-time preview
- Save and reset functionality for stylish settings
- SCFM_Stylish_Manager class for frontend styling

### Added (Address Format Override)
- Custom address format integration for billing and shipping fields
- "Show in Address Format" toggle for billing/shipping fields
- Position control for custom fields in address format
- Custom field placeholders in WooCommerce address templates
- Support for order details, My Account, and email address display
- Country-specific address format compatibility
- Automatic field insertion based on position
- Address format preview in field editor
- Filters: woocommerce_localisation_address_formats, woocommerce_formatted_address_replacements
- Filters: woocommerce_order_formatted_billing_address, woocommerce_order_formatted_shipping_address
- Filters: woocommerce_my_account_my_address_formatted_address
- Utility methods for address formatting and placeholder retrieval
- SCFM_Address_Formatter class with full address management

### Added (Advanced Features)
- Conditional logic support for custom fields
- Dynamic field visibility based on other field values
- Real-time field show/hide with smooth animations
- Advanced field rendering with custom classes and attributes
- Field state monitoring and automatic updates
- Conditional logic evaluation (equals, not_equals, contains, empty, etc.)
- Support for AND/OR operators in conditional rules
- Field dependency tracking and validation
- Enhanced CSS animations (fadeIn, slideIn, spin)
- Loading states for conditional fields
- Custom field markers and visual indicators
- Half-width field support
- Dark mode support for fields
- Responsive design improvements
- Field data passed to JavaScript via wp_localize_script
- MutationObserver for dynamic content updates
- Form data collection and evaluation system

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
