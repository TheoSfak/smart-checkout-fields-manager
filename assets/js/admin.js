/**
 * Admin JavaScript
 *
 * @package Smart_Checkout_Fields_Manager
 */

(function($) {
    'use strict';
    
    var SCFM_Admin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.loadFields();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Tab switching
            $('.nav-tab').on('click', this.switchTab);
            
            // Add field button
            $('.scfm-add-field').on('click', this.addField);
            
            // Import/Export buttons
            $('.scfm-export-fields').on('click', this.exportFields);
            $('.scfm-import-fields').on('click', this.importFields);
            
            // Reset fields button
            $('.scfm-reset-fields').on('click', this.resetFields);
            
            // GitHub update button
            $('#scfm-update-github').on('click', this.updateFromGitHub);
            
            // Edit field button (delegated)
            $(document).on('click', '.scfm-btn-edit', this.editField);
            
            // Delete field button (delegated)
            $(document).on('click', '.scfm-btn-delete', this.deleteField);
            
            // Toggle field status (delegated)
            $(document).on('change', '.scfm-toggle input', this.toggleField);
            
            // Modal events
            $('.scfm-modal-close').on('click', this.closeModal);
            $('.scfm-modal-overlay').on('click', this.closeModal);
            $('#scfm-save-field').on('click', this.saveField);
            
            // Field type change
            $('#scfm-field-type').on('change', function() {
                SCFM_Admin.toggleOptionsRow($(this).val());
            });
            
            // Field section change - show/hide address format row
            $('#scfm-field-section').on('change', function() {
                var section = $(this).val();
                if (section === 'billing' || section === 'shipping') {
                    $('#scfm-address-format-row').show();
                } else {
                    $('#scfm-address-format-row').hide();
                }
            });
            
            // Prevent modal close on content click
            $('.scfm-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },
        
        /**
         * Switch tab
         */
        switchTab: function(e) {
            e.preventDefault();
            
            var $tab = $(this);
            var tabId = $tab.data('tab');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            // Show corresponding content
            $('.scfm-tab-content').hide();
            $('#' + tabId).show();
            
            // Load fields for the newly visible tab
            SCFM_Admin.loadFields();
        },
        
        /**
         * Initialize sortable
         */
        initSortable: function() {
            $('.scfm-sortable-fields').sortable({
                handle: '.scfm-drag-handle',
                placeholder: 'scfm-sortable-placeholder',
                update: function(event, ui) {
                    SCFM_Admin.updatePositions($(this));
                }
            });
        },
        
        /**
         * Load fields for current tab
         */
        loadFields: function() {
            var $activeTab = $('.scfm-tab-content:visible');
            var section = $activeTab.find('.scfm-sortable-fields').data('section');
            
            if (!section) return;
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_get_fields',
                    nonce: scfmAdmin.nonce,
                    section: section
                },
                success: function(response) {
                    if (response.success) {
                        SCFM_Admin.renderFields(section, response.data.fields);
                    }
                },
                error: function() {
                    SCFM_Admin.showNotice(scfmAdmin.strings.error, 'error');
                }
            });
        },
        
        /**
         * Render fields in table
         */
        renderFields: function(section, fields) {
            var $tbody = $('.scfm-sortable-fields[data-section="' + section + '"]');
            $tbody.empty();
            
            if (Object.keys(fields).length === 0) {
                $tbody.html('<tr><td colspan="7" class="scfm-no-fields">' + 
                    'No fields yet. Default WooCommerce fields will be used.' + 
                    '</td></tr>');
                return;
            }
            
            // Render each field
            $.each(fields, function(fieldId, field) {
                var row = SCFM_Admin.renderFieldRow(fieldId, field);
                $tbody.append(row);
            });
            
            // Destroy existing sortable if it exists
            if ($tbody.hasClass('ui-sortable')) {
                $tbody.sortable('destroy');
            }
            
            // Initialize sortable
            $tbody.sortable({
                handle: '.scfm-drag-handle',
                placeholder: 'scfm-sortable-placeholder',
                update: function(event, ui) {
                    SCFM_Admin.updatePositions($(this));
                }
            });
        },
        
        /**
         * Render a single field row
         */
        renderFieldRow: function(fieldId, field) {
            var isCustom = field.custom !== false;
            var isDefaultWC = field.default_wc === true;
            var isThirdParty = field.third_party === true;
            var typeLabel = field.type || 'text';
            var typeBadgeClass = isCustom ? 'scfm-custom' : (isThirdParty ? 'scfm-third-party' : 'scfm-default');
            var requiredIcon = field.required ? '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>' : '-';
            var enabledChecked = field.enabled ? 'checked' : '';
            
            var deleteBtn = '';
            if (isCustom && !isDefaultWC) {
                deleteBtn = '<button type="button" class="scfm-btn-icon scfm-btn-delete" data-field-id="' + fieldId + '" title="Delete">' +
                           '<span class="dashicons dashicons-trash"></span></button>';
            }
            
            var row = '<tr data-field-id="' + fieldId + '">' +
                '<td class="scfm-drag-handle"></td>' +
                '<td><code>' + fieldId + '</code></td>' +
                '<td><span class="scfm-field-type ' + typeBadgeClass + '">' + typeLabel + '</span></td>' +
                '<td>' + field.label + '</td>' +
                '<td class="scfm-text-center">' + requiredIcon + '</td>' +
                '<td class="scfm-text-center">' +
                    '<label class="scfm-toggle" data-field-id="' + fieldId + '">' +
                        '<input type="checkbox" ' + enabledChecked + '>' +
                        '<span class="scfm-toggle-slider"></span>' +
                    '</label>' +
                '</td>' +
                '<td class="scfm-text-center">' +
                    '<div class="scfm-action-buttons">' +
                        '<button type="button" class="scfm-btn-icon scfm-btn-edit" data-field-id="' + fieldId + '" title="Edit">' +
                            '<span class="dashicons dashicons-edit"></span>' +
                        '</button>' +
                        deleteBtn +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            return row;
        },
        
        /**
         * Add new field
         */
        addField: function(e) {
            e.preventDefault();
            var section = $(this).data('section');
            
            // Reset form
            $('#scfm-field-form')[0].reset();
            $('#scfm-field-id').val('');
            $('#scfm-field-section').val(section);
            $('#scfm-modal-title').text('Add Custom Field');
            $('#scfm-field-enabled').prop('checked', true);
            $('input[name="field_data[visibility][order_details]"]').prop('checked', true);
            $('input[name="field_data[visibility][admin_emails]"]').prop('checked', true);
            $('input[name="field_data[visibility][customer_emails]"]').prop('checked', true);
            
            // Show modal
            $('#scfm-field-modal').fadeIn(200);
            $('#scfm-field-label').focus();
        },
        
        /**
         * Edit field
         */
        editField: function(e) {
            e.preventDefault();
            var fieldId = $(this).data('field-id');
            var section = $(this).closest('.scfm-sortable-fields').data('section');
            
            // Get field data from server
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_get_fields',
                    nonce: scfmAdmin.nonce,
                    section: section
                },
                success: function(response) {
                    if (response.success && response.data.fields[fieldId]) {
                        SCFM_Admin.openEditModal(section, fieldId, response.data.fields[fieldId]);
                    }
                }
            });
        },
        
        /**
         * Open edit modal with field data
         */
        openEditModal: function(section, fieldId, field) {
            // Set form values
            $('#scfm-field-id').val(fieldId);
            $('#scfm-field-section').val(section);
            
            // Show/hide address format row based on section
            if (section === 'billing' || section === 'shipping') {
                $('#scfm-address-format-row').show();
            } else {
                $('#scfm-address-format-row').hide();
            }
            
            // Check if it's a default WC field
            var isDefaultWC = field.default_wc === true;
            if (isDefaultWC) {
                $('#scfm-modal-title').text('Edit Default WooCommerce Field');
                // Disable field type change for default fields
                $('#scfm-field-type').prop('disabled', true);
            } else {
                $('#scfm-modal-title').text('Edit Field');
                $('#scfm-field-type').prop('disabled', false);
            }
            
            $('#scfm-field-type').val(field.type || 'text');
            $('#scfm-field-label').val(field.label || '');
            $('#scfm-field-placeholder').val(field.placeholder || '');
            $('#scfm-field-default').val(field.default || '');
            $('#scfm-field-priority').val(field.priority || 100);
            $('#scfm-field-required').prop('checked', field.required || false);
            $('#scfm-field-enabled').prop('checked', field.enabled !== false);
            
            // Set class
            if (field.class && Array.isArray(field.class)) {
                var mainClass = field.class.find(function(c) {
                    return c.indexOf('form-row') === 0;
                });
                if (mainClass) {
                    $('#scfm-field-class').val(mainClass);
                }
            }
            
            // Set options if field type has options
            if (field.options) {
                var optionsText = '';
                if (typeof field.options === 'object') {
                    $.each(field.options, function(key, value) {
                        optionsText += key + '|' + value + '\n';
                    });
                }
                $('#scfm-field-options').val(optionsText.trim());
            }
            
            // Set validation rules
            $('input[name="field_data[validation][]"]').prop('checked', false);
            if (field.validation) {
                var validationArray = Array.isArray(field.validation) ? field.validation : [field.validation];
                validationArray.forEach(function(rule) {
                    $('input[name="field_data[validation][]"][value="' + rule + '"]').prop('checked', true);
                });
            }
            
            // Set visibility
            if (field.visibility) {
                $('input[name="field_data[visibility][order_details]"]').prop('checked', field.visibility.order_details !== false);
                $('input[name="field_data[visibility][admin_emails]"]').prop('checked', field.visibility.admin_emails !== false);
                $('input[name="field_data[visibility][customer_emails]"]').prop('checked', field.visibility.customer_emails !== false);
            }
            
            // Set block checkout visibility
            $('#scfm-field-block-visible').prop('checked', field.block_checkout_visible !== false);
            $('#scfm-field-block-location').val(field.block_checkout_location || '');
            
            // Set address format settings
            $('#scfm-field-show-in-address').prop('checked', field.show_in_address_format || false);
            $('#scfm-field-address-position').val(field.address_format_position || 0);
            
            // Toggle options row visibility
            SCFM_Admin.toggleOptionsRow($('#scfm-field-type').val());
            
            // Show modal
            $('#scfm-field-modal').fadeIn(200);
        },
        
        /**
         * Delete field
         */
        deleteField: function(e) {
            e.preventDefault();
            
            if (!confirm(scfmAdmin.strings.confirm_delete)) {
                return;
            }
            
            var fieldId = $(this).data('field-id');
            var section = $(this).closest('.scfm-sortable-fields').data('section');
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_delete_field',
                    nonce: scfmAdmin.nonce,
                    section: section,
                    field_id: fieldId
                },
                success: function(response) {
                    if (response.success) {
                        SCFM_Admin.loadFields();
                        SCFM_Admin.showNotice(response.data.message, 'success');
                    }
                },
                error: function() {
                    SCFM_Admin.showNotice(scfmAdmin.strings.error, 'error');
                }
            });
        },
        
        /**
         * Toggle field enabled status
         */
        toggleField: function() {
            var fieldId = $(this).closest('.scfm-toggle').data('field-id');
            var section = $(this).closest('.scfm-sortable-fields').data('section');
            var enabled = $(this).is(':checked');
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_toggle_field',
                    nonce: scfmAdmin.nonce,
                    section: section,
                    field_id: fieldId,
                    enabled: enabled
                }
            });
        },
        
        /**
         * Update field positions after drag-and-drop
         */
        updatePositions: function($sortable) {
            var section = $sortable.data('section');
            var positions = [];
            
            $sortable.find('tr').each(function(index) {
                var fieldId = $(this).data('field-id');
                if (fieldId) {
                    positions.push({
                        field_id: fieldId,
                        position: index
                    });
                }
            });
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_update_positions',
                    nonce: scfmAdmin.nonce,
                    section: section,
                    positions: positions
                }
            });
        },
        
        /**
         * Reset fields to defaults
         */
        resetFields: function(e) {
            e.preventDefault();
            
            if (!confirm(scfmAdmin.strings.confirm_reset)) {
                return;
            }
            
            var section = $(this).data('section');
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_reset_fields',
                    nonce: scfmAdmin.nonce,
                    section: section
                },
                success: function(response) {
                    if (response.success) {
                        SCFM_Admin.loadFields();
                        SCFM_Admin.showNotice(response.data.message, 'success');
                    }
                },
                error: function() {
                    SCFM_Admin.showNotice(scfmAdmin.strings.error, 'error');
                }
            });
        },
        
        /**
         * Show notification
         */
        showNotice: function(message, type) {
            var $notice = $('.scfm-admin-notice');
            $notice.removeClass('success error').addClass(type);
            $notice.text(message);
            $notice.slideDown();
            
            setTimeout(function() {
                $notice.slideUp();
            }, 5000);
        },
        
        /**
         * Close modal
         */
        closeModal: function(e) {
            e.preventDefault();
            $('#scfm-field-modal').fadeOut(200);
        },
        
        /**
         * Save field from modal
         */
        saveField: function(e) {
            e.preventDefault();
            
            // Validate form
            if (!$('#scfm-field-label').val()) {
                alert('Please enter a field label.');
                $('#scfm-field-label').focus();
                return;
            }
            
            // Serialize form data
            var formData = {
                action: 'scfm_save_field',
                nonce: scfmAdmin.nonce,
                section: $('#scfm-field-section').val(),
                field_id: $('#scfm-field-id').val(),
                field_data: {}
            };
            
            // Get form values
            formData.field_data.type = $('#scfm-field-type').val();
            formData.field_data.label = $('#scfm-field-label').val();
            formData.field_data.placeholder = $('#scfm-field-placeholder').val();
            formData.field_data.default = $('#scfm-field-default').val();
            formData.field_data.priority = parseInt($('#scfm-field-priority').val()) || 100;
            formData.field_data.required = $('#scfm-field-required').is(':checked');
            formData.field_data.enabled = $('#scfm-field-enabled').is(':checked');
            
            // Get class
            var selectedClass = $('#scfm-field-class').val();
            formData.field_data.class = [selectedClass];
            
            // Get options if applicable
            var optionsText = $('#scfm-field-options').val();
            if (optionsText) {
                var optionsArray = optionsText.split('\n').filter(function(line) {
                    return line.trim() !== '';
                });
                var optionsObj = {};
                optionsArray.forEach(function(line) {
                    var parts = line.split('|');
                    if (parts.length === 2) {
                        optionsObj[parts[0].trim()] = parts[1].trim();
                    } else {
                        optionsObj[line.trim()] = line.trim();
                    }
                });
                formData.field_data.options = optionsObj;
            }
            
            // Get validation rules
            var validationRules = [];
            $('input[name="field_data[validation][]"]:checked').each(function() {
                validationRules.push($(this).val());
            });
            if (validationRules.length > 0) {
                formData.field_data.validation = validationRules;
            }
            
            // Get visibility
            formData.field_data.visibility = {
                order_details: $('input[name="field_data[visibility][order_details]"]').is(':checked'),
                admin_emails: $('input[name="field_data[visibility][admin_emails]"]').is(':checked'),
                customer_emails: $('input[name="field_data[visibility][customer_emails]"]').is(':checked')
            };
            
            // Disable save button
            var $saveBtn = $('#scfm-save-field');
            $saveBtn.prop('disabled', true).text(scfmAdmin.strings.saving);
            
            // Send AJAX request
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        SCFM_Admin.closeModal({preventDefault: function(){}});
                        SCFM_Admin.loadFields();
                        SCFM_Admin.showNotice(response.data.message, 'success');
                    } else {
                        alert(response.data.message || scfmAdmin.strings.error);
                    }
                },
                error: function() {
                    alert(scfmAdmin.strings.error);
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).text('Save Field');
                }
            });
        },
        
        /**
         * Toggle options row visibility based on field type
         */
        toggleOptionsRow: function(fieldType) {
            var typesWithOptions = ['select', 'multiselect', 'radio', 'checkboxgroup'];
            if (typesWithOptions.indexOf(fieldType) !== -1) {
                $('#scfm-field-options-row').show();
            } else {
                $('#scfm-field-options-row').hide();
            }
        },
        
        /**
         * Export fields
         */
        exportFields: function(e) {
            e.preventDefault();
            var section = $(this).data('section');
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_export_fields',
                    nonce: scfmAdmin.nonce,
                    section: section
                },
                success: function(response) {
                    if (response.success) {
                        // Create download
                        var dataStr = JSON.stringify(response.data.data, null, 2);
                        var dataBlob = new Blob([dataStr], {type: 'application/json'});
                        var url = URL.createObjectURL(dataBlob);
                        
                        // Create download link
                        var link = document.createElement('a');
                        link.download = response.data.filename;
                        link.href = url;
                        link.click();
                        
                        SCFM_Admin.showNotice('Fields exported successfully', 'success');
                    }
                },
                error: function() {
                    SCFM_Admin.showNotice(scfmAdmin.strings.error, 'error');
                }
            });
        },
        
        /**
         * Import fields
         */
        importFields: function(e) {
            e.preventDefault();
            var section = $(this).data('section');
            
            // Create file input
            var fileInput = $('<input type="file" accept=".json">');
            
            fileInput.on('change', function(e) {
                var file = e.target.files[0];
                
                if (!file) {
                    return;
                }
                
                // Validate file type
                if (file.type !== 'application/json') {
                    alert('Please select a valid JSON file.');
                    return;
                }
                
                // Confirm import
                if (!confirm('This will import fields and may overwrite existing custom fields. A backup will be created automatically. Continue?')) {
                    return;
                }
                
                // Read file
                var reader = new FileReader();
                reader.onload = function(e) {
                    var importData = e.target.result;
                    
                    // Send to server
                    $.ajax({
                        url: scfmAdmin.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'scfm_import_fields',
                            nonce: scfmAdmin.nonce,
                            section: section,
                            import_data: importData
                        },
                        success: function(response) {
                            if (response.success) {
                                SCFM_Admin.loadFields();
                                SCFM_Admin.showNotice(response.data.message, 'success');
                            } else {
                                SCFM_Admin.showNotice(response.data.message || scfmAdmin.strings.error, 'error');
                            }
                        },
                        error: function() {
                            SCFM_Admin.showNotice('Import failed. Please check the file format.', 'error');
                        }
                    });
                };
                
                reader.readAsText(file);
            });
            
            // Trigger file selection
            fileInput.click();
        },
        
        /**
         * Initialize Stylish tab
         */
        initStylish: function() {
            // Color pickers
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.scfm-color-picker').wpColorPicker({
                    change: function() {
                        SCFM_Admin.updatePreview();
                    }
                });
            }
            
            // Range sliders
            $('input[type="range"]').on('input', function() {
                $(this).next('.scfm-range-value').text($(this).val());
                SCFM_Admin.updatePreview();
            });
            
            // Initialize range values
            $('input[type="range"]').each(function() {
                $(this).next('.scfm-range-value').text($(this).val());
            });
            
            // Power beautify toggle
            $('#scfm-power-beautify').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#scfm-individual-options').slideUp();
                    SCFM_Admin.applyPowerBeautify();
                } else {
                    $('#scfm-individual-options').slideDown();
                }
                SCFM_Admin.updatePreview();
            });
            
            // All style inputs
            $('#scfm-individual-options input, #scfm-individual-options select').on('change', function() {
                SCFM_Admin.updatePreview();
            });
            
            // Save stylish settings
            $('#scfm-save-stylish').on('click', function() {
                SCFM_Admin.saveStylish();
            });
            
            // Reset stylish settings
            $('#scfm-reset-stylish').on('click', function() {
                if (confirm('Are you sure you want to reset all stylish settings to defaults?')) {
                    SCFM_Admin.resetStylish();
                }
            });
            
            // Initial preview update
            SCFM_Admin.updatePreview();
        },
        
        /**
         * Apply power beautify values
         */
        applyPowerBeautify: function() {
            $('input[name="stylish[primary_color]"]').wpColorPicker('color', '#667eea');
            $('input[name="stylish[background_color]"]').wpColorPicker('color', '#ffffff');
            $('input[name="stylish[text_color]"]').wpColorPicker('color', '#1a202c');
            $('input[name="stylish[label_color]"]').wpColorPicker('color', '#667eea');
            $('input[name="stylish[border_radius]"]').val(16).trigger('input');
            $('select[name="stylish[shadow]"]').val('glow');
            $('input[name="stylish[hover_effect]"]').prop('checked', true);
            $('select[name="stylish[focus_effect]"]').val('glow');
            $('select[name="stylish[font_family]"]').val('poppins');
            $('input[name="stylish[font_size]"]').val(16).trigger('input');
            $('select[name="stylish[font_weight]"]').val('500');
            $('input[name="stylish[placeholder_color]"]').wpColorPicker('color', '#94a3b8');
            $('input[name="stylish[placeholder_italic]"]').prop('checked', true);
            $('input[name="stylish[button_style]"]').prop('checked', true);
            $('input[name="stylish[button_accent]"]').wpColorPicker('color', '#f093fb');
            $('select[name="stylish[entrance_animation]"]').val('bounce');
            $('select[name="stylish[transition_speed]"]').val('normal');
        },
        
        /**
         * Update preview
         */
        updatePreview: function() {
            var primaryColor = $('input[name="stylish[primary_color]"]').val();
            var bgColor = $('input[name="stylish[background_color]"]').val();
            var textColor = $('input[name="stylish[text_color]"]').val();
            var labelColor = $('input[name="stylish[label_color]"]').val();
            var borderRadius = $('input[name="stylish[border_radius]"]').val();
            var fontSize = $('input[name="stylish[font_size]"]').val();
            var fontWeight = $('select[name="stylish[font_weight]"]').val();
            var shadow = $('select[name="stylish[shadow]"]').val();
            var placeholderColor = $('input[name="stylish[placeholder_color]"]').val();
            
            var shadowValues = {
                'none': 'none',
                'light': '0 1px 3px rgba(0, 0, 0, 0.05)',
                'medium': '0 2px 8px rgba(0, 0, 0, 0.08)',
                'heavy': '0 4px 16px rgba(0, 0, 0, 0.12)',
                'glow': '0 0 20px rgba(79, 70, 229, 0.15)'
            };
            
            $('.scfm-preview-field').css({
                'background-color': bgColor,
                'color': textColor,
                'border-radius': borderRadius + 'px',
                'font-size': fontSize + 'px',
                'font-weight': fontWeight,
                'box-shadow': shadowValues[shadow],
                'border': '2px solid #e2e8f0'
            });
            
            $('.scfm-preview-field').on('focus', function() {
                $(this).css('border-color', primaryColor);
            }).on('blur', function() {
                $(this).css('border-color', '#e2e8f0');
            });
            
            $('.scfm-preview-field-wrapper label').css('color', labelColor);
            
            // Update placeholder color (requires style injection)
            var styleId = 'scfm-preview-placeholder-style';
            $('#' + styleId).remove();
            $('<style id="' + styleId + '">.scfm-preview-field::placeholder { color: ' + placeholderColor + ' !important; }</style>').appendTo('head');
        },
        
        /**
         * Save stylish settings
         */
        saveStylish: function() {
            var $button = $('#scfm-save-stylish');
            var originalText = $button.html();
            
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + scfmAdmin.strings.saving);
            
            var formData = {};
            $('#scfm-individual-options input, #scfm-individual-options select, #scfm-power-beautify').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                if (name) {
                    var key = name.replace('stylish[', '').replace(']', '');
                    if ($input.attr('type') === 'checkbox') {
                        formData[key] = $input.is(':checked') ? 1 : 0;
                    } else {
                        formData[key] = $input.val();
                    }
                }
            });
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_save_stylish',
                    nonce: scfmAdmin.nonce,
                    options: formData
                },
                success: function(response) {
                    if (response.success) {
                        SCFM_Admin.showNotice(response.data.message, 'success');
                    } else {
                        SCFM_Admin.showNotice(response.data.message || scfmAdmin.strings.error, 'error');
                    }
                },
                error: function() {
                    SCFM_Admin.showNotice(scfmAdmin.strings.error, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalText);
                }
            });
        },
        
        /**
         * Reset stylish settings
         */
        resetStylish: function() {
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_reset_stylish',
                    nonce: scfmAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        SCFM_Admin.showNotice(response.data.message || scfmAdmin.strings.error, 'error');
                    }
                },
                error: function() {
                    SCFM_Admin.showNotice(scfmAdmin.strings.error, 'error');
                }
            });
        },
        
        /**
         * Update plugin from GitHub
         */
        updateFromGitHub: function(e) {
            e.preventDefault();
            
            if (!confirm(scfmAdmin.strings.confirm_update)) {
                return;
            }
            
            var $button = $(this);
            var originalText = $button.html();
            var $status = $('#scfm-update-status');
            
            $button.prop('disabled', true).html('<span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>' + scfmAdmin.strings.updating);
            $status.html('<div class="notice notice-info inline"><p>' + scfmAdmin.strings.updating + '</p></div>');
            
            $.ajax({
                url: scfmAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'scfm_update_from_github',
                    nonce: scfmAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $status.html('<div class="notice notice-error inline"><p>' + (response.data.message || scfmAdmin.strings.error) + '</p></div>');
                        $button.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    $status.html('<div class="notice notice-error inline"><p>' + scfmAdmin.strings.error + '</p></div>');
                    $button.prop('disabled', false).html(originalText);
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        SCFM_Admin.init();
        
        // Initialize stylish tab if it exists
        if ($('#stylish').length) {
            SCFM_Admin.initStylish();
        }
    });
    
})(jQuery);
