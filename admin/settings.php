<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ProductAnalyticsPro_settings
{
    private $option_name = 'product_analytics_pro_settings';
    private $default_settings = array(
        'retention_days' => 365,
        'allowed_sites' => array(),
    );

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_form_submission'));
    }

    public function init()
    {
        // Initialize plugin
        $this->maybe_create_default_settings();
    }

    public function register_settings()
    {
        register_setting('product_analytics_pro_settings_group', $this->option_name);
    }

    private function maybe_create_default_settings()
    {
        if (!get_option($this->option_name)) {
            update_option($this->option_name, $this->default_settings);
        }
    }

    public function get_settings()
    {
        $settings = get_option($this->option_name, $this->default_settings);
        return wp_parse_args($settings, $this->default_settings);
    }

    public function handle_form_submission()
    {
        // Check if form was submitted
        if (!isset($_POST['pap_save_settings']) || !isset($_POST['pap_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['pap_nonce'], 'pap_settings_nonce')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Security check failed</p></div>';
            });
            return;
        }

        // Check user permissions
        if (!current_user_can('manage_options')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Insufficient permissions</p></div>';
            });
            return;
        }

        $retention_days = intval($_POST['retention_days']);
        $allowed_sites = array_filter(array_map('trim', explode("\n", sanitize_textarea_field($_POST['allowed_sites']))));

        // Validate retention days
        if ($retention_days < 1) {
            $retention_days = 365;
        }

        // Validate URLs
        $valid_sites = array();
        foreach ($allowed_sites as $site) {
            if (filter_var($site, FILTER_VALIDATE_URL)) {
                $valid_sites[] = $site;
            }
        }

        $settings = array(
            'retention_days' => $retention_days,
            'allowed_sites' => $valid_sites,
        );

        if (update_option($this->option_name, $settings)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Failed to save settings</p></div>';
            });
        }
    }

    public function settings_page()
    {
        $settings = $this->get_settings();
        $allowed_sites_text = implode("\n", $settings['allowed_sites']);
        ?>
        <div class="wrap pap-wrap">
            <div class="pap-header">
                <h1 class="pap-title">Product Analytics Pro - Settings</h1>
            </div>

            <div class="pap-settings-container">
                <form method="post" action="">
                    <?php wp_nonce_field('pap_settings_nonce', 'pap_nonce'); ?>
                    
                    <div class="pap-settings-section">
                        <h2>API Settings</h2>
                        <div class="pap-form-group">
                            <label>REST API Base URL</label>
                            <input type="text" value="<?php echo esc_html(rest_url('product-analytics/v1/')); ?>" readonly>
                            <p class="pap-help-text">This is your API endpoint URL for external integrations</p>
                        </div>
                        
                        <div class="pap-form-group">
                            <label>API Authentication</label>
                            <p class="pap-help-text">Use WordPress authentication for API access</p>
                        </div>
                    </div>

                    <div class="pap-settings-section">
                        <h2>Allowed Sites</h2>
                        <div class="pap-form-group">
                            <label for="allowed_sites">Your Site URLs</label>
                            <textarea id="allowed_sites" name="allowed_sites" rows="8" placeholder="Enter your site URLs, one per line&#10;https://example.com&#10;https://mystore.com"><?php echo esc_textarea($allowed_sites_text); ?></textarea>
                            <p class="pap-help-text">Add your own site URLs here, one per line.</p>
                        </div>
                    </div>

                    <div class="pap-settings-section">
                        <h2>Data Management</h2>
                        <div class="pap-form-group">
                            <label for="retention_days">Data Retention (days)</label>
                            <input type="number" id="retention_days" name="retention_days" value="<?php echo esc_attr($settings['retention_days']); ?>" min="1" max="3650">
                            <p class="pap-help-text">How long to keep analytics data before automatic deletion</p>
                        </div>
                    </div>

                    <div class="pap-settings-section">
                        <button type="submit" name="pap_save_settings" class="button button-primary pap-save-btn">Save Settings</button>
                        <button type="button" class="button pap-reset-btn">Reset to Defaults</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.pap-reset-btn').on('click', function() {
                if (confirm('Are you sure you want to reset all settings to defaults?')) {
                    $('#retention_days').val('365');
                    $('#allowed_sites').val('');
                }
            });
        });
        </script>
        <?php
    }

    // Helper method to check if a site is allowed
    public function is_own_site($url)
    {
        $settings = $this->get_settings();
        $allowed_sites = $settings['allowed_sites'];
        
        if (empty($allowed_sites)) {
            return true; // Allow all if no restrictions set
        }
        
        foreach ($allowed_sites as $allowed_site) {
            if (strpos($url, $allowed_site) === 0) {
                return true;
            }
        }
        
        return false;
    }
}