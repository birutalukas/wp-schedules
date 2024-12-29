<?php
/**
 * Plugin Name: Tvarkaraščiai
 * Description: Autobusų reisų ir Šiukšlių išvežimo grafikų sistema su stotelių ir laiko paieška. Shortcode panaudojimas: <strong>Miesto autobusai: </strong>[bus_schedules type="city_routes"]. <strong>Tarpmiestiniai autobusai: </strong>[bus_schedules type="intercity_routes"]
 * Version: 1.0
 * Author: Lukas Biruta
 * Author URI: https://biruta.lt
 */


require_once plugin_dir_path(__FILE__) . 'includes/acf.php';
require_once plugin_dir_path(__FILE__) . 'includes/bus-schedule/cpt-routes.php';
require_once plugin_dir_path(__FILE__) . 'includes/bus-schedule/cpt-stops.php';
require_once plugin_dir_path(__FILE__) . 'includes/bus-schedule/alpine-data.php';
require_once plugin_dir_path(__FILE__) . 'includes/bus-schedule/shortcode.php';


// Enqueue scripts & styles.
function bs_enqueue_scripts() {

    // Styles
    wp_enqueue_style('leaflet-style', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_style('schedule-style', plugin_dir_url(__FILE__) . 'assets/styles/style.css');

    // Scripts
    wp_enqueue_script('flatpckr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, false);
    wp_enqueue_script('flatpckr-lt', 'https://npmcdn.com/flatpickr/dist/l10n/lt.js', [], null, false);
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true);
    wp_enqueue_script('bs-script', plugin_dir_url(__FILE__) . 'assets/scripts/script.js', ['jquery', 'flatpckr', 'flatpckr-lt', 'leaflet'], null, true);
    wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.14.7/dist/cdn.min.js', ['bs-script'], null, true);

    wp_localize_script('bs-script', 'autobusaiAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);

}
add_action('wp_enqueue_scripts', 'bs_enqueue_scripts');

function bs_enqueue_admin_scripts() {
    wp_enqueue_style('schedule-style', plugin_dir_url(__FILE__) . 'assets/styles/admin.css');
}
add_action('admin_enqueue_scripts', 'bs_enqueue_admin_scripts');
