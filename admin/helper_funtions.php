<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class ProductAnalyticsPro_help
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        
    }

    public function init()
    {
        // Initialize plugin
    }
    public function calculate_days_active($activation_date, $deactivation_date)
    {
        $start = new DateTime($activation_date);
        $end = $deactivation_date ? new DateTime($deactivation_date) : new DateTime();
        return $start->diff($end)->days;
    }


}