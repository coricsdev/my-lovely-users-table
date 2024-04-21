<?php

declare(strict_types=1);

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use MyLovelyUsersTable\API\Handler;

class HandlerTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        
        $basePluginPath = '/Users/ricodadiz/Local Sites/syde-plugin-test/app/public/wp-content/plugins/my-lovely-users-table/';
        if (!defined('MY_LOVELY_USERS_TABLE_PLUGIN_DIR')) {
            define('MY_LOVELY_USERS_TABLE_PLUGIN_DIR', $basePluginPath);
        }
    
        // Define a mock of WP_Error class
        if (!class_exists('WP_Error')) {
            \Mockery::mock('alias:WP_Error')
                ->shouldReceive('add_data')
                ->andReturn(null);
        }

        Functions\when('filter_input')->alias(function($type, $name, $filter) {
            return $type === INPUT_POST ? ($_POST[$name] ?? null) : null;
        });    
        // Mock responses for wp_remote_get based on the URL
        Functions\when('wp_remote_get')->alias(function ($url) {
            // Check if the URL is for a specific user ID
            if (preg_match('/https:\/\/jsonplaceholder.typicode.com\/users\/(\d+)/', $url, $matches)) {
                $userId = $matches[1];
                // Assuming to simulate a successful response for user ID 1
                if ($userId == '1') {
                    return [
                        'body' => json_encode(['id' => 1, 'name' => 'Leanne Graham']), // Simulate a successful API response for user 1
                        'response' => ['code' => 200]
                    ];
                } else {
                    // Simulate no user found for other IDs
                    return new \WP_Error('no_user_found', 'No user found with that ID.');
                }
            }
        
            // General fetch for all users
            if ($url === 'https://jsonplaceholder.typicode.com/users') {
                return [
                    'body' => json_encode([['id' => 1, 'name' => 'Leanne Graham']]), // Simulate API response for a list of users
                    'response' => ['code' => 200]
                ];
            }
        
            
            return new \WP_Error('http_request_failed', 'A connection error occurred.');
        });

        
        
    
        // Setup the directory existence check to always return false
        Functions\when('is_dir')->justReturn(false);
        
        // Expect mkdir to be called with specific parameters and return true
        Functions\expect('mkdir')
            ->once()
            ->with(MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'cache/', 0755, true)
            ->andReturn(true);
    
        // Expect add_action to be called with specific parameters
        Functions\expect('add_action')
            ->once()
            ->with('wp_ajax_fetch_user_details', \Mockery::type('callable'))
            ->andReturnTrue();
        Functions\expect('add_action')
            ->once()
            ->with('wp_ajax_nopriv_fetch_user_details', \Mockery::type('callable'))
            ->andReturnTrue();
    
        Functions\when('is_wp_error')->alias(function ($response) {
            return $response instanceof WP_Error;
        });
    
        Functions\when('wp_remote_retrieve_response_code')->alias(function ($response) {
            return $response['response']['code'] ?? 0;
        });
    
        Functions\when('wp_remote_retrieve_body')->alias(function ($response) {
            return $response['body'];
        });
    
        Functions\when('file_put_contents')->justReturn(true);
    }
    

    public function testConstructorCreatesCacheDirectoryAndSetsUpHooks() {
        $handler = new Handler(); // This should trigger the constructor logic
    }

    public function testFetchUserDetails() {
        // Mock the AJAX request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['user_id'] = '1'; // Mocked user ID

        // Include the file containing the AJAX action handler class
        require_once __DIR__ . '/../includes/API/Handler.php';

        // Mock the wp_send_json_success function
        Brain\Monkey\Functions\expect('wp_send_json_success')
            ->once()
            ->andReturnUsing(function ($response) {
                // Check if the response contains the expected user details
                $expected_response = '{"id":1,"name":"Leanne Graham","username":"Bret","email":"Sincere@april.biz"}';
                $result = json_encode($response);
                if (strpos($result, $expected_response) !== false) {
                    echo 'User details found in the response.';
                } else {
                    echo 'Expected user details not found in the response.';
                }
            });

        // Create an instance of the Handler class
        $handler = new \MyLovelyUsersTable\API\Handler();

        // Capture output
        ob_start();
        $handler->fetchUserDetails(); // Call the method through the instance
        $result = ob_get_clean();
    }

    
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }
}