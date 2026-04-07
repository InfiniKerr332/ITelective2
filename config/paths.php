<?php
/**
 * WMSU ARL Hub: Global Path Configuration
 */

// Project Base URL (Update this if moving to a different folder or domain)
// Dynamic Base URL Detection
$current_script = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', trim($current_script, '/'));
// If in a subfolder like /ARLsystem/... then path_parts[0] is the folder
$project_name = !empty($path_parts[0]) && str_contains(dirname($_SERVER['SCRIPT_NAME']), $path_parts[0]) ? $path_parts[0] : '';
define('BASE_URL', $project_name ? "/$project_name/" : "/");


// File System Paths
define('DIR_ROOT', dirname(__DIR__) . '/');
define('DIR_CONFIG', DIR_ROOT . 'config/');
define('DIR_INCLUDES', DIR_ROOT . 'includes/');
define('DIR_UPLOADS', DIR_ROOT . 'uploads/');
define('DIR_ACTIONS', DIR_ROOT . 'actions/');

// Helper to get consistent URLs for assets and pages
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}
?>
