<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_rest_api_setup
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function init()
    {
        // Initialize plugin
    }

    public function register_rest_routes()
    {
        register_rest_route('product-analytics/v1', '/track/(?P<product_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'track_analytics'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('product-analytics/v1', '/deactivate/(?P<product_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'track_deactivation'),
            'permission_callback' => '__return_true'
        ));
    }

    public function track_analytics($request)
    {
        $product_id = $request->get_param('product_id');
        $data = $request->get_json_params();

        if (!is_array($data) || !isset($data['site_url'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing or invalid site_url in request body.',
                'request' => $data
            ), 400);
        }

        global $wpdb;

        // Check if entry exists
        $existing = $wpdb->get_row($wpdb->prepare("
            SELECT id FROM {$wpdb->prefix}pap_analytics 
            WHERE product_id = %s AND site_url = %s
        ", $product_id, $data['site_url']));

        if ($existing) {
            // Update existing entry
            $wpdb->update(
                $wpdb->prefix . 'pap_analytics',
                array(
                    'status' => 'active',
                    'multisite' => isset($data['multisite']) ? (bool)$data['multisite'] : 0,
                    'wp_version' => sanitize_text_field(wp_unslash($data['wp_version'])),
                    'php_version' => sanitize_text_field(wp_unslash($data['php_version'])),
                    'server_software' => sanitize_text_field(wp_unslash($data['server_software'])),
                    'mysql_version' => sanitize_text_field(wp_unslash($data['mysql_version'])),
                    'location' => sanitize_text_field(wp_unslash($data['location'])),
                    'plugin_version' => sanitize_text_field(wp_unslash($data['plugin_version'])),
                    'other_plugins' => json_encode(wp_unslash($data['other_plugins'])),
                    'active_theme' => sanitize_text_field(wp_unslash($data['active_theme'])),
                    'using_pro' => isset($data['using_pro']) ? (bool)$data['using_pro'] : 0,
                    'license_key' => sanitize_text_field(wp_unslash($data['license_key'])),
                    'last_seen' => current_time('mysql')
                ),
                array('id' => $existing->id)
            );
        } else {
            // Insert new entry
            $wpdb->insert(
                $wpdb->prefix . 'pap_analytics',
                array(
                    'product_id' => $product_id,
                    'site_url' => sanitize_url($data['site_url']),
                    'status' => 'active',
                    'multisite' => isset($data['multisite']) ? (bool)$data['multisite'] : 0,
                    'wp_version' => sanitize_text_field(wp_unslash($data['wp_version'])),
                    'php_version' => sanitize_text_field(wp_unslash($data['php_version'])),
                    'server_software' => sanitize_text_field(wp_unslash($data['server_software'])),
                    'mysql_version' => sanitize_text_field(wp_unslash($data['mysql_version'])),
                    'location' => sanitize_text_field(wp_unslash($data['location'])),
                    'plugin_version' => sanitize_text_field(wp_unslash($data['plugin_version'])),
                    'other_plugins' => json_encode($data['other_plugins']),
                    'active_theme' => sanitize_text_field(wp_unslash($data['active_theme'])),
                    'using_pro' => isset($data['using_pro']) ? (bool)$data['using_pro'] : 0,
                    'license_key' => sanitize_text_field(wp_unslash($data['license_key'])),
                    'activation_date' => current_time('mysql'),
                    'last_seen' => current_time('mysql')
                )
            );
        }

        // Fetch the latest analytics entry for this product/site
        $entry = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}pap_analytics 
            WHERE product_id = %s AND site_url = %s
            ORDER BY last_seen DESC LIMIT 1
        ", $product_id, $data['site_url']));

        return new WP_REST_Response(array(
            'success' => true,
            'request' => $request,
            'data' => $entry
        ), 200);
    }

    public function track_deactivation($request)
    {
        $product_id = $request->get_param('product_id');
        $data = $request->get_json_params();

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'pap_analytics',
            array(
                'status' => 'inactive',
                'deactivate_reason' => sanitize_text_field(wp_unslash($data['reason'])),
                'deactivation_date' => current_time('mysql')
            ),
            array(
                'product_id' => $product_id,
                'site_url' => sanitize_url($data['site_url'])
            )
        );

        return new WP_REST_Response(array('success' => true), 200);
    }
}
