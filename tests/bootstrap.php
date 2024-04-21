<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("The autoloader expected at {$autoloadPath} does not exist.");
}
require_once $autoloadPath;

// Initialize Brain\Monkey for WordPress function mocking
use Brain\Monkey;
Monkey\setUp();

register_shutdown_function(function() {
    // Ensure that Brain\Monkey is torn down after tests
    Monkey\tearDown();
});
