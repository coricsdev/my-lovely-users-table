<?php

declare(strict_types=1);

if (!function_exists('add_action')) {
    echo 'Hi there! I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Include utility functions
require_once MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'includes/utilities.php';

use MyLovelyUsersTable\Table\Settings;
use MyLovelyUsersTable\API\Handler;
use MyLovelyUsersTable\Table\Table;

// Hook the plugin initialization
add_action('plugins_loaded', static function () {
    $apiHandler = new Handler();
    $table = new Table($apiHandler);
    new Settings();
    $table->run();
});
