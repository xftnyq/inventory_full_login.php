<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('config.php');

$error = '';

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    if ($username === 'admin' && $password === 'password') { 
        $_SESSION['user_id'] = 1; 
        $_SESSION['username'] = 'Admin';
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #3b82f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-body">
        <h3 class="card-title text-center mb-4"><i class="fas fa-cubes me-2"></i> System Login</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control rounded-0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control rounded-0" required>
            </div>
            <div class="d-grid">
                <button type="submit" name="login" class="btn btn-primary rounded-0">Login</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>