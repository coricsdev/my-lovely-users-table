<?php

namespace MyLovelyUsersTable\API;

class Handler {
    private $cache_dir;
    const CACHE_EXPIRATION = HOUR_IN_SECONDS; // 3600; If HOUR_IN_SECONDS is not defined.

    public function __construct() {
        $this->cache_dir = MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'cache/';
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks/actions.
     */
    private function setup_hooks() {
        add_action('wp_ajax_fetch_user_details', [$this, 'fetch_user_details']);
        add_action('wp_ajax_nopriv_fetch_user_details', [$this, 'fetch_user_details']);
    }

    /**
     * Get cached data if available and fresh.
     *
     * @param string $filename Cache filename.
     * @return mixed Cached data or false if not available/fresh.
     */
    private function get_cached_data($filename) {
        $cache_file = $this->cache_dir . $filename;
        if (file_exists($cache_file) && time() - filemtime($cache_file) < self::CACHE_EXPIRATION) {
            return json_decode(file_get_contents($cache_file), true);
        }
        return false;
    }

    /**
     * Cache the given data.
     *
     * @param string $filename Cache filename.
     * @param mixed $data Data to cache.
     */
    private function set_cached_data($filename, $data) {
        $cache_file = $this->cache_dir . $filename;
        file_put_contents($cache_file, json_encode($data));
    }

    /**
     * Fetch users from the external API and cache the result.
     *
     * @return array Users array or empty array on failure.
     */
    public function fetch_users_from_api() {
        $cached_users = $this->get_cached_data('users.json');
        if ($cached_users !== false) {
            return $cached_users;
        }

        $response = wp_remote_get('https://jsonplaceholder.typicode.com/users');
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            error_log('Error fetching users: ' . wp_remote_retrieve_response_message($response));
            return [];
        }

        $users = json_decode(wp_remote_retrieve_body($response), true);
        $this->set_cached_data('users.json', $users);
        return $users;
    }

    /**
     * Fetch details for a single user by ID.
     *
     * @param int $user_id User ID.
     * @return array|null User details or null on failure.
     */
    public function get_user_details_by_id($user_id) {
        $response = wp_remote_get("https://jsonplaceholder.typicode.com/users/{$user_id}");
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            return null;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    //Handle AJAX request to fetch user details.
    public function fetch_user_details() {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if ($user_id <= 0) {
            wp_send_json_error('Invalid user ID.');
            wp_die();
        }

        $user_details = $this->get_user_details_by_id($user_id);
        if ($user_details) {
            wp_send_json_success($user_details);
        } else {
            wp_send_json_error('User not found.');
        }

        wp_die(); // End AJAX request.
    }
}
