<?php

namespace MyLovelyUsersTable\Table;

class Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_options_page']);
        add_action('admin_init', [$this, 'initialize_settings']);
    }

    public function add_options_page() {
        add_options_page(
            'My Lovely Users Table Settings',
            'My Lovely Users Table Settings',
            'manage_options',
            'my-lovely-users-table',
            [$this, 'render_options_page']
        );
    }

    public function render_options_page() {
        ?>
        <div class="wrap">
            <h2>My Lovely Users Table Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('my-lovely-users-table-options');
                do_settings_sections('my-lovely-users-table');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function initialize_settings() {
        register_setting(
            'my-lovely-users-table-options',
            'my_lovely_users_table_endpoint',
            [
                'type' => 'string',
                'sanitize_callback' => [$this, 'validate_endpoint'],
                'default' => 'my-lovely-users-table',
            ]
        );
    
        add_settings_section(
            'my-lovely-users-table-main',
            'Main Settings',
            null,
            'my-lovely-users-table'
        );
    
        add_settings_field(
            'my-lovely-users-table-endpoint',
            'Endpoint',
            [$this, 'endpoint_field_callback'],
            'my-lovely-users-table',
            'my-lovely-users-table-main'
        );
    }
    
    public function validate_endpoint($input) {
        // List of WordPress REST API route bases to avoid
        $reserved_routes = [
            'wp/v2', // WordPress core REST API
            'wp-json', // Common REST API prefix
            'oembed/1.0', // oEmbed routes
            'wc/v1', 'wc/v2', 'wc/v3', // WooCommerce REST API versions
            'buddypress/v1', // BuddyPress REST API
            'acf/v3', // Advanced Custom Fields REST API
            'jetpack/v4', // Jetpack REST API
            'yoast/v1', // Yoast SEO REST API
            'contact-form-7/v1', // Contact Form 7 REST API
            'wp-site-health/v1', // Site Health REST API in WP 5.6+
            // Core WordPress paths to avoid
            'wp-admin', // WordPress admin access
            'wp-login', // WordPress login page
            'wp-content', // WordPress content directory
            'wp-includes', // WordPress includes folder
            // Common WordPress pages and operations
            'posts', 'pages', // Post and Page slugs
            'categories', 'tags', // Default taxonomy bases
            'author', // Author archives
            'search', // Search pages
            'admin-ajax', // WordPress AJAX operations
            // Add more plugin-specific or custom REST API routes as needed
            // Custom post types and taxonomies should also be considered
        ]; 
        /**
         * Filters the list of reserved routes to avoid conflicts.
         *
         * This allows developers to add or remove routes from the list of reserved routes
         * that the plugin checks to prevent conflicts with WordPress API REST API endpoints
         * and other important WordPress paths.
         *
         * @since 1.0.0
         *
         * @param array $reserved_routes The original list of reserved routes.
         * @return array Modified list of reserved routes.
         */
        $reserved_routes = apply_filters('my_lovely_users_table_reserved_routes', $reserved_routes);
     
        foreach ($reserved_routes as $route) {
            if (strpos($input, $route) !== false) {
                add_settings_error(
                    'my_lovely_users_table_endpoint',
                    'endpoint-error',
                    'Do not use WordPress API REST endpoints or reserved words as custom endpoint.',
                    'error'
                );
                // Return the previous value to prevent saving the invalid input
                return get_option('my_lovely_users_table_endpoint');
            }
        }
    
        // Input is valid
        return sanitize_text_field($input);
    }
    
    public function endpoint_field_callback() {
        $endpoint = get_option('my_lovely_users_table_endpoint', 'my-lovely-users-table');
        echo "<input type='text' name='my_lovely_users_table_endpoint' value='" . esc_attr($endpoint) . "' />";
    }
}
