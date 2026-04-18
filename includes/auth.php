<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['admin_id']);
}

function get_role() {
    return $_SESSION['role'] ?? 2; // Default to Standard User
}

function require_login($min_role = 2) {
    if (!is_logged_in()) {
        $baseUrl = get_base_url();
        header("Location: $baseUrl/admin/login.php");
        exit;
    }
    if (get_role() > $min_role) {
        die("Access Denied: Insufficient permissions.");
    }
}

function login($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT id, password, role FROM admin_profile WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'] ?? 0;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    $baseUrl = get_base_url();
    header("Location: $baseUrl/admin/login.php");
    exit;
}
