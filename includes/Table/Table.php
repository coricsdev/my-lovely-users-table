<?php

declare(strict_types=1);

namespace MyLovelyUsersTable\Table;

use MyLovelyUsersTable\API\Handler;

class Table
{
    protected $apiHandler;
    protected $templatePath;

    public function __construct(Handler $apiHandler = null, $templatePath = null)
    {
        $this->apiHandler = $apiHandler ?: new Handler();
        $this->templatePath = $templatePath ?: plugin_dir_path(dirname(__DIR__)) . 'public/';
        $this->registerSettings();
    }

    public function run(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('init', [$this, 'addEndpoint']);
        add_filter('query_vars', [$this, 'queryVars']);
        add_action('template_redirect', [$this, 'templateRedirect']);
        add_action('update_option_my_lovely_users_table_endpoint', [$this, 'flushRewriteRules']);

        add_action('wp_ajax_nopriv_fetch_user_details', [$this->apiHandler, 'fetchUserDetails']);
        add_action('wp_ajax_fetch_user_details', [$this->apiHandler, 'fetchUserDetails']);
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'mlut-ajax-script',
            plugin_dir_url(dirname(__DIR__, 1)) . 'js/script.js',
            ['jquery'],
            '1.0',
            true
        );
        wp_localize_script(
            'mlut-ajax-script',
            'mlutAjax',
            ['ajaxurl' => admin_url('admin-ajax.php')]
        );
    }

    public function addEndpoint(): void
    {
        $endpoint = get_option('my_lovely_users_table_endpoint', 'my-lovely-users-table');
        add_rewrite_rule('^' . $endpoint . '/?', 'index.php?mlut_users_table=1', 'top');
    }

    public function flushRewriteRules(): void
    {
        $this->addEndpoint();
        flush_rewrite_rules();
    }

    public function registerSettings(): void
    {
        register_setting(
            'my-lovely-users-table-options',
            'my_lovely_users_table_endpoint',
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'my-lovely-users-table',
            ]
        );
    }

    public function queryVars(array $vars): array
    {
        $vars[] = 'mlut_users_table';
        return $vars;
    }

    public function loadTemplate(): void
    {

        $templateName = 'template.php';
        $fullTemplatePath = $this->templatePath . $templateName;

        if ($this->checkFileExists($fullTemplatePath)) {
            $this->includeTemplate($fullTemplatePath);
            return;
        }

        echo '<p>Error: No template found for My Lovely Users Table.</p>';
    }

    // Add methods to abstract file system checks and allows easier mocking
    protected function checkFileExists($path)
    {

        return file_exists($path);
    }

    protected function includeTemplate($path)
    {

        include $path;
    }

    public function templateRedirect(): void
    {
        $isCustomEndpoint = intval(get_query_var('mlut_users_table', 0));
        if ($isCustomEndpoint) {
            $this->loadTemplate();
            exit;
        }
    }
}
