<?php
include("config.php");
include("lang.php");


if(!isset($_SESSION['username']) || !isset($_SESSION['need_password_change'])) {
    header("Location: auth_login.php");
    exit();
}

$username = $_SESSION['username'];
$error = "";
$success = "";

// Get user's current password hash to verify old password if needed
$query = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if(isset($_POST['change'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password matches
    if(!password_verify($current_password, $user['password_hash'])) {
        $error = __('current_password_incorrect');
    }
    // Check new password length
    elseif(strlen($new_password) < 6) {
        $error = __('password_length_error');
    }
    // Check if passwords match
    elseif($new_password !== $confirm_password) {
        $error = __('password_mismatch');
    }
    // Check if using default password
    elseif($new_password == "123" || $new_password == "123456") {
        $error = __('password_default_error');
    }
    else {
        // HASH THE NEW PASSWORD!
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $update_query = "UPDATE users SET password_hash='$hashed_password' WHERE username='$username'";
        if(mysqli_query($conn, $update_query)) {
            unset($_SESSION['need_password_change']);
            $success = "✅ " . __('password_changed');
            
            echo '<script>
                setTimeout(function() {
                    window.location.href = "' . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'librarian/dashboard.php') . '";
                }, 3000);
            </script>';
        } else {
            $error = __('update_failed');
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('change_password'); ?> - <?php echo __('site_title'); ?></title>
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

.circle3 {
  width: 300px;
  height: 300px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: radial-gradient(circle at center, #f7941d03, #fff20003, #a61d2103);
  animation: pulse 15s infinite;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  33% { transform: translate(30px, -30px) rotate(120deg); }
  66% { transform: translate(-20px, 20px) rotate(240deg); }
}

@keyframes pulse {
  0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.3; }
  50% { transform: translate(-50%, -50%) scale(1.3); opacity: 0.1; }
}

.change-wrapper {
  width: 100%;
  max-width: 500px;
  padding: 20px;
  animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.change-card {
  background: white;
  border-radius: 30px;
  padding: 35px;
  box-shadow: 0 20px 50px rgba(166, 29, 33, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.5);
  position: relative;
  overflow: hidden;
}

.change-card::before {
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
  margin-bottom: 25px;
}

.logo-mini {
  width: 90px;
  height: 90px;
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
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
}

.logo-icon {
  font-size: 40px;
  color: white;
}

.logo-area h2 {
  color: #a61d21;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 5px;
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

.alert-box {
  background: #fff8e1;
  border-left: 5px solid #fff200;
  padding: 16px 20px;
  border-radius: 16px;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.alert-text {
  color: #a61d21;
  font-weight: 600;
  font-size: 15px;
}

.alert-text small {
  color: #666;
  font-weight: normal;
  display: block;
  margin-top: 5px;
}

.user-info {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 16px;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid #e0e0e0;
}

.user-avatar {
  width: 50px;
  height: 50px;
  background: #a61d21;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  font-weight: 700;
  border: 2px solid #fff200;
}

.user-details h4 {
  color: #333;
  font-size: 18px;
  margin-bottom: 4px;
}

.user-details p {
  color: #666;
  font-size: 14px;
}

.role-badge {
  background: #fff20020;
  color: #a61d21;
  padding: 4px 12px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
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
  color: #fff200;
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

.password-strength {
  margin-top: 10px;
}

.strength-bar {
  height: 6px;
  background: #e0e0e0;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 8px;
}

.strength-fill {
  height: 100%;
  width: 0;
  border-radius: 3px;
  transition: 0.3s;
}

.strength-text {
  font-size: 13px;
  color: #666;
}

.change-btn {
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
  margin: 25px 0 15px;
  position: relative;
  overflow: hidden;
  transition: 0.3s;
}

.change-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(255, 242, 0, 0.4);
}

.error-message {
  background: #ffebee;
  color: #c62828;
  padding: 14px 20px;
  border-radius: 50px;
  margin-bottom: 25px;
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

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 20px;
  border-radius: 16px;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 15px;
  font-size: 16px;
  border: 2px solid #28a745;
  animation: slideDown 0.5s;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.redirect-timer {
  background: #e8f5e9;
  border-radius: 16px;
  padding: 15px;
  margin-top: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 2px dashed #28a745;
}

.requirements {
  background: #f8f9fa;
  border-radius: 16px;
  padding: 16px;
  margin-top: 20px;
}

.requirements h5 {
  color: #666;
  font-size: 14px;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.requirement-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #666;
  font-size: 13px;
  margin-bottom: 8px;
}

.requirement-item i {
  color: #28a745;
  font-size: 14px;
}

.requirement-item.disabled i {
  color: #ccc;
}

.current-password {
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 2px dashed #f7941d;
}

@media (max-width: 480px) {
  .change-card { padding: 25px; }
  .logo-mini { width: 80px; height: 80px; }
  .logo-area h2 { font-size: 24px; }
  .lang-switcher { top: 10px; right: 10px; }
  .user-info { flex-direction: column; text-align: center; }
}
</style>
</head>
<body>

<div class="background-circles">
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>
  <div class="circle circle3"></div>
</div>

<div class="change-wrapper">
  <div class="change-card">
    
    <div class="lang-switcher">
      <a href="?lang=en" class="lang-btn <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] == 'en' ? 'active' : ''; ?>">EN</a>
      <a href="?lang=am" class="lang-btn <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] == 'am' ? 'active' : ''; ?>">አማ</a>
    </div>
    
    <div class="logo-area">
      <div class="logo-mini">
        <?php if(file_exists("image/icon.php")): ?>
          <img src="image/icon.php" alt="<?php echo __('site_title'); ?>">
        <?php else: ?>
          <div class="logo-icon">🔐</div>
        <?php endif; ?>
      </div>
      <h2><?php echo __('change_password'); ?></h2>
    </div>
    
    <div class="alert-box">
      <span class="alert-icon">🔐</span>
      <div class="alert-text">
        <?php echo __('force_password_change'); ?>
        <small><?php echo __('force_password_message'); ?></small>
      </div>
    </div>
    
    <div class="user-info">
      <div class="user-avatar">
        <?php echo strtoupper(substr($username, 0, 1)); ?>
      </div>
      <div class="user-details">
        <h4><?php echo htmlspecialchars($username); ?></h4>
        <p>
          <span>👑</span> <?php echo __('role'); ?>: 
          <span class="role-badge">
            <?php echo ucfirst($_SESSION['role']); ?>
          </span>
        </p>
      </div>
    </div>
    
    <?php if($error != ""): ?>
      <div class="error-message">
        <span style="font-size:18px;">⚠️</span>
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <?php if($success != ""): ?>
      <div class="success-message">
        <span style="font-size:24px;">✅</span>
        <div>
          <strong><?php echo $success; ?></strong>
          <p style="margin-top: 5px; font-size: 14px;">
            <?php echo __('redirecting'); ?> <span id="countdown">3</span> <?php echo __('seconds'); ?>...
          </p>
        </div>
      </div>
      
      <div class="redirect-timer">
        <span class="timer-icon">⏳</span>
        <span class="timer-text"><?php echo __('redirect_message'); ?></span>
      </div>
    <?php endif; ?>
    
    <?php if(empty($success)): ?>
    <form method="post" id="changeForm">
      
      <div class="current-password">
        <div class="form-group">
          <label>
            <span>🔑</span> <?php echo __('current_password'); ?>
          </label>
          <div class="input-wrapper">
            <span class="input-icon">🔒</span>
            <input type="password" name="current_password" id="current_password" class="input-field" placeholder="<?php echo __('enter_current_password'); ?>" required>
          </div>
        </div>
      </div>
      
      <div class="form-group">
        <label>
          <span>🔒</span> <?php echo __('new_password'); ?>
        </label>
        <div class="input-wrapper">
          <span class="input-icon">🔐</span>
          <input type="password" name="new_password" id="new_password" class="input-field" placeholder="<?php echo __('new_password'); ?>" required minlength="6" onkeyup="checkStrength()">
        </div>
        
        <div class="password-strength">
          <div class="strength-bar">
            <div id="strengthFill" class="strength-fill" style="width: 0%; background: #e0e0e0;"></div>
          </div>
          <span id="strengthText" class="strength-text"><?php echo __('enter_password'); ?></span>
        </div>
      </div>
      
      <div class="form-group">
        <label>
          <span>✅</span> <?php echo __('confirm_password'); ?>
        </label>
        <div class="input-wrapper">
          <span class="input-icon">✓</span>
          <input type="password" name="confirm_password" id="confirm_password" class="input-field" placeholder="<?php echo __('confirm_password'); ?>" required onkeyup="checkMatch()">
        </div>
      </div>
      
      <div class="requirements">
        <h5>
          <span>📋</span> <?php echo __('password_requirements'); ?>
        </h5>
        <div id="req_length" class="requirement-item disabled">
          <i>○</i> <?php echo __('min_length'); ?>
        </div>
        <div id="req_not_default" class="requirement-item disabled">
          <i>○</i> <?php echo __('not_default'); ?>
        </div>
        <div id="req_match" class="requirement-item disabled">
          <i>○</i> <?php echo __('passwords_match'); ?>
        </div>
      </div>
      
      <button type="submit" name="change" class="change-btn" id="submitBtn">
        <span style="font-size:20px;">🔄</span>
        <?php echo __('update_password'); ?>
      </button>
    </form>
    <?php endif; ?>
    
  </div>
</div>

<script>
function checkStrength() {
  var password = document.getElementById('new_password').value;
  var strength = 0;
  var strengthFill = document.getElementById('strengthFill');
  var strengthText = document.getElementById('strengthText');
  
  if(password.length >= 6) {
    strength += 33;
    document.getElementById('req_length').className = 'requirement-item';
    document.getElementById('req_length').innerHTML = '<i>✓</i> <?php echo __('min_length'); ?>';
  } else {
    document.getElementById('req_length').className = 'requirement-item disabled';
    document.getElementById('req_length').innerHTML = '<i>○</i> <?php echo __('min_length'); ?>';
  }
  
  if(password != '123' && password != '123456' && password != '') {
    strength += 33;
    document.getElementById('req_not_default').className = 'requirement-item';
    document.getElementById('req_not_default').innerHTML = '<i>✓</i> <?php echo __('not_default'); ?>';
  } else {
    document.getElementById('req_not_default').className = 'requirement-item disabled';
    document.getElementById('req_not_default').innerHTML = '<i>○</i> <?php echo __('not_default'); ?>';
  }
  
  if(password.length == 0) {
    strengthFill.style.width = '0%';
    strengthFill.style.background = '#e0e0e0';
    strengthText.innerHTML = '<?php echo __('enter_password'); ?>';
  } else if(strength <= 33) {
    strengthFill.style.width = '33%';
    strengthFill.style.background = '#dc3545';
    strengthText.innerHTML = '<?php echo __('weak'); ?>';
  } else if(strength <= 66) {
    strengthFill.style.width = '66%';
    strengthFill.style.background = '#ffc107';
    strengthText.innerHTML = '<?php echo __('medium'); ?>';
  } else {
    strengthFill.style.width = '100%';
    strengthFill.style.background = '#28a745';
    strengthText.innerHTML = '<?php echo __('strong'); ?>';
  }
  
  checkMatch();
}

function checkMatch() {
  var password = document.getElementById('new_password').value;
  var confirm = document.getElementById('confirm_password').value;
  var matchReq = document.getElementById('req_match');
  var submitBtn = document.getElementById('submitBtn');
  
  if(confirm.length > 0) {
    if(password === confirm) {
      matchReq.className = 'requirement-item';
      matchReq.innerHTML = '<i>✓</i> <?php echo __('passwords_match'); ?>';
    } else {
      matchReq.className = 'requirement-item disabled';
      matchReq.innerHTML = '<i>○</i> <?php echo __('passwords_do_not_match'); ?>';
    }
  } else {
    matchReq.className = 'requirement-item disabled';
    matchReq.innerHTML = '<i>○</i> <?php echo __('passwords_match'); ?>';
  }
}

<?php if($success != ""): ?>
  var seconds = 3;
  var countdown = setInterval(function() {
    seconds--;
    document.getElementById('countdown').innerHTML = seconds;
    if(seconds <= 0) {
      clearInterval(countdown);
    }
  }, 1000);
<?php endif; ?>
</script>

</body>
</html>