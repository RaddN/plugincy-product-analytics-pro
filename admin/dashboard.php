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
        $current_tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING)??'dashboard';
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
        $sites = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}pap_analytics 
            WHERE product_id = %s 
            ORDER BY last_seen DESC
        ", $product_id));
    ?>
        <div class="pap-sites-table">
            <table class="pap-table">
                <thead>
                    <tr>
                        <th>Site URL</th>
                        <th>Status</th>
                        <th>Multisite</th>
                        <th>WordPress</th>
                        <th>PHP</th>
                        <th>Days Active</th>
                        <th>Pro</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sites as $site): ?>
                        <tr>
                            <td><?php echo esc_html($site->site_url); ?></td>
                            <td>
                                <span class="pap-status pap-status-<?php echo esc_html($site->status); ?>">
                                    <?php echo esc_html(ucfirst($site->status)); ?>
                                </span>
                            </td>
                            <td><?php echo $site->multisite ? 'Yes' : 'No'; ?></td>
                            <td><?php echo esc_html($site->wp_version); ?></td>
                            <td><?php echo esc_html($site->php_version); ?></td>
                            <td><?php echo esc_html($this->help->calculate_days_active($site->activation_date, $site->deactivation_date)); ?></td>
                            <td><?php echo $site->using_pro ? 'Yes' : 'No'; ?></td>
                            <td>
                                <button class="pap-btn pap-btn-small pap-view-details" data-site-id="<?php echo esc_html($site->id); ?>">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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