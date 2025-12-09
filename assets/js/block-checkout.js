/**
 * Block Checkout JavaScript
 *
 * @package Smart_Checkout_Fields_Manager
 */

(function() {
    'use strict';
    
    // Wait for WooCommerce Blocks to be ready
    if (typeof window.wp === 'undefined' || typeof window.wp.data === 'undefined') {
        return;
    }
    
    const { registerCheckoutFilters } = window.wc.blocksCheckout;
    
    // Add custom validation for our fields
    const validateCustomFields = (result, extensions, context) => {
        const checkoutData = context.checkoutData;
        
        // Custom validation logic can be added here
        // Example: validate custom field patterns, formats, etc.
        
        return result;
    };
    
    // Register validation filter
    if (registerCheckoutFilters) {
        registerCheckoutFilters('scfm-custom-fields', {
            // Add custom field validation
            checkoutValidation: validateCustomFields,
        });
    }
    
    // Add custom field styling and behavior
    document.addEventListener('DOMContentLoaded', function() {
        // Add custom classes to our fields for easier styling
        const addCustomClasses = function() {
            const customFields = document.querySelectorAll('[data-scfm-custom-field]');
            
            customFields.forEach(function(field) {
                field.classList.add('scfm-custom-field');
            });
        };
        
        // Run on initial load
        addCustomClasses();
        
        // Re-run when checkout updates (using MutationObserver)
        const checkoutContainer = document.querySelector('.wc-block-checkout');
        
        if (checkoutContainer) {
            const observer = new MutationObserver(function(mutations) {
                addCustomClasses();
            });
            
            observer.observe(checkoutContainer, {
                childList: true,
                subtree: true
            });
        }
    });
    
})();
