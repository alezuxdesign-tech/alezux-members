<?php
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');

function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {}
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {}
function add_shortcode($tag, $callback) {}
function register_activation_hook($file, $function) {}
function register_deactivation_hook($file, $function) {}
function plugin_dir_path($file) { return dirname($file) . '/'; }
function plugin_dir_url($file) { return 'http://example.com/wp-content/plugins/alezux-members/'; }

echo "Cargando alezux-members.php...\n";
try {
    require 'alezux-members.php';
    echo "¡Carga exitosa!\n";
} catch (Throwable $e) {
    echo "ERROR FATAL: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine() . "\n";
}
