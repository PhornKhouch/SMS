<?php
session_start();

/**
 * Check if a user is logged in
 * @return bool Returns true if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Log in a user
 * @param int $userId The user ID to log in
 * @param string $username The username of the user
 * @param string $role The role of the user (e.g., 'admin', 'teacher', 'student')
 */
function login($userId, $username, $role) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
}

/**
 * Log out the current user
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * Get the current user's role
 * @return string|null Returns the user's role if logged in, null otherwise
 */
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}
