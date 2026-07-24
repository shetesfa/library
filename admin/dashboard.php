<?php
include("../config.php");
include("../lang.php");
check_login('admin');

// Handle book deletion
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($conn,"DELETE FROM books WHERE id=$id");
    header("Location: dashboard.php");
    exit();
}

// Build search query with category filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

$where_sql = '';
if(!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_sql = "WHERE (b.book_name LIKE '%$search%' 
                   OR b.writer_name LIKE '%$search%' 
                   OR c.name LIKE '%$search%')";
}

if(!empty($category_filter)) {
    $category_filter_escaped = intval($category_filter);
    if(empty($where_sql)) {
        $where_sql = "WHERE b.category_id = $category_filter_escaped";
    } else {
        $where_sql .= " AND b.category_id = $category_filter_escaped";
    }
}

// Get books with search and category filter
$books = mysqli_query($conn, "SELECT b.id, b.book_name, b.writer_name, b.published_year, b.price, b.quantity, b.book_code, b.registered_by, b.registration_date, c.name as category_name 
    FROM books b 
    JOIN categories c ON b.category_id = c.id 
    $where_sql 
    ORDER BY b.id DESC");

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

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
<title><?php echo __('admin_dashboard'); ?> - <?php echo __('site_title'); ?></title>
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
  opacity: 0.12;
  z-index: -1;
}

.logo-1 { top: -200px; left: -200px; }
.logo-2 { bottom: -250px; right: -150px; width: 500px; height: 500px; }
.logo-3 { top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 800px; opacity: 0.06; }

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
  max-width: 1400px;
  margin: 20px auto;
  padding: 15px;
  animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

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

.stat-info small {
  color: #666;
  font-size: 11px;
}

.management-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 15px;
  margin-bottom: 25px;
}

.card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 18px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 10px 25px rgba(166, 29, 33, 0.1);
  position: relative;
  overflow: hidden;
}

.card::before {
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

.card h3 {
  color: #a61d21;
  font-size: 18px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 8px;
  border-bottom: 2px solid #f0f0f0;
}

.button-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

a.button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  background: linear-gradient(135deg, #a61d21 0%, #7e191b 100%);
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  border: 2px solid transparent;
  box-shadow: 0 3px 10px rgba(166, 29, 33, 0.2);
  transition: all 0.3s ease;
  flex: 1 1 calc(50% - 10px);
  min-width: 130px;
}

a.button:hover {
  transform: translateY(-2px);
  border-color: #fff200;
  box-shadow: 0 8px 20px rgba(166, 29, 33, 0.3);
}

.books-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 20px;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(166, 29, 33, 0.1);
  margin-top: 15px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
  gap: 12px;
}

.section-header h2 {
  color: #a61d21;
  font-size: 22px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.search-container {
  background: linear-gradient(135deg, #fff20010, #f7941d10);
  padding: 18px;
  border-radius: 16px;
  margin-bottom: 20px;
  border: 2px solid #fff20030;
}

.search-box {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.search-input-wrapper {
  flex: 2;
  position: relative;
  min-width: 200px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #f7941d;
}

.search-input {
  width: 100%;
  padding: 12px 18px 12px 45px;
  border: 2px solid #fff200;
  border-radius: 40px;
  font-size: 15px;
  background: white;
  box-shadow: 0 3px 12px rgba(255, 242, 0, 0.2);
}

.search-input:focus {
  outline: none;
  border-color: #f7941d;
  box-shadow: 0 5px 20px rgba(247, 148, 29, 0.3);
}

.category-filter {
  flex: 1;
  min-width: 180px;
}

.category-select {
  width: 100%;
  padding: 12px 20px;
  border: 2px solid #fff200;
  border-radius: 40px;
  font-size: 15px;
  background: white;
  cursor: pointer;
}

.search-button {
  padding: 12px 28px;
  background: linear-gradient(135deg, #f7941d, #e07e1a);
  color: white;
  border: none;
  border-radius: 40px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 2px solid #fff200;
  transition: 0.3s;
}

.search-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(247, 148, 29, 0.4);
}

.reset-button {
  padding: 10px 20px;
  background: #666;
  color: white;
  text-decoration: none;
  border-radius: 40px;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: 0.3s;
}

.reset-button:hover {
  background: #555;
  transform: translateY(-2px);
}

.stats-badge {
  background: #f7941d;
  color: white;
  padding: 5px 16px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.table-responsive {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #f0f0f0;
  -webkit-overflow-scrolling: touch;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  min-width: 800px;
}

th {
  background: linear-gradient(135deg, #a61d21, #7e191b);
  color: white;
  padding: 10px 10px;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  text-align: left;
  cursor: pointer;
}

td {
  padding: 10px 10px;
  border-bottom: 1px solid #f0f0f0;
  color: #333;
  font-size: 14px;
}

tr:hover td {
  background: rgba(255, 242, 0, 0.05);
}

.book-clickable {
  color: #a61d21;
  cursor: pointer;
  font-weight: 600;
  text-decoration: underline;
  transition: 0.3s;
}

.book-clickable:hover {
  color: #f7941d;
}

.category-tag {
  background: #f7941d20;
  padding: 4px 12px;
  border-radius: 20px;
  color: #f7941d;
  font-weight: 600;
  font-size: 12px;
  display: inline-block;
}

.code-badge {
  background: #a61d21;
  color: white;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.quantity-badge {
  padding: 4px 12px;
  border-radius: 20px;
  color: white;
  font-weight: 600;
  font-size: 12px;
  display: inline-block;
}

.quantity-instock {
  background: #28a745;
}

.quantity-outstock {
  background: #dc3545;
}

.delete-btn {
  padding: 5px 12px;
  background: #ff4444;
  color: white;
  text-decoration: none;
  border-radius: 5px;
  font-size: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: 0.3s;
  border: none;
  cursor: pointer;
}

.delete-btn:hover {
  background: #cc0000;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
}

/* Modal Styles */
.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.6);
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
  max-width: 600px;
  width: 100%;
  position: relative;
  border-top: 5px solid #fff200;
  box-shadow: 0 20px 40px rgba(166, 29, 33, 0.2);
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
}

.modal-header h3 {
  color: #a61d21;
  font-size: 22px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #999;
  padding: 8px;
  border-radius: 50%;
  transition: 0.3s;
}

.close-btn:hover {
  color: #a61d21;
  background: #fff20020;
}

.book-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.book-detail-item {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 12px;
  border-left: 4px solid #f7941d;
}

.book-detail-item.full-width {
  grid-column: 1 / -1;
}

.book-detail-label {
  font-size: 12px;
  color: #999;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 5px;
}

.book-detail-value {
  font-size: 16px;
  color: #333;
  font-weight: 600;
}

.book-detail-code {
  display: inline-block;
  background: #a61d21;
  color: white;
  padding: 8px 20px;
  border-radius: 30px;
  font-size: 18px;
  font-weight: 700;
  margin: 10px 0;
}

.empty-message {
  text-align: center;
  padding: 30px !important;
  color: #999;
  font-size: 16px;
}

.empty-message span {
  font-size: 40px;
  display: block;
  margin-bottom: 10px;
  opacity: 0.5;
}

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
  
  .container {
    padding: 12px;
  }
  
  .management-grid {
    grid-template-columns: 1fr;
  }
  
  .button-group {
    flex-direction: column;
  }
  
  a.button {
    width: 100%;
  }
  
  .search-box {
    flex-direction: column;
  }
  
  .search-input-wrapper,
  .category-filter {
    width: 100%;
  }
  
  .search-button, .reset-button {
    width: 100%;
    justify-content: center;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .book-detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
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
    <h2><?php echo __('admin_dashboard'); ?></h2>
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
  
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">📚</div>
      <div class="stat-info">
        <h4><?php echo __('total_titles'); ?></h4>
        <p><?php echo $total_titles ?: 0; ?></p>
        <small><?php echo __('different_books'); ?></small>
      </div>
    </div>
    
    
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-info">
        <h4><?php echo __('available_now'); ?></h4>
        <p><?php echo $current_available ?: 0; ?></p>
        <small><?php echo __('ready_to_borrow'); ?></small>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📖</div>
      <div class="stat-info">
        <h4><?php echo __('borrowed'); ?></h4>
        <p><?php echo $borrowed_count ?: 0; ?></p>
        <small><?php echo __('currently_out'); ?></small>
      </div>
    </div>
  </div>
  
  <div class="management-grid">
    <div class="card">
      <h3>👥 <?php echo __('manage_users'); ?></h3>
      <div class="button-group">
        <a href="add_user.php" class="button">👥 <?php echo __('manage_users'); ?></a>
      </div>
    </div>
    <div class="card">
      <h3>📚 <?php echo __('manage_books'); ?></h3>
      <div class="button-group">
        <a href="manage_books.php" class="button">📚 <?php echo __('books_categories'); ?></a>
        <a href="borrow_history.php" class="button">📋 <?php echo __('borrow_history'); ?></a>
      </div>
    </div>
  </div>

  <div class="search-container">
    <form method="GET" action="dashboard.php">
      <div class="search-box">
        <div class="search-input-wrapper">
          <span class="search-icon">🔍</span>
          <input type="text" 
                 name="search" 
                 class="search-input" 
                 placeholder="<?php echo __('search_books'); ?>"
                 value="<?php echo htmlspecialchars($search); ?>"
                 autocomplete="off">
        </div>
        <div class="category-filter">
          <select name="category" class="category-select">
            <option value="">📑 <?php echo __('all_categories'); ?></option>
            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
              <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo $cat['name']; ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <button type="submit" class="search-button">
          <span>🔍</span> <?php echo __('search'); ?>
        </button>
        <?php if(!empty($search) || !empty($category_filter)): ?>
          <a href="dashboard.php" class="reset-button">
            <span>✖</span> <?php echo __('clear'); ?>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="books-section">
    <div class="section-header">
      <h2>📚 <?php echo __('books_inventory'); ?></h2>
      <span class="stats-badge">
        📊 <?php echo mysqli_num_rows($books); ?> <?php echo __('books_found'); ?>
      </span>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>🏷️ <?php echo __('code'); ?></th>
            <th><?php echo __('book_name'); ?></th>
            <th><?php echo __('writer'); ?></th>
            <th><?php echo __('category'); ?></th>
            <th><?php echo __('price'); ?></th>
            <th><?php echo __('quantity'); ?></th>
            <th><?php echo __('registered_by'); ?></th>
            <th><?php echo __('actions'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if(mysqli_num_rows($books) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($books)): ?>
            <tr>
              <td><span class="code-badge"><?php echo $row['book_code']; ?></span></td>
              <td>
                <span class="book-clickable" onclick="openBookDetail(<?php echo $row['id']; ?>)">
                  <?php echo htmlspecialchars($row['book_name']); ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($row['writer_name']); ?></td>
              <td>
                <span class="category-tag">
                  📑 <?php echo $row['category_name']; ?>
                </span>
              </td>
              <td><?php echo number_format($row['price'], 2); ?></td>
              <td>
                <span class="quantity-badge <?php echo $row['quantity'] > 0 ? 'quantity-instock' : 'quantity-outstock'; ?>">
                  <?php echo $row['quantity']; ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($row['registered_by'] ?: '-'); ?></td>
              <td>
                <a href="dashboard.php?delete=<?php echo $row['id']; ?>" 
                   onclick="return confirm('<?php echo __('confirm_delete'); ?>')" 
                   class="delete-btn">
                  🗑️
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="empty-message">
                <span>📚🔍</span><br>
                <?php if(!empty($search) || !empty($category_filter)): ?>
                  <?php echo __('no_books_search'); ?>
                <?php else: ?>
                  <?php echo __('no_books'); ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Book Detail Modal -->
<div id="bookDetailModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>
        <span>📘</span> <?php echo __('የመጽሀፉ ሙሉ መረጃ'); ?>
      </h3>
      <button class="close-btn" onclick="closeBookDetail()">&times;</button>
    </div>
    <div id="bookDetailContent">
      <p style="text-align: center; color: #999;"><?php echo __('loading'); ?>...</p>
    </div>
  </div>
</div>

<script>
function openBookDetail(bookId) {
    document.getElementById('bookDetailModal').classList.add('active');
    document.getElementById('bookDetailContent').innerHTML = '<p style="text-align: center; color: #999;"><?php echo __("loading"); ?>...</p>';
    
    // Fetch book details via AJAX
    fetch('get_book_detail.php?id=' + bookId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('bookDetailContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('bookDetailContent').innerHTML = '<p style="text-align: center; color: red;">Error loading details</p>';
        });
}

function closeBookDetail() {
    document.getElementById('bookDetailModal').classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('bookDetailModal');
    if (event.target == modal) {
        closeBookDetail();
    }
}
</script>

</body>
</html>