<?php

declare(strict_types=1);

namespace MyLovelyUsersTable\API;

class Handler
{
    private $cacheDir;

    public function __construct()
    {
        $this->cacheDir = MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        $this->setupHooks();
    }

    private function setupHooks(): void
    {
        add_action('wp_ajax_fetch_user_details', [$this, 'fetchUserDetails']);
        add_action('wp_ajax_nopriv_fetch_user_details', [$this, 'fetchUserDetails']);
    }

    private function getCachedData(string $filename): ?array
    {
        $cacheFile = $this->cacheDir . $filename;
        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < HOUR_IN_SECONDS) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        return null;
    }

    private function setCachedData(string $filename, array $data): void
    {
        $cacheFile = $this->cacheDir . $filename;
        file_put_contents($cacheFile, json_encode($data));
    }

    public function fetchUsersFromApi(): array
    {
        $response = wp_remote_get('https://jsonplaceholder.typicode.com/users');
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            // Log error or handle it accordingly
            return [];
        }

        $users = json_decode(wp_remote_retrieve_body($response), true);
        $this->setCachedData('users.json', $users);

        return $users;
    }

    private function getUserDetailsById(int $userId): ?array
    {
        $response = wp_remote_get("https://jsonplaceholder.typicode.com/users/{$userId}");
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }
        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function fetchUserDetails(): void
    {
        // Sanitize and cast user_id input to an integer.
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        $userId = is_numeric($userId) ? (int) $userId : null;

        if (!$userId) {
            wp_send_json_error('Invalid user ID.');
            return;
        }

        $userDetails = $this->getUserDetailsById($userId);

        if ($userDetails) {
            wp_send_json_success($userDetails);
            return;
        }

        wp_send_json_error('User not found.');
    }
}
