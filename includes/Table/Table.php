<?php


namespace MyLovelyUsersTable\Table;

use MyLovelyUsersTable\API\Handler;

class Table {
    protected $api_handler;

    public function __construct() {
        // Adjusted to use PSR-4 autoloading and namespaces
        $this->api_handler = new Handler();
        $this->register_settings();
    }

    public function run() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('init', [$this, 'add_endpoint']);
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('template_redirect', [$this, 'template_redirect']);
        add_action('update_option_my_lovely_users_table_endpoint', [$this, 'flush_rewrite_rules']);
        // Using the instantiated $api_handler object for AJAX actions
        add_action('wp_ajax_nopriv_fetch_user_details', [$this->api_handler, 'fetch_user_details']);
        add_action('wp_ajax_fetch_user_details', [$this->api_handler, 'fetch_user_details']);
    }

    public function enqueue_scripts() {
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
    
    public function add_endpoint() {
        $endpoint = get_option('my_lovely_users_table_endpoint', 'my-lovely-users-table');
        add_rewrite_rule('^' . $endpoint . '/?', 'index.php?mlut_users_table=1', 'top');
    }

    public function flush_rewrite_rules() {
        $this->add_endpoint(); // Re-register the custom endpoint.
        flush_rewrite_rules();
    }

    public function register_settings() {
        register_setting('my-lovely-users-table-options', 'my_lovely_users_table_endpoint', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field', // Basic sanitization.
            'default' => 'my-lovely-users-table'
        ]);
    }
    
    public function query_vars($vars) {
        $vars[] = 'mlut_users_table';
        return $vars;
    }

    public function load_template() {
        $template_name = 'template.php';
        
        // Define the path within the theme for overrides
        $theme_template_path = trailingslashit(get_stylesheet_directory()) . 'my-lovely-users-table/' . $template_name;
        
        if (file_exists($theme_template_path)) {
            $template = $theme_template_path;
        } else {
            $template = MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'public/' . $template_name;
        }
        
        if (file_exists($template)) {
            include $template;
        } else {
            echo '<p>Error: No template found for My Lovely Users Table.</p>';
        }
    }

    public function template_redirect() {
        $is_custom_endpoint = intval(get_query_var('mlut_users_table', 0));
        if ($is_custom_endpoint) {
            $this->load_template();
            exit;
        }
    }
}
