<?php
my_lovely_users_table_header();

// Ensure the file is being called from within WordPress
if (!defined('WPINC')) {
    exit;
}

use MyLovelyUsersTable\API\Handler;

$api_handler = new Handler();
$users = $api_handler->fetch_users_from_api();

// Define the default columns.
$columns = [
    'id' => 'ID',
    'name' => 'Name',
    'username' => 'Username',
];

// Apply a filter to allow modification of the columns
$columns = apply_filters('my_lovely_users_table_modify_columns', $columns);

if (empty($users)) {
    echo 'No users found.';
} else {
    // Action hook before the table rendering
    do_action('my_lovely_users_before_table_render');
    ?>

<div id="users-table">
    <h2>Users</h2>
    <table>
        <thead>
            <tr>
                <?php foreach ($columns as $key => $value): ?>
                    <th><?php echo esc_html($value); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <?php foreach ($columns as $key => $value): ?>
                    <td>
                    <?php 
                        // Display data based on column key. Adjust according to your data structure.
                        if (in_array($key, ['id', 'name', 'username'])) {
                            // Display ID, Name, or Username with a link
                            echo '<a href="#" class="user-link" data-id="' . esc_attr($user['id']) . '">' . esc_html($user[$key]) . '</a>';
                        } else {
                            // For other columns, just display the data
                            echo isset($user[$key]) ? esc_html($user[$key]) : ''; 
                        }
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
    // Action hook after the table rendering
    do_action('my_lovely_users_after_table_render');
}
my_lovely_users_table_footer();
?>
