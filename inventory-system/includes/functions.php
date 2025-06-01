<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Get user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get setting value
function getSetting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['setting_value'] : $default;
}

// Update setting
function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

// Get stock data for dashboard
function getStockData() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT c.name as category, c.type,
               COUNT(i.id) as item_count,
               SUM(i.current_stock) as total_stock,
               SUM(i.min_stock) as total_min,
               SUM(i.max_stock) as total_max
        FROM categories c 
        LEFT JOIN items i ON c.id = i.category_id 
        GROUP BY c.id, c.name, c.type
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get items by category
function getItemsByCategory($type) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT i.*, c.name as category_name 
        FROM items i 
        JOIN categories c ON i.category_id = c.id 
        WHERE c.type = ?
        ORDER BY i.name
    ");
    $stmt->execute([$type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Format number for display
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

// Get current month production plans
function getProductionPlans($month = null, $year = null) {
    global $pdo;
    if (!$month) $month = date('m');
    if (!$year) $year = date('Y');
    
    $stmt = $pdo->prepare("
        SELECT pp.*, i.name as item_name, i.unit
        FROM production_plans pp
        JOIN items i ON pp.item_id = i.id
        WHERE MONTH(pp.plan_date) = ? AND YEAR(pp.plan_date) = ?
        ORDER BY pp.plan_date, i.name
    ");
    $stmt->execute([$month, $year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
