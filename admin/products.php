<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_products
{

    private $pap_db_calls;
    public function __construct()
    {
        add_action('init', array($this, 'init'));
        $this->pap_db_calls = new ProductAnalyticsPro_db();
        
    }

    public function init()
    {
        // Initialize plugin
    }

    public function products_page()
    {
        global $wpdb;
        $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pap_products ORDER BY created_at DESC");
?>
        <div class="wrap pap-wrap">
            <div class="pap-header">
                <h1 class="pap-title">Product Analytics</h1>
                <a href="<?php echo esc_url(admin_url('admin.php?page=add-product')); ?>" class="pap-btn pap-btn-primary">
                    <span class="dashicons dashicons-plus"></span> Add New Product
                </a>
            </div>

            <div class="pap-products-grid">
                <?php if (empty($products)): ?>
                    <div class="pap-empty-state">
                        <div class="pap-empty-icon">📊</div>
                        <h3>No Products Yet</h3>
                        <p>Create your first product to start tracking analytics</p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=add-product')); ?>" class="pap-btn pap-btn-primary">Add Your First Product</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="pap-product-card" data-product-id="<?php echo esc_html($product->id); ?>">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=product-dashboard&product_id=' . $product->product_id)); ?>" class="overlaybtn"></a>
                            <div class="pap-card-header">
                                <h3><?php echo esc_html($product->product_name); ?></h3>
                                <div class="pap-card-menu">
                                    <button class="pap-menu-toggle" data-product-id="<?php echo esc_html($product->id); ?>">⋮</button>
                                    <div class="pap-dropdown-menu">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=product-dashboard&product_id=' . $product->product_id)); ?>">📊 Dashboard</a>
                                        <a href="#" class="pap-edit-product" data-product-id="<?php echo esc_html($product->id); ?>">✏️ Edit</a>
                                        <a href="#" class="pap-delete-product" data-product-id="<?php echo esc_html($product->id); ?>">🗑️ Delete</a>
                                    </div>
                                </div>
                            </div>
                            <div class="pap-card-content">
                                <p><?php echo esc_html($product->description); ?></p>
                                <div class="pap-product-id">ID: <?php echo esc_html($product->product_id); ?></div>
                                <div class="pap-product-stats">
                                    <?php
                                    $stats = $this->pap_db_calls->get_product_stats($product->product_id);
                                    ?>
                                    <div class="pap-stat">
                                        <span class="pap-stat-number"><?php echo esc_html($stats['total_installs']); ?></span>
                                        <span class="pap-stat-label">Total Installs</span>
                                    </div>
                                    <div class="pap-stat">
                                        <span class="pap-stat-number"><?php echo esc_html($stats['active_installs']); ?></span>
                                        <span class="pap-stat-label">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    public function add_product_page()
    {
        // Remove any whitespace/output before this point
        if ( isset($_POST['pap_nonce']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pap_nonce'])), 'pap_add_product')) {
            wp_die('Security check failed');
        }
        
        if (isset($_POST['submit_product'])) {
            $this->save_product();
        }
    ?>
        <div class="wrap pap-wrap">
            <div class="pap-header">
                <h1 class="pap-title">Add New Product</h1>
            </div>

            <div class="pap-form-container">
                <form method="post" class="pap-form">
                    <?php wp_nonce_field('pap_add_product', 'pap_nonce'); ?>

                    <div class="pap-form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>

                    <div class="pap-form-group">
                        <label for="product_id">Product ID</label>
                        <input type="text" id="product_id" name="product_id" required>
                        <p class="pap-help-text">Unique identifier for REST API endpoints</p>
                    </div>

                    <div class="pap-form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="pap-form-actions">
                        <button type="submit" name="submit_product" class="pap-btn pap-btn-primary">Create Product</button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=product-analytics')); ?>" class="pap-btn pap-btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
    }
    private function save_product()
    {
        // Remove any whitespace/output before this point
        if ( isset($_POST['pap_nonce']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pap_nonce'])), 'pap_add_product')) {
            wp_die('Security check failed');
        }

        global $wpdb;

        $product_name =  isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])):"";
        $product_id = isset($_POST['product_id']) ? sanitize_text_field(wp_unslash($_POST['product_id'])):"";
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])):"";

        $result = $wpdb->insert(
            $wpdb->prefix . 'pap_products',
            array(
                'product_name' => $product_name,
                'product_id' => $product_id,
                'description' => $description
            )
        );

        if ($result) {
            echo '<script>
            window.location.href = "' . esc_url(admin_url('admin.php?page=product-analytics&m=created')) . '";
            </script>';
            exit;
        }
    }

}