<?php
include("config.php");
include("lang.php");

// If already logged in, redirect
if(isset($_SESSION['username'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: librarian/dashboard.php");
    }
    exit();
}

$error = "";

// Handle login
if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username='$username' AND is_active=1";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Check if password is hashed or plain text
        $password_match = false;
        
        if (password_verify($password, $user['password_hash'])) {
            $password_match = true;
        } 
        // Fallback for plain text passwords (temporary)
        elseif ($password === $user['password_hash']) {
            $password_match = true;
            // Re-hash the plain password
            $new_hash = password_hash($password, PASSWORD_BCRYPT);
            mysqli_query($conn, "UPDATE users SET password_hash='$new_hash' WHERE username='$username'");
        }
        
        if ($password_match) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            
            // Check if using default password (123 or 123456)
            if(password_verify('123', $user['password_hash']) || 
               password_verify('123456', $user['password_hash']) || 
               $password === '123' || 
               $password === '123456') {
                $_SESSION['need_password_change'] = true;
                header("Location: change_password.php");
                exit();
            }
            
            // Redirect based on role
            if($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: librarian/dashboard.php");
            }
            exit();
        } else {
            $error = __('invalid_login');
        }
    } else {
        $error = __('invalid_login');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('login'); ?> - <?php echo __('site_title'); ?></title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, #f7f7f7 0%, #f0f0f0 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow-x: hidden;
}

.background-circles {
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: -1;
    overflow: hidden;
}

.circle {
    position: absolute;
    border-radius: 50%;
    animation: float 20s infinite ease-in-out;
}

.circle1 {
    width: 500px;
    height: 500px;
    top: -150px;
    left: -150px;
    background: radial-gradient(circle at center, #fff20008, #f7941d08, #a61d2108);
}

.circle2 {
    width: 600px;
    height: 600px;
    bottom: -200px;
    right: -150px;
    background: radial-gradient(circle at center, #a61d2105, #f7941d05, #fff20005);
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -30px) rotate(120deg); }
    66% { transform: translate(-20px, 20px) rotate(240deg); }
}

.login-wrapper {
    width: 100%;
    max-width: 450px;
    padding: 20px;
    animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-card {
    background: white;
    border-radius: 30px;
    padding: 40px 35px;
    box-shadow: 0 20px 50px rgba(166, 29, 33, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.5);
    position: relative;
    overflow: hidden;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #fff200, #f7941d, #a61d21);
    animation: gradientMove 3s infinite linear;
    background-size: 200% 200%;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.logo-area {
    text-align: center;
    margin-bottom: 30px;
}

.logo-mini {
    width: 100px;
    height: 100px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: radial-gradient(
        circle at center,
        #fff200 0%,
        #f7941d 45%,
        #a61d21 85%,
        #7e191b 100%
    );
    border: 4px solid white;
    box-shadow: 0 10px 25px rgba(166, 29, 33, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-mini img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.logo-icon {
    font-size: 50px;
    color: white;
}

.logo-area h2 {
    color: #a61d21;
    font-size: 22px;
    font-weight: 700;
}

.lang-switcher {
    position: absolute;
    top: 20px;
    right: 20px;
    display: flex;
    gap: 5px;
    background: rgba(0,0,0,0.05);
    padding: 3px;
    border-radius: 40px;
}

.lang-btn {
    padding: 6px 14px;
    border-radius: 30px;
    color: #666;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: 0.3s;
}

.lang-btn.active {
    background: #a61d21;
    color: white;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #a61d21;
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #f7941d;
}

.input-field {
    width: 100%;
    padding: 14px 20px 14px 50px;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    font-size: 16px;
    transition: 0.3s;
}

.input-field:focus {
    border-color: #fff200;
    outline: none;
    box-shadow: 0 0 0 4px rgba(255, 242, 0, 0.1);
}

.login-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #a61d21, #7e191b);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border: 2px solid #fff200;
    box-shadow: 0 10px 25px rgba(166, 29, 33, 0.3);
    margin-top: 10px;
    transition: 0.3s;
}

.login-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(255, 242, 0, 0.4);
}

.error-message {
    background: #ffebee;
    color: #c62828;
    padding: 14px 20px;
    border-radius: 50px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    border: 2px solid #ef9a9a;
    animation: shake 0.5s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.test-credentials {
    background: #fff8e1;
    padding: 15px;
    border-radius: 12px;
    margin-top: 20px;
    border-left: 4px solid #f7941d;
}

.test-credentials h4 {
    color: #f7941d;
    font-size: 14px;
    margin-bottom: 10px;
}

.test-credentials p {
    color: #666;
    font-size: 13px;
    margin-bottom: 5px;
}

.test-credentials strong {
    color: #a61d21;
}

.back-link {
    text-align: center;
    margin-top: 20px;
}

.back-link a {
    color: #666;
    text-decoration: none;
    font-size: 14px;
    transition: 0.3s;
}

.back-link a:hover {
    color: #a61d21;
}

@media (max-width: 480px) {
    .login-card { padding: 25px; }
    .logo-mini { width: 80px; height: 80px; }
    .logo-area h2 { font-size: 20px; }
    .lang-switcher { top: 10px; right: 10px; }
}
</style>
</head>
<body>

<div class="background-circles">
    <div class="circle circle1"></div>
    <div class="circle circle2"></div>
</div>

<div class="login-wrapper">
    <div class="login-card">
        
        <div class="lang-switcher">
            <a href="?lang=en" class="lang-btn <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] == 'en' ? 'active' : ''; ?>">EN</a>
            <a href="?lang=am" class="lang-btn <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] == 'am' ? 'active' : ''; ?>">አማ</a>
        </div>
        
        <div class="logo-area">
            <div class="logo-mini">
                <?php if(file_exists("image/icon.php")): ?>
                    <img src="image/icon.php" alt="<?php echo __('site_title'); ?>">
                <?php else: ?>
                    <div class="logo-icon">📚</div>
                <?php endif; ?>
            </div>
            <h2><?php echo __('welcome_back'); ?></h2>
            <p style="color: #666; margin-top: 5px;"><?php echo __('sign_in'); ?></p>
        </div>
        
        <?php if($error != ""): ?>
            <div class="error-message">
                <span>⚠️</span> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" id="loginForm">
            <div class="form-group">
                <label>
                    <span>👤</span> <?php echo __('username'); ?>
                </label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" class="input-field" placeholder="<?php echo __('username'); ?>" required autocomplete="off">
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <span>🔒</span> <?php echo __('password'); ?>
                </label>
                <div class="input-wrapper">
                    <span class="input-icon">🔐</span>
                    <input type="password" name="password" class="input-field" placeholder="<?php echo __('password'); ?>" required>
                </div>
            </div>
            
            <button type="submit" name="login" class="login-btn">
                <span>🔐</span> <?php echo __('sign_in'); ?>
            </button>
        </form>
        
        <div class="test-credentials">
            <h4>📋 <?php echo __('test_credentials'); ?></h4>
            <p>👑 Admin: <strong>Admin1</strong> / <strong>123456</strong></p>
            <p>📚 Librarian: <strong>Lib1</strong> / <strong>123456</strong></p>
        </div>
        
        <div class="back-link">
            <a href="index.php">← <?php echo __('back_to_home'); ?></a>
        </div>
        
    </div>
</div>

</body>
</html>