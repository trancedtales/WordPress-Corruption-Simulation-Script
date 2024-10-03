<?php

$servername = "localhost";
$username = "u718145230_testroot	";
$password = "M5p&cHp8t";
$database = "u718145230_testdb";
$wp_path = "/";
$wp_theme = "twentytwentyfour";
$theme_functions_file = $wp_path . "/wp-content/themes/{$wp_theme}/functions.php";
$backup_functions_file = $wp_path . "/wp-content/themes/{$wp_theme}/functions-backup.php";

$wp_config_path = $wp_path . "/wp-config.php";
$wp_config_backup = $wp_path . "/wp-config-backup.php";

if (file_exists($wp_config_path)) {
    rename($wp_config_path, $wp_config_backup);
    echo "wp-config.php renamed to wp-config-backup.php\n";
} else {
    echo "wp-config.php file not found.\n";
}

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$siteurl_query = "SELECT option_value FROM wp_options WHERE option_name = 'siteurl'";
$result = $conn->query($siteurl_query);
$row = $result->fetch_assoc();
$original_siteurl = $row['option_value'];

$corrupted_siteurl = "http://invalid-url.com";
$sql = "UPDATE wp_options SET option_value='$corrupted_siteurl' WHERE option_name='siteurl' OR option_name='home'";
if ($conn->query($sql) === TRUE) {
    echo "Site URL changed to '$corrupted_siteurl'.\n";
} else {
    echo "Error updating site URL: " . $conn->error;
}

if (file_exists($theme_functions_file)) {
    copy($theme_functions_file, $backup_functions_file);
    file_put_contents($theme_functions_file, "<?php die('Temporarily corrupted'); ?>", FILE_APPEND);
    echo "functions.php file modified to corrupt theme temporarily.\n";
} else {
    echo "functions.php file not found.\n";
}
$conn->close();
?>
