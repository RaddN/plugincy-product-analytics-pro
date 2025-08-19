<?php
// dashboard.php
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

        <!-- Notes Modal -->
        <div id="pap-notes-modal" class="pap-modal" style="display: none;">
            <div class="pap-modal-content">
                <div class="pap-modal-header">
                    <h3>Site Notes</h3>
                    <button class="pap-modal-close">&times;</button>
                </div>
                <div class="pap-modal-body">
                    <form id="pap-notes-form">
                        <input type="hidden" id="pap-notes-site-id" name="site_id">

                        <div class="pap-form-group">
                            <label for="pap-note-color">Note Icon Color</label>
                            <input type="color" id="pap-note-color" name="note_color" class="pap-color-picker">
                            <p class="description">Choose a color for the note icon that will appear next to the site URL</p>
                        </div>

                        <div class="pap-form-group">
                            <label for="pap-note-comment">Note</label>
                            <textarea id="pap-note-comment" name="note_comment" rows="5" class="pap-textarea"></textarea>
                        </div>

                        <div class="pap-form-actions">
                            <button type="submit" class="pap-btn pap-btn-primary">Save Note</button>
                            <button type="button" class="pap-btn pap-btn-secondary pap-modal-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
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
                <a href="/wp-admin/admin.php?page=product-dashboard&product_id=03&tab=sites" class="pap-stat-card">
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
                </a>
                <a href="/wp-admin/admin.php?page=product-dashboard&product_id=03&tab=sites&site_tab=client" class="pap-stat-card">
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
                </a>
                <a href="/wp-admin/admin.php?page=product-dashboard&product_id=03&tab=deactivations" class="pap-stat-card">
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
                </a>
                <a href="#monthlyinstallChart" class="pap-stat-card">
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
                </a>
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

    private function get_sites_by_date_groups($sites, $date_field = 'activation_date')
    {
        $groups = [
            'today' => [],
            'yesterday' => [],
            'dates' => []
        ];

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        foreach ($sites as $site) {
            $site_date = date('Y-m-d', strtotime($site->$date_field));

            if ($site_date === $today) {
                $groups['today'][] = $site;
            } elseif ($site_date === $yesterday) {
                $groups['yesterday'][] = $site;
            } else {
                if (!isset($groups['dates'][$site_date])) {
                    $groups['dates'][$site_date] = [];
                }
                $groups['dates'][$site_date][] = $site;
            }
        }

        // Sort dates in descending order
        krsort($groups['dates']);

        return $groups;
    }
    /**
     * Get sites filtered by tab type
     */
    private function get_filtered_sites_by_tab($all_sites, $tab, $settings)
    {
        $filtered_sites = [];

        foreach ($all_sites as $site) {
            $is_own = $settings->is_own_site($site->site_url);
            $is_pro = $site->using_pro;

            switch ($tab) {
                case 'own':
                    if ($is_own) $filtered_sites[] = $site;
                    break;
                case 'client':
                    if (!$is_own) $filtered_sites[] = $site;
                    break;
                case 'pro':
                    if ($is_pro) $filtered_sites[] = $site;
                    break;
                case 'all':
                default:
                    $filtered_sites[] = $site;
                    break;
            }
        }

        return $filtered_sites;
    }
    /**
     * Get tab counts
     */
    private function get_tab_counts($all_sites, $settings)
    {
        $counts = [
            'all' => count($all_sites),
            'own' => 0,
            'client' => 0,
            'pro' => 0
        ];

        foreach ($all_sites as $site) {
            $is_own = $settings->is_own_site($site->site_url);
            $is_pro = $site->using_pro;

            if ($is_own) $counts['own']++;
            else $counts['client']++;

            if ($is_pro) $counts['pro']++;
        }

        return $counts;
    }
    /**
     * Render site row
     */
    private function render_site_row($site, $is_deactivation = false)
    {
    ?>
        <tr>
            <td>
                <a href="<?php echo esc_url($site->site_url); ?>" target="_blank" style="color: unset; text-decoration: none;">
                    <?php echo esc_html($site->site_url); ?>
                </a>
                <?php if ($site->using_pro): ?>
                    <span class="pap-pro-badge">PRO</span>
                <?php endif; ?>
            </td>
            <?php if ($is_deactivation): ?>
                <td><?php echo esc_html(date('g:i A', strtotime($site->deactivation_date))); ?></td>
                <td>
                    <span class="pap-reason-badge" title="<?php echo esc_attr($site->deactivate_reason); ?>">
                        <?php echo esc_html($site->deactivate_reason ?: 'No reason provided'); ?>
                    </span>
                </td>
                <td>
                    <span class="pap-days-used">
                        <?php echo esc_html($this->help->calculate_days_active($site->activation_date, $site->deactivation_date)); ?>
                    </span>
                </td>
            <?php else: ?>
                <td>
                    <span class="pap-status pap-status-<?php echo esc_html($site->status); ?>">
                        <?php echo esc_html(ucfirst($site->status)); ?>
                    </span>
                </td>
                <td><?php echo esc_html(date('g:i A', strtotime($site->activation_date))); ?></td>
                <td><?php echo $site->multisite ? 'Yes' : 'No'; ?></td>
                <td><?php echo esc_html($this->help->calculate_days_active($site->activation_date, $site->deactivation_date)); ?></td>
            <?php endif; ?>
            <td><?php echo esc_html($site->wp_version); ?></td>
            <td><?php echo esc_html($site->php_version); ?></td>
            <td><?php echo esc_html($site->active_theme ?? 'N/A'); ?></td>
            <td class="pap-actions">
                <button class="pap-btn pap-btn-small pap-view-details" data-site-id="<?php echo esc_html($site->id); ?>">
                    Details
                </button>
                <button class="pap-btn pap-btn-small pap-btn-edit" data-site-id="<?php echo esc_html($site->id); ?>" title="Edit Site">
                    <span class="dashicons dashicons-edit"></span>
                </button>
                <button class="pap-btn pap-btn-small pap-btn-delete" data-site-id="<?php echo esc_html($site->id); ?>" title="Delete Site">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <button class="pap-btn pap-btn-small pap-btn-notes" data-site-id="<?php echo esc_html($site->id); ?>" title="Add Note" style="color: #fff; background: <?php echo isset($site->note_color) ? esc_attr($site->note_color) : '#868686'; ?>;">
                    <span class="dashicons dashicons-admin-comments"></span>
                    <?php echo isset($site->note_color) ? 1 : ''; ?>
                </button>
            </td>
        </tr>
    <?php
    }

    /**
     * Enhanced render_sites_tab method
     */
    private function render_sites_tab($product_id)
    {
        global $wpdb;

        // Get current parameters
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $order_by = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'activation_date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        $tab = isset($_GET['site_tab']) ? sanitize_text_field($_GET['site_tab']) : 'all';

        // Validate parameters
        $valid_order_by = ['status', 'multisite', 'wp_version', 'php_version', 'days_active', 'activation_date', 'site_url'];
        if (!in_array($order_by, $valid_order_by)) {
            $order_by = 'activation_date';
        }
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

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

        // Get all sites
        $all_sites_query = "SELECT * FROM {$wpdb->prefix}pap_analytics $where_clause $order_clause";
        $all_sites = $wpdb->get_results($wpdb->prepare($all_sites_query, $where_params));

        // Get settings instance
        $settings = new ProductAnalyticsPro_settings();

        // Get tab counts
        $tab_counts = $this->get_tab_counts($all_sites, $settings);

        // Filter sites based on tab
        $filtered_sites = $this->get_filtered_sites_by_tab($all_sites, $tab, $settings);

        // Group sites by date
        $date_groups = $this->get_sites_by_date_groups($filtered_sites, 'activation_date');

    ?>
        <div class="pap-sites-container">
            <!-- Enhanced Tab Navigation -->
            <div class="pap-tab-nav">
                <a href="<?php echo esc_url(add_query_arg(['site_tab' => 'all'])); ?>"
                    class="pap-tab-link <?php echo $tab === 'all' ? 'active' : ''; ?>">
                    All Sites (<?php echo $tab_counts['all']; ?>)
                </a>
                <a href="<?php echo esc_url(add_query_arg(['site_tab' => 'own'])); ?>"
                    class="pap-tab-link <?php echo $tab === 'own' ? 'active' : ''; ?>">
                    Own Sites (<?php echo $tab_counts['own']; ?>)
                </a>
                <a href="<?php echo esc_url(add_query_arg(['site_tab' => 'client'])); ?>"
                    class="pap-tab-link <?php echo $tab === 'client' ? 'active' : ''; ?>">
                    Client Sites (<?php echo $tab_counts['client']; ?>)
                </a>
                <a href="<?php echo esc_url(add_query_arg(['site_tab' => 'pro'])); ?>"
                    class="pap-tab-link <?php echo $tab === 'pro' ? 'active' : ''; ?>">
                    Pro Sites (<?php echo $tab_counts['pro']; ?>)
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
                        <a href="<?php echo esc_url(remove_query_arg(['search'])); ?>"
                            class="pap-btn pap-btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Sites organized by date -->
            <div class="pap-sites-by-date">
                <?php if (empty($filtered_sites)): ?>
                    <div class="pap-no-results">
                        <?php echo empty($search) ? 'No sites found.' : 'No sites found matching your search.'; ?>
                    </div>
                <?php else: ?>

                    <!-- Today's Sites -->
                    <?php if (!empty($date_groups['today'])): ?>
                        <div class="pap-date-group">
                            <h3 class="pap-date-header">Today (<?php echo count($date_groups['today']); ?> sites)</h3>
                            <div class="pap-sites-table">
                                <table class="pap-table">
                                    <thead>
                                        <tr>
                                            <th>Site URL</th>
                                            <th>Status</th>
                                            <th>Activation Time</th>
                                            <th>Multisite</th>
                                            <th>Days Active</th>
                                            <th>WordPress</th>
                                            <th>PHP</th>
                                            <th>Active Theme</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($date_groups['today'] as $site): ?>
                                            <?php $this->render_site_row($site, false);; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Yesterday's Sites -->
                    <?php if (!empty($date_groups['yesterday'])): ?>
                        <div class="pap-date-group">
                            <h3 class="pap-date-header">Yesterday (<?php echo count($date_groups['yesterday']); ?> sites)</h3>
                            <div class="pap-sites-table">
                                <table class="pap-table">
                                    <thead>
                                        <tr>
                                            <th>Site URL</th>
                                            <th>Status</th>
                                            <th>Activation Time</th>
                                            <th>Multisite</th>
                                            <th>Days Active</th>
                                            <th>WordPress</th>
                                            <th>PHP</th>
                                            <th>Active Theme</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($date_groups['yesterday'] as $site): ?>
                                            <?php $this->render_site_row($site, false);; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Other Dates -->
                    <?php foreach ($date_groups['dates'] as $date => $sites): ?>
                        <div class="pap-date-group">
                            <h3 class="pap-date-header">
                                <?php echo esc_html(date('F j, Y', strtotime($date))); ?>
                                (<?php echo count($sites); ?> sites)
                            </h3>
                            <div class="pap-sites-table">
                                <table class="pap-table">
                                    <thead>
                                        <tr>
                                            <th>Site URL</th>
                                            <th>Status</th>
                                            <th>Activation Time</th>
                                            <th>Multisite</th>
                                            <th>Days Active</th>
                                            <th>WordPress</th>
                                            <th>PHP</th>
                                            <th>Active Theme</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sites as $site): ?>
                                            <?php $this->render_site_row($site, false);; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    private function render_deactivations_tab($product_id)
    {
        global $wpdb;
        $stats = $this->pap_db_calls->get_detailed_stats($product_id);

        // Get current parameters for filtering
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $order_by = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'deactivation_date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        $deactivation_tab = isset($_GET['deactivation_tab']) ? sanitize_text_field($_GET['deactivation_tab']) : 'all';

        // Validate parameters
        $valid_order_by = ['deactivation_date', 'site_url', 'deactivate_reason', 'days_active', 'active_theme'];
        if (!in_array($order_by, $valid_order_by)) {
            $order_by = 'deactivation_date';
        }
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        // Build WHERE clause
        $where_conditions = ["product_id = %s", "status = 'inactive'", "deactivation_date IS NOT NULL"];
        $where_params = [$product_id];

        if (!empty($search)) {
            $where_conditions[] = "site_url LIKE %s";
            $where_params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

        // Special handling for days_active ordering
        if ($order_by === 'days_active') {
            $order_clause = "ORDER BY DATEDIFF(deactivation_date, activation_date) $order";
        } else {
            $order_clause = "ORDER BY $order_by $order";
        }

        // Get all deactivations
        $all_deactivations_query = "SELECT * FROM {$wpdb->prefix}pap_analytics $where_clause $order_clause";
        $all_deactivations = $wpdb->get_results($wpdb->prepare($all_deactivations_query, $where_params));

        // Get settings instance
        $settings = new ProductAnalyticsPro_settings();

        // Get tab counts
        $tab_counts = $this->get_tab_counts($all_deactivations, $settings);

        // Filter deactivations based on tab
        $filtered_deactivations = $this->get_filtered_sites_by_tab($all_deactivations, $deactivation_tab, $settings);

        // Group deactivations by date
        $date_groups = $this->get_sites_by_date_groups($filtered_deactivations, 'deactivation_date');

    ?>
        <div class="pap-deactivations">

            <!-- Stats Cards -->
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

            <!-- Search and Controls -->
            <div class="pap-table-controls">
                <form method="get" class="pap-search-form">
                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? ''); ?>" />
                    <input type="hidden" name="tab" value="<?php echo esc_attr($_GET['tab'] ?? ''); ?>" />
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                    <input type="hidden" name="deactivation_tab" value="<?php echo esc_attr($deactivation_tab); ?>" />
                    <input type="search" name="search" placeholder="Search by website URL..."
                        value="<?php echo esc_attr($search); ?>" />
                    <button type="submit" class="pap-btn pap-btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo esc_url(remove_query_arg(['search'])); ?>"
                            class="pap-btn pap-btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Deactivations organized by date -->
            <div class="pap-deactivations-container">
                <div class="pap-deactivations-by-date">
                    <?php if (empty($filtered_deactivations)): ?>
                        <div class="pap-no-results">
                            <?php echo empty($search) ? 'No deactivations found.' : 'No deactivations found matching your search.'; ?>
                        </div>
                    <?php else: ?>

                        <!-- Today's Deactivations -->
                        <?php if (!empty($date_groups['today'])): ?>
                            <div class="pap-date-group">
                                <h3 class="pap-date-header">Today (<?php echo count($date_groups['today']); ?> deactivations)</h3>
                                <div class="pap-deactivations-table">
                                    <table class="pap-table">
                                        <thead>
                                            <tr>
                                                <th>Site URL</th>
                                                <th>Deactivation Time</th>
                                                <th>Reason</th>
                                                <th>Days Used</th>
                                                <th>WordPress</th>
                                                <th>PHP</th>
                                                <th>Active Theme</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($date_groups['today'] as $deactivation): ?>
                                                <?php $this->render_site_row($deactivation, true);; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Yesterday's Deactivations -->
                        <?php if (!empty($date_groups['yesterday'])): ?>
                            <div class="pap-date-group">
                                <h3 class="pap-date-header">Yesterday (<?php echo count($date_groups['yesterday']); ?> deactivations)</h3>
                                <div class="pap-deactivations-table">
                                    <table class="pap-table">
                                        <thead>
                                            <tr>
                                                <th>Site URL</th>
                                                <th>Deactivation Date</th>
                                                <th>Reason</th>
                                                <th>Days Used</th>
                                                <th>WordPress</th>
                                                <th>PHP</th>
                                                <th>Active Theme</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($date_groups['yesterday'] as $deactivation): ?>
                                                <?php $this->render_site_row($deactivation, true);; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Other Dates -->
                        <?php foreach ($date_groups['dates'] as $date => $deactivations): ?>
                            <div class="pap-date-group">
                                <h3 class="pap-date-header">
                                    <?php echo esc_html(date('F j, Y', strtotime($date))); ?>
                                    (<?php echo count($deactivations); ?> deactivations)
                                </h3>
                                <div class="pap-deactivations-table">
                                    <table class="pap-table">
                                        <thead>
                                            <tr>
                                                <th>Site URL</th>
                                                <th>Deactivation Date</th>
                                                <th>Reason</th>
                                                <th>Days Used</th>
                                                <th>WordPress</th>
                                                <th>PHP</th>
                                                <th>Active Theme</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($deactivations as $deactivation): ?>
                                                <?php $this->render_site_row($deactivation, true);; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
    }
}
