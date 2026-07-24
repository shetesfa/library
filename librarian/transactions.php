<?php
include("../config.php");
include("../lang.php");


$message = "";
$message_type = "";

// Handle Return Book
if(isset($_POST['return'])){
    $id = intval($_POST['borrow_id']);
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT book_id FROM borrow_records WHERE id=$id"));
    $book_id = $row['book_id'];

    mysqli_query($conn,"UPDATE borrow_records SET status='returned', return_date='".date("Y-m-d")."' WHERE id=$id");
    mysqli_query($conn,"UPDATE books SET quantity=quantity+1 WHERE id=$book_id");
    $message = "✅ " . __('book_returned');
    $message_type = "success";
}

// Search History
$search = isset($_GET['search']) ? $_GET['search'] : '';

if(!empty($search)){
    $search = mysqli_real_escape_string($conn,$search);
    $records = mysqli_query($conn,"SELECT br.*, b.book_name, b.writer_name 
    FROM borrow_records br 
    JOIN books b ON br.book_id=b.id
    WHERE br.borrower_name LIKE '%$search%' 
       OR b.book_name LIKE '%$search%' 
       OR br.status LIKE '%$search%'
       OR br.class LIKE '%$search%'
    ORDER BY br.id DESC");
}else{
    $records = mysqli_query($conn,"SELECT br.*, b.book_name, b.writer_name 
    FROM borrow_records br 
    JOIN books b ON br.book_id=b.id
    ORDER BY br.id DESC LIMIT 100");
}

// Get currently borrowed books
$borrowed_books = mysqli_query($conn,"SELECT br.id, br.borrower_name, br.class, br.borrow_date, br.due_date, b.book_name 
FROM borrow_records br 
JOIN books b ON br.book_id=b.id 
WHERE br.status='borrowed'
ORDER BY br.due_date ASC");

// Stats
$total_borrowed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='borrowed'"))['total'];
$total_returned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='returned'"))['total'];
$total_transactions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records"))['total'];

function is_overdue($due_date) {
    return (strtotime($due_date) < strtotime(date("Y-m-d")));
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('transactions'); ?> - <?php echo __('site_title'); ?></title>
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
  max-width: 1400px;
  margin: 20px auto;
  padding: 15px;
  animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

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

.borrowed-section {
  background: white;
  border-radius: 20px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  border: 1px solid #eee;
}

.section-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-title h3 {
  color: #a61d21;
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.borrowed-count {
  background: #a61d21;
  color: white;
  padding: 6px 16px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
}

.borrowed-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.borrowed-card {
  background: #fafafa;
  border-radius: 16px;
  padding: 20px;
  border-left: 5px solid #fff200;
  transition: 0.3s;
}

.borrowed-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(166, 29, 33, 0.1);
}

.borrowed-card.overdue {
  border-left-color: #dc3545;
  background: #ffebee;
}

.borrower-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.borrower-avatar {
  width: 45px;
  height: 45px;
  background: #a61d2120;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: #a61d21;
}

.borrower-details h4 {
  color: #333;
  font-size: 18px;
  margin-bottom: 4px;
}

.borrower-details p {
  color: #666;
  font-size: 13px;
}

.book-detail {
  background: white;
  padding: 12px;
  border-radius: 12px;
  margin: 12px 0;
}

.book-title {
  font-weight: 700;
  color: #333;
  margin-bottom: 5px;
}

.due-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.due-normal {
  background: #fff20020;
  color: #856404;
}

.due-overdue {
  background: #dc354520;
  color: #dc3545;
}

.return-btn {
  width: 100%;
  padding: 12px;
  background: #28a745;
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
  margin-top: 12px;
}

.return-btn:hover {
  background: #218838;
  transform: scale(1.02);
}

.history-section {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.search-box {
  margin-bottom: 20px;
}

.search-form {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-wrapper {
  flex: 1;
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
  padding: 12px 16px 12px 48px;
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

.search-btn {
  padding: 12px 24px;
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

.table-responsive {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #f0f0f0;
  margin-top: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 900px;
}

th {
  background: #a61d21;
  color: white;
  padding: 12px 10px;
  font-size: 14px;
  font-weight: 600;
  text-align: left;
}

td {
  padding: 10px;
  border-bottom: 1px solid #f0f0f0;
  color: #333;
  font-size: 14px;
}

tr:hover td {
  background: #fff8e1;
}

.status-badge {
  padding: 5px 12px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.status-borrowed {
  background: #fff20020;
  color: #856404;
}

.status-returned {
  background: #28a74520;
  color: #28a745;
}

.status-overdue {
  background: #dc354520;
  color: #dc3545;
}

.overdue-text {
  color: #dc3545;
  font-weight: 700;
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

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.empty-state span {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
  opacity: 0.5;
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
  
  .nav-tabs {
    flex-direction: column;
    border-radius: 20px;
  }
  
  .borrowed-grid {
    grid-template-columns: 1fr;
  }
  
  .search-form {
    flex-direction: column;
  }
  
  .search-wrapper {
    width: 100%;
  }
  
  .search-btn {
    width: 100%;
    justify-content: center;
  }
  
  .section-title {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
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
    <h2><?php echo __('transactions'); ?></h2>
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
  
  <div class="nav-tabs">
    <a href="dashboard.php" class="tab">
      <span>📚</span> <?php echo __('books'); ?>
    </a>
    <a href="transactions.php" class="tab active">
      <span>🔄</span> <?php echo __('transactions'); ?>
    </a>
  </div>
  
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">🔄</div>
      <div class="stat-info">
        <h4><?php echo __('total_transactions'); ?></h4>
        <p><?php echo $total_transactions ?: 0; ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📖</div>
      <div class="stat-info">
        <h4><?php echo __('borrowed'); ?></h4>
        <p><?php echo $total_borrowed ?: 0; ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-info">
        <h4><?php echo __('returned'); ?></h4>
        <p><?php echo $total_returned ?: 0; ?></p>
      </div>
    </div>
  </div>
  
  <?php if($message != ""): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
      <span><?php echo $message_type == 'success' ? '✅' : '⚠️'; ?></span>
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  
  <div class="borrowed-section">
    <div class="section-title">
      <h3>
        <span>📖</span> <?php echo __('currently_borrowed'); ?>
      </h3>
      <span class="borrowed-count"><?php echo mysqli_num_rows($borrowed_books); ?> <?php echo __('books'); ?></span>
    </div>
    
    <?php if(mysqli_num_rows($borrowed_books) > 0): ?>
      <div class="borrowed-grid">
        <?php while($borrowed = mysqli_fetch_assoc($borrowed_books)): 
          $overdue = is_overdue($borrowed['due_date']);
        ?>
          <div class="borrowed-card <?php echo $overdue ? 'overdue' : ''; ?>">
            <div class="borrower-info">
              <div class="borrower-avatar">
                <?php echo strtoupper(substr($borrowed['borrower_name'], 0, 1)); ?>
              </div>
              <div class="borrower-details">
                <h4><?php echo htmlspecialchars($borrowed['borrower_name']); ?></h4>
                <p><?php echo $borrowed['class']; ?></p>
              </div>
            </div>
            
            <div class="book-detail">
              <div class="book-title">📘 <?php echo htmlspecialchars($borrowed['book_name']); ?></div>
              <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                <span style="color: #666; font-size: 13px;">
                  📅 <?php echo __('borrow_date'); ?>: <?php echo formatDate($borrowed['borrow_date']); ?>
                </span>
                <span class="due-badge <?php echo $overdue ? 'due-overdue' : 'due-normal'; ?>">
                  <?php if($overdue): ?>
                    ⚠️ <?php echo __('overdue'); ?>
                  <?php else: ?>
                    ⏰ <?php echo __('due_date'); ?>: <?php echo formatDate($borrowed['due_date']); ?>
                  <?php endif; ?>
                </span>
              </div>
            </div>
            
            <form method="post">
              <input type="hidden" name="borrow_id" value="<?php echo $borrowed['id']; ?>">
              <button type="submit" name="return" class="return-btn" onclick="return confirm('<?php echo __('confirm_return'); ?>')">
                <span>🔄</span> <?php echo __('return_book'); ?>
              </button>
            </form>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state" style="background: none;">
        <span>📖</span>
        <p><?php echo __('no_borrowed_books'); ?></p>
      </div>
    <?php endif; ?>
  </div>
  
  <div class="history-section">
    <div class="section-title">
      <h3>
        <span>📋</span> <?php echo __('borrow_history'); ?>
      </h3>
    </div>
    
    <div class="search-box">
      <form method="GET" action="transactions.php" class="search-form">
        <div class="search-wrapper">
          <span class="search-icon">🔍</span>
          <input type="text" 
                 name="search" 
                 class="search-input" 
                 placeholder="<?php echo __('search_transactions'); ?>"
                 value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="search-btn">
          <span>🔍</span> <?php echo __('search'); ?>
        </button>
        <?php if(!empty($search)): ?>
          <a href="transactions.php" style="display: flex; align-items: center; padding: 12px 20px; color: #666; text-decoration: none;">
            ✖ <?php echo __('clear'); ?>
          </a>
        <?php endif; ?>
      </form>
    </div>
    
    <?php if(mysqli_num_rows($records) > 0): ?>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th><?php echo __('borrower_name'); ?></th>
              <th><?php echo __('class'); ?></th>
              <th><?php echo __('phone'); ?></th>
              <th><?php echo __('book'); ?></th>
              <th><?php echo __('borrow_date'); ?></th>
              <th><?php echo __('due_date'); ?></th>
              <th><?php echo __('return_date'); ?></th>
              <th><?php echo __('status'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = mysqli_fetch_assoc($records)): 
              $status = $row['status'];
              $status_class = 'status-borrowed';
              $status_text = __('borrowed');
              
              if($status == 'returned') {
                $status_class = 'status-returned';
                $status_text = __('returned');
              } elseif($status == 'borrowed' && is_overdue($row['due_date'])) {
                $status_class = 'status-overdue';
                $status_text = __('overdue');
              }
            ?>
              <tr>
                <td><strong>#<?php echo $row['id']; ?></strong></td>
                <td><?php echo htmlspecialchars($row['borrower_name']); ?></td>
                <td><?php echo $row['class']; ?></td>
                <td><?php echo $row['phone_number']; ?></td>
                <td><?php echo htmlspecialchars($row['book_name']); ?></td>
                <td><?php echo formatDate($row['borrow_date']); ?></td>
                <td class="<?php echo ($status == 'borrowed' && is_overdue($row['due_date'])) ? 'overdue-text' : ''; ?>">
                  <?php echo formatDate($row['due_date']); ?>
                </td>
                <td><?php echo $row['return_date'] ? formatDate($row['return_date']) : '-'; ?></td>
                <td>
                  <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo $status_text; ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <span>📋</span>
        <p><?php echo !empty($search) ? __('no_transactions_search') : __('no_transactions'); ?></p>
      </div>
    <?php endif; ?>
    
    <a href="dashboard.php" class="back-btn">
      <span>←</span> <?php echo __('back_to_dashboard'); ?>
    </a>
  </div>
  
</div>

</body>
</html>