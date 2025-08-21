<?php

/**
 * Plugin Name: Plugincy Product Analytics Pro
 * Description: Advanced product analytics and tracking system with REST API
 * Version: 1.0.9
 * Author: plugincy
 * Author URI: https://plugincy.com
 * Text Domain: plugincy-product-analytics-pro
 * license: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PAP_VERSION', '1.0.9');
define('PAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PAP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'admin/admin.php';
require_once plugin_dir_path(__FILE__) . 'admin/products.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'admin/db.php';
require_once plugin_dir_path(__FILE__) . 'admin/dashboard.php';
require_once plugin_dir_path(__FILE__) . 'admin/helper_funtions.php';
require_once plugin_dir_path(__FILE__) . 'admin/ajax_calls.php';
require_once plugin_dir_path(__FILE__) . 'admin/rest_api.php';

class ProductAnalyticsPro
{
    private $pap_db_calls;

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        new ProductAnalyticsPro_admin();
        new ProductAnalyticsPro_ajax();
        new ProductAnalyticsPro_rest_api_setup();
        $this->pap_db_calls = new ProductAnalyticsPro_db();
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        // Create tables on activation
        register_activation_hook(__FILE__, array($this->pap_db_calls, 'create_tables'));
    }

    public function init()
    {
        // Initialize plugin
    }

    public function admin_scripts($hook)
    {
        if (strpos($hook, 'product-analytics') === false && strpos($hook, 'add-product') === false && strpos($hook, 'analytics-settings') === false && strpos($hook, 'product-dashboard') === false  && strpos($hook, 'product-dashboard') === false) {
            return;
        }

        wp_enqueue_script('chart-js', PAP_PLUGIN_URL . 'assets/chart.js', array(), '4.5.0', true);
        wp_enqueue_script('pap-admin', PAP_PLUGIN_URL . 'assets/admin.js', array('jquery', 'chart-js'), PAP_VERSION, true);
        wp_enqueue_style('pap-admin', PAP_PLUGIN_URL . 'assets/admin.css', array(), PAP_VERSION);

        // Get product_id from URL if present, fallback to first product
        $product_id = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_STRING);
        if (empty($product_id)) {
            global $wpdb;
            $first_product = $wpdb->get_var("SELECT product_id FROM {$wpdb->prefix}pap_products ORDER BY created_at ASC LIMIT 1");
            $product_id = $first_product ? $first_product : '';
        }
        $stats = $product_id ? $this->pap_db_calls->get_detailed_stats($product_id) : array();

        wp_localize_script('pap-admin', 'pap_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pap_nonce'),
            'rest_url' => rest_url('product-analytics/v1/'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'chartData' => $stats,
            'messages' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'product-analytics-pro'),
                'loading' => __('Loading...', 'product-analytics-pro'),
                'error' => __('An error occurred. Please try again.', 'product-analytics-pro'),
                'success' => __('Operation completed successfully.', 'product-analytics-pro')
            )
        ));
    }
}

// Initialize the plugin
new ProductAnalyticsPro();
