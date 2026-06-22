<?php
// ============================================================
//  Forest Trove — Site Configuration
//  Copy this file to config.php and fill in your values.
//  Do NOT commit credentials to source control.
// ============================================================

// --- Database ---
define('DB_HOST',     'localhost');
define('DB_NAME',     'forest_trove');
define('DB_USER',     'root');
define('DB_PASS',     'Hungrysalami5%');
define('DB_CHARSET',  'utf8mb4');

// --- Google Maps ---
// Get a key at https://console.cloud.google.com/
define('GOOGLE_MAPS_API_KEY', 'AIzaSyCfpeM1c8bPkX9OmhSYSIimpDhcmTzqfUw');

// --- Site ---
define('SITE_URL',    'https://foresttrove.com');  // no trailing slash
define('SITE_NAME',   'Forest Trove');
define('UPLOAD_DIR',  __DIR__ . '/../assets/images/uploads/');
define('UPLOAD_URL',  SITE_URL . '/assets/images/uploads/');

// --- Session ---
define('SESSION_NAME', 'forest_trove_session');
