# My Lovely Users Plugin

## Features

### Custom Endpoint
- **Option to change the endpoint**: Allows for customization of the REST endpoint to which the plugin interfaces.

### Action Hooks
- **Frontend Rendering Action Hooks**:
  - `my_lovely_users_before_table_render`
  - `my_lovely_users_after_table_render`

#### Example Usage

**Before Table Render Hook:**
```php
function my_custom_before_table_message() {
    echo '<div class="custom-message-above-table">';
    echo '<p>⭐ Welcome to our user directory! Here you can find information about all our members. ⭐</p>';
    echo '</div>';
}
add_action('my_lovely_users_before_table_render', 'my_custom_before_table_message');
