<?php
/*
Plugin Name: My Currency Rates Plugin
Description: Fetches and displays currency exchange rates in the admin area.
Version: 1.0
Author: Your Name
License: GPL2
Text Domain: my-currency-rates-plugin
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('MY_CURRENCY_RATES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MY_CURRENCY_RATES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
if (!class_exists('MyCurrencyRates\Plugin')) {
    require_once MY_CURRENCY_RATES_PLUGIN_DIR . 'includes/class-plugin.php';
}

// Initialize the plugin
function my_currency_rates_plugin_init() {
    new MyCurrencyRates\Plugin();
}
add_action('plugins_loaded', 'my_currency_rates_plugin_init');
