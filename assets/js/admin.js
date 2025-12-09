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
            this.initSortable();
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
            
            // Reset fields button
            $('.scfm-reset-fields').on('click', this.resetFields);
            
            // Edit field button (delegated)
            $(document).on('click', '.scfm-btn-edit', this.editField);
            
            // Delete field button (delegated)
            $(document).on('click', '.scfm-btn-delete', this.deleteField);
            
            // Toggle field status (delegated)
            $(document).on('change', '.scfm-toggle input', this.toggleField);
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
        },
        
        /**
         * Render a single field row
         */
        renderFieldRow: function(fieldId, field) {
            var isCustom = field.custom !== false;
            var isDefaultWC = field.default_wc === true;
            var typeLabel = field.type || 'text';
            var typeBadgeClass = isCustom ? 'scfm-custom' : 'scfm-default';
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
            
            // TODO: Open modal in Phase 2
            alert('Add field modal will be implemented in Phase 2');
        },
        
        /**
         * Edit field
         */
        editField: function(e) {
            e.preventDefault();
            var fieldId = $(this).data('field-id');
            
            // TODO: Open edit modal in Phase 2
            alert('Edit field modal will be implemented in Phase 2');
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
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        SCFM_Admin.init();
    });
    
})(jQuery);
