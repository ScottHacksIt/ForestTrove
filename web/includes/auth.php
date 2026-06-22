<?php
// ============================================================
//  Forest Trove — Authentication Helpers
// ============================================================

require_once __DIR__ . '/../config/config.php';

// Start session once
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/** Returns true if a user is logged in. */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/** Returns true if the logged-in user has the Admin role. */
function isAdmin(): bool {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}

/** Redirects to login if not logged in. */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../login.php' : '/login.php'));
        exit;
    }
}

/** Redirects to home if not an admin. */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../index.php' : '/index.php'));
        exit;
    }
}

/**
 * Saves an uploaded image file and returns the relative path, or null on failure.
 * @param array $file  Element from $_FILES
 * @param string $prefix  Filename prefix ('treasure_' or 'seeker_')
 */
function saveUploadedImage(array $file, string $prefix = 'img_'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        return null;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
    $dest = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    return 'assets/images/uploads/' . $filename;
}
