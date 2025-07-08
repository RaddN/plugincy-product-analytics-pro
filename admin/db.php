<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_db
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        
    }

    public function init()
    {
        // Initialize plugin
    }
    public function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Products table
        $products_table = $wpdb->prefix . 'pap_products';
        $sql_products = "CREATE TABLE $products_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            product_id varchar(255) NOT NULL UNIQUE,
            product_name varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Analytics data table
        $analytics_table = $wpdb->prefix . 'pap_analytics';
        $sql_analytics = "CREATE TABLE $analytics_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            product_id varchar(255) NOT NULL,
            site_url varchar(255) NOT NULL,
            status varchar(20) DEFAULT 'active',
            multisite tinyint(1) DEFAULT 0,
            deactivate_reason text,
            wp_version varchar(20),
            php_version varchar(20),
            server_software varchar(255),
            mysql_version varchar(20),
            location varchar(100),
            plugin_version varchar(20),
            other_plugins text,
            active_theme varchar(255),
            activation_date datetime DEFAULT CURRENT_TIMESTAMP,
            deactivation_date datetime NULL,
            using_pro tinyint(1) DEFAULT 0,
            license_key varchar(255),
            last_seen datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_products);
        dbDelta($sql_analytics);
    }

    public function get_product_stats($product_id)
    {
        global $wpdb;

        $total = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}pap_analytics WHERE product_id = %s
        ", $product_id));

        $active = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}pap_analytics WHERE product_id = %s AND status = 'active'
        ", $product_id));

        return array(
            'total_installs' => $total ?: 0,
            'active_installs' => $active ?: 0
        );
    }
    public function get_detailed_stats($product_id)
    {
        global $wpdb;

        $stats = $this->get_product_stats($product_id);
        $stats['inactive_installs'] = $stats['total_installs'] - $stats['active_installs'];

        // Get 30-day activations
        $stats['activations_30_days'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}pap_analytics 
            WHERE product_id = %s AND activation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ", $product_id));

        // Chart data: Active vs Inactive
        $stats['chart_data'] = array(
            'active' => $stats['active_installs'],
            'inactive' => $stats['inactive_installs']
        );

        // Multisite Usage
        $multisite_counts = $wpdb->get_results($wpdb->prepare("
            SELECT multisite, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY multisite
        ", $product_id), ARRAY_A);
        $stats['multisite_usage'] = array('multisite' => 0, 'singlesite' => 0);
        foreach ($multisite_counts as $row) {
            if ($row['multisite']) {
                $stats['multisite_usage']['multisite'] += intval($row['count']);
            } else {
                $stats['multisite_usage']['singlesite'] += intval($row['count']);
            }
        }

        // WordPress Versions
        $wp_versions = $wpdb->get_results($wpdb->prepare("
            SELECT wp_version, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY wp_version
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['wp_versions'] = array();
        foreach ($wp_versions as $row) {
            $stats['wp_versions'][$row['wp_version']] = intval($row['count']);
        }

        // PHP Versions
        $php_versions = $wpdb->get_results($wpdb->prepare("
            SELECT php_version, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY php_version
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['php_versions'] = array();
        foreach ($php_versions as $row) {
            $stats['php_versions'][$row['php_version']] = intval($row['count']);
        }

        // Pro vs Free
        $pro_counts = $wpdb->get_results($wpdb->prepare("
            SELECT using_pro, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY using_pro
        ", $product_id), ARRAY_A);
        $stats['pro_vs_free'] = array('Pro' => 0, 'Free' => 0);
        foreach ($pro_counts as $row) {
            if ($row['using_pro']) {
                $stats['pro_vs_free']['Pro'] += intval($row['count']);
            } else {
                $stats['pro_vs_free']['Free'] += intval($row['count']);
            }
        }

        // Deactivation Reasons
        $deactivation_reasons = $wpdb->get_results($wpdb->prepare("
            SELECT deactivate_reason, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND status = 'inactive' AND deactivate_reason IS NOT NULL AND deactivate_reason != ''
            GROUP BY deactivate_reason
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['deactivation_reasons'] = array();
        foreach ($deactivation_reasons as $row) {
            $stats['deactivation_reasons'][$row['deactivate_reason']] = intval($row['count']);
        }

        // Server Software
        $server_software = $wpdb->get_results($wpdb->prepare("
            SELECT server_software, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY server_software
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['server_software'] = array();
        foreach ($server_software as $row) {
            $stats['server_software'][$row['server_software']] = intval($row['count']);
        }

        // MySQL Versions
        $mysql_versions = $wpdb->get_results($wpdb->prepare("
            SELECT mysql_version, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY mysql_version
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['mysql_versions'] = array();
        foreach ($mysql_versions as $row) {
            $stats['mysql_versions'][$row['mysql_version']] = intval($row['count']);
        }

        // Plugin Versions
        $plugin_versions = $wpdb->get_results($wpdb->prepare("
            SELECT plugin_version, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
            GROUP BY plugin_version
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['plugin_versions'] = array();
        foreach ($plugin_versions as $row) {
            $stats['plugin_versions'][$row['plugin_version']] = intval($row['count']);
        }

        // Locations (Mostly use from)
        $locations = $wpdb->get_results($wpdb->prepare("
            SELECT location, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND location IS NOT NULL AND location != ''
            GROUP BY location
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['locations'] = array();
        foreach ($locations as $row) {
            $stats['locations'][$row['location']] = intval($row['count']);
        }

        // Top 10 sites most days used
        $top_sites = $wpdb->get_results($wpdb->prepare("
            SELECT site_url, activation_date, COALESCE(deactivation_date, NOW()) as end_date
            FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s
        ", $product_id));
        $site_days = array();
        foreach ($top_sites as $site) {
            $start = new DateTime($site->activation_date);
            $end = new DateTime($site->end_date);
            $days = $start->diff($end)->days;
            $site_days[$site->site_url] = $days;
        }
        arsort($site_days);
        $stats['top_sites_days_used'] = array_slice($site_days, 0, 10, true);

        // Most used plugins with our product (chart)
        $plugin_counts = array();
        $other_plugins_rows = $wpdb->get_results($wpdb->prepare("
            SELECT other_plugins FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND other_plugins IS NOT NULL AND other_plugins != ''
        ", $product_id));
        foreach ($other_plugins_rows as $row) {
            $plugins = json_decode($row->other_plugins, true);
            if (is_array($plugins)) {
                foreach ($plugins as $plugin) {
                    $plugin = is_array($plugin) && isset($plugin['name']) ? $plugin['name'] : $plugin;
                    if (!empty($plugin)) {
                        if (!isset($plugin_counts[$plugin])) $plugin_counts[$plugin] = 0;
                        $plugin_counts[$plugin]++;
                    }
                }
            }
        }
        arsort($plugin_counts);
        $stats['most_used_plugins'] = array_slice($plugin_counts, 0, 10, true);

        // Most used themes with our product
        $theme_counts = $wpdb->get_results($wpdb->prepare("
            SELECT active_theme, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND active_theme IS NOT NULL AND active_theme != ''
            GROUP BY active_theme
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['most_used_themes'] = array();
        foreach ($theme_counts as $row) {
            $stats['most_used_themes'][$row['active_theme']] = intval($row['count']);
        }

        // People deactivated the most from themes
        $deactivated_themes = $wpdb->get_results($wpdb->prepare("
            SELECT active_theme, COUNT(*) as count FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND status = 'inactive' AND active_theme IS NOT NULL AND active_theme != ''
            GROUP BY active_theme
            ORDER BY count DESC
            LIMIT 10
        ", $product_id), ARRAY_A);
        $stats['deactivated_themes'] = array();
        foreach ($deactivated_themes as $row) {
            $stats['deactivated_themes'][$row['active_theme']] = intval($row['count']);
        }
        // installs in this year month wise
        $current_year = gmdate('Y');
        $monthly_installs = $wpdb->get_results($wpdb->prepare("
            SELECT MONTH(activation_date) as month, COUNT(*) as count
            FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND YEAR(activation_date) = %d
            GROUP BY month
            ORDER BY month ASC
        ", $product_id, $current_year), ARRAY_A);

        $stats['installs_this_year'] = array_fill(1, 12, 0);
        foreach ($monthly_installs as $row) {
            $stats['installs_this_year'][intval($row['month'])] = intval($row['count']);
        }

        // uninstalls in this year month wise
        $current_year = gmdate('Y');
        $monthly_uninstalls = $wpdb->get_results($wpdb->prepare("
            SELECT MONTH(deactivation_date) as month, COUNT(*) as count
            FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND YEAR(deactivation_date) = %d AND status = 'inactive'
            GROUP BY month
            ORDER BY month ASC
        ", $product_id, $current_year), ARRAY_A);

        $stats['uninstalls_this_year'] = array_fill(1, 12, 0);
        foreach ($monthly_uninstalls as $row) {
            $stats['uninstalls_this_year'][intval($row['month'])] = intval($row['count']);
        }

        // installs in this month date wise
        $current_year = gmdate('Y');
        $current_month = gmdate('n');
        $daily_installs = $wpdb->get_results($wpdb->prepare("
            SELECT DAY(activation_date) as day, COUNT(*) as count
            FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND YEAR(activation_date) = %d AND MONTH(activation_date) = %d
            GROUP BY day
            ORDER BY day ASC
        ", $product_id, $current_year, $current_month), ARRAY_A);

        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
        $stats['installs_this_month'] = array_fill(1, $days_in_month, 0);
        foreach ($daily_installs as $row) {
            $stats['installs_this_month'][intval($row['day'])] = intval($row['count']);
        }

        // uninstalls in this month date wise
        $daily_uninstalls = $wpdb->get_results($wpdb->prepare("
            SELECT DAY(deactivation_date) as day, COUNT(*) as count
            FROM {$wpdb->prefix}pap_analytics
            WHERE product_id = %s AND YEAR(deactivation_date) = %d AND MONTH(deactivation_date) = %d AND status = 'inactive'
            GROUP BY day
            ORDER BY day ASC
        ", $product_id, $current_year, $current_month), ARRAY_A);

        $stats['uninstalls_this_month'] = array_fill(1, $days_in_month, 0);
        foreach ($daily_uninstalls as $row) {
            $stats['uninstalls_this_month'][intval($row['day'])] = intval($row['count']);
        }

        return $stats;
    }
}