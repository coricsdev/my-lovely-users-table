<?php

declare(strict_types=1);

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use MyLovelyUsersTable\Table\Settings;
class SettingsTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    
        // Mock WordPress functions used by your settings
        Functions\when('add_options_page')->justReturn(true);
        Functions\when('register_setting')->justReturn(true);
        Functions\when('add_settings_section')->justReturn(true);
        Functions\when('add_settings_field')->justReturn(true);
        Functions\when('settings_fields')->justReturn('');
        Functions\when('do_settings_sections')->justReturn('');
    
        // Mock submit_button to output a specific HTML snippet for verification
        Functions\when('submit_button')->justEcho('<input type="submit" value="Save Changes">');
        Functions\when('sanitize_text_field')->alias(function($input) {
            return trim($input);
        });
        Functions\when('apply_filters')->alias(function ($tag, $value) {
            return $value;
        });
    
        // Adjusting get_option to return different values based on input
        Functions\when('get_option')->alias(function ($option_name, $default = null) {
            switch ($option_name) {
                case 'my_lovely_users_table_endpoint':
                    // Return 'default-endpoint' when testing validateEndpoint or other specific tests
                    return 'default-endpoint';
                default:
                    return $default; // Return a default or null if not specifically mocked
            }
        });
    
        Functions\when('esc_attr')->alias(function ($input) {
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        });
    }

    public function testConstructorAddsCorrectActions() {
        // Expect that add_action is called correctly for 'admin_menu'
        Functions\expect('add_action')
            ->once()
            ->with('admin_menu', \Mockery::type('array'))  // Assuming the action callback is an array
            ->andReturnTrue();

        // Expect that add_action is called correctly for 'admin_init'
        Functions\expect('add_action')
            ->once()
            ->with('admin_init', \Mockery::type('array'))  // Assuming the action callback is an array
            ->andReturnTrue();

        // Instantiate Settings to trigger the constructor
        $settings = new Settings();
    }

    public function testAddOptionsPageRegistersPage() {
        Functions\expect('add_options_page')->zeroOrMoreTimes()->andReturnUsing(function () {
            error_log('add_options_page called with arguments: ' . print_r(func_get_args(), true));
            return true;
        });
    
        $settings = new Settings();
        $settings->addOptionsPage();
    }

    public function testRenderOptionsPageOutputsCorrectHTML() {
        $settings = new Settings();
        
        ob_start();
        $settings->renderOptionsPage();
        $output = ob_get_clean();
        
        // Assertions to check if the output contains expected HTML tags
        $this->assertStringContainsString('<form method="post" action="options.php">', $output);
        $this->assertStringContainsString('My Lovely Users Table Settings', $output);
        $this->assertStringContainsString('<input type="submit" value="Save Changes">', $output);
    }

    public function testInitializeSettingsRegistersAllComponentsCorrectly() {
        $settings = new Settings();
    
        // Resetting and setting up Brain\Monkey environment
        Monkey\tearDown();
        Monkey\setUp();
    
        // Mock WordPress functions
        Functions\expect('register_setting')
            ->once()
            ->withArgs(function ($option_name, $option_group, $args) {
                return $option_name === 'my-lovely-users-table-options' &&
                       $option_group === 'my_lovely_users_table_endpoint' &&
                       is_array($args) &&
                       isset($args['sanitize_callback']) &&
                       is_callable($args['sanitize_callback']);
            })
            ->andReturn(true);
    
        Functions\expect('add_settings_section')
            ->once()
            ->with('my-lovely-users-table-main', 'Main Settings', null, 'my-lovely-users-table')
            ->andReturn(true);
    
        Functions\expect('add_settings_field')
            ->once()
            ->andReturn(true);
    
        // Execute the method under test
        $settings->initializeSettings();
    }

    public function testValidateEndpointWithReservedRoute() {
        $settings = new Settings();

        // Expect add_settings_error to be called when a reserved route is in the input
        Functions\expect('add_settings_error')
            ->once()
            ->with('my_lovely_users_table_endpoint', 'endpoint-error', 'Do not use WordPress API REST endpoints or reserved words as custom endpoint.', 'error');

        $result = $settings->validateEndpoint('wp-admin');
        $this->assertSame('default-endpoint', $result, 'Should return the default endpoint when a reserved route is used.');
    }

    public function testEndpointFieldCallbackOutputsCorrectHTML() {
        $settings = new Settings();
    
        ob_start();
        $settings->endpointFieldCallback();
        $output = ob_get_clean();
    
        $expectedHtml = "<input type='text' name='my_lovely_users_table_endpoint' value='default-endpoint' />";
        $this->assertStringContainsString($expectedHtml, $output, "The output HTML should contain the correct input field and value.");
    }
        
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }
}
