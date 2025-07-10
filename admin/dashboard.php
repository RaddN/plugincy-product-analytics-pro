<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_dashboard
{

    private $pap_db_calls;
    private $help;
    public function __construct()
    {
        add_action('init', array($this, 'init'));
        $this->pap_db_calls = new ProductAnalyticsPro_db();
        $this->help = new ProductAnalyticsPro_help();
    }

    public function init()
    {
        // Initialize plugin
    }

    public function product_dashboard_page()
    {
        $product_id = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_STRING);
        if (empty($product_id)) {
            wp_die('Product ID is required');
        }

        global $wpdb;
        $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pap_products WHERE product_id = %s", $product_id));

        if (!$product) {
            wp_die('Product not found');
        }
        $current_tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING) ?? 'dashboard';
?>
        <div class="wrap pap-wrap">
            <div class="pap-header">
                <h1 class="pap-title"><?php echo esc_html($product->product_name); ?> Analytics</h1>
                <a href="<?php echo esc_url(admin_url('admin.php?page=product-analytics')); ?>" class="pap-btn pap-btn-secondary">← Back to Products</a>
            </div>

            <div class="pap-tabs">
                <a href="<?php echo esc_url(admin_url('admin.php?page=product-dashboard&product_id=' . $product_id . '&tab=dashboard')); ?>"
                    class="pap-tab <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">📊 Dashboard</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=product-dashboard&product_id=' . $product_id . '&tab=sites')); ?>"
                    class="pap-tab <?php echo $current_tab === 'sites' ? 'active' : ''; ?>">🌐 Sites</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=product-dashboard&product_id=' . $product_id . '&tab=deactivations')); ?>"
                    class="pap-tab <?php echo $current_tab === 'deactivations' ? 'active' : ''; ?>">❌ Deactivations</a>
            </div>

            <div class="pap-tab-content">
                <?php
                switch ($current_tab) {
                    case 'dashboard':
                        $this->render_dashboard_tab($product_id);
                        break;
                    case 'sites':
                        $this->render_sites_tab($product_id);
                        break;
                    case 'deactivations':
                        $this->render_deactivations_tab($product_id);
                        break;
                }
                ?>
            </div>
        </div>
    <?php
    }

    private function render_dashboard_tab($product_id)
    {
        $stats = $this->pap_db_calls->get_detailed_stats($product_id);
    ?>
        <div class="pap-dashboard">
            <div class="pap-stats-grid">
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['total_installs']); ?></h3>
                        <p>Total Installs</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112.001" height="40" viewBox="0 0 112.001 40">
                            <defs>
                                <linearGradient id="svg-linear-left" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#667eea"></stop>
                                    <stop offset="1" stop-color="#667eea" stop-opacity="0.502"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-left)"></path>
                        </svg></div>
                </div>
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['active_installs']); ?></h3>
                        <p>Active Installs</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112" height="40" viewBox="0 0 112 40">
                            <defs>
                                <linearGradient id="svg-linear-right" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#a3a0fb"></stop>
                                    <stop offset="1" stop-color="#a3a0fb" stop-opacity="0.502"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-right)"></path>
                        </svg></div>
                </div>
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['inactive_installs']); ?></h3>
                        <p>Inactive Installs</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112" height="40" viewBox="0 0 112 40">
                            <defs>
                                <linearGradient id="svg-linear-middle" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#f56565"></stop>
                                    <stop offset="1" stop-color="#fff5f5"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-middle)"></path>
                        </svg></div>
                </div>
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['activations_30_days']); ?></h3>
                        <p>Activations (30 days)</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112" height="40" viewBox="0 0 112 40">
                            <defs>
                                <linearGradient id="svg-linear-right2" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#764ba2"></stop>
                                    <stop offset="1" stop-color="#764ba2" stop-opacity="0.502"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-right2)"></path>
                        </svg></div>
                </div>
            </div>

            <div class="pap-charts-grid">
                <div class="pap-chart-card">
                    <h3>Active vs Inactive</h3>
                    <canvas id="activeInactiveChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>Pro vs Free</h3>
                    <canvas id="proFreeChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>Multisite Usage</h3>
                    <canvas id="multisiteChart"></canvas>
                </div>
                <div class="pap-chart-card" style="grid-column: 1 / -1;">
                    <h3>Installs in This Month (Date Wise)</h3>
                    <canvas id="monthlyinstallChart"></canvas>
                </div>
                <div class="pap-chart-card" style="grid-column: 1 / -1;">
                    <h3>Installs in This Year (Month Wise)</h3>
                    <canvas id="yearlyinstallChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>WordPress Versions</h3>
                    <canvas id="wpVersionChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>PHP Versions</h3>
                    <canvas id="phpVersionChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>Server Software</h3>
                    <canvas id="serverChart"></canvas>
                </div>

                <div class="pap-chart-card">
                    <h3>Deactivation Reasons</h3>
                    <canvas id="deactivationChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>plugin versions</h3>
                    <canvas id="pluginversionsChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>Mostly use from</h3>
                    <canvas id="locationsChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>Most used themes</h3>
                    <canvas id="usedthemeChart"></canvas>
                </div>
                <div class="pap-chart-card">
                    <h3>People deactivated the most from themes</h3>
                    <canvas id="deactiveusedthemeChart"></canvas>
                </div>

                <div class="pap-chart-card">
                    <h3>MySQL Versions</h3>
                    <canvas id="mysqlversionsChart"></canvas>
                </div>


                <div class="pap-chart-card" style="grid-column: 1 / -1;">
                    <h3>top 10 site most dates used</h3>
                    <canvas id="top10mostusedChart"></canvas>
                </div>
                <div class="pap-chart-card" style="grid-column: 1 / -1;">
                    <h3>Most used plugins with</h3>
                    <canvas id="usedpluginChart"></canvas>
                </div>

            </div>
        </div>
    <?php
    }

    private function render_sites_tab($product_id)
    {
        global $wpdb;

        // Get current page, search, and order parameters
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $order_by = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'last_seen';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        $tab = isset($_GET['site_tab']) ? sanitize_text_field($_GET['site_tab']) : 'client';

        // Validate order_by parameter
        $valid_order_by = ['status', 'multisite', 'wp_version', 'php_version', 'days_active', 'last_seen'];
        if (!in_array($order_by, $valid_order_by)) {
            $order_by = 'last_seen';
        }

        // Validate order parameter
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $per_page = 20;
        $offset = ($current_page - 1) * $per_page;

        // Build WHERE clause
        $where_conditions = ["product_id = %s"];
        $where_params = [$product_id];

        if (!empty($search)) {
            $where_conditions[] = "site_url LIKE %s";
            $where_params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

        // Special handling for days_active ordering
        if ($order_by === 'days_active') {
            $order_clause = "ORDER BY (CASE 
            WHEN deactivation_date IS NULL THEN DATEDIFF(NOW(), activation_date)
            ELSE DATEDIFF(deactivation_date, activation_date)
        END) $order";
        } else {
            $order_clause = "ORDER BY $order_by $order";
        }

        // Get ALL sites matching the base criteria (without pagination)
        $all_sites_query = "SELECT * FROM {$wpdb->prefix}pap_analytics $where_clause $order_clause";
        $all_sites = $wpdb->get_results($wpdb->prepare($all_sites_query, $where_params));

        // Filter sites based on tab (own vs client)
        $settings = new ProductAnalyticsPro_settings();
        $filtered_sites = [];

        foreach ($all_sites as $site) {
            $is_own = $settings->is_own_site($site->site_url);
            if (($tab === 'own' && $is_own) || ($tab === 'client' && !$is_own)) {
                $filtered_sites[] = $site;
            }
        }

        // Calculate pagination based on filtered results
        $total_filtered_sites = count($filtered_sites);
        $total_pages = ceil($total_filtered_sites / $per_page);

        // Apply pagination to filtered results
        $paginated_sites = array_slice($filtered_sites, $offset, $per_page);

        // Get counts for tab labels
        $own_count = 0;
        $client_count = 0;
        foreach ($all_sites as $site) {
            $is_own = $settings->is_own_site($site->site_url);
            if ($is_own) {
                $own_count++;
            } else {
                $client_count++;
            }
        }

    ?>
        <div class="pap-sites-container">
            <!-- Tab Navigation -->
            <div class="pap-tab-nav">
                <a href="<?php echo esc_url(add_query_arg(['site_tab' => 'own', 'paged' => 1])); ?>"
                    class="pap-tab-link <?php echo $tab === 'own' ? 'active' : ''; ?>">
                    Own Sites (<?php echo $own_count; ?>)
                </a>
                <a href="<?php echo esc_url(add_query_arg(['site_tab' => 'client', 'paged' => 1])); ?>"
                    class="pap-tab-link <?php echo $tab === 'client' ? 'active' : ''; ?>">
                    Client Sites (<?php echo $client_count; ?>)
                </a>
            </div>

            <!-- Search and Controls -->
            <div class="pap-table-controls">
                <form method="get" class="pap-search-form">
                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? ''); ?>" />
                    <input type="hidden" name="tab" value="<?php echo esc_attr($_GET['tab'] ?? ''); ?>" />
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                    <input type="hidden" name="site_tab" value="<?php echo esc_attr($tab); ?>" />
                    <input type="search" name="search" placeholder="Search by website URL..."
                        value="<?php echo esc_attr($search); ?>" />
                    <button type="submit" class="pap-btn pap-btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo esc_url(remove_query_arg(['search', 'paged'])); ?>"
                            class="pap-btn pap-btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Sites Table -->
            <div class="pap-sites-table">
                <table class="pap-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="<?php echo esc_url(add_query_arg([
                                                'orderby' => 'site_url',
                                                'order' => ($order_by === 'site_url' && $order === 'ASC') ? 'DESC' : 'ASC',
                                                'paged' => 1
                                            ])); ?>">
                                    Site URL
                                    <?php if ($order_by === 'site_url'): ?>
                                        <span class="pap-sort-indicator"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo esc_url(add_query_arg([
                                                'orderby' => 'status',
                                                'order' => ($order_by === 'status' && $order === 'ASC') ? 'DESC' : 'ASC',
                                                'paged' => 1
                                            ])); ?>">
                                    Status
                                    <?php if ($order_by === 'status'): ?>
                                        <span class="pap-sort-indicator"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo esc_url(add_query_arg([
                                                'orderby' => 'multisite',
                                                'order' => ($order_by === 'multisite' && $order === 'ASC') ? 'DESC' : 'ASC',
                                                'paged' => 1
                                            ])); ?>">
                                    Multisite
                                    <?php if ($order_by === 'multisite'): ?>
                                        <span class="pap-sort-indicator"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo esc_url(add_query_arg([
                                                'orderby' => 'wp_version',
                                                'order' => ($order_by === 'wp_version' && $order === 'ASC') ? 'DESC' : 'ASC',
                                                'paged' => 1
                                            ])); ?>">
                                    WordPress
                                    <?php if ($order_by === 'wp_version'): ?>
                                        <span class="pap-sort-indicator"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo esc_url(add_query_arg([
                                                'orderby' => 'php_version',
                                                'order' => ($order_by === 'php_version' && $order === 'ASC') ? 'DESC' : 'ASC',
                                                'paged' => 1
                                            ])); ?>">
                                    PHP
                                    <?php if ($order_by === 'php_version'): ?>
                                        <span class="pap-sort-indicator"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo esc_url(add_query_arg([
                                                'orderby' => 'days_active',
                                                'order' => ($order_by === 'days_active' && $order === 'ASC') ? 'DESC' : 'ASC',
                                                'paged' => 1
                                            ])); ?>">
                                    Days Active
                                    <?php if ($order_by === 'days_active'): ?>
                                        <span class="pap-sort-indicator"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Active Theme</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginated_sites)): ?>
                            <tr>
                                <td colspan="8" class="pap-no-results">
                                    <?php echo empty($search) ? 'No sites found.' : 'No sites found matching your search.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paginated_sites as $site): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url($site->site_url); ?>" target="_blank" style="color: unset; text-decoration: none;"><?php echo esc_html($site->site_url); ?></a>
                                        <?php if ($site->using_pro): ?>
                                            <span class="pap-pro-badge">PRO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="pap-status pap-status-<?php echo esc_html($site->status); ?>">
                                            <?php echo esc_html(ucfirst($site->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $site->multisite ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo esc_html($site->wp_version); ?></td>
                                    <td><?php echo esc_html($site->php_version); ?></td>
                                    <td><?php echo esc_html($this->help->calculate_days_active($site->activation_date, $site->deactivation_date)); ?></td>
                                    <td><?php echo esc_html($site->active_theme ?? 'N/A'); ?></td>
                                    <td>
                                        <button class="pap-btn pap-btn-small pap-view-details" data-site-id="<?php echo esc_html($site->id); ?>">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pap-pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo esc_url(add_query_arg(['paged' => $current_page - 1])); ?>"
                            class="pap-btn pap-btn-secondary">« Previous</a>
                    <?php endif; ?>

                    <span class="pap-pagination-info">
                        Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                        (<?php echo $total_filtered_sites; ?> <?php echo $tab; ?> sites)
                    </span>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo esc_url(add_query_arg(['paged' => $current_page + 1])); ?>"
                            class="pap-btn pap-btn-secondary">Next »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .pap-sites-container {
                margin-top: 20px;
            }

            .pap-tab-nav {
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
            }

            .pap-tab-link {
                display: inline-block;
                padding: 10px 20px;
                text-decoration: none;
                color: #666;
                border-bottom: 2px solid transparent;
                margin-right: 10px;
            }

            .pap-tab-link.active {
                color: #0073aa;
                border-bottom-color: #0073aa;
                font-weight: bold;
            }

            .pap-table-controls {
                margin-bottom: 20px;
            }

            .pap-search-form {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .pap-search-form input[type="search"] {
                min-width: 300px;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .pap-table th a {
                text-decoration: none;
                color: #333;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .pap-table th a:hover {
                color: #0073aa;
            }

            .pap-sort-indicator {
                font-size: 12px;
                color: #0073aa;
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

            .pap-no-results {
                text-align: center;
                color: #666;
                font-style: italic;
                padding: 20px;
            }

            .pap-pagination {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 20px;
                padding: 15px 0;
                border-top: 1px solid #ddd;
            }

            .pap-pagination-info {
                color: #666;
                font-size: 14px;
            }

            .pap-btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
            }

            .pap-btn-primary {
                background: #0073aa;
                color: white;
            }

            .pap-btn-secondary {
                background: #f7f7f7;
                color: #333;
                border: 1px solid #ddd;
            }

            .pap-btn-small {
                padding: 5px 10px;
                font-size: 12px;
            }

            .pap-btn:hover {
                opacity: 0.9;
            }
        </style>
    <?php
    }

    private function render_deactivations_tab($product_id)
    {
        global $wpdb;
        $stats = $this->pap_db_calls->get_detailed_stats($product_id);
        $deactivations = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}pap_analytics 
            WHERE product_id = %s AND status = 'inactive' AND deactivation_date IS NOT NULL
            ORDER BY deactivation_date DESC
        ", $product_id));
    ?>
        <div class="pap-deactivations">
            <div class="pap-filters">
                <input type="date" id="start_date" placeholder="Start Date">
                <input type="date" id="end_date" placeholder="End Date">
                <button class="pap-btn pap-btn-primary" id="apply_filters">Apply Filters</button>
            </div>

            <div class="pap-deactivation-stats">
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['total_installs']); ?></h3>
                        <p>Total Installs</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112.001" height="40" viewBox="0 0 112.001 40">
                            <defs>
                                <linearGradient id="svg-linear-left" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#667eea"></stop>
                                    <stop offset="1" stop-color="#667eea" stop-opacity="0.502"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-left)"></path>
                        </svg></div>
                </div>
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['active_installs']); ?></h3>
                        <p>Active Installs</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112" height="40" viewBox="0 0 112 40">
                            <defs>
                                <linearGradient id="svg-linear-right" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#a3a0fb"></stop>
                                    <stop offset="1" stop-color="#a3a0fb" stop-opacity="0.502"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-right)"></path>
                        </svg></div>
                </div>
                <div class="pap-stat-card">
                    <div class="pap-stat-content">
                        <h3><?php echo esc_html($stats['inactive_installs']); ?></h3>
                        <p>Inactive Installs</p>
                    </div>
                    <div class="pap-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="112" height="40" viewBox="0 0 112 40">
                            <defs>
                                <linearGradient id="svg-linear-middle" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                                    <stop offset="0" stop-color="#f56565"></stop>
                                    <stop offset="1" stop-color="#fff5f5"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M98,119V101h6v18Zm-9,0V89h8v30Zm-9,0V99h8v20Zm-9,0V106h8v13Zm-9,0V94h8v25Zm-9,0V84h8v35Zm-9,0V94h8v25Zm-9,0V99h8v20Zm-9,0V79h8v40Zm-9,0V89h8v30Zm-7,0V101h6v18Zm-9,0V89H9v30Zm-9,0V99H0v20Z" transform="translate(8 -79)" fill="url(#svg-linear-middle)"></path>
                        </svg></div>
                </div>
            </div>

            <div class="pap-deactivations-table">
                <table class="pap-table">
                    <thead>
                        <tr>
                            <th>Site URL</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Days Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deactivations as $deactivation): ?>
                            <tr>
                                <td><?php echo esc_html($deactivation->site_url); ?></td>
                                <td><?php echo esc_html(gmdate('Y-m-d', strtotime($deactivation->deactivation_date))); ?></td>
                                <td><?php echo esc_html($deactivation->deactivate_reason); ?></td>
                                <td><?php echo esc_html($this->help->calculate_days_active($deactivation->activation_date, $deactivation->deactivation_date)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php
    }
}
