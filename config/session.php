<?php
// config/session.php
session_start();

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function requireHeadAdmin() {
    requireLogin();
    if ($_SESSION['role'] != 'head_admin') {
        header("Location: ../resident/dashboard.php");
        exit();
    }
}

function requireSecondaryAdmin() {
    requireLogin();
    if ($_SESSION['role'] != 'secondary_admin') {
        header("Location: ../resident/dashboard.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!in_array($_SESSION['role'], ['head_admin', 'secondary_admin'])) {
        header("Location: ../resident/dashboard.php");
        exit();
    }
}

function isHeadAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'head_admin';
}

function isSecondaryAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'secondary_admin';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>