<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #1f2937;
            --sidebar-color: #d1d5db;
            --sidebar-active-bg: #3b82f6;
            --main-bg: #e5e7eb;
            --card-bg: white;
            --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        body {
            background-color: var(--main-bg);
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-color);
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .sidebar .logo {
            padding: 0 20px 30px;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .sidebar-nav .nav-link {
            color: var(--sidebar-color);
            padding: 12px 20px;
            margin: 5px 0;
            display: flex;
            align-items: center;
            border-radius: 0;
            transition: all 0.2s;
        }
        .sidebar-nav .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .sidebar-nav .nav-link:hover {
            background-color: #374151;
            color: white;
        }
        .sidebar-nav .nav-item.active .nav-link {
            background-color: var(--sidebar-active-bg);
            color: white;
            font-weight: bold;
            border-radius: 0 5px 5px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 0;
        }
        .top-header {
            background-color: white;
            padding: 15px 30px;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: var(--shadow-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .top-header .search-box {
            width: 300px;
        }
        .top-header .user-info {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ccc;
            margin-right: 10px;
            object-fit: cover;
        }
        .card {
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            border: none;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .btn-primary-custom {
            background-color: var(--sidebar-active-bg);
            color: white;
            border-color: var(--sidebar-active-bg);
        }
        .btn-primary-custom:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .btn-secondary-custom {
            background-color: #6b7280;
            color: white;
            border-color: #6b7280;
        }
        .btn-info, .btn-danger, .btn-primary {
             border-radius: 4px;
        }
        .text-primary-custom {
            color: var(--sidebar-active-bg) !important;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <i class="fas fa-cube me-2" style="color: #3b82f6;"></i> Inventory
    </div>
    
    <ul class="nav flex-column sidebar-nav">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

        <li class="nav-item <?php echo ($current_page == 'index.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'product.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="product.php"><i class="fas fa-box"></i> Product</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'purchase.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="purchase.php"><i class="fas fa-shopping-cart"></i> Purchase</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'order.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="order.php"><i class="fas fa-truck-loading"></i> Order</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'customer.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="customer.php"><i class="fas fa-users"></i> Customer</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'supplier.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="supplier.php"><i class="fas fa-truck"></i> Supplier</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'category.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="category.php"><i class="fas fa-folder"></i> Category</a>
        </li>
        <li class="nav-item <?php echo ($current_page == 'brand.php' ? 'active' : ''); ?>">
            <a class="nav-link" href="brand.php"><i class="fas fa-tags"></i> Brand</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fas fa-chart-line"></i> Report</a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h5 class="mb-0">
                <?php 
                $page_title = ucfirst(str_replace('.php', '', $current_page));
                echo ($page_title == 'Index' ? 'Dashboard' : str_replace('_', ' ', $page_title));
                ?>
            </h5>
            <small class="text-muted">Welcome back, manager.</small>
        </div>
        
        <div class="search-box">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search..." aria-label="Search">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
        </div>

        <div class="user-info dropdown">
            <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar bg-secondary d-flex justify-content-center align-items-center text-white">
                    <i class="fas fa-user"></i>
                </div>
                <div class="text-end">
                    <p class="mb-0 fw-bold">Hello, Admin</p>
                    <small class="text-muted">Administrator</small>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end rounded-0">
                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
    
    <div class="container-fluid p-4">