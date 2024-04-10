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
