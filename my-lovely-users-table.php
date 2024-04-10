<?php
/**
 * Plugin Name: My Lovely Users Table
 * Description: Display a table of users from an external API with details fetched asynchronously.
 * Version: 1.0
 * Author: Rico Dadiz
 */

if (!defined('WPINC')) {
    die;
}

// Include Composer's autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Define plugin directory constant for easy access to file paths
define('MY_LOVELY_USERS_TABLE_PLUGIN_DIR', plugin_dir_path(__FILE__));

use MyLovelyUsersTable\Table\Settings as MyLovelyUsersTableSettings;
use MyLovelyUsersTable\Table\Table as MyLovelyUsersTable;

// Instantiate the settings class to set up the admin settings page.
new MyLovelyUsersTableSettings();

function run_my_lovely_users_table() {
    $plugin = new MyLovelyUsersTable();
    $plugin->run();
}
run_my_lovely_users_table();

function my_lovely_users_table_header() {
    require plugin_dir_path(__FILE__) . 'public/header.php';
}

function my_lovely_users_table_footer() {
    require plugin_dir_path(__FILE__) . 'public/footer.php';
}
