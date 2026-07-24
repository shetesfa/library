<?php
include("../config.php");
include("../lang.php");
check_login('librarian');

$message = "";
$message_type = "";

// Handle Borrow Book
if(isset($_POST['borrow'])){
    $book_id = intval($_POST['book_id']);
    $borrower_name = mysqli_real_escape_string($conn,$_POST['borrower_name']);
    $class = mysqli_real_escape_string($conn,$_POST['class']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $borrow_date = date("Y-m-d");
    $due_date = date("Y-m-d", strtotime("+7 days"));

    // Check if borrower already has a book borrowed
    $check = mysqli_query($conn,"SELECT * FROM borrow_records WHERE borrower_name='$borrower_name' AND status='borrowed'");
    if(mysqli_num_rows($check)==0){
        mysqli_query($conn,"INSERT INTO borrow_records (book_id,borrower_name,class,phone_number,borrow_date,due_date,status)
        VALUES ($book_id,'$borrower_name','$class','$phone','$borrow_date','$due_date','borrowed')");
        mysqli_query($conn,"UPDATE books SET quantity=quantity-1 WHERE id=$book_id");
        
        // Get book title for modal
        $book_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT book_name FROM books WHERE id=$book_id"));
        ?>
        <script>
            showSuccessModal(
                '<?php echo addslashes($book_info['book_name']); ?>',
                '<?php echo addslashes($borrower_name); ?>',
                '<?php echo formatDate($due_date); ?>'
            );
        </script>
        <?php
    }else{
        $message = "⚠️ " . __('already_borrowed');
        $message_type = "error";
    }
}

// Search Books
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

$where_sql = "WHERE b.quantity > 0";
if(!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_sql .= " AND (b.book_name LIKE '%$search%' 
                   OR b.writer_name LIKE '%$search%' 
                   OR c.name LIKE '%$search%')";
}
if(!empty($category_filter)) {
    $category_filter = intval($category_filter);
    $where_sql .= " AND b.category_id = $category_filter";
}

// Get all books with categories
$books = mysqli_query($conn, "SELECT b.*, c.name as category_name 
    FROM books b 
    JOIN categories c ON b.category_id = c.id 
    $where_sql 
    ORDER BY b.book_name ASC");

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Get borrower names for datalist
$borrowers = mysqli_query($conn, "SELECT DISTINCT borrower_name FROM borrow_records ORDER BY borrower_name");

// STATS - CORRECT CALCULATION
$current_available = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total FROM books"))['total'];
$borrowed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='borrowed'"))['total'];
$total_copies_ever = $current_available + $borrowed_count;
$total_titles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM books"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('librarian_dashboard'); ?> - <?php echo __('site_title'); ?></title>
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

/* Vibrant Logo-Inspired Background Elements */
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

/* Header */
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

/* Language Switcher */
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

/* Logout Button */
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

/* Container */
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

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 25px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 18px;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  border-left: 5px solid #fff200;
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: #a61d2120;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #a61d21;
}

.stat-info h4 {
  color: #666;
  font-size: 14px;
  margin-bottom: 5px;
}

.stat-info p {
  color: #a61d21;
  font-size: 24px;
  font-weight: 700;
}

/* Navigation Tabs */
.nav-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  background: white;
  padding: 10px;
  border-radius: 50px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.tab {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border-radius: 40px;
  text-decoration: none;
  color: #666;
  font-weight: 600;
  transition: 0.3s;
}

.tab.active {
  background: #a61d21;
  color: white;
}

.tab:hover:not(.active) {
  background: #f0f0f0;
}

/* Search Section */
.search-section {
  background: white;
  padding: 20px;
  border-radius: 20px;
  margin-bottom: 25px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  border: 1px solid #eee;
}

.search-form {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-wrapper {
  flex: 2;
  position: relative;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #fff200;
  font-size: 16px;
}

.search-input {
  width: 100%;
  padding: 14px 16px 14px 48px;
  border: 2px solid #e0e0e0;
  border-radius: 40px;
  font-size: 15px;
  transition: 0.3s;
}

.search-input:focus {
  border-color: #fff200;
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 242, 0, 0.1);
}

.category-wrapper {
  flex: 1;
  min-width: 180px;
}

.category-select {
  width: 100%;
  padding: 14px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 40px;
  font-size: 15px;
  background: white;
  cursor: pointer;
}

.search-btn {
  padding: 14px 28px;
  background: #a61d21;
  color: white;
  border: none;
  border-radius: 40px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 2px solid #fff200;
  transition: 0.3s;
}

.search-btn:hover {
  background: #7e191b;
  transform: translateY(-2px);
}

/* Book Grid */
.book-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.book-card {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  border: 1px solid #eee;
  transition: 0.3s;
  position: relative;
  overflow: hidden;
}

.book-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(166, 29, 33, 0.1);
  border-color: #fff200;
}

.book-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: #fff200;
}

.book-category {
  background: #fff20020;
  color: #a61d21;
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
  margin-bottom: 12px;
}

.book-title {
  font-size: 18px;
  font-weight: 700;
  color: #333;
  margin-bottom: 8px;
}

.book-writer {
  color: #666;
  font-size: 14px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.book-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding: 10px 0;
  border-top: 1px solid #f0f0f0;
  border-bottom: 1px solid #f0f0f0;
}

.book-price {
  color: #a61d21;
  font-weight: 700;
  font-size: 18px;
}

.book-stock {
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 600;
}

.in-stock {
  background: #28a74520;
  color: #28a745;
}

.low-stock {
  background: #ffc10720;
  color: #856404;
}

.borrow-btn {
  width: 100%;
  padding: 12px;
  background: #a61d21;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: 0.3s;
  border: 2px solid #fff200;
}

.borrow-btn:hover {
  background: #7e191b;
  transform: scale(1.02);
}

.borrow-btn:disabled {
  background: #ccc;
  border-color: #999;
  cursor: not-allowed;
}

/* Modal Styles */
.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.modal.active {
  display: flex;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 24px;
  max-width: 500px;
  width: 100%;
  position: relative;
  border-top: 5px solid #fff200;
  box-shadow: 0 20px 40px rgba(166, 29, 33, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h3 {
  color: #a61d21;
  font-size: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #999;
  padding: 8px;
}

.close-btn:hover {
  color: #a61d21;
}

/* Success Modal */
.success-icon {
  font-size: 60px;
  color: #28a745;
  margin-bottom: 15px;
}

.success-details {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 12px;
  margin: 20px 0;
  text-align: left;
}

.countdown {
  color: #a61d21;
  font-weight: 700;
}

/* Form Styles */
.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #a61d21;
  margin-bottom: 6px;
}

.form-control {
  width: 100%;
  padding: 14px 18px;
  border: 2px solid #e0e0e0;
  border-radius: 16px;
  font-size: 15px;
  transition: 0.3s;
}

.form-control:focus {
  border-color: #fff200;
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 242, 0, 0.1);
}

.submit-btn {
  width: 100%;
  padding: 16px;
  background: #a61d21;
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 2px solid #fff200;
  transition: 0.3s;
  margin-top: 10px;
  text-decoration: none;
}

.submit-btn:hover {
  background: #7e191b;
  transform: translateY(-2px);
}

/* Alert Messages */
.alert {
  padding: 16px 20px;
  border-radius: 16px;
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

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 20px;
  color: #999;
}

.empty-state span {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
  opacity: 0.5;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  header {
    flex-direction: column;
    gap: 12px;
    padding: 15px;
  }
  
  .logo-area {
    flex-direction: column;
    text-align: center;
  }
  
  header img {
    height: 35px;
  }
  
  header h2 {
    font-size: 18px;
  }
  
  .lang-switcher {
    margin-right: 0;
    margin-bottom: 10px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .nav-tabs {
    flex-direction: column;
    border-radius: 20px;
  }
  
  .search-form {
    flex-direction: column;
  }
  
  .search-wrapper,
  .category-wrapper {
    width: 100%;
  }
  
  .search-btn {
    width: 100%;
    justify-content: center;
  }
  
  .book-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .container {
    padding: 12px;
  }
}
</style>
</head>
<body>

<!-- Background Elements -->
<div class="logo-background logo-1"></div>
<div class="logo-background logo-2"></div>
<div class="logo-background logo-3"></div>

<header>
  <div class="logo-area">
    <img src="../image/icon.php" alt="<?php echo __('site_title'); ?>">
    <h2><?php echo __('librarian_dashboard'); ?></h2>
  </div>
  <div style="display: flex; align-items: center;">
    <!-- Language Switcher -->
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
  
  <!-- STATS CARDS - With translations -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">📚</div>
      <div class="stat-info">
        <h4><?php echo __('total_titles'); ?></h4>
        <p><?php echo $total_titles ?: 0; ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-info">
        <h4><?php echo __('available_now'); ?></h4>
        <p><?php echo $current_available ?: 0; ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📖</div>
      <div class="stat-info">
        <h4><?php echo __('borrowed'); ?></h4>
        <p><?php echo $borrowed_count ?: 0; ?></p>
      </div>
    </div>
  </div>
  
  <!-- Navigation Tabs with translations -->
  <div class="nav-tabs">
    <a href="dashboard.php" class="tab active">
      <span>📚</span> <?php echo __('books'); ?>
    </a>
    <a href="transactions.php" class="tab">
      <span>🔄</span> <?php echo __('transactions'); ?>
    </a>
  </div>
  
  <!-- Message Alert with translations -->
  <?php if($message != ""): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
      <span><?php echo $message_type == 'success' ? '✅' : '⚠️'; ?></span>
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  
  <!-- Search Section with translations -->
  <div class="search-section">
    <form method="GET" action="dashboard.php" class="search-form">
      <div class="search-wrapper">
        <span class="search-icon">🔍</span>
        <input type="text" 
               name="search" 
               class="search-input" 
               placeholder="<?php echo __('search_books'); ?>"
               value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <div class="category-wrapper">
        <select name="category" class="category-select">
          <option value="">📑 <?php echo __('all_categories'); ?></option>
          <?php 
          mysqli_data_seek($categories, 0);
          while($cat = mysqli_fetch_assoc($categories)): 
          ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
              <?php echo $cat['name']; ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <button type="submit" class="search-btn">
        <span>🔍</span> <?php echo __('search'); ?>
      </button>
      <?php if(!empty($search) || !empty($category_filter)): ?>
        <a href="dashboard.php" style="display: flex; align-items: center; padding: 14px 20px; color: #666; text-decoration: none;">
          ✖ <?php echo __('clear'); ?>
        </a>
      <?php endif; ?>
    </form>
  </div>
  
  <!-- Books Grid with translations -->
  <?php if(mysqli_num_rows($books) > 0): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h3 style="color: #a61d21; display: flex; align-items: center; gap: 8px;">
        <span>📖</span> <?php echo __('available_books'); ?> (<?php echo mysqli_num_rows($books); ?>)
      </h3>
    </div>
    
    <div class="book-grid">
      <?php while($book = mysqli_fetch_assoc($books)): ?>
        <div class="book-card">
          <span class="book-category">📑 <?php echo $book['category_name']; ?></span>
          <h3 class="book-title"><?php echo htmlspecialchars($book['book_name']); ?></h3>
          <div class="book-writer">
            <span>✍️</span> <?php echo htmlspecialchars($book['writer_name']); ?>
          </div>
          <div class="book-details">
            <span class="book-price">₹<?php echo number_format($book['price'], 2); ?></span>
            <?php if($book['quantity'] > 5): ?>
              <span class="book-stock in-stock">📊 <?php echo $book['quantity']; ?> <?php echo __('in_stock'); ?></span>
            <?php elseif($book['quantity'] > 0): ?>
              <span class="book-stock low-stock">⚠️ <?php echo __('only_left'); ?> <?php echo $book['quantity']; ?></span>
            <?php else: ?>
              <span class="book-stock" style="background: #dc354520; color: #dc3545;">❌ <?php echo __('out_of_stock'); ?></span>
            <?php endif; ?>
          </div>
          <div style="display: flex; gap: 8px;">
            <?php if($book['quantity'] > 0): ?>
              <button onclick="openBorrowModal(<?php echo $book['id']; ?>, '<?php echo addslashes($book['book_name']); ?>')" class="borrow-btn">
                <span>📤</span> <?php echo __('borrow'); ?>
              </button>
            <?php else: ?>
              <button class="borrow-btn" disabled>
                <span>❌</span> <?php echo __('out_of_stock'); ?>
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <span>📚🔍</span>
      <h3 style="margin-bottom: 8px;"><?php echo __('no_books_found'); ?></h3>
      <p style="color: #666;">
        <?php if(!empty($search) || !empty($category_filter)): ?>
          <?php echo __('try_adjusting_search'); ?>
        <?php else: ?>
          <?php echo __('no_books_available'); ?>
        <?php endif; ?>
      </p>
    </div>
  <?php endif; ?>
  
</div>

<!-- Borrow Modal with translations -->
<div id="borrowModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>
        <span>📤</span> <?php echo __('borrow_book'); ?>
      </h3>
      <button class="close-btn" onclick="closeBorrowModal()">&times;</button>
    </div>
    
    <form method="post" action="dashboard.php" id="borrowForm">
      <input type="hidden" name="book_id" id="book_id">
      
      <div class="form-group">
        <label>📘 <?php echo __('book_title'); ?></label>
        <input type="text" id="book_title_display" class="form-control" readonly style="background: #f5f5f5;">
      </div>
      
      <div class="form-group">
        <label>👤 <?php echo __('borrower_name'); ?></label>
        <input type="text" name="borrower_name" class="form-control" placeholder="<?php echo __('borrower_name'); ?>" required autocomplete="off" list="borrowerList">
        <datalist id="borrowerList">
          <?php while($b = mysqli_fetch_assoc($borrowers)): ?>
            <option value="<?php echo $b['borrower_name']; ?>">
          <?php endwhile; ?>
        </datalist>
      </div>
      
      <div class="form-group">
        <label>🏫 <?php echo __('class'); ?></label>
        <input type="text" name="class" class="form-control" placeholder="<?php echo __('class'); ?>" required>
      </div>
      
      <div class="form-group">
        <label>📱 <?php echo __('phone_number'); ?></label>
        <input type="text" name="phone" class="form-control" placeholder="<?php echo __('phone_number'); ?>" required>
      </div>
      
      <div style="background: #FFF8E1; padding: 15px; border-radius: 16px; margin-bottom: 15px; border-left: 4px solid #fff200;">
        <div style="display: flex; align-items: center; gap: 8px; color: #a61d21; font-weight: 600; margin-bottom: 5px;">
          <span>📅</span> <?php echo __('borrow_period'); ?>
        </div>
        <p style="color: #666; font-size: 14px;">
          <?php echo __('borrow_period_message'); ?>
        </p>
      </div>
      
      <button type="submit" name="borrow" class="submit-btn">
        <span>✅</span> <?php echo __('confirm_borrow'); ?>
      </button>
    </form>
  </div>
</div>

<!-- Success Modal with translations -->
<div id="successModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 400px; text-align: center;">
    <div class="success-icon">✅</div>
    <h3 style="color: #a61d21; margin-bottom: 10px;"><?php echo __('book_borrowed_success'); ?></h3>
    
    <div class="success-details">
      <p style="margin-bottom: 8px;"><strong>📘 <?php echo __('book_title'); ?>:</strong> <span id="successBookTitle"></span></p>
      <p style="margin-bottom: 8px;"><strong>👤 <?php echo __('borrower_name'); ?>:</strong> <span id="successBorrower"></span></p>
      <p style="margin-bottom: 8px;"><strong>📅 <?php echo __('due_date'); ?>:</strong> <span id="successDueDate"></span></p>
    </div>
    
    <p style="color: #666; margin-bottom: 20px;">
      <?php echo __('redirecting'); ?> <span id="countdown" class="countdown">5</span> <?php echo __('seconds'); ?>...
    </p>
    
    <div style="display: flex; gap: 10px;">
      <a href="transactions.php" class="submit-btn" style="flex: 1; background: #a61d21; margin-top: 0;">
        <?php echo __('transactions'); ?> →
      </a>
    </div>
  </div>
</div>

<script>
// Borrow Modal Functions
function openBorrowModal(bookId, bookTitle) {
  document.getElementById('borrowModal').classList.add('active');
  document.getElementById('book_id').value = bookId;
  document.getElementById('book_title_display').value = bookTitle;
}

function closeBorrowModal() {
  document.getElementById('borrowModal').classList.remove('active');
}

// Success Modal Function with 5 Second Timer
function showSuccessModal(bookTitle, borrower, dueDate) {
  document.getElementById('successBookTitle').innerHTML = bookTitle;
  document.getElementById('successBorrower').innerHTML = borrower;
  document.getElementById('successDueDate').innerHTML = dueDate;
  document.getElementById('successModal').style.display = 'flex';
  
  let seconds = 5;
  const countdown = setInterval(function() {
    seconds--;
    document.getElementById('countdown').innerHTML = seconds;
    if(seconds <= 0) {
      clearInterval(countdown);
      window.location.href = 'transactions.php';
    }
  }, 1000);
}

// Close modal when clicking outside
window.onclick = function(event) {
  const borrowModal = document.getElementById('borrowModal');
  const successModal = document.getElementById('successModal');
  if (event.target == borrowModal) {
    closeBorrowModal();
  }
  if (event.target == successModal) {
    successModal.style.display = 'none';
  }
}
</script>

</body>
</html>