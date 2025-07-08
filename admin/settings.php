<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_settings
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        
    }

    public function init()
    {
        // Initialize plugin
    }

    public function settings_page()
    {
    ?>
        <div class="wrap pap-wrap">
            <div class="pap-header">
                <h1 class="pap-title">Settings</h1>
            </div>

            <div class="pap-settings-container">
                <div class="pap-settings-section">
                    <h2>API Settings</h2>
                    <div class="pap-form-group">
                        <label>REST API Base URL</label>
                        <input type="text" value="<?php echo  esc_html(rest_url('product-analytics/v1/')); ?>" readonly>
                    </div>
                    <div class="pap-form-group">
                        <label>API Authentication</label>
                        <p class="pap-help-text">Use WordPress authentication for API access</p>
                    </div>
                </div>

                <div class="pap-settings-section">
                    <h2>Data Retention</h2>
                    <div class="pap-form-group">
                        <label for="retention_days">Keep data for (days)</label>
                        <input type="number" id="retention_days" value="365" min="1">
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

}