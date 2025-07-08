<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_admin
{

    private $proucts_page;
    private $settings_page;
    private $dashboard;

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
         $this->proucts_page = new ProductAnalyticsPro_products();
         $this->settings_page = new ProductAnalyticsPro_settings();
         $this->dashboard = new ProductAnalyticsPro_dashboard();

    }

    public function init()
    {
        // Initialize plugin
    }
    public function admin_menu()
    {
        add_menu_page(
            'Product Analytics',
            'Product Analytics',
            'manage_options',
            'product-analytics',
            array($this->proucts_page, 'products_page'),
            'dashicons-chart-bar',
            30
        );

        add_submenu_page(
            'product-analytics',
            'Products',
            'Products',
            'manage_options',
            'product-analytics',
            array($this->proucts_page, 'products_page')
        );

        add_submenu_page(
            'product-analytics',
            'Add Product',
            'Add Product',
            'manage_options',
            'add-product',
            array($this->proucts_page, 'add_product_page')
        );

        add_submenu_page(
            'product-analytics',
            'Settings',
            'Settings',
            'manage_options',
            'analytics-settings',
            array($this->settings_page, 'settings_page')
        );

        // Hidden pages for individual product views
        add_submenu_page(
            null,
            'Product Dashboard',
            'Product Dashboard',
            'manage_options',
            'product-dashboard',
            array($this->dashboard, 'product_dashboard_page')
        );
    }

}