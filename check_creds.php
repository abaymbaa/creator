<?php
require_once 'c:/Users/Byambaa/Local Sites/math/app/public/wp-load.php';
$option = get_option('jet_form_builder_settings__qpay');
echo "Option value: " . var_export($option, true) . "\n";
$decoded = json_decode($option, true);
echo "Decoded: " . var_export($decoded, true) . "\n";
