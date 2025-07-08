<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_ajax
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_pap_get_site_details', array($this, 'ajax_get_site_details'));
        add_action('wp_ajax_pap_get_product', array($this, 'ajax_get_product_details'));
        add_action('wp_ajax_pap_update_product', array($this, 'ajax_update_product'));
        add_action('wp_ajax_pap_delete_product', array($this, 'ajax_delete_product'));
        
    }

    public function init()
    {
        // Initialize plugin
    }

    public function ajax_get_site_details()
    {
        check_ajax_referer('pap_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        if (empty($_POST['site_id'])) {
            wp_send_json_error(array('message' => 'Missing site_id'), 400);
        }

        global $wpdb;
        $site_id = intval($_POST['site_id']);
        $site = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pap_analytics WHERE id = %d",
            $site_id
        ));

        if (!$site) {
            wp_send_json_error(array('message' => 'Site not found'), 404);
        }

        // Decode other_plugins if present
        $site->other_plugins = $site->other_plugins ? json_decode($site->other_plugins, true) : array();

        wp_send_json_success($site);
    }
    public function ajax_get_product_details()
    {
        check_ajax_referer('pap_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        if (empty($_POST['product_id'])) {
            wp_send_json_error(array('message' => 'Missing product_id'), 400);
        }

        global $wpdb;
        $product_id = intval($_POST['product_id']); // Using ID instead of product_id string

        // Get product details by ID
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pap_products WHERE id = %d",
            $product_id
        ));

        if (!$product) {
            wp_send_json_error(array('message' => 'Product not found'), 404);
        }

        wp_send_json_success($product);
    }
    /**
     * AJAX handler for updating a product
     */
    public function ajax_update_product()
    {
        check_ajax_referer('pap_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        if (empty($_POST['product_id'])) {
            wp_send_json_error(array('message' => 'Missing product_id'), 400);
        }

        global $wpdb;
        $product_id = intval($_POST['product_id']);

        $product_name = isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])) : '';
        $product_slug = isset($_POST['product_slug']) ? sanitize_text_field(wp_unslash($_POST['product_slug'])) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';

        if (empty($product_slug) && isset($_POST['product_id_field'])) {
            $product_slug = sanitize_text_field(wp_unslash($_POST['product_id_field']));
        }

        if (empty($product_name)) {
            wp_send_json_error(array('message' => 'Product name is required'), 400);
        }

        $existing_product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pap_products WHERE product_id = %d",
            $product_id
        ));

        if (!$existing_product) {
            wp_send_json_error(array('message' => 'Product not found'), 404);
        }

        $update_data = array(
            'product_name' => $product_name,
            'description' => $description
        );

        if ($existing_product->product_name === $product_name && $existing_product->description === $description) {
            wp_send_json_success(array('message' => 'No changes detected'), 200);
        }

        $result = $wpdb->update(
            $wpdb->prefix . 'pap_products',
            $update_data,
            array('product_id' => $product_id),
            array('%s', '%s'),
            array('%d')
        );

        if ($wpdb->last_error) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error), 500);
        }

        if ($result !== false) {
            if (!empty($product_slug) && $existing_product->product_id !== $product_slug) {
                $wpdb->update(
                    $wpdb->prefix . 'pap_analytics',
                    array('product_id' => $product_slug),
                    array('product_id' => $existing_product->product_id),
                    array('%s'),
                    array('%s')
                );
            }

            wp_send_json_success(array(
                'message' => 'Product updated successfully',
                'rows_affected' => $result,
                'data' => $update_data,
            ));
        } else {
            wp_send_json_error(array('message' => 'Error updating product'), 500);
        }
    }

    /**
     * AJAX handler for deleting a product
     */
    public function ajax_delete_product()
    {
        check_ajax_referer('pap_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        if (empty($_POST['product_id'])) {
            wp_send_json_error(array('message' => 'Missing product_id'), 400);
        }

        global $wpdb;
        $product_id = intval($_POST['product_id']);

        // Check if product exists
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pap_products WHERE id = %d",
            $product_id
        ));

        if (!$product) {
            wp_send_json_error(array('message' => 'Product not found'), 404);
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Delete analytics data first (foreign key constraint)
            $analytics_deleted = $wpdb->delete(
                $wpdb->prefix . 'pap_analytics',
                array('product_id' => $product->product_id),
                array('%s')
            );

            // Delete product
            $product_deleted = $wpdb->delete(
                $wpdb->prefix . 'pap_products',
                array('id' => $product_id),
                array('%d')
            );

            if ($product_deleted !== false) {
                $wpdb->query('COMMIT');
                wp_send_json_success(array(
                    'message' => 'Product deleted successfully',
                    'analytics_records_deleted' => $analytics_deleted
                ));
            } else {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(array('message' => 'Error deleting product'), 500);
            }
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'Error deleting product: ' . $e->getMessage()), 500);
        }
    }
}