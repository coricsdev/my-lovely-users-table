<?php

/**
 * Plugin Name: My Lovely Users Table
 * Description: Display a table of users from an external API with details fetched asynchronously.
 * Version: 1.0
 * Author: Rico Dadiz
 */

declare(strict_types=1);

if (!defined('WPINC')) {
    die;
}

// Include Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Define plugin directory constant
define('MY_LOVELY_USERS_TABLE_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include the plugin initialization file
require_once MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'includes/plugin-init.php';
