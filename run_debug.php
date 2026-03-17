<?php
require_once 'c:/Users/Byambaa/Local Sites/math/app/public/wp-load.php';
$active_plugins = get_option('active_plugins');
$options = get_option('jet_form_builder_settings__qpay');
$decoded = json_decode($options, true);

$output = "Active Plugins:\n" . print_r($active_plugins, true) . "\n\n";
$output .= "QPay Settings (Jet_FB):\n" . var_export($options, true) . "\n\n";
$output .= "Decoded Settings:\n" . print_r($decoded, true) . "\n\n";

file_put_contents('c:/Users/Byambaa/Local Sites/math/app/public/wp-content/debug_qpay.txt', $output);
echo "Debug data written to wp-content/debug_qpay.txt";
