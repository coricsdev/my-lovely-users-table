jQuery(document).ready(function($) {
    $('.user-link').on('click', function(e) {
        e.preventDefault();
        var userId = $(this).data('id');

        $.ajax({
            url: mlutAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'fetch_user_details', // Ensure this matches your PHP action hook.
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    $('#user-details').html(`
                        <table>
                            <h3>User Details</h3>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>${response.data.name}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${response.data.email}</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>${response.data.address.street}, ${response.data.address.suite}, ${response.data.address.city}, ${response.data.address.zipcode} | Lat: ${response.data.address.geo.lat}, Lng: ${response.data.address.geo.lng}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>${response.data.phone}</td>
                            </tr>
                            <tr>
                                <td><strong>Website:</strong></td>
                                <td><a href="http://${response.data.website}" target="_blank">${response.data.website}</a></td>
                            </tr>
                            <tr>
                                <td><strong>Company:</strong></td>
                                <td>${response.data.company.name}</td>
                            </tr>
                            <tr>
                                <td><strong>Catchphrase:</strong></td>
                                <td>${response.data.company.catchPhrase}</td>
                            </tr>
                            <tr>
                                <td><strong>Business Slogan (BS):</strong></td>
                                <td>${response.data.company.bs}</td>
                            </tr>
                        </table>
                    `);
                } else {
                    $('#user-details').html('<p>Error loading user details.</p>');
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors.
                $('#user-details').html('<p>Failed to load user details. Please check your connection and try again.</p>');
            }
        });
    });
});
