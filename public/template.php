<?php

declare(strict_types=1);

\MyLovelyUsersTable\header();

if (!defined('WPINC')) {
    exit;
}

use MyLovelyUsersTable\API\Handler;

$apiHandler = new Handler();
$users = $apiHandler->fetchUsersFromApi();

$columns = [
    'id' => 'ID',
    'name' => 'Name',
    'username' => 'Username',
];

$columns = apply_filters('my_lovely_users_table_modify_columns', $columns);

if (empty($users)) {
    echo 'No users found.';
    my_lovely_users_table_footer();
    return;
}

do_action('my_lovely_users_before_table_render');
?>

<div id="users-table">
    <h2>Users</h2>
    <table>
        <thead>
            <tr>
                <?php foreach ($columns as $key => $value) : ?>
                    <th><?php echo esc_html($value); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
            <tr>
                <?php foreach ($columns as $key => $value) : ?>
                    <td>
                    <?php
                    // Checking if the key matches the expected ones and echoing accordingly.
                    if (in_array($key, ['id', 'name', 'username'], true)) {
                        echo '<a href="#" class="user-link" data-id="'
                            . esc_attr($user['id']) . '">'
                            . esc_html($user[$key])
                            . '</a>';
                    }
                    // For keys not matching 'id', 'name', or 'username', handle differently.
                    // This handles the scenario without using else by effectively having a fallback pattern.
                    echo !in_array($key, ['id', 'name', 'username'], true)
                        ? (isset($user[$key]) ? esc_html($user[$key]) : '')
                        : '';
                    ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div id="user-details">
    <!-- User details will be loaded here -->
</div>

<?php
do_action('my_lovely_users_after_table_render');
\MyLovelyUsersTable\footer();
