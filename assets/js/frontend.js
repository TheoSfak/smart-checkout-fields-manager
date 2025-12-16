/**
 * Frontend Checkout JavaScript
 * Smart Checkout Fields Manager
 */

(function($) {
    'use strict';
    
    var SCFM_Checkout = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initDatePickers();
            this.initMultiSelect();
            this.initPhoneMasking();
            this.initRealTimeValidation();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Handle multiselect help text
            $(document).on('focus', 'select[multiple].scfm-multiselect', function() {
                if (!$(this).next('.scfm-help-text').length) {
                    $(this).after('<span class="scfm-help-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple options</span>');
                }
            });
            
            // Handle checkbox group
            $(document).on('change', '.scfm-checkbox-group input[type="checkbox"]', function() {
                SCFM_Checkout.updateCheckboxGroupValue($(this).closest('.scfm-checkbox-group'));
            });
            
            // Handle validation on blur
            $(document).on('blur', '.woocommerce-checkout input, .woocommerce-checkout select, .woocommerce-checkout textarea', function() {
                SCFM_Checkout.validateField($(this));
            });
            
            // Clear validation on focus
            $(document).on('focus', '.woocommerce-checkout input, .woocommerce-checkout select, .woocommerce-checkout textarea', function() {
                $(this).removeClass('scfm-field-invalid scfm-field-valid scfm-field-error');
                $(this).siblings('.scfm-validation-message').remove();
            });
        },
        
        /**
         * Initialize date pickers (enhance with placeholders)
         */
        initDatePickers: function() {
            var dateInputs = $('input[type="date"], input[type="datetime-local"], input[type="time"], input[type="month"], input[type="week"]');
            
            dateInputs.each(function() {
                var $input = $(this);
                var type = $input.attr('type');
                
                // Add helpful placeholder
                if (!$input.attr('placeholder')) {
                    var placeholder = '';
                    switch(type) {
                        case 'date':
                            placeholder = 'MM/DD/YYYY';
                            break;
                        case 'datetime-local':
                            placeholder = 'MM/DD/YYYY, HH:MM';
                            break;
                        case 'time':
                            placeholder = 'HH:MM';
                            break;
                        case 'month':
                            placeholder = 'YYYY-MM';
                            break;
                        case 'week':
                            placeholder = 'YYYY-Www';
                            break;
                    }
                    if (placeholder) {
                        $input.attr('placeholder', placeholder);
                    }
                }
            });
        },
        
        /**
         * Initialize multi-select enhancement
         */
        initMultiSelect: function() {
            // Find all multiselect fields and ensure they have the proper attributes
            $('select[multiple], .scfm-multiselect').each(function() {
                var $select = $(this);
                if (!$select.attr('multiple')) {
                    $select.attr('multiple', 'multiple');
                }
                
                // Force height with JavaScript to override any CSS
                $select.css({
                    'height': '130px',
                    'max-height': '130px',
                    'min-height': '130px',
                    'overflow-y': 'auto',
                    'display': 'block',
                    'width': '100%',
                    'box-sizing': 'border-box'
                });
                
                // Ensure wrapper is also constrained
                var $wrapper = $select.closest('.scfm-multiselect-wrapper');
                if ($wrapper.length) {
                    $wrapper.css({
                        'height': '130px',
                        'max-height': '130px',
                        'overflow': 'hidden',
                        'display': 'block'
                    });
                }
                
                // Add help text if not already present
                if (!$select.parent().find('.scfm-help-text').length) {
                    $select.parent().append('<span class="scfm-help-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple options</span>');
                }
            });
            
            // Re-apply after AJAX updates (WooCommerce checkout updates)
            $(document.body).on('updated_checkout', function() {
                SCFM_Checkout.initMultiSelect();
            });
        },
        
        /**
         * Initialize phone masking (basic)
         */
        initPhoneMasking: function() {
            $(document).on('input', 'input[type="tel"]', function(e) {
                var $input = $(this);
                var value = $input.val();
                
                // Remove non-numeric characters except + at start
                var cleaned = value.replace(/[^\d+]/g, '');
                
                // Ensure + only at start
                if (cleaned.indexOf('+') > 0) {
                    cleaned = cleaned.replace(/\+/g, '');
                }
                
                // Basic formatting for US numbers (optional)
                if (cleaned.length === 10 && !cleaned.startsWith('+')) {
                    cleaned = '(' + cleaned.substring(0, 3) + ') ' + 
                             cleaned.substring(3, 6) + '-' + 
                             cleaned.substring(6);
                }
                
                if (cleaned !== value) {
                    $input.val(cleaned);
                }
            });
        },
        
        /**
         * Real-time validation
         */
        initRealTimeValidation: function() {
            // Email validation
            $(document).on('blur', 'input[type="email"]', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && !SCFM_Checkout.isValidEmail(value)) {
                    SCFM_Checkout.showValidationError($input, 'Please enter a valid email address');
                } else if (value) {
                    SCFM_Checkout.showValidationSuccess($input);
                }
            });
            
            // URL validation
            $(document).on('blur', 'input[type="url"]', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && !SCFM_Checkout.isValidURL(value)) {
                    SCFM_Checkout.showValidationError($input, 'Please enter a valid URL');
                } else if (value) {
                    SCFM_Checkout.showValidationSuccess($input);
                }
            });
            
            // Number validation
            $(document).on('blur', 'input[type="number"]', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && isNaN(value)) {
                    SCFM_Checkout.showValidationError($input, 'Please enter a valid number');
                } else if (value) {
                    SCFM_Checkout.showValidationSuccess($input);
                }
            });
        },
        
        /**
         * Validate field
         */
        validateField: function($input) {
            var value = $input.val();
            var type = $input.attr('type');
            
            // Skip if empty and not required
            if (!value && !$input.prop('required')) {
                return true;
            }
            
            // Required check
            if ($input.prop('required') && !value) {
                this.showValidationError($input, 'This field is required');
                return false;
            }
            
            return true;
        },
        
        /**
         * Show validation error
         */
        showValidationError: function($input, message) {
            $input.removeClass('scfm-field-valid').addClass('scfm-field-invalid scfm-field-error');
            
            if (!$input.siblings('.scfm-validation-message').length) {
                $input.after('<span class="scfm-validation-message">' + message + '</span>');
            }
        },
        
        /**
         * Show validation success
         */
        showValidationSuccess: function($input) {
            $input.removeClass('scfm-field-invalid').addClass('scfm-field-valid');
            $input.siblings('.scfm-validation-message').remove();
        },
        
        /**
         * Update checkbox group value
         */
        updateCheckboxGroupValue: function($group) {
            var values = [];
            $group.find('input[type="checkbox"]:checked').each(function() {
                values.push($(this).val());
            });
            
            // Store in hidden input if needed
            var $hidden = $group.find('input[type="hidden"]');
            if ($hidden.length) {
                $hidden.val(values.join(','));
            }
        },
        
        /**
         * Validate email
         */
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        /**
         * Validate URL
         */
        isValidURL: function(url) {
            try {
                new URL(url);
                return true;
            } catch(e) {
                return false;
            }
        }
    };
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        SCFM_Checkout.init();
    });
    
    /**
     * Re-initialize after AJAX updates
     */
    $(document.body).on('updated_checkout', function() {
        SCFM_Checkout.init();
    });
    
})(jQuery);
