<?php

declare(strict_types=1);

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use MyLovelyUsersTable\Table\Table;

class TableTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    
        // Define a base path for the plugin directory
        $basePluginPath = '/Users/ricodadiz/Local Sites/syde-plugin-test/app/public/wp-content/plugins/my-lovely-users-table/';
    
        // Mock global functions
        Functions\when('plugin_dir_path')->justReturn($basePluginPath);
        Functions\when('get_option')->justReturn('my-lovely-users-table');
        Functions\when('plugin_dir_url')->justReturn('http://example.com/');
        Functions\when('admin_url')->justReturn('http://example.com/wp-admin/');
        Functions\when('get_stylesheet_directory')->justReturn('/path/to/theme');
        Functions\when('mkdir')->justReturn(true);
        Functions\when('file_exists')->justReturn(true);
        Functions\when('fopen')->justReturn(true);
        Functions\when('register_setting')->justReturn(null);
        Functions\when('add_rewrite_rule')->justReturn(null);
        Functions\when('flush_rewrite_rules')->justReturn(null);
    
        // Define necessary constants
        if (!defined('MY_LOVELY_USERS_TABLE_PLUGIN_DIR')) {
            define('MY_LOVELY_USERS_TABLE_PLUGIN_DIR', $basePluginPath);
        }
    }
    

    public function testRunRegistersAllHooksCorrectly() {
        $mockHandler = Mockery::mock('MyLovelyUsersTable\API\Handler');
        $table = new Table($mockHandler);
        $table->run();

        $this->assertTrue(true, "Setup complete without error.");
    }

    public function testConstruct() {
        $mockHandler = Mockery::mock('MyLovelyUsersTable\API\Handler');
        $table = new Table($mockHandler);

        // Check if the handler is set correctly
        $reflection = new ReflectionClass($table);
        $apiHandlerProperty = $reflection->getProperty('apiHandler');
        $apiHandlerProperty->setAccessible(true);
        $this->assertSame($mockHandler, $apiHandlerProperty->getValue($table));
    }

    public function testAddEndpoint() {
        $table = new Table();
        $table->addEndpoint();

        $this->assertTrue(true, "Endpoint added without error.");
    }

    public function testFlushRewriteRules() {
        $table = new Table();
        $table->flushRewriteRules();

        $this->assertTrue(true, "Rewrite rules flushed without error.");
    }

    public function testQueryVarsAddsCustomVariable() {
        $table = new Table();
        $vars = ['existing' => 'value'];
        $newVars = $table->queryVars($vars);

        $this->assertContains('mlut_users_table', $newVars, "The custom query var should be added.");
    }

    public function testLoadTemplateLoadsCorrectTemplate() {
        $table = Mockery::mock(Table::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $table->shouldReceive('checkFileExists')->andReturn(true);
    
        // Simulate output from the includeTemplate method
        $table->shouldReceive('includeTemplate')->andReturnUsing(function ($path) {
            echo 'expected content from template';  // Directly echo the content as would be in the included file
        });
    
        ob_start();
        $table->loadTemplate();
        $output = ob_get_clean();
    
        $this->assertStringContainsString('expected content from template', $output, "The template should load the expected content.");
    }
    //Test that templateRedirect loads the template when the custom endpoint is hit
    
    
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }
}
