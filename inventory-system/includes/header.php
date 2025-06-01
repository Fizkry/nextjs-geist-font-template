<?php
require_once 'includes/functions.php';
requireLogin();

$siteTitle = getSetting('site_title', 'Inventory Control System');
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 60px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 1rem;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-header h4 {
            opacity: 0;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .sidebar-menu span {
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-menu span {
            opacity: 0;
        }
        
        .submenu {
            background-color: rgba(0,0,0,0.1);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .submenu.show {
            max-height: 300px;
        }
        
        .submenu a {
            padding-left: 3rem;
            font-size: 0.9rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .top-navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #666;
            cursor: pointer;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-boxes fa-2x mb-2"></i>
            <h4>Inventory</h4>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="toggleSubmenu('stockSubmenu')">
                    <i class="fas fa-boxes"></i>
                    <span>Data Stock</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu" id="stockSubmenu">
                    <li><a href="stock.php?type=raw_material"><span>Raw Material</span></a></li>
                    <li><a href="stock.php?type=semi_finished"><span>Barang Setengah Jadi</span></a></li>
                    <li><a href="stock.php?type=finished_good"><span>Finish Good</span></a></li>
                    <li><a href="stock.php?type=consumable"><span>Consumable</span></a></li>
                </ul>
            </li>
            <li>
                <a href="production-planning.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Planning Produksi</span>
                </a>
            </li>
            <li>
                <a href="production-result.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Production Result</span>
                </a>
            </li>
            <?php if (hasRole('admin')): ?>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div>
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Selamat datang, <?php echo $currentUser['username']; ?></span>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="content-area">
