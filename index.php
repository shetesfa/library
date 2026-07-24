<?php
include("config.php");
include("lang.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('site_title'); ?></title>
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', 'Arial', sans-serif;
  background: linear-gradient(135deg, #f7f7f7 0%, #f0f0f0 100%);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
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
  width: 400px;
  height: 400px;
  top: -100px;
  left: -100px;
  background: radial-gradient(circle at center, #fff20015, #f7941d15, #a61d2115);
}

.circle2 {
  width: 500px;
  height: 500px;
  bottom: -150px;
  right: -100px;
  background: radial-gradient(circle at center, #a61d2110, #f7941d10, #fff20010);
}

.circle3 {
  width: 300px;
  height: 300px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: radial-gradient(circle at center, #f7941d08, #fff20008, #a61d2108);
  animation: pulse 15s infinite;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  33% { transform: translate(30px, -30px) rotate(120deg); }
  66% { transform: translate(-20px, 20px) rotate(240deg); }
}

@keyframes pulse {
  0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
  50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.3; }
}

.lang-switcher {
  position: fixed;
  top: 20px;
  right: 20px;
  display: flex;
  gap: 5px;
  background: white;
  padding: 5px;
  border-radius: 40px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  z-index: 100;
}

.lang-btn {
  padding: 8px 16px;
  border-radius: 30px;
  color: #666;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  transition: 0.3s;
}

.lang-btn.active {
  background: #a61d21;
  color: white;
}

.container {
  max-width: 1200px;
  margin: 20px;
  padding: 40px;
  text-align: center;
  position: relative;
  z-index: 10;
}

.logo-wrapper {
  margin-bottom: 30px;
  animation: fadeInDown 1.2s ease-out;
}

.logo-circle {
  width: 200px;
  height: 200px;
  margin: 0 auto 25px;
  border-radius: 50%;
  background: radial-gradient(
    circle at center,
    #fff200 0%,
    #f7941d 45%,
    #a61d21 85%,
    #7e191b 100%
  );
  border: 6px solid white;
  box-shadow: 0 15px 40px rgba(166, 29, 33, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.5s;
  animation: logoReveal 1.5s ease-out;
}

.logo-circle:hover {
  transform: scale(1.05) rotate(5deg);
  box-shadow: 0 25px 60px rgba(255, 242, 0, 0.4);
}

.logo-circle img {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.9);
}

.logo-icon {
  font-size: 80px;
  color: white;
  text-shadow: 2px 2px 10px rgba(0,0,0,0.2);
}

.library-name {
  margin-bottom: 20px;
  animation: fadeInUp 1s ease-out 0.3s both;
}

.library-name h1 {
  font-size: 56px;
  font-weight: 800;
  letter-spacing: 2px;
  margin-bottom: 10px;
  background: linear-gradient(135deg, #a61d21, #7e191b);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 5px 5px 15px rgba(166, 29, 33, 0.2);
  line-height: 1.2;
}

.library-name span {
  font-size: 24px;
  color: #f7941d;
  font-weight: 500;
  display: block;
  letter-spacing: 8px;
  text-transform: uppercase;
  margin-top: 10px;
}

.tagline {
  margin-bottom: 40px;
  animation: fadeInUp 1s ease-out 0.6s both;
}

.tagline h2 {
  font-size: 36px;
  color: #333;
  margin-bottom: 15px;
  font-weight: 600;
  position: relative;
  display: inline-block;
}

.tagline h2::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background: linear-gradient(90deg, #fff200, #f7941d, #a61d21);
  border-radius: 4px;
  animation: widthPulse 2s infinite;
}

@keyframes widthPulse {
  0%, 100% { width: 80px; }
  50% { width: 120px; }
}

.tagline p {
  font-size: 20px;
  color: #666;
  line-height: 1.6;
  max-width: 700px;
  margin: 20px auto 0;
}

.features {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
  margin: 50px 0;
  animation: fadeInUp 1s ease-out 0.9s both;
}

.feature-card {
  background: white;
  padding: 30px 20px;
  border-radius: 20px;
  box-shadow: 0 15px 35px rgba(166, 29, 33, 0.1);
  transition: 0.4s;
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.feature-card::before {
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

.feature-card:hover {
  transform: translateY(-15px);
  box-shadow: 0 30px 50px rgba(166, 29, 33, 0.2);
}

.feature-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #fff20020, #f7941d20);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 40px;
  color: #a61d21;
  transition: 0.3s;
}

.feature-card:hover .feature-icon {
  transform: scale(1.1) rotate(10deg);
  background: linear-gradient(135deg, #fff20040, #f7941d40);
}

.feature-card h3 {
  color: #a61d21;
  font-size: 22px;
  margin-bottom: 12px;
  font-weight: 700;
}

.feature-card p {
  color: #666;
  font-size: 15px;
  line-height: 1.5;
}

.login-btn {
  margin-top: 20px;
  animation: fadeInUp 1s ease-out 1.2s both;
}

.btn-glow {
  padding: 18px 50px;
  font-size: 22px;
  font-weight: 700;
  color: white;
  background: linear-gradient(135deg, #a61d21, #7e191b);
  border: none;
  border-radius: 50px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: 0.4s;
  box-shadow: 0 15px 35px rgba(166, 29, 33, 0.4);
  border: 2px solid #fff200;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.btn-glow:hover {
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 25px 45px rgba(255, 242, 0, 0.4);
  background: linear-gradient(135deg, #7e191b, #611315);
}

.footer {
  margin-top: 60px;
  padding: 20px;
  color: #999;
  font-size: 14px;
  animation: fadeIn 1s ease-out 1.5s both;
  border-top: 1px solid rgba(0,0,0,0.05);
}

.footer span {
  color: #a61d21;
  font-weight: 700;
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-50px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(50px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes logoReveal {
  0% { opacity: 0; transform: scale(0.5) rotate(-180deg); }
  100% { opacity: 1; transform: scale(1) rotate(0); }
}

@media (max-width: 992px) {
  .library-name h1 { font-size: 48px; }
  .features { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .logo-circle { width: 160px; height: 160px; }
  .logo-circle img { width: 130px; height: 130px; }
  .logo-icon { font-size: 60px; }
  .library-name h1 { font-size: 36px; }
  .library-name span { font-size: 18px; letter-spacing: 4px; }
  .tagline h2 { font-size: 28px; }
  .tagline p { font-size: 18px; }
  .features { grid-template-columns: 1fr; gap: 20px; }
  .btn-glow { padding: 15px 40px; font-size: 20px; }
}

@media (max-width: 480px) {
  .container { padding: 20px; }
  .logo-circle { width: 140px; height: 140px; }
  .logo-circle img { width: 110px; height: 110px; }
  .logo-icon { font-size: 50px; }
  .library-name h1 { font-size: 28px; }
  .library-name span { font-size: 16px; letter-spacing: 2px; }
  .tagline h2 { font-size: 24px; }
  .tagline p { font-size: 16px; }
  .btn-glow { padding: 12px 30px; font-size: 18px; width: 100%; }
  .lang-switcher { top: 10px; right: 10px; }
  .lang-btn { padding: 6px 12px; font-size: 12px; }
}
</style>
</head>
<body>

<div class="lang-switcher">
  <a href="?lang=en" class="lang-btn <?php echo $_SESSION['lang'] == 'en' ? 'active' : ''; ?>">EN</a>
  <a href="?lang=am" class="lang-btn <?php echo $_SESSION['lang'] == 'am' ? 'active' : ''; ?>">አማ</a>
</div>

<div class="background-circles">
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>
  <div class="circle circle3"></div>
</div>

<div class="container">
  
  <div class="logo-wrapper">
    <div class="logo-circle">
      <?php if(file_exists("image/icon.php")): ?>
        <img src="image/icon.php" alt="<?php echo __('site_title'); ?>">
      <?php else: ?>
        <div class="logo-icon">📚</div>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="library-name">
    <h1><?php echo __('atsede_teguhan'); ?></h1>
    <span><?php echo __('sunday_school_library'); ?></span>
  </div>
  
  <div class="tagline">
    <h2><?php echo __('welcome_to_library'); ?></h2>
    <p><?php echo __('manage_books_borrowing'); ?></p>
  </div>
  
  <div class="features">
    <div class="feature-card">
      <div class="feature-icon">📚</div>
      <h3><?php echo __('book_management'); ?></h3>
      <p><?php echo __('book_management_desc'); ?></p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">👥</div>
      <h3><?php echo __('user_management'); ?></h3>
      <p><?php echo __('user_management_desc'); ?></p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🔄</div>
      <h3><?php echo __('borrow_return'); ?></h3>
      <p><?php echo __('borrow_return_desc'); ?></p>
    </div>
  </div>
  
  <div class="login-btn">
    <button onclick="window.location.href='auth_login.php'" class="btn-glow">
      🔐 <?php echo __('access_library'); ?>
    </button>
  </div>
  
  <div class="footer">
    <p>© <?php echo date('Y'); ?> <span><?php echo __('atsede_teguhan'); ?></span> · <?php echo __('all_rights_reserved'); ?></p>
    <p style="margin-top: 8px; font-size: 12px;"><?php echo __('bible_verse'); ?></p>
  </div>
  
</div>

</body>
</html>