<?php
// This path assumes your config file is in the root directory.
require_once __DIR__.'/config.php';

if (is_logged_in()) {
    header('Location: ' . base_url('dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = 'An unexpected error occurred. Please refresh and try again.';
    } else {
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $db->prepare("INSERT INTO audit_log(actor, action, details, created_at) VALUES(?,?,?,?)")
                ->execute([$username, 'login_success', 'Successful login', date('c')]);
            header('Location: '. base_url('dashboard.php'));
            exit;
        } else {
            $error = 'Incorrect login details';
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar-ly">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - <?php echo e(setting('app_title', 'Gold System')); ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Cairo', sans-serif;
        background-color: #f4f7f6;
    }
    .btn-gold {
        background-color: #D4AF37;
        color: #fff;
        border: none;
        font-weight: bold;
    }
    .btn-gold:hover {
        background-color: #b89a30;
        color: #fff;
    }
    .login-container .form-control, .login-container .input-group-text {
        font-size: 1.1rem;
    }
    .login-container .input-group-text {
        background-color: #e9ecef;
    }

    /* ✅ Code for the slow logo animation */
    .login-logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        padding: 8px;
        background: #fff;
        object-fit: cover;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        animation: floatAnimation 5s ease-in-out infinite; /* Applying the animation */
    }
    @keyframes floatAnimation {
        0% { transform: translateY(0); }
        50% { transform: translateY(-10px); } /* Gently moves up */
        100% { transform: translateY(0); }
    }
    /* End of animation code */

</style>
</head>
<body>
<div class="d-flex justify-content-center align-items-center vw-100 vh-100">
    <div class="login-container card p-4 shadow" style="width:450px;border-top:5px solid #D4AF37;">
        <div class="text-center mb-3">
            <img src="<?php echo base_url('assets/newlogofix.PNG'); ?>" class="login-logo" alt="Logo">
            <h1 class="fs-4 fw-bold mt-3"><?php echo e(setting('app_title', 'Gold and Currency System')); ?></h1>
            <p class="text-muted">Please enter your account details</p>
        </div>
        <?php if($error): ?>
            <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label class="form-label small">Username</label>
            <div class="mb-3 input-group">
                <input type="text" name="username" class="form-control" placeholder="admin" required>
                <span class="input-group-text"><i class="bi bi-person"></i></span>
            </div>

            <label class="form-label small">Password</label>
            <div class="mb-4 input-group">
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
            </div>
            
            <button class="btn btn-gold w-100 py-2">Login</button>
        </form>
        <div class="footer text-center text-muted small mt-3">© <?php echo date('Y');?> All rights reserved</div>
    </div>
</div>
</body>
</html>