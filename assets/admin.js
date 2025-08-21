/**
 * Plugincy Product Analytics Pro - Admin JavaScript
 */

(function ($) {
    'use strict';

    const PAP = {
        init: function () {
            this.addStyles();
            this.bindEvents();
            this.initCharts();
            this.initTables();
            this.checkUrlForNotification();
        },

        bindEvents: function () {
            // Product card menu toggles
            $(document).on('click', '.pap-menu-toggle', this.toggleProductMenu);

            // Product actions
            $(document).on('click', '.pap-edit-product', this.editProduct);
            $(document).on('click', '.pap-delete-product', this.deleteProduct);

            // Site details
            $(document).on('click', '.pap-view-details', this.viewSiteDetails);

            // Filters
            $(document).on('click', '#apply_filters', this.applyFilters);

            // Form submissions
            // $(document).on('submit', '.pap-form', this.handleFormSubmit);

            // Close dropdowns when clicking outside
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.pap-card-menu').length) {
                    $('.pap-dropdown-menu').removeClass('show');
                }
            });
        },

        checkUrlForNotification: function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('m') && urlParams.get('m') === 'created') {
                this.showNotification('Product added successfully', 'success');
            }
        },

        toggleProductMenu: function (e) {
            e.stopPropagation();
            const $menu = $(this).siblings('.pap-dropdown-menu');
            $('.pap-dropdown-menu').not($menu).removeClass('show');
            $menu.toggleClass('show');
        },

        editProduct: function (e) {
            e.preventDefault();
            const productId = $(this).data('product-id');

            // Create modal or redirect to edit page
            PAP.showEditModal(productId);
        },

        deleteProduct: function (e) {
            e.preventDefault();
            const productId = $(this).data('product-id');

            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                PAP.deleteProductAjax(productId);
            }
        },

        showEditModal: function (productId) {
            // Get product data
            $.post(pap_ajax.ajax_url, {
                action: 'pap_get_product',
                product_id: productId,
                nonce: pap_ajax.nonce
            }, function (response) {
                if (response.success) {
                    PAP.renderEditModal(response.data);
                } else {
                    PAP.showNotification('Error loading product data', 'error');
                }
            });
        },

        renderEditModal: function (product) {
            const modal = `
                <div class="pap-modal-overlay" id="editModal">
                    <div class="pap-modal">
                        <div class="pap-modal-header">
                            <h3>Edit Product</h3>
                            <button class="pap-modal-close">&times;</button>
                        </div>
                        <div class="pap-modal-body">
                            <form id="editProductForm">
                                <div class="pap-form-group">
                                    <label for="edit_product_name">Product Name</label>
                                    <input type="text" id="edit_product_name" name="product_name" value="${product.product_name}" required>
                                </div>
                                <div class="pap-form-group">
                                    <label for="edit_product_id">Product ID</label>
                                    <input type="text" id="edit_product_id" name="product_id" value="${product.product_id}" readonly>
                                </div>
                                <div class="pap-form-group">
                                    <label for="edit_description">Description</label>
                                    <textarea id="edit_description" name="description" rows="4">${product.description}</textarea>
                                </div>
                                <div class="pap-form-actions">
                                    <button type="submit" class="pap-btn pap-btn-primary">Update Product</button>
                                    <button type="button" class="pap-btn pap-btn-secondary pap-modal-close">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modal);

            // Bind modal events
            $('#editModal').on('click', '.pap-modal-close', this.closeModal);
            $('#editModal').on('click', '.pap-modal-overlay', function (e) {
                if (e.target === this) PAP.closeModal();
            });
            $('#editProductForm').on('submit', this.updateProduct);
        },

        closeModal: function () {
            $('.pap-modal-overlay').fadeOut(300, function () {
                $(this).remove();
            });
        },

        updateProduct: function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'pap_update_product');
            formData.append('nonce', pap_ajax.nonce);

            $.ajax({
                url: pap_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        PAP.showNotification('Product updated successfully', 'success');
                        PAP.closeModal();
                        location.reload();
                    } else {
                        PAP.showNotification(response.data.message || 'Error updating product', 'error');
                    }
                },
                error: function () {
                    PAP.showNotification('Error updating product', 'error');
                }
            });
        },

        deleteProductAjax: function (productId) {
            $.post(pap_ajax.ajax_url, {
                action: 'pap_delete_product',
                product_id: productId,
                nonce: pap_ajax.nonce
            }, function (response) {
                if (response.success) {
                    PAP.showNotification('Product deleted successfully', 'success');
                    $(`.pap-product-card[data-product-id="${productId}"]`).fadeOut(300, function () {
                        $(this).remove();
                    });
                } else {
                    PAP.showNotification(response.data.message || 'Error deleting product', 'error');
                }
            });
        },

        viewSiteDetails: function (e) {
            e.preventDefault();
            const siteId = $(this).data('site-id');

            $.post(pap_ajax.ajax_url, {
                action: 'pap_get_site_details',
                site_id: siteId,
                nonce: pap_ajax.nonce
            }, function (response) {
                if (response.success) {
                    PAP.showSiteDetailsModal(response.data);
                } else {
                    PAP.showNotification('Error loading site details', 'error');
                }
            });
        },

        showSiteDetailsModal: function (site) {
            const otherPlugins = site.other_plugins;
            const pluginsList = Array.isArray(otherPlugins) ?
                otherPlugins.map(plugin => `<li class="pap-plugin-item">
            <span class="pap-plugin-name">${plugin["name"]}</span>
            <span class="pap-plugin-version">v${plugin["version"]}</span>
        </li>`).join('') :
                '<li class="pap-no-plugins">No additional plugins detected</li>';

            // Status color mapping
            const statusColors = {
                'active': '#10B981',
                'inactive': '#EF4444',
                'pending': '#F59E0B',
                'suspended': '#DC2626'
            };

            const statusColor = statusColors[site.status] || '#6B7280';

            const modal = `
        <div class="pap-modal-overlay" id="siteDetailsModal">
            <div class="pap-modal pap-modal-large">
                <div class="pap-modal-header">
                    <div class="pap-modal-title-section">
                        <h3 class="pap-modal-title">Site Details</h3>
                        <div class="pap-site-url-container">
                            <span class="pap-site-url">${site.site_url}</span>
                            <button class="pap-copy-url" data-url="${site.site_url}" title="Copy URL">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button class="pap-modal-close" aria-label="Close modal">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="pap-modal-body">
                    <div class="pap-site-details">
                        <!-- Status Banner -->
                        <div class="pap-status-banner" style="background-color: ${statusColor}15; border-left: 4px solid ${statusColor};">
                            <div class="pap-status-info">
                                <span class="pap-status-dot" style="background-color: ${statusColor};"></span>
                                <span class="pap-status-text" style="color: ${statusColor};">${site.status.toUpperCase()}</span>
                                ${site.using_pro === "1" ? '<span class="pap-pro-badge">PRO</span>' : ''}
                            </div>
                        </div>

                        <!-- Tabs Navigation -->
                        <div class="pap-tabs-nav">
                            <button class="pap-tab-btn active" data-tab="overview">Overview</button>
                            <button class="pap-tab-btn" data-tab="technical">Technical</button>
                            <button class="pap-tab-btn" data-tab="plugins">Plugins</button>
                            <button class="pap-tab-btn" data-tab="license">License</button>
                        </div>

                        <!-- Tab Contents -->
                        <div class="pap-site-info-tab-content active" data-tab="overview">
                            <div class="pap-details-grid">
                                <div class="pap-detail-card">
                                    <div class="pap-detail-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                                        </svg>
                                        <h4>Site Status</h4>
                                    </div>
                                    <div class="pap-detail-content">
                                        <div class="pap-status-display">
                                            <span class="pap-status-indicator" style="background-color: ${statusColor};"></span>
                                            ${site.status.charAt(0).toUpperCase() + site.status.slice(1)}
                                        </div>
                                        ${site.deactivate_reason && site.status !=="active" ? `<p class="pap-deactivate-reason">${site.deactivate_reason}</p>` : ''}
                                        ${site.activation_date  ? `<p class="pap-deactivate-reason">Activated On: ${site.activation_date}</p>` : ''}
                                    </div>
                                </div>

                                <div class="pap-detail-card">
                                    <div class="pap-detail-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                            <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                        </svg>
                                        <h4>WordPress Info</h4>
                                    </div>
                                    <div class="pap-detail-content">
                                        <div class="pap-version-info">
                                            <span class="pap-version-label">Version:</span>
                                            <span class="pap-version-value">${site.wp_version}</span>
                                        </div>
                                        <div class="pap-theme-info">
                                            <span class="pap-theme-label">Active Theme:</span>
                                            <span class="pap-theme-value">${site.active_theme}</span>
                                        </div>
                                        <div class="pap-theme-info">
                                            <span class="pap-theme-label">Multisite Enabled:</span>
                                            <span class="pap-theme-value">${site.multisite ==="1" ?'yes':'No'}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pap-detail-card">
                                    <div class="pap-detail-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <h4>Location</h4>
                                    </div>
                                    <div class="pap-detail-content">
                                        <span class="pap-location-value">${site.location || 'Not specified'}</span>
                                    </div>
                                </div>
                                <div class="pap-detail-card">
                                    <div class="pap-detail-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                                            <line x1="4" y1="4" x2="20" y2="20"></line>
                                        </svg>
                                        <h4>Last Deactivation Reason</h4>
                                    </div>
                                    <div class="pap-detail-content">                                        
                                        ${site.deactivate_reason ? `<p class="pap-deactivate-reason">${site.deactivate_reason}</p>` : ''}
                                        ${site.deactivation_date ? `<p class="pap-deactivate-reason">Deactivated On: ${site.deactivation_date}</p>` : ''}
                                        ${site.last_seen  ? `<p class="pap-deactivate-reason">Last Seen On: ${site.last_seen}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pap-site-info-tab-content" data-tab="technical">
                            <div class="pap-details-grid">
                                <div class="pap-detail-card">
                                    <div class="pap-detail-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                            <line x1="8" y1="21" x2="16" y2="21"></line>
                                            <line x1="12" y1="17" x2="12" y2="21"></line>
                                        </svg>
                                        <h4>Server Environment</h4>
                                    </div>
                                    <div class="pap-detail-content">
                                        <div class="pap-tech-specs">
                                            <div class="pap-spec-item">
                                                <span class="pap-spec-label">PHP Version:</span>
                                                <span class="pap-spec-value">${site.php_version}</span>
                                            </div>
                                            <div class="pap-spec-item">
                                                <span class="pap-spec-label">MySQL Version:</span>
                                                <span class="pap-spec-value">${site.mysql_version}</span>
                                            </div>
                                            <div class="pap-spec-item">
                                                <span class="pap-spec-label">Server Software:</span>
                                                <span class="pap-spec-value">${site.server_software}</span>
                                            </div>
                                            <div class="pap-spec-item">
                                                <span class="pap-spec-label">Plugin Version:</span>
                                                <span class="pap-spec-value">${site.plugin_version}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pap-site-info-tab-content" data-tab="plugins">
                            <div class="pap-plugins-section">
                                <div class="pap-plugins-header">
                                    <h4>Installed Plugins</h4>
                                    <span class="pap-plugins-count">${Array.isArray(otherPlugins) ? otherPlugins.length : 0} plugins</span>
                                </div>
                                <div class="pap-plugins-list">
                                    <ul class="pap-plugins-ul">${pluginsList}</ul>
                                </div>
                            </div>
                        </div>

                        <div class="pap-site-info-tab-content" data-tab="license">
                            <div class="pap-details-grid">
                                <div class="pap-detail-card">
                                    <div class="pap-detail-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <h4>License Information</h4>
                                    </div>
                                    <div class="pap-detail-content">
                                        <div class="pap-license-info">
                                            <div class="pap-license-key">
                                                <span class="pap-license-label">License Key:</span>
                                                <div class="pap-license-value-container">
                                                    <span class="pap-license-value">${site.license_key || 'Not available'}</span>
                                                    ${site.license_key ? `<button class="pap-copy-license" data-license="${site.license_key}" title="Copy License Key">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                        </svg>
                                                    </button>` : ''}
                                                </div>
                                            </div>                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            #siteDetailsModal .pap-modal-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .pap-modal-title-section {
                flex: 1;
            }

            .pap-modal-title {
                margin: 0 0 8px 0;
                font-size: 24px;
                font-weight: 600;
                color: #fff !important;
            }

            .pap-site-url-container {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 8px;
            }

            .pap-site-url {
                font-size: 14px;
                opacity: 0.9;
                font-family: monospace;
                background: rgba(255, 255, 255, 0.1);
                padding: 4px 8px;
                border-radius: 4px;
            }

            .pap-copy-url, .pap-copy-license {
                background: rgba(255, 255, 255, 0.1);
                border: none;
                color: white;
                padding: 6px;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .pap-copy-url:hover, .pap-copy-license:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: translateY(-1px);
            }

            .pap-modal-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                padding: 8px;
                border-radius: 6px;
                transition: all 0.2s;
            }

            .pap-modal-close:hover {
                background: rgba(255, 255, 255, 0.1);
                transform: rotate(90deg);
            }

            .pap-modal-body {
                padding: 0;
                max-height: calc(90vh - 100px);
                overflow-y: auto;
            }

            .pap-status-banner {
                padding: 16px 20px;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .pap-status-info {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .pap-status-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            .pap-status-text {
                font-weight: 600;
                font-size: 14px;
                letter-spacing: 0.5px;
            }

            .pap-pro-badge {
                background: linear-gradient(135deg, #ffd700, #ffb700);
                color: #333;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.5px;
            }

            .pap-tabs-nav {
                display: flex;
                background: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
                padding: 0 20px;
            }

            .pap-tab-btn {
                background: none;
                border: none;
                padding: 16px 20px;
                cursor: pointer;
                font-weight: 500;
                color: #6c757d;
                border-bottom: 3px solid transparent;
                transition: all 0.2s;
            }

            .pap-tab-btn:hover {
                color: #495057;
                background: rgba(0, 0, 0, 0.02);
            }

            .pap-tab-btn.active {
                color: #667eea;
                border-bottom-color: #667eea;
                background: white;
            }

            .pap-site-info-tab-content {
                display: none;
                padding: 20px;
                animation: fadeInUp 0.3s ease-out;
            }

            .pap-site-info-tab-content.active {
                display: block;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .pap-details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }

            .pap-detail-card {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
                transition: all 0.2s;
            }

            .pap-detail-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }

            .pap-detail-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 1px solid #e9ecef;
            }

            .pap-detail-header svg {
                color: #667eea;
            }

            .pap-detail-header h4 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #333;
            }

            .pap-detail-content {
                line-height: 1.6;
            }

            .pap-status-display {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 500;
                margin-bottom: 8px;
            }

            .pap-status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
            }

            .pap-deactivate-reason {
                color: #6c757d;
                font-size: 14px;
                margin: 8px 0 0 0;
                font-style: italic;
            }

            .pap-version-info, .pap-theme-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
            }

            .pap-version-label, .pap-theme-label, .pap-spec-label {
                font-weight: 500;
                color: #6c757d;
            }

            .pap-version-value, .pap-theme-value, .pap-spec-value {
                font-family: monospace;
                background: #f8f9fa;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 14px;
            }

            .pap-location-value {
                font-size: 16px;
                font-weight: 500;
                color: #333;
            }

            .pap-tech-specs {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .pap-spec-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .pap-plugins-section {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                overflow: hidden;
            }

            .pap-plugins-header {
                background: #f8f9fa;
                padding: 16px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #e9ecef;
            }

            .pap-plugins-header h4 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
            }

            .pap-plugins-count {
                background: #667eea;
                color: white;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 500;
            }

            .pap-plugins-list {
                max-height: 300px;
                overflow-y: auto;
            }

            .pap-plugins-ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .pap-plugin-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 20px;
                border-bottom: 1px solid #f1f3f4;
                transition: background-color 0.2s;
            }

            .pap-plugin-item:hover {
                background: #f8f9fa;
            }

            .pap-plugin-item:last-child {
                border-bottom: none;
            }

            .pap-plugin-name {
                font-weight: 500;
                color: #333;
            }

            .pap-plugin-version {
                color: #6c757d;
                font-size: 14px;
                font-family: monospace;
                background: #f8f9fa;
                padding: 2px 6px;
                border-radius: 4px;
            }

            .pap-no-plugins {
                padding: 20px;
                text-align: center;
                color: #6c757d;
                font-style: italic;
            }

            .pap-license-info {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .pap-license-key, .pap-deactivation-date {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .pap-license-label, .pap-deactivation-label {
                font-weight: 500;
                color: #6c757d;
                font-size: 14px;
            }

            .pap-license-value-container {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .pap-license-value, .pap-deactivation-value {
                font-family: monospace;
                background: #f8f9fa;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 14px;
                flex: 1;
            }

            .pap-copy-license {
                color: #667eea;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
            }

            .pap-copy-license:hover {
                background: #e9ecef;
                color: #495057;
            }

            @media (max-width: 768px) {
                .pap-modal {
                    width: 95%;
                    max-height: 95vh;
                }
                
                .pap-details-grid {
                    grid-template-columns: 1fr;
                }
                
                .pap-tabs-nav {
                    flex-wrap: wrap;
                    padding: 0 10px;
                }
                
                .pap-tab-btn {
                    padding: 12px 16px;
                    font-size: 14px;
                }
            }
        </style>
    `;

            $('body').append(modal);

            // Initialize tabs functionality
            const initTabs = () => {
                const tabBtns = document.querySelectorAll('.pap-tab-btn');
                const tabContents = document.querySelectorAll('.pap-site-info-tab-content');

                tabBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const targetTab = btn.dataset.tab;

                        // Remove active class from all tabs and contents
                        tabBtns.forEach(b => b.classList.remove('active'));
                        tabContents.forEach(c => c.classList.remove('active'));

                        // Add active class to clicked tab and corresponding content
                        btn.classList.add('active');
                        document.querySelector(`[data-tab="${targetTab}"].pap-site-info-tab-content`).classList.add('active');
                    });
                });
            };

            // Initialize copy functionality
            const initCopyFunctionality = () => {
                // Copy URL functionality
                document.querySelector('.pap-copy-url')?.addEventListener('click', function () {
                    const url = this.dataset.url;
                    navigator.clipboard.writeText(url).then(() => {
                        this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"></polyline></svg>';
                        setTimeout(() => {
                            this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
                        }, 2000);
                    });
                });

                // Copy license functionality
                document.querySelector('.pap-copy-license')?.addEventListener('click', function () {
                    const license = this.dataset.license;
                    navigator.clipboard.writeText(license).then(() => {
                        this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"></polyline></svg>';
                        setTimeout(() => {
                            this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
                        }, 2000);
                    });
                });
            };

            // Initialize all functionality
            setTimeout(() => {
                initTabs();
                initCopyFunctionality();
            }, 100);

            // Bind modal events
            $('#siteDetailsModal').on('click', '.pap-modal-close', this.closeModal);
            $('#siteDetailsModal').on('click', '.pap-modal-overlay', function (e) {
                if (e.target === this) PAP.closeModal();
            });

            // Keyboard support
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $('#siteDetailsModal').length) {
                    PAP.closeModal();
                }
            });
        },

        showNotification: function (message, type) {
            // Remove existing notifications
            $('.pap-notification').remove();

            const notificationClass = type === 'error' ? 'pap-notification-error' : 'pap-notification-success';
            const icon = type === 'error' ? '❌' : '✅';

            const notification = `
                <div class="pap-notification ${notificationClass}" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${type === 'error' ? '#EF4444' : '#10B981'};
                    color: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    min-width: 300px;
                    animation: slideIn 0.3s ease-out;
                ">
                    <span>${icon}</span>
                    <span>${message}</span>
                    <button class="pap-notification-close" style="
                        background: none;
                        border: none;
                        color: white;
                        cursor: pointer;
                        font-size: 18px;
                        margin-left: auto;
                        padding: 0;
                    ">&times;</button>
                </div>
            `;

            $('body').append(notification);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                $('.pap-notification').fadeOut(300, function () {
                    $(this).remove();
                });
            }, 5000);

            // Close on click
            $('.pap-notification-close').on('click', function () {
                $(this).parent().fadeOut(300, function () {
                    $(this).remove();
                });
            });
        },

        initCharts: function () {
            // Initialize charts if Chart.js is available and chart elements exist
            if (typeof Chart === 'undefined') return;
            const data = pap_ajax.chartData;

            // Common chart options
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            };

            // Color palettes
            const colors = {
                primary: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'],
                status: ['#10B981', '#EF4444'],
                versions: ['#3B82F6', '#1E40AF', '#60A5FA', '#93C5FD', '#DBEAFE'],
                red: ['#EF4444', '#DC2626', '#B91C1C', '#991B1B', '#7F1D1D', '#FCA5A5', '#F87171', '#FECACA'],
                neutral: ['#6B7280', '#F59E0B']
            };

            // Active vs Inactive Chart
            const activeInactiveCtx = document.getElementById('activeInactiveChart');
            if (activeInactiveCtx && data.chart_data) {
                new Chart(activeInactiveCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [
                                parseInt(data.chart_data.active, 10) || 0,
                                parseInt(data.chart_data.inactive, 10) || 0
                            ],
                            backgroundColor: colors.status,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // Multisite Chart
            const multisiteCtx = document.getElementById('multisiteChart');
            if (multisiteCtx) {
                new Chart(multisiteCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Single Site', 'Multisite'],
                        datasets: [{
                            data: [data.multisite_usage.singlesite || 0, data.multisite_usage.multisite || 0],
                            backgroundColor: ['#3B82F6', '#8B5CF6'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // WordPress Versions Chart - Bar chart for better comparison
            const wpVersionCtx = document.getElementById('wpVersionChart');
            if (wpVersionCtx && data.wp_versions) {
                const wpVersions = Object.keys(data.wp_versions);
                const wpVersionCounts = Object.values(data.wp_versions);

                new Chart(wpVersionCtx, {
                    type: 'bar',
                    data: {
                        labels: wpVersions,
                        datasets: [{
                            label: 'Sites',
                            data: wpVersionCounts,
                            backgroundColor: colors.versions.slice(0, wpVersions.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // PHP Versions Chart - Bar chart for better comparison
            const phpVersionCtx = document.getElementById('phpVersionChart');
            if (phpVersionCtx && data.php_versions) {
                const phpVersions = Object.keys(data.php_versions);
                const phpVersionCounts = Object.values(data.php_versions);

                new Chart(phpVersionCtx, {
                    type: 'bar',
                    data: {
                        labels: phpVersions,
                        datasets: [{
                            label: 'Sites',
                            data: phpVersionCounts,
                            backgroundColor: colors.primary.slice(0, phpVersions.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Pro vs Free Chart
            const proFreeCtx = document.getElementById('proFreeChart');
            if (proFreeCtx) {
                new Chart(proFreeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Free', 'Pro'],
                        datasets: [{
                            data: [data.pro_vs_free.Free || 0, data.pro_vs_free.Pro || 0],
                            backgroundColor: colors.neutral,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // Deactivation Reasons Chart - Horizontal bar for better readability
            const deactivationCtx = document.getElementById('deactivationChart');
            if (deactivationCtx && data.deactivation_reasons) {
                const deactivationLabels = Object.keys(data.deactivation_reasons);
                const deactivationCounts = Object.values(data.deactivation_reasons);

                new Chart(deactivationCtx, {
                    type: 'bar',
                    data: {
                        labels: deactivationLabels,
                        datasets: [{
                            label: 'Count',
                            data: deactivationCounts,
                            backgroundColor: colors.red.slice(0, deactivationLabels.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Server Software Chart
            const serverChart = document.getElementById('serverChart');
            if (serverChart && data.server_software) {
                new Chart(serverChart, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.server_software),
                        datasets: [{
                            data: Object.values(data.server_software),
                            backgroundColor: colors.primary.slice(0, Object.keys(data.server_software).length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // MySQL Versions Chart
            const mysqlversionsChart = document.getElementById('mysqlversionsChart');
            if (mysqlversionsChart && data.mysql_versions) {
                new Chart(mysqlversionsChart, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.mysql_versions),
                        datasets: [{
                            data: Object.values(data.mysql_versions),
                            backgroundColor: colors.versions.slice(0, Object.keys(data.mysql_versions).length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // Plugin Versions Chart
            const pluginversionsChart = document.getElementById('pluginversionsChart');
            if (pluginversionsChart && data.plugin_versions) {
                new Chart(pluginversionsChart, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.plugin_versions),
                        datasets: [{
                            data: Object.values(data.plugin_versions),
                            backgroundColor: colors.primary.slice(0, Object.keys(data.plugin_versions).length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // Locations Chart - Polar area for geographic data
            const locationsChart = document.getElementById('locationsChart');
            if (locationsChart && data.locations) {
                new Chart(locationsChart, {
                    type: 'polarArea',
                    data: {
                        labels: Object.keys(data.locations),
                        datasets: [{
                            data: Object.values(data.locations),
                            backgroundColor: colors.primary.slice(0, Object.keys(data.locations).length).map(color => color + '80'),
                            borderColor: colors.primary.slice(0, Object.keys(data.locations).length),
                            borderWidth: 2
                        }]
                    },
                    options: commonOptions
                });
            }

            // Top 10 Most Used Sites Chart
            const top10mostusedChart = document.getElementById('top10mostusedChart');
            if (top10mostusedChart && data.top_sites_days_used) {
                const topSites = Object.keys(data.top_sites_days_used);
                const topSitesCounts = Object.values(data.top_sites_days_used);

                new Chart(top10mostusedChart, {
                    type: 'bar',
                    data: {
                        labels: topSites,
                        datasets: [{
                            label: 'Days Used',
                            data: topSitesCounts,
                            backgroundColor: colors.primary.slice(0, topSites.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            }

            // Most Used Plugins Chart
            const usedpluginChart = document.getElementById('usedpluginChart');
            if (usedpluginChart && data.most_used_plugins) {
                const plugins = Object.keys(data.most_used_plugins);
                const pluginCounts = Object.values(data.most_used_plugins);

                new Chart(usedpluginChart, {
                    type: 'bar',
                    data: {
                        labels: plugins,
                        datasets: [{
                            label: 'Usage Count',
                            data: pluginCounts,
                            backgroundColor: colors.primary.slice(0, plugins.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Most Used Themes Chart
            const usedthemeChart = document.getElementById('usedthemeChart');
            if (usedthemeChart && data.most_used_themes) {
                const themes = Object.keys(data.most_used_themes);
                const themeCounts = Object.values(data.most_used_themes);

                new Chart(usedthemeChart, {
                    type: 'bar',
                    data: {
                        labels: themes,
                        datasets: [{
                            label: 'Usage Count',
                            data: themeCounts,
                            backgroundColor: colors.primary.slice(0, themes.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Deactivated Themes Chart
            const deactiveusedthemeChart = document.getElementById('deactiveusedthemeChart');
            if (deactiveusedthemeChart && data.deactivated_themes) {
                new Chart(deactiveusedthemeChart, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.deactivated_themes),
                        datasets: [{
                            data: Object.values(data.deactivated_themes),
                            backgroundColor: colors.red.slice(0, Object.keys(data.deactivated_themes).length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%'
                    }
                });
            }

            // Yearly Install/Uninstall Chart - Area chart for trends
            const yearlyinstallChart = document.getElementById('yearlyinstallChart');
            if (yearlyinstallChart) {
                new Chart(yearlyinstallChart, {
                    type: 'line',
                    data: {
                        labels: [
                            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                        ],
                        datasets: [
                            {
                                label: 'Installs',
                                data: data.installs_this_year ? Object.values(data.installs_this_year) : [],
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16,185,129,0.1)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#10B981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6
                            },
                            {
                                label: 'Uninstalls',
                                data: data.uninstalls_this_year ? Object.values(data.uninstalls_this_year) : [],
                                borderColor: '#EF4444',
                                backgroundColor: 'rgba(239,68,68,0.1)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#EF4444',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
            // Monthly Install/Uninstall Chart - Area chart for trends
            const monthlyinstallChart = document.getElementById('monthlyinstallChart');
            if (monthlyinstallChart) {
                new Chart(monthlyinstallChart, {
                    type: 'bar', // Changed from 'line' to 'bar'
                    data: {
                        labels: data.installs_this_month ? Object.keys(data.installs_this_month) : [],
                        datasets: [
                            {
                                label: 'Installs',
                                data: data.installs_this_month ? Object.values(data.installs_this_month) : [],
                                backgroundColor: 'rgba(16,185,129,0.7)',
                                borderColor: '#10B981',
                                borderWidth: 1
                            },
                            {
                                label: 'Uninstalls',
                                data: data.uninstalls_this_month ? Object.values(data.uninstalls_this_month) : [],
                                backgroundColor: 'rgba(239,68,68,0.7)',
                                borderColor: '#EF4444',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        },

        initTables: function () {
            // Initialize DataTables if available
            if (typeof $.fn.DataTable !== 'undefined') {
                $('.pap-table').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        search: 'Search sites:',
                        lengthMenu: 'Show _MENU_ sites per page',
                        info: 'Showing _START_ to _END_ of _TOTAL_ sites',
                        paginate: {
                            first: 'First',
                            last: 'Last',
                            next: 'Next',
                            previous: 'Previous'
                        }
                    }
                });
            }
        },

        applyFilters: function (e) {
            e.preventDefault();

            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            if (!startDate || !endDate) {
                PAP.showNotification('Please select both start and end dates', 'error');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                PAP.showNotification('Start date cannot be after end date', 'error');
                return;
            }

            // Filter the deactivations table
            const tableRows = $('.pap-deactivations-table tbody tr');
            let visibleCount = 0;

            tableRows.each(function () {
                const rowDate = $(this).find('td:nth-child(2)').text();
                const rowDateObj = new Date(rowDate);
                const startDateObj = new Date(startDate);
                const endDateObj = new Date(endDate);

                if (rowDateObj >= startDateObj && rowDateObj <= endDateObj) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            PAP.showNotification(`Filtered to ${visibleCount} deactivations`, 'success');
        },

        handleFormSubmit: function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Disable submit button and show loading
            $submitBtn.prop('disabled', true).text('Processing...');

            // Validate form fields
            const requiredFields = $form.find('[required]');
            let isValid = true;

            requiredFields.each(function () {
                if (!$(this).val().trim()) {
                    isValid = false;
                    $(this).addClass('pap-error');
                } else {
                    $(this).removeClass('pap-error');
                }
            });

            if (!isValid) {
                PAP.showNotification('Please fill in all required fields', 'error');
                $submitBtn.prop('disabled', false).text(originalText);
                return;
            }

            // If this is not the edit form, proceed with normal submission
            if (!$form.is('#editProductForm')) {
                $form.off('submit').submit();
                return;
            }

            // Handle edit form via AJAX (already handled in updateProduct)
            PAP.updateProduct.call(this, e);
        },

        // Add CSS for animations and styles
        addStyles: function () {
            if ($('#pap-dynamic-styles').length) return;

            const styles = `
                <style id="pap-dynamic-styles">
                    @keyframes slideIn {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    
                    .pap-error {
                        border-color: #EF4444 !important;
                        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1) !important;
                    }
                    
                    .pap-dropdown-menu.show {
                        display: block;
                        animation: fadeIn 0.2s ease-out;
                    }
                    
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(-10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    
                    .pap-modal-overlay {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: rgba(0, 0, 0, 0.5);
                        animation: fadeIn 0.3s ease-out;
                    }
                    
                    .pap-modal {
                        animation: slideUp 0.3s ease-out;
                    }
                    
                    @keyframes slideUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
            `;

            $('head').append(styles);
        }
    };

    // Initialize when document is ready
    $(document).ready(function () {
        PAP.init();
    });

    // Make PAP globally available
    window.PAP = PAP;

})(jQuery);








// Site management JavaScript for Product Analytics Pro
jQuery(document).ready(function($) {
    
    // Site Edit Modal Functions
    const SiteManager = {
        
        // Initialize event handlers
        init: function() {
            this.bindEvents();
        },
        
        // Bind click events to edit and delete buttons
        bindEvents: function() {
            $(document).on('click', '.pap-btn-edit', this.handleEditClick);
            $(document).on('click', '.pap-btn-delete', this.handleDeleteClick);
            $(document).on('click', '.pap-modal-site-close', this.closeModal);
            $(document).on('click', '.pap-modal-overlay', function(e) {
                if (e.target === this) {
                    SiteManager.closeModal();
                }
            });
        },
        
        // Handle edit button click
        handleEditClick: function(e) {
            e.preventDefault();
            const siteId = $(this).data('site-id');
            SiteManager.getSiteDetails(siteId);
        },
        
        // Handle delete button click
        handleDeleteClick: function(e) {
            e.preventDefault();
            const siteId = $(this).data('site-id');
            SiteManager.showDeleteConfirmation(siteId);
        },
        
        // Get site details via AJAX
        getSiteDetails: function(siteId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pap_get_site_details',
                    site_id: siteId,
                    nonce: pap_ajax.nonce
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('body').append('<div class="pap-loading">Loading...</div>');
                },
                success: function(response) {
                    $('.pap-loading').remove();
                    if (response.success) {
                        SiteManager.renderEditModal(response.data);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    $('.pap-loading').remove();
                    alert('Error loading site details');
                }
            });
        },
        
        // Render edit modal
        renderEditModal: function(site) {
            const otherPlugins = site.other_plugins ? JSON.stringify(site.other_plugins, null, 2) : '';
            
            const modal = `
                <div class="pap-modal-overlay" id="editSiteModal">
                    <div class="pap-modal-site">
                        <div class="pap-modal-site-header">
                            <h3>Edit Site Details</h3>
                            <button class="pap-modal-site-close">&times;</button>
                        </div>
                        <div class="pap-modal-site-body">
                            <form id="editSiteForm">
                                <input type="hidden" name="site_id" value="${site.id}">
                                
                                <div class="pap-form-group">
                                    <label for="edit_site_url">Site URL</label>
                                    <input type="url" id="edit_site_url" name="site_url" value="${site.site_url}" required>
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_product_id">Product ID</label>
                                    <input type="text" id="edit_product_id" name="product_id" value="${site.product_id}" required>
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_status">Status</label>
                                    <select id="edit_status" name="status">
                                        <option value="active" ${site.status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="inactive" ${site.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                        <option value="pending" ${site.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    </select>
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_wp_version">WordPress Version</label>
                                    <input type="text" id="edit_wp_version" name="wp_version" value="${site.wp_version}">
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_php_version">PHP Version</label>
                                    <input type="text" id="edit_php_version" name="php_version" value="${site.php_version}">
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_active_theme">Active Theme</label>
                                    <input type="text" id="edit_active_theme" name="active_theme" value="${site.active_theme || ''}">
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_multisite">Multisite</label>
                                    <select id="edit_multisite" name="multisite">
                                        <option value="0" ${site.multisite === 0 ? 'selected' : ''}>No</option>
                                        <option value="1" ${site.multisite === 1 ? 'selected' : ''}>Yes</option>
                                    </select>
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_using_pro">Using Pro</label>
                                    <select id="edit_using_pro" name="using_pro">
                                        <option value="0" ${site.using_pro === 0 ? 'selected' : ''}>No</option>
                                        <option value="1" ${site.using_pro === 1 ? 'selected' : ''}>Yes</option>
                                    </select>
                                </div>
                                
                                <div class="pap-form-group">
                                    <label for="edit_other_plugins">Other Plugins (comma separated)</label>
                                    <textarea id="edit_other_plugins" name="other_plugins" rows="3">${otherPlugins}</textarea>
                                </div>

                                <div class="pap-form-group">
                                    <label for="edit_deactivate_reason">Deactivate Reason</label>
                                    <input type="text" id="edit_deactivate_reason" name="deactivate_reason" value="${site.deactivate_reason || ''}">
                                </div>

                                <div class="pap-form-group">
                                    <label for="edit_license_key">License Key</label>
                                    <input type="text" id="edit_license_key" name="license_key" value="${site.license_key || ''}">
                                </div>

                                <div class="pap-form-group">
                                    <label for="edit_location">Location</label>
                                    <input type="text" id="edit_location" name="location" value="${site.location || ''}">
                                </div>

                                <div class="pap-form-group">
                                    <label for="edit_mysql_version">MySQL Version</label>
                                    <input type="text" id="edit_mysql_version" name="mysql_version" value="${site.mysql_version || ''}">
                                </div>

                                <div class="pap-form-group">
                                    <label for="edit_plugin_version">Plugin Version</label>
                                    <input type="text" id="edit_plugin_version" name="plugin_version" value="${site.plugin_version || ''}">
                                </div>

                                <div class="pap-form-group">
                                    <label for="edit_server_software">Server Software</label>
                                    <input type="text" id="edit_server_software" name="server_software" value="${site.server_software || ''}">
                                </div>
                                
                                <div class="pap-form-actions">
                                    <button type="submit" class="pap-btn pap-btn-primary">Update Site</button>
                                    <button type="button" class="pap-btn pap-btn-secondary pap-modal-site-close">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modal);
            
            // Bind form submit event
            $('#editSiteForm').on('submit', this.updateSite);
        },
        
        // Update site via AJAX
        updateSite: function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const ajaxData = {
                action: 'pap_update_site',
                nonce: pap_ajax.nonce
            };
            
            // Convert FormData to regular object
            for (let [key, value] of formData.entries()) {
                // If updating other_plugins, try to parse JSON if possible
                if (key === 'other_plugins') {
                    ajaxData[key] = JSON.parse(value);
                } else {
                    ajaxData[key] = value;
                }
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: ajaxData,
                beforeSend: function() {
                    $('#editSiteForm button[type="submit"]').prop('disabled', true).text('Updating...');
                },
                success: function(response) {
                    if (response.success) {
                        PAP.showNotification('Site updated successfully!', 'success');
                        SiteManager.closeModal();
                        setTimeout(function() {
                            location.reload(); // Refresh the page to show updated data
                        }, 1000);
                    } else {
                        PAP.showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    PAP.showNotification('Error updating site', 'error');
                },
                complete: function() {
                    $('#editSiteForm button[type="submit"]').prop('disabled', false).text('Update Site');
                }
            });
        },
        
        // Show delete confirmation modal
        showDeleteConfirmation: function(siteId) {
            const modal = `
                <div class="pap-modal-overlay" id="deleteSiteModal">
                    <div class="pap-modal-site pap-modal-site-small">
                        <div class="pap-modal-site-header">
                            <h3>Delete Site</h3>
                            <button class="pap-modal-site-close">&times;</button>
                        </div>
                        <div class="pap-modal-site-body">
                            <p>Are you sure you want to delete this site? This action cannot be undone.</p>
                            <div class="pap-form-actions">
                                <button type="button" class="pap-btn pap-btn-danger" id="confirmDeleteSite" data-site-id="${siteId}">Delete</button>
                                <button type="button" class="pap-btn pap-btn-secondary pap-modal-site-close">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modal);
            
            // Bind delete confirmation
            $('#confirmDeleteSite').on('click', this.deleteSite);
        },
        
        // Delete site via AJAX
        deleteSite: function(e) {
            e.preventDefault();
            const siteId = $(this).data('site-id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pap_delete_site',
                    site_id: siteId,
                    nonce: pap_ajax.nonce
                },
                beforeSend: function() {
                    $('#confirmDeleteSite').prop('disabled', true).text('Deleting...');
                },
                success: function(response) {
                    if (response.success) {
                        PAP.showNotification('Site deleted successfully!', 'success');
                        SiteManager.closeModal();
                        // Remove the corresponding table row for the deleted site
                        $(`.pap-btn-delete[data-site-id="${siteId}"]`).closest('tr').fadeOut(300, function () {
                            $(this).remove();
                        });
                    } else {
                        PAP.showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    PAP.showNotification('Error deleting site', 'error');
                },
                complete: function() {
                    $('#confirmDeleteSite').prop('disabled', false).text('Delete');
                }
            });
        },
        
        // Close modal
        closeModal: function() {
            $('.pap-modal-overlay').remove();
        }
    };
    
    // Initialize site manager
    SiteManager.init();
});











jQuery(document).ready(function($) {
    // Handle notes button click
    $(document).on('click', '.pap-btn-notes', function(e) {
        e.preventDefault();
        var siteId = $(this).data('site-id');
        
        // Reset form
        $('#pap-notes-form')[0].reset();
        $('#pap-notes-site-id').val(siteId);
        
        // Get existing note
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pap_get_site_note',
                site_id: siteId,
                nonce: pap_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#pap-note-color').find('option').prop('selected', false);
                    $('#pap-note-color').find(`option[value="${response.data.note_color || ''}"]`).prop('selected', true);
                    $('#pap-note-comment').val(response.data.note_comment || '');
                }
                $('#pap-notes-modal').show();
            },
            error: function() {
                alert('Error loading note data');
            }
        });
    });
    
    // Handle notes form submission
    $('#pap-notes-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pap_save_site_note',
                site_id: $('#pap-notes-site-id').val(),
                note_color: $('#pap-note-color').val(),
                note_comment: $('#pap-note-comment').val(),
                nonce: pap_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload the page to show the updated note icon
                    window.location.reload();
                } else {
                    alert('Error saving note: ' + response.data.message);
                }
            },
            error: function() {
                alert('Error saving note');
            }
        });
    });
    
    // Close modal
    $('.pap-modal-close, .pap-modal-cancel').on('click', function() {
        $('#pap-notes-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('pap-modal')) {
            $('#pap-notes-modal').hide();
        }
    });
});