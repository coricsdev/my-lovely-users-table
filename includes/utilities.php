<?php

declare(strict_types=1);

namespace MyLovelyUsersTable;

function header(): void
{
    require MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'public/header.php';
}

function footer(): void
{
    require MY_LOVELY_USERS_TABLE_PLUGIN_DIR . 'public/footer.php';
}
