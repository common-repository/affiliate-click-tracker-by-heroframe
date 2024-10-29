<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// create the map between storage keys and heroframe elements
$heroframe_keys_map = [
    'public_key' => 'heroframe_public_key',
    'private_key' => 'heroframe_private_key',
    'activation_status' => 'heroframe_activation_status'
];

$heroframe_keys_form_map = [
    'public_key' => 'heroframe_public_key',
    'private_key' => 'heroframe_private_key'
];

$heroframe_defaults = [
    'public_key' => '',
    'private_key' => '',
    'activation_status' => '<span class="heroframeinactive">Inactive</span>'
];
