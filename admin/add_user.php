<?php
include("../config.php");
include("../lang.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth_login.php");
    exit();
}

$message = "";
$message_type = "";

// Get all users for display
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// Add User
if(isset($_POST['add'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Hash the default password
    $default_password = "123";
    $hashed_password = password_hash($default_password, PASSWORD_BCRYPT);
    
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "INSERT INTO users (username, password_hash, role) VALUES ('$username', '$hashed_password', '$role')");
        $message = "✅ " . __('user_added') . " " . __('default_password') . ": <strong>123</strong>";
        $message_type = "success";
        $users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
    } else {
        $message = "⚠️ " . __('username_exists');
        $message_type = "error";
    }
}

// Remove User
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $current_user = $_SESSION['username'];
    $user_to_delete = mysqli_query($conn, "SELECT username FROM users WHERE id=$id");
    $user_data = mysqli_fetch_assoc($user_to_delete);
    
    if($user_data['username'] != $current_user) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        $message = "✅ " . __('user_removed');
        $message_type = "success";
        $users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
    } else {
        $message = "⚠️ " . __('cannot_delete_self');
        $message_type = "error";
    }
}

// Reset Password
if(isset($_POST['reset'])){
    $id = intval($_POST['user_id']);
    $user_to_reset = mysqli_query($conn, "SELECT username FROM users WHERE id=$id");
    $user_data = mysqli_fetch_assoc($user_to_reset);
    
    // Hash the reset password
    $reset_password = "123";
    $hashed_password = password_hash($reset_password, PASSWORD_BCRYPT);
    
    mysqli_query($conn, "UPDATE users SET password_hash='$hashed_password' WHERE id=$id");
    $message = "✅ " . __('password_reset') . " " . $user_data['username'];
    $message_type = "success";
    $users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
}

// Check if user has default password (123)
function hasDefaultPassword($password_hash) {
    return password_verify("123", $password_hash) || password_verify("123456", $password_hash);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('user_management'); ?> - <?php echo __('site_title'); ?></title>
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
  position: relative;
  overflow-x: hidden;
}

.logo-background {
  position: fixed;
  width: 600px;
  height: 600px;
  border-radius: 50%;
  background: radial-gradient(
    circle at center,
    #fff200 0%,
    #f7941d 35%,
    #a61d21 70%,
    #7e191b 100%
  );
  border: 8px solid rgba(255, 255, 255, 0.9);
  box-shadow: 0 0 50px rgba(166, 29, 33, 0.3);
  opacity: 0.15;
  z-index: -1;
}

.logo-1 { top: -200px; left: -200px; }
.logo-2 { bottom: -250px; right: -150px; width: 500px; height: 500px; }
.logo-3 { top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 800px; opacity: 0.08; }

header {
  background: linear-gradient(135deg, rgba(166, 29, 33, 0.95) 0%, rgba(126, 25, 27, 0.95) 100%);
  color: white;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  backdrop-filter: blur(10px);
  border-bottom: 3px solid #fff200;
  box-shadow: 0 5px 15px rgba(166, 29, 33, 0.2);
  position: sticky;
  top: 0;
  z-index: 100;
}

.logo-area {
  display: flex;
  align-items: center;
  gap: 12px;
}

header img {
  height: 40px;
  width: auto;
}

header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  background: linear-gradient(135deg, #fff200, #ffffff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.lang-switcher {
  display: flex;
  gap: 5px;
  background: rgba(255,255,255,0.1);
  padding: 3px;
  border-radius: 40px;
  margin-right: 15px;
}

.lang-btn {
  padding: 6px 14px;
  border-radius: 30px;
  color: white;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: 0.3s;
}

.lang-btn.active {
  background: #fff200;
  color: #a61d21;
}

.lang-btn:hover:not(.active) {
  background: rgba(255,255,255,0.2);
}

.logout-btn a {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 242, 0, 0.2);
  color: white;
  text-decoration: none;
  padding: 8px 18px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
  border: 2px solid #fff200;
  transition: 0.3s;
}

.logout-btn a:hover {
  background: #fff200;
  color: #a61d21;
}

.container {
  max-width: 1200px;
  margin: 20px auto;
  padding: 15px;
  animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.main-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 25px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 15px 35px rgba(166, 29, 33, 0.1);
  position: relative;
  overflow: hidden;
}

.main-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #fff200, #f7941d, #a61d21);
  animation: gradientMove 3s infinite linear;
  background-size: 200% 200%;
}

@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.main-card h2 {
  color: #a61d21;
  font-size: 24px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 2px solid #f0f0f0;
}

.main-card h2 span {
  background: #fff200;
  color: #a61d21;
  padding: 5px 15px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
}

.user-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 25px;
}

@media (max-width: 992px) {
  .user-grid {
    grid-template-columns: 1fr;
  }
}

.add-section {
  background: white;
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.add-section h3 {
  color: #f7941d;
  font-size: 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding-bottom: 10px;
  border-bottom: 2px solid #fff200;
}

.list-section {
  background: white;
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.list-section h3 {
  color: #a61d21;
  font-size: 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding-bottom: 10px;
  border-bottom: 2px solid #a61d21;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 700;
  color: #a61d21;
  margin-bottom: 8px;
}

.form-group label span {
  margin-right: 8px;
  color: #f7941d;
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 15px;
  transition: 0.3s;
}

.form-control:focus {
  border-color: #f7941d;
  outline: none;
  box-shadow: 0 0 0 3px rgba(247, 148, 29, 0.1);
}

select.form-control {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23a61d21'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 18px;
  padding-right: 45px;
}

.add-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #f7941d, #e07e1a);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 2px solid #fff200;
  transition: 0.3s;
}

.add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(247, 148, 29, 0.3);
}

.users-list {
  max-height: 450px;
  overflow-y: auto;
}

.user-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  transition: 0.3s;
}

.user-item:hover {
  background: rgba(255, 242, 0, 0.05);
  transform: translateX(5px);
  border-radius: 10px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #a61d21, #7e191b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
  font-weight: 700;
  border: 2px solid #fff200;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 15px;
  font-weight: 700;
  color: #333;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-role {
  font-size: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 600;
  display: inline-block;
  margin-top: 5px;
}

.role-admin {
  background: #a61d2120;
  color: #a61d21;
  border: 1px solid #a61d21;
}

.role-librarian {
  background: #f7941d20;
  color: #f7941d;
  border: 1px solid #f7941d;
}

.default-password {
  font-size: 11px;
  color: #28a745;
  background: #d4edda;
  padding: 2px 10px;
  border-radius: 12px;
}

.current-user {
  background: #fff20030;
  padding: 2px 10px;
  border-radius: 12px;
  color: #a61d21;
  font-size: 11px;
  font-weight: 700;
}

.action-buttons {
  display: flex;
  gap: 8px;
  align-items: center;
}

.reset-btn {
  padding: 6px 14px;
  background: linear-gradient(135deg, #f7941d, #e07e1a);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: 0.3s;
}

.reset-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(247, 148, 29, 0.3);
}

.delete-btn {
  padding: 6px 14px;
  background: #ff4444;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: 0.3s;
}

.delete-btn:hover {
  background: #cc0000;
  transform: translateY(-2px);
}

.delete-btn.disabled {
  background: #ccc;
  pointer-events: none;
}

.alert {
  padding: 14px 18px;
  border-radius: 12px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 15px;
  animation: slideDown 0.5s;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 2px solid #28a745;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 2px solid #dc3545;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #666;
  color: white;
  text-decoration: none;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
  margin-top: 20px;
  transition: 0.3s;
  border: 2px solid #fff200;
}

.back-btn:hover {
  background: #555;
  transform: translateY(-2px);
}

.stats-badge {
  display: inline-block;
  padding: 6px 16px;
  background: #f7941d;
  color: white;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 600;
  margin-left: 12px;
}

.info-box {
  background: #f7941d20;
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 20px;
  border-left: 4px solid #f7941d;
}

.info-box-title {
  color: #f7941d;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.empty-state span {
  font-size: 48px;
  display: block;
  margin-bottom: 15px;
  opacity: 0.5;
}

.security-note {
  background: #fff3cd;
  color: #856404;
  padding: 10px;
  border-radius: 8px;
  margin-top: 10px;
  font-size: 12px;
  border: 1px solid #ffeeba;
}

@media (max-width: 768px) {
  header {
    flex-direction: column;
    gap: 12px;
  }
  
  .logo-area {
    flex-direction: column;
    text-align: center;
  }
  
  .lang-switcher {
    margin-right: 0;
    margin-bottom: 10px;
  }
  
  .user-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .user-info {
    width: 100%;
  }
  
  .action-buttons {
    width: 100%;
  }
  
  .reset-btn, .delete-btn {
    flex: 1;
    justify-content: center;
  }
}
</style>
</head>
<body>

<div class="logo-background logo-1"></div>
<div class="logo-background logo-2"></div>
<div class="logo-background logo-3"></div>

<header>
  <div class="logo-area">
    <img src="../image/icon.php" alt="<?php echo __('site_title'); ?>">
    <h2><?php echo __('user_management'); ?></h2>
  </div>
  <div style="display: flex; align-items: center;">
    <div class="lang-switcher">
      <a href="?lang=en" class="lang-btn <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] == 'en' ? 'active' : ''; ?>">EN</a>
      <a href="?lang=am" class="lang-btn <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] == 'am' ? 'active' : ''; ?>">አማ</a>
    </div>
    <div class="logout-btn">
      <a href="../auth_logout.php">
        <span>🚪</span> <?php echo __('logout'); ?>
      </a>
    </div>
  </div>
</header>

<div class="container">
  <div class="main-card">
    <h2>
      👥 <?php echo __('user_management'); ?>
      <span class="stats-badge">
        <?php echo mysqli_num_rows($users); ?> <?php echo __('total'); ?>
      </span>
    </h2>
    
    <?php if($message != ""): ?>
      <div class="alert alert-<?php echo $message_type; ?>">
        <span><?php echo $message_type == 'success' ? '✅' : '⚠️'; ?></span>
        <?php echo $message; ?>
      </div>
    <?php endif; ?>
    
    <div class="user-grid">
      <div class="add-section">
        <h3>
          <span>➕</span> <?php echo __('add_user'); ?>
        </h3>
        <form method="post">
          <div class="form-group">
            <label class="required">
              <span>👤</span> <?php echo __('username'); ?>
            </label>
            <input type="text" name="username" class="form-control" placeholder="<?php echo __('username'); ?>" required autocomplete="off">
          </div>
          
          <div class="form-group">
            <label class="required">
              <span>⚡</span> <?php echo __('role'); ?>
            </label>
            <select name="role" class="form-control" required>
              <option value="" disabled selected>-- <?php echo __('select_role'); ?> --</option>
              <option value="admin">👑 <?php echo __('admin'); ?></option>
              <option value="librarian">📚 <?php echo __('librarian'); ?></option>
            </select>
          </div>
          
          <div class="info-box">
            <div class="info-box-title">
              <span>ℹ️</span> <?php echo __('default_password'); ?>
            </div>
            <div class="info-box-text">
              <?php echo __('default_password_message'); ?>: <strong>123</strong>
            </div>
          </div>
          
          <div class="security-note">
            🔒 <?php echo __('password_hashed_notice'); ?>
          </div>
          
          <button type="submit" name="add" class="add-btn">
            <span>➕</span> <?php echo __('add_user'); ?>
          </button>
        </form>
      </div>
      
      <div class="list-section">
        <h3>
          <span>📋</span> <?php echo __('user_list'); ?>
        </h3>
        
        <?php if(mysqli_num_rows($users) > 0): ?>
          <div class="users-list">
            <?php while($user = mysqli_fetch_assoc($users)): 
              $current_user = $_SESSION['username'];
              $is_current = ($user['username'] == $current_user);
              $first_letter = strtoupper(substr($user['username'], 0, 1));
              $has_default = hasDefaultPassword($user['password_hash']);
            ?>
              <div class="user-item">
                <div class="user-info">
                  <div class="user-avatar"><?php echo $first_letter; ?></div>
                  <div class="user-details">
                    <div class="user-name">
                      <?php echo htmlspecialchars($user['username']); ?>
                      <?php if($is_current): ?>
                        <span class="current-user"><?php echo __('current_user'); ?></span>
                      <?php endif; ?>
                      <?php if($has_default): ?>
                        <span class="default-password"><?php echo __('default_password'); ?></span>
                      <?php endif; ?>
                    </div>
                    <span class="user-role role-<?php echo $user['role']; ?>">
                      <?php if($user['role'] == 'admin'): ?>👑 <?php echo __('admin'); ?>
                      <?php else: ?>📚 <?php echo __('librarian'); ?>
                      <?php endif; ?>
                    </span>
                  </div>
                </div>
                
                <div class="action-buttons">
                  <form method="post" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" name="reset" class="reset-btn" onclick="return confirm('<?php echo __('confirm_reset'); ?> <?php echo $user['username']; ?>?')">
                      <span>🔄</span> <?php echo __('reset_password'); ?>
                    </button>
                  </form>
                  
                  <?php if(!$is_current): ?>
                    <a href="add_user.php?delete=<?php echo $user['id']; ?>" 
                       onclick="return confirm('<?php echo __('confirm_remove'); ?> <?php echo $user['username']; ?>?')" 
                       class="delete-btn">
                      🗑️ <?php echo __('remove'); ?>
                    </a>
                  <?php else: ?>
                    <span class="delete-btn disabled">🚫 <?php echo __('current_user'); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <span>👥</span>
            <h3 style="color: #999;"><?php echo __('no_users'); ?></h3>
            <p style="color: #ccc;"><?php echo __('add_first_user'); ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <a href="dashboard.php" class="back-btn">
      <span>←</span> <?php echo __('back_to_dashboard'); ?>
    </a>
  </div>
</div>

</body>
</html>