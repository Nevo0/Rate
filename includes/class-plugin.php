<?php
namespace MyCurrencyRates;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Plugin {

    public function __construct() {
        // Enqueue scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Register AJAX handler
        add_action('wp_ajax_my_currency_rates_action', [$this, 'ajax_handler']);
    }

    /**
     * Enqueue scripts and styles.
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_scripts($hook_suffix) {
        // Only enqueue on the post_tag taxonomy page
        if ($hook_suffix === 'edit-tags.php' && isset($_GET['taxonomy']) && sanitize_text_field($_GET['taxonomy']) === 'post_tag') {

            // Enqueue wp-element (includes React and ReactDOM)
            wp_enqueue_script('wp-element');

            // Enqueue custom React app
            wp_enqueue_script(
                'my-currency-rates-app',
                MY_CURRENCY_RATES_PLUGIN_URL . 'assets/js/my-currency-rates-app.js',
                ['wp-element'],
                '1.0.0',
                true
            );

            // Localize script to pass data from PHP to JS
            wp_localize_script('my-currency-rates-app', 'myCurrencyRates', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('my_currency_rates_nonce'),
            ]);
        }
    }

    /**
     * Handle AJAX requests.
     */
    public function ajax_handler() {
        // Check nonce for security
        check_ajax_referer('my_currency_rates_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized', 403);
        }

        // Fetch currency rates
        $currency_rates = $this->get_currency_rates();

        if (empty($currency_rates)) {
            wp_send_json_error('Error fetching currency rates');
        } else {
            wp_send_json_success($currency_rates);
        }
    }

    /**
     * Fetch currency rates from the API or cache.
     *
     * @return array
     */
    private function get_currency_rates() {
        // Check if transient exists
        $currency_rates = get_transient('my_currency_rates');

        if (!$currency_rates) {
            $api_urls = [
                'usd'  => 'https://api.nbp.pl/api/exchangerates/rates/a/usd/',
                'eur'  => 'https://api.nbp.pl/api/exchangerates/rates/a/eur/',
                'gold' => 'https://api.nbp.pl/api/cenyzlota',
            ];

            $currency_rates = [];

            // Fetch USD rate
            $usd_response = wp_remote_get($api_urls['usd']);
            if (!is_wp_error($usd_response) && wp_remote_retrieve_response_code($usd_response) === 200) {
                $usd_data = json_decode(wp_remote_retrieve_body($usd_response), true);
                $currency_rates['usd'] = $usd_data['rates'][0]['mid'];
            } else {
                $currency_rates['usd'] = 'Error fetching USD rate';
            }

            // Fetch EUR rate
            $eur_response = wp_remote_get($api_urls['eur']);
            if (!is_wp_error($eur_response) && wp_remote_retrieve_response_code($eur_response) === 200) {
                $eur_data = json_decode(wp_remote_retrieve_body($eur_response), true);
                $currency_rates['eur'] = $eur_data['rates'][0]['mid'];
            } else {
                $currency_rates['eur'] = 'Error fetching EUR rate';
            }

            // Fetch Gold price
            $gold_response = wp_remote_get($api_urls['gold']);
            if (!is_wp_error($gold_response) && wp_remote_retrieve_response_code($gold_response) === 200) {
                $gold_data = json_decode(wp_remote_retrieve_body($gold_response), true);
                $currency_rates['gold'] = $gold_data[0]['cena'];
            } else {
                $currency_rates['gold'] = 'Error fetching Gold rate';
            }

            // PLN is always 1
            $currency_rates['pln'] = 1;

            // Set transient for caching (1 hour)
            set_transient('my_currency_rates', $currency_rates, HOUR_IN_SECONDS);
        }

        return $currency_rates;
    }
}
