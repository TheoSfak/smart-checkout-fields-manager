/**
 * Block Checkout JavaScript - Advanced Rendering
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
    const { useEffect } = window.wp.element;
    const { select, subscribe } = window.wp.data;
    
    // Store for conditional logic state
    let conditionalFieldsState = {};
    
    // Add custom validation for our fields
    const validateCustomFields = (result, extensions, context) => {
        const checkoutData = context.checkoutData;
        
        // Custom validation logic
        if (typeof scfmBlockCheckout !== 'undefined' && scfmBlockCheckout.fields) {
            Object.keys(scfmBlockCheckout.fields).forEach(function(fieldId) {
                const fieldConfig = scfmBlockCheckout.fields[fieldId];
                
                // Check conditional logic
                if (fieldConfig.conditional_logic) {
                    const shouldShow = evaluateConditionalLogic(
                        fieldConfig.conditional_logic,
                        checkoutData
                    );
                    
                    conditionalFieldsState[fieldId] = shouldShow;
                    
                    // Toggle field visibility
                    toggleFieldVisibility(fieldId, shouldShow);
                }
            });
        }
        
        return result;
    };
    
    /**
     * Evaluate conditional logic rules
     */
    const evaluateConditionalLogic = function(logic, formData) {
        if (!logic || !logic.rules || logic.rules.length === 0) {
            return true;
        }
        
        const operator = logic.operator || 'and';
        const results = [];
        
        logic.rules.forEach(function(rule) {
            const fieldValue = formData[rule.field] || '';
            let result = false;
            
            switch (rule.condition) {
                case 'equals':
                    result = (fieldValue == rule.value);
                    break;
                case 'not_equals':
                    result = (fieldValue != rule.value);
                    break;
                case 'contains':
                    result = (String(fieldValue).indexOf(rule.value) !== -1);
                    break;
                case 'not_contains':
                    result = (String(fieldValue).indexOf(rule.value) === -1);
                    break;
                case 'empty':
                    result = !fieldValue;
                    break;
                case 'not_empty':
                    result = !!fieldValue;
                    break;
                default:
                    result = true;
            }
            
            results.push(result);
        });
        
        // Evaluate based on operator
        if (operator === 'and') {
            return results.every(function(r) { return r === true; });
        } else {
            return results.some(function(r) { return r === true; });
        }
    };
    
    /**
     * Toggle field visibility in block checkout
     */
    const toggleFieldVisibility = function(fieldId, show) {
        // Find field container
        const fieldSelectors = [
            '[data-field-id="' + fieldId + '"]',
            '#' + fieldId,
            '[name="' + fieldId + '"]',
            '.wc-block-components-text-input[data-field-key="' + fieldId + '"]',
            '.wc-block-components-textarea[data-field-key="' + fieldId + '"]',
            '.wc-block-components-checkbox[data-field-key="' + fieldId + '"]',
            '.wc-block-components-combobox[data-field-key="' + fieldId + '"]'
        ];
        
        fieldSelectors.forEach(function(selector) {
            const elements = document.querySelectorAll(selector);
            elements.forEach(function(element) {
                const container = element.closest('.wc-block-components-text-input') ||
                                 element.closest('.wc-block-components-textarea') ||
                                 element.closest('.wc-block-components-checkbox') ||
                                 element.closest('.wc-block-components-combobox') ||
                                 element.parentElement;
                
                if (container) {
                    if (show) {
                        container.style.display = '';
                        container.classList.remove('scfm-hidden');
                    } else {
                        container.style.display = 'none';
                        container.classList.add('scfm-hidden');
                        // Clear field value when hidden
                        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                            element.value = '';
                        }
                    }
                }
            });
        });
    };
    
    /**
     * Add custom field styling and behavior
     */
    const enhanceFieldRendering = function() {
        if (typeof scfmBlockCheckout === 'undefined' || !scfmBlockCheckout.fields) {
            return;
        }
        
        Object.keys(scfmBlockCheckout.fields).forEach(function(fieldId) {
            const fieldConfig = scfmBlockCheckout.fields[fieldId];
            
            // Find field elements
            const fieldElements = document.querySelectorAll(
                '[data-field-id="' + fieldId + '"], ' +
                '[name="' + fieldId + '"], ' +
                '#' + fieldId
            );
            
            fieldElements.forEach(function(element) {
                // Add custom classes
                if (fieldConfig.custom_classes && Array.isArray(fieldConfig.custom_classes)) {
                    fieldConfig.custom_classes.forEach(function(className) {
                        element.classList.add(className);
                    });
                }
                
                // Add data attribute for field tracking
                element.setAttribute('data-scfm-field', fieldId);
                element.setAttribute('data-scfm-type', fieldConfig.type);
                
                // Add container classes
                const container = element.closest('.wc-block-components-text-input') ||
                                 element.closest('.wc-block-components-textarea') ||
                                 element.closest('.wc-block-components-checkbox') ||
                                 element.closest('.wc-block-components-combobox');
                
                if (container) {
                    container.classList.add('scfm-custom-field');
                    container.setAttribute('data-scfm-field-id', fieldId);
                }
            });
        });
    };
    
    /**
     * Monitor checkout field changes for conditional logic
     */
    const monitorFieldChanges = function() {
        const checkoutForm = document.querySelector('.wc-block-checkout');
        
        if (!checkoutForm) {
            return;
        }
        
        // Listen for input changes
        checkoutForm.addEventListener('change', function(e) {
            if (e.target.matches('input, select, textarea')) {
                // Re-evaluate conditional logic
                if (typeof scfmBlockCheckout !== 'undefined' && scfmBlockCheckout.fields) {
                    const formData = getFormData();
                    
                    Object.keys(scfmBlockCheckout.fields).forEach(function(fieldId) {
                        const fieldConfig = scfmBlockCheckout.fields[fieldId];
                        
                        if (fieldConfig.conditional_logic) {
                            const shouldShow = evaluateConditionalLogic(
                                fieldConfig.conditional_logic,
                                formData
                            );
                            
                            if (conditionalFieldsState[fieldId] !== shouldShow) {
                                conditionalFieldsState[fieldId] = shouldShow;
                                toggleFieldVisibility(fieldId, shouldShow);
                            }
                        }
                    });
                }
            }
        });
    };
    
    /**
     * Get current form data
     */
    const getFormData = function() {
        const formData = {};
        const inputs = document.querySelectorAll('.wc-block-checkout input, .wc-block-checkout select, .wc-block-checkout textarea');
        
        inputs.forEach(function(input) {
            if (input.name) {
                if (input.type === 'checkbox') {
                    formData[input.name] = input.checked ? input.value : '';
                } else {
                    formData[input.name] = input.value;
                }
            }
        });
        
        return formData;
    };
    
    // Register validation filter
    if (registerCheckoutFilters) {
        registerCheckoutFilters('scfm-custom-fields', {
            checkoutValidation: validateCustomFields,
        });
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        enhanceFieldRendering();
        monitorFieldChanges();
        
        // Re-run enhancement when checkout updates
        const checkoutContainer = document.querySelector('.wc-block-checkout');
        
        if (checkoutContainer) {
            const observer = new MutationObserver(function(mutations) {
                enhanceFieldRendering();
            });
            
            observer.observe(checkoutContainer, {
                childList: true,
                subtree: true
            });
        }
        
        // Initial conditional logic evaluation
        if (typeof scfmBlockCheckout !== 'undefined' && scfmBlockCheckout.fields) {
            setTimeout(function() {
                const formData = getFormData();
                
                Object.keys(scfmBlockCheckout.fields).forEach(function(fieldId) {
                    const fieldConfig = scfmBlockCheckout.fields[fieldId];
                    
                    if (fieldConfig.conditional_logic) {
                        const shouldShow = evaluateConditionalLogic(
                            fieldConfig.conditional_logic,
                            formData
                        );
                        
                        conditionalFieldsState[fieldId] = shouldShow;
                        toggleFieldVisibility(fieldId, shouldShow);
                    }
                });
            }, 500);
        }
    });
    
})();
