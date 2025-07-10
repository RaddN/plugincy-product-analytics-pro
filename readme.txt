=== Plugincy Product Analytics Pro ===
Contributors:  plugincy
Tags: analytics, tracking, statistics, dashboard, rest-api
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced product analytics and tracking system with comprehensive dashboard and REST API integration.

== Description ==

Plugincy Product Analytics Pro is a powerful WordPress plugin that provides comprehensive product analytics and tracking capabilities. Perfect for e-commerce stores, digital product sellers, and businesses looking to gain deep insights into their product performance.

**Key Features:**

* **Advanced Analytics Dashboard** - Interactive charts and graphs powered by Chart.js
* **Product Performance Tracking** - Monitor views, clicks, conversions, and engagement
* **REST API Integration** - Complete API for external integrations and custom applications
* **Real-time Statistics** - Live data updates and detailed reporting
* **Multi-product Support** - Track unlimited products with individual analytics
* **Custom Database Tables** - Optimized storage for fast query performance
* **Admin Interface** - Clean, intuitive backend for easy management
* **AJAX-powered Interface** - Smooth user experience with dynamic content loading

**Perfect For:**

* E-commerce stores wanting detailed product insights
* Digital marketers tracking campaign performance
* Product managers analyzing user behavior
* Developers building custom analytics solutions
* Business owners making data-driven decisions

**What You Can Track:**

* Product views and impressions
* Click-through rates
* Conversion metrics
* User engagement patterns
* Performance trends over time
* Custom analytics events

The plugin creates its own database tables for optimal performance and provides a comprehensive REST API for integration with external systems, mobile apps, or custom dashboards.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugincy-product-analytics-pro` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to 'Product Analytics' in your WordPress admin menu to configure the plugin.
4. Add your first product to start tracking analytics.
5. Configure your settings in the 'Analytics Settings' section.

**Requirements:**
* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce? =

Yes, Plugincy Product Analytics Pro is designed to work alongside WooCommerce and other e-commerce plugins. It provides additional analytics beyond what's typically available.

= Can I export analytics data? =

Yes, the plugin provides a REST API that allows you to export and integrate your analytics data with external systems.

= Does it affect site performance? =

No, the plugin is optimized for performance with efficient database queries and lightweight frontend tracking.

= Is there a limit to how many products I can track? =

No, you can track unlimited products with the plugin.

= Can I customize the dashboard? =

The plugin provides a comprehensive dashboard with various chart types and filtering options. Additional customization may require custom development.

= Does it work with caching plugins? =

Yes, the plugin is compatible with popular caching plugins. Analytics tracking uses AJAX calls that bypass cache.

= Is the data stored securely? =

Yes, all data is stored in your WordPress database with proper sanitization and security measures.

== Screenshots ==

1. **Main Dashboard** - Overview of all product analytics with interactive charts
2. **Product Management** - Add and manage products for tracking
3. **Detailed Analytics** - In-depth statistics for individual products
4. **Settings Panel** - Configure plugin options and preferences
5. **REST API Documentation** - Built-in API documentation and testing

== Changelog ==

= 1.0.3 =
* Initial release
* Core analytics functionality
* REST API integration
* Admin dashboard with Chart.js
* Product management system
* Settings configuration
* Database optimization
* AJAX-powered interface

== Upgrade Notice ==

= 1.0.3 =
Initial release of Plugincy Product Analytics Pro with comprehensive product tracking and analytics capabilities.

== Support ==

For support, documentation, and feature requests, please visit our support page or contact us directly.

**Documentation:** Full API documentation and user guides available in the plugin admin area.

**REST API Endpoints:**
* GET /wp-json/product-analytics/v1/products - List all products
* GET /wp-json/product-analytics/v1/stats/{product_id} - Get product statistics
* POST /wp-json/product-analytics/v1/track - Track events
* And many more...

== Developer Information ==

**Hooks and Filters:**
The plugin provides various hooks and filters for developers to extend functionality:

* `pap_before_track_event` - Filter before tracking events
* `pap_after_track_event` - Action after tracking events
* `pap_dashboard_stats` - Filter dashboard statistics
* `pap_rest_api_response` - Filter REST API responses

**Custom Development:**
The plugin is built with extensibility in mind. Developers can easily add custom tracking events, modify dashboard layouts, and integrate with third-party services.

== Privacy Policy ==

This plugin tracks product analytics data including:
* Product views and interactions
* User behavior patterns (anonymized)
* Performance metrics

All data is stored locally in your WordPress database. No data is sent to external servers unless explicitly configured by the site administrator.

== Credits ==

* Chart.js library for beautiful, responsive charts
* WordPress REST API for seamless integrations
* jQuery for smooth user interactions