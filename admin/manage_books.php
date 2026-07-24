<?php
include("../config.php");
include("../lang.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth_login.php");
    exit();
}

$category_message = "";
$category_message_type = "";
$book_message = "";
$book_message_type = "";

// Get the next book code for a category
function getNextBookCode($conn, $category_id) {
    $result = mysqli_query($conn, "SELECT book_code FROM books WHERE category_id = $category_id ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last_code = $row['book_code'];
        preg_match('/^(\d+)/', $last_code, $matches);
        if (isset($matches[1])) {
            $next_num = intval($matches[1]) + 1;
            return str_pad($next_num, 2, '0', STR_PAD_LEFT);
        }
    }
    return '01';
}

// Get last code for a category (for display)
function getLastCode($conn, $category_id) {
    $result = mysqli_query($conn, "SELECT book_code FROM books WHERE category_id = $category_id ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['book_code'];
    }
    return '—';
}

// ============ CATEGORY FUNCTIONS ============
if(isset($_POST['add_category'])){
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $check = mysqli_query($conn, "SELECT * FROM categories WHERE name='$name'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')");
        $category_message = "✅ " . __('category_added');
        $category_message_type = "success";
    } else {
        $category_message = "⚠️ " . __('category_exists');
        $category_message_type = "error";
    }
}

if(isset($_GET['delete_category'])){
    $id = intval($_GET['delete_category']);
    $check_books = mysqli_query($conn, "SELECT * FROM books WHERE category_id=$id");
    if(mysqli_num_rows($check_books) == 0){
        mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
        $category_message = "✅ " . __('category_removed');
        $category_message_type = "success";
    } else {
        $category_message = "⚠️ " . __('category_in_use');
        $category_message_type = "error";
    }
}

// ============ BOOK FUNCTIONS ============
if(isset($_POST['add_book'])){
    $category_id = intval($_POST['category_id']);
    $book_name = mysqli_real_escape_string($conn, $_POST['book_name']);
    $writer_name = mysqli_real_escape_string($conn, $_POST['writer_name']);
    $published_year = mysqli_real_escape_string($conn, $_POST['published_year']);
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $quantity = intval($_POST['quantity']);
    $registered_by = mysqli_real_escape_string($conn, $_POST['registered_by']);
    $registration_date = getCurrentEthiopianDate();
    
    // Generate book code
    $base_code = getNextBookCode($conn, $category_id);
    
    if ($quantity > 1) {
        // For multiple copies, generate codes like 45A, 45B, 45C
        $letters = range('A', 'Z');
        $codes_generated = [];
        for ($i = 0; $i < $quantity; $i++) {
            $book_code = $base_code . $letters[$i];
            $codes_generated[] = $book_code;
            mysqli_query($conn, "INSERT INTO books (category_id, book_name, writer_name, published_year, price, quantity, book_code, registered_by, registration_date) 
            VALUES ($category_id, '$book_name', '$writer_name', '$published_year', $price, 1, '$book_code', '$registered_by', '$registration_date')");
        }
        $code_display = $codes_generated[0] . ' - ' . end($codes_generated);
    } else {
        $book_code = $base_code;
        mysqli_query($conn, "INSERT INTO books (category_id, book_name, writer_name, published_year, price, quantity, book_code, registered_by, registration_date) 
        VALUES ($category_id, '$book_name', '$writer_name', '$published_year', $price, 1, '$book_code', '$registered_by', '$registration_date')");
        $code_display = $book_code;
    }
    
    $book_message = "✅ " . __('book_added') . " — ኮድ: " . $code_display . " | የተመዘገበበት ቀን: " . $registration_date;
    $book_message_type = "success";
}

// ============ GET DATA ============
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$recent_books = mysqli_query($conn, "SELECT b.*, c.name as category_name 
    FROM books b 
    JOIN categories c ON b.category_id = c.id 
    ORDER BY b.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('books_categories'); ?> - <?php echo __('site_title'); ?></title>
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
  background: radial-gradient(circle at center, #fff200 0%, #f7941d 35%, #a61d21 70%, #7e191b 100%);
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
  flex-wrap: wrap;
  gap: 10px;
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
  max-width: 1400px;
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

.books-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
  margin-bottom: 25px;
}

@media (max-width: 992px) {
  .books-grid {
    grid-template-columns: 1fr;
  }
}

.category-section {
  background: white;
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.category-section h3 {
  color: #f7941d;
  font-size: 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding-bottom: 10px;
  border-bottom: 2px solid #fff200;
}

.book-section {
  background: white;
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.book-section h3 {
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

.add-book-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #a61d21, #7e191b);
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

.add-book-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(166, 29, 33, 0.3);
}

.categories-list {
  margin-top: 25px;
  max-height: 400px;
  overflow-y: auto;
  border-top: 1px solid #f0f0f0;
  padding-top: 20px;
}

.categories-list h4 {
  color: #666;
  font-size: 15px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.category-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  transition: 0.3s;
}

.category-item:hover {
  background: rgba(255, 242, 0, 0.05);
  transform: translateX(5px);
  border-radius: 10px;
}

.category-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-icon {
  width: 35px;
  height: 35px;
  background: linear-gradient(135deg, #fff20020, #f7941d20);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f7941d;
  font-size: 16px;
}

.category-name {
  font-size: 15px;
  font-weight: 600;
  color: #333;
}

.category-meta {
  font-size: 12px;
  color: #999;
}

.category-delete {
  padding: 6px 14px;
  background: #ff4444;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  transition: 0.3s;
}

.category-delete:hover {
  background: #cc0000;
  transform: translateY(-2px);
}

.category-delete.disabled {
  background: #ccc;
  pointer-events: none;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.input-group {
  display: flex;
  align-items: center;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
}

.input-group:focus-within {
  border-color: #f7941d;
  box-shadow: 0 0 0 3px rgba(247, 148, 29, 0.1);
}

.input-group-prepend {
  background: linear-gradient(135deg, #f7941d, #e07e1a);
  color: white;
  padding: 12px 16px;
  font-size: 16px;
  font-weight: 700;
  border: none;
}

.input-group .form-control {
  border: none;
  border-radius: 0;
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

.next-code-display {
  background: #fff8e1;
  padding: 12px 16px;
  border-radius: 12px;
  margin-bottom: 20px;
  border-left: 4px solid #f7941d;
  font-weight: 600;
  color: #a61d21;
  display: flex;
  align-items: center;
  gap: 8px;
}

.recent-books {
  margin-top: 25px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 16px;
}

.recent-books h4 {
  color: #a61d21;
  font-size: 16px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.recent-book-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #e0e0e0;
}

.recent-book-item:last-child {
  border-bottom: none;
}

.recent-book-info {
  display: flex;
  gap: 15px;
  font-size: 14px;
  align-items: center;
}

.recent-book-code {
  background: #a61d21;
  color: white;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.recent-book-name {
  font-weight: 700;
  color: #333;
}

.recent-book-category {
  color: #f7941d;
  background: #f7941d20;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 12px;
}

.recent-book-date {
  font-size: 12px;
  color: #28a745;
  font-weight: 600;
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

.empty-state {
  text-align: center;
  padding: 30px;
  color: #999;
  background: #f8f9fa;
  border-radius: 16px;
}

.empty-state span {
  font-size: 40px;
  display: block;
  margin-bottom: 12px;
  opacity: 0.5;
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
    <h2><?php echo __('books_categories'); ?></h2>
  </div>
  <div style="display: flex; align-items: center;">
    <div class="lang-switcher">
      <a href="?lang=en" class="lang-btn <?php echo $_SESSION['lang'] == 'en' ? 'active' : ''; ?>">EN</a>
      <a href="?lang=am" class="lang-btn <?php echo $_SESSION['lang'] == 'am' ? 'active' : ''; ?>">አማ</a>
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
      📚 መጻሕፍትን አስተዳድር / <?php echo __('books_categories'); ?>
      <span><?php echo mysqli_num_rows($categories); ?> <?php echo __('categories'); ?></span>
    </h2>
    
    <div class="books-grid">
      <!-- LEFT COLUMN: Category Management -->
      <div class="category-section">
        <h3>
          <span>📑</span> <?php echo __('manage_categories'); ?>
        </h3>
        
        <?php if($category_message != ""): ?>
          <div class="alert alert-<?php echo $category_message_type; ?>">
            <span><?php echo $category_message_type == 'success' ? '✅' : '⚠️'; ?></span>
            <?php echo $category_message; ?>
          </div>
        <?php endif; ?>
        
        <form method="post">
          <div class="form-group">
            <label>
              <span>➕</span> <?php echo __('category_name'); ?>
            </label>
            <input type="text" name="category_name" class="form-control" placeholder="<?php echo __('category_name'); ?>" required autocomplete="off">
          </div>
          <button type="submit" name="add_category" class="add-btn">
            <span>➕</span> <?php echo __('add_category'); ?>
          </button>
        </form>
        
        <div class="categories-list">
          <h4>
            <span>📋</span> <?php echo __('existing_categories'); ?> (<?php echo mysqli_num_rows($categories); ?>)
          </h4>
          
          <?php if(mysqli_num_rows($categories) > 0): ?>
            <?php 
            mysqli_data_seek($categories, 0);
            while($cat = mysqli_fetch_assoc($categories)): 
              $check_books = mysqli_query($conn, "SELECT COUNT(*) as count FROM books WHERE category_id=" . $cat['id']);
              $book_count = mysqli_fetch_assoc($check_books)['count'];
              $last_code = getLastCode($conn, $cat['id']);
            ?>
              <div class="category-item">
                <div class="category-info">
                  <div class="category-icon">📂</div>
                  <div>
                    <div class="category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <div class="category-meta">
                      📚 <?php echo $book_count; ?> መጻሕፍት / books 
                      • 🏷️ የመጨረሻ ኮድ: <strong><?php echo $last_code; ?></strong>
                    </div>
                  </div>
                </div>
                <?php if($book_count == 0): ?>
                  <a href="manage_books.php?delete_category=<?php echo $cat['id']; ?>" 
                     onclick="return confirm('<?php echo __('confirm_delete_category'); ?> <?php echo $cat['name']; ?>?')" 
                     class="category-delete">
                    🗑️ ሰርዝ
                  </a>
                <?php else: ?>
                  <span class="category-delete disabled" title="<?php echo __('category_in_use'); ?>">
                    🚫 በጥቅም ላይ
                  </span>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="empty-state">
              <span>📂</span>
              <p><?php echo __('no_categories'); ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- RIGHT COLUMN: Add Book -->
      <div class="book-section">
        <h3>
          <span>📖</span> መጽሐፍ ጨምር / <?php echo __('add_book'); ?>
        </h3>
        
        <?php if($book_message != ""): ?>
          <div class="alert alert-<?php echo $book_message_type; ?>">
            <span><?php echo $book_message_type == 'success' ? '✅' : '⚠️'; ?></span>
            <?php echo $book_message; ?>
          </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($categories) > 0): ?>
          <form method="post" id="addBookForm">
            <div class="form-group">
              <label>
                <span>📑</span> ምድብ ምረጥ / <?php echo __('select_category'); ?>
              </label>
              <select name="category_id" id="category_id" class="form-control" required onchange="updateNextCode()">
                <option value="" disabled selected>-- ምድብ ምረጥ --</option>
                <?php 
                mysqli_data_seek($categories, 0);
                while($cat = mysqli_fetch_assoc($categories)): 
                  $lc = getLastCode($conn, $cat['id']);
                ?>
                  <option value="<?php echo $cat['id']; ?>" data-last-code="<?php echo $lc; ?>">
                    📚 <?php echo $cat['name']; ?> (የመጨረሻ ኮድ: <?php echo $lc; ?>)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            
            <!-- Next Code Display -->
            <div class="next-code-display" id="nextCodeDisplay" style="display: none;">
              <span>🏷️</span> 
              ቀጣይ ኮድ / Next Code: <strong id="nextCodeValue">--</strong>
            </div>
            
            <div class="form-group">
              <label>
                <span>📕</span> የመጽሐፍ ስም / <?php echo __('book_name'); ?>
              </label>
              <input type="text" name="book_name" class="form-control" placeholder="የመጽሐፉን ስም ያስገቡ" required autocomplete="off">
            </div>
            
            <div class="form-group">
              <label>
                <span>✍️</span> የጸሐፊ ስም / <?php echo __('writer'); ?>
              </label>
              <input type="text" name="writer_name" class="form-control" placeholder="የጸሐፊውን ስም ያስገቡ" autocomplete="off">
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label>
                  <span>📅</span> ዓመተ ምህረት / <?php echo __('published_year'); ?>
                </label>
                <input type="text" name="published_year" class="form-control" placeholder="2014 ዓ.ም">
              </div>
              
              <div class="form-group">
                <label>
                  <span>📊</span> ብዛት / <?php echo __('quantity'); ?>
                </label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required onchange="updateNextCode()">
              </div>
            </div>
            
            <div class="form-group">
              <label>
                <span>💰</span> ዋጋ / <?php echo __('price'); ?> (ብር)
              </label>
              <div class="input-group">
                <span class="input-group-prepend">ብር</span>
                <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0" value="0.00">
              </div>
            </div>
            
            <div class="form-group">
              <label>
                <span>👤</span> የመፅሀፉ አስገቢ ስም / Registered By
              </label>
              <input type="text" name="registered_by" class="form-control" placeholder="አስገቢውን ስም ያስገቡ" required autocomplete="off">
            </div>
            
            <button type="submit" name="add_book" class="add-book-btn">
              <span>➕</span> መጽሐፍ ጨምር / <?php echo __('add_book'); ?>
            </button>
          </form>
        <?php else: ?>
          <div class="empty-state">
            <span>📂</span>
            <h3 style="color: #999; margin-bottom: 10px;"><?php echo __('no_categories'); ?></h3>
            <p style="color: #666; margin-bottom: 20px;"><?php echo __('add_first_category'); ?></p>
          </div>
        <?php endif; ?>
        
        <!-- Recent Books -->
        <?php if(mysqli_num_rows($recent_books) > 0): ?>
          <div class="recent-books">
            <h4>
              <span>🕐</span> በቅርቡ የተጨመሩ መጻሕፍት / Recent Books
            </h4>
            <?php while($book = mysqli_fetch_assoc($recent_books)): ?>
              <div class="recent-book-item">
                <div class="recent-book-info">
                  <span class="recent-book-code"><?php echo $book['book_code']; ?></span>
                  <span class="recent-book-name">📘 <?php echo htmlspecialchars($book['book_name']); ?></span>
                  <span class="recent-book-category"><?php echo $book['category_name']; ?></span>
                </div>
                <span class="recent-book-date">
                  📆 <?php echo $book['registration_date'] ?: '—'; ?>
                </span>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <a href="dashboard.php" class="back-btn">
      <span>←</span> ወደ ዳሽቦርድ ተመለስ / <?php echo __('back_to_dashboard'); ?>
    </a>
  </div>
</div>

<script>
function updateNextCode() {
    var categorySelect = document.getElementById('category_id');
    var quantity = document.getElementById('quantity').value || 1;
    var nextCodeDisplay = document.getElementById('nextCodeDisplay');
    var nextCodeValue = document.getElementById('nextCodeValue');
    
    if (categorySelect.value) {
        var selectedOption = categorySelect.options[categorySelect.selectedIndex];
        var lastCode = selectedOption.getAttribute('data-last-code');
        
        if (lastCode && lastCode !== '—') {
            var numMatch = lastCode.match(/^(\d+)/);
            if (numMatch) {
                var nextNum = parseInt(numMatch[1]) + 1;
                var nextCode = String(nextNum).padStart(2, '0');
                
                if (parseInt(quantity) > 1) {
                    var letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    var endLetter = letters[Math.min(parseInt(quantity) - 1, 25)];
                    nextCode += 'A - ' + nextCode + endLetter;
                }
                
                nextCodeValue.textContent = nextCode;
            } else {
                nextCodeValue.textContent = '01';
            }
        } else {
            nextCodeValue.textContent = parseInt(quantity) > 1 ? '01A - 01' + String.fromCharCode(64 + Math.min(parseInt(quantity), 26)) : '01';
        }
        
        nextCodeDisplay.style.display = 'flex';
    } else {
        nextCodeDisplay.style.display = 'none';
    }
}
</script>

</body>
</html>