<?php
include("../config.php");
include("../lang.php");
check_login('admin');

// Ethiopian Months
$ethiopian_months = [
    1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ኅዳር', 4 => 'ታኅሳስ',
    5 => 'ጥር', 6 => 'የካቲት', 7 => 'መጋቢት', 8 => 'ሚያዝያ',
    9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ',
    13 => 'ጳጉሜ'
];

// Get filter values
$month_filter = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year_filter = isset($_GET['year']) ? intval($_GET['year']) : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query conditions
$conditions = [];

// Month filter (approximate conversion)
if ($month_filter > 0 && $year_filter > 0) {
    // Approximate Gregorian date range for Ethiopian month
    $gregorian_start_month = $month_filter + 8;
    $gregorian_start_year = $year_filter + 8;
    if ($gregorian_start_month > 12) {
        $gregorian_start_month -= 12;
        $gregorian_start_year++;
    }
    
    $start_date = "$gregorian_start_year-" . str_pad($gregorian_start_month, 2, '0', STR_PAD_LEFT) . "-01";
    if ($gregorian_start_month == 12) {
        $end_date = ($gregorian_start_year + 1) . "-01-01";
    } else {
        $end_date = "$gregorian_start_year-" . str_pad($gregorian_start_month + 1, 2, '0', STR_PAD_LEFT) . "-01";
    }
    $conditions[] = "br.borrow_date >= '$start_date' AND br.borrow_date < '$end_date'";
}

if (!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $conditions[] = "br.status = '$status_filter'";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(br.borrower_name LIKE '%$search%' OR b.book_name LIKE '%$search%' OR br.class LIKE '%$search%')";
}

$where_sql = '';
if (!empty($conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $conditions);
}

// Get records
$records = mysqli_query($conn, "SELECT br.*, b.book_name, b.book_code, b.writer_name 
    FROM borrow_records br 
    JOIN books b ON br.book_id = b.id 
    $where_sql 
    ORDER BY br.id DESC");

// Stats
$total_borrowed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='borrowed'"))['total'];
$total_returned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='returned'"))['total'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='borrowed' AND due_date < CURDATE()"))['total'];

// Handle Excel Download
if (isset($_GET['download_excel'])) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=borrow_history_' . date('Y-m-d') . '.xls');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>የመጽሐፍ ኮድ</th><th>የመጽሐፍ ስም</th><th>ደራሲ</th><th>ተዋሳይ</th><th>ክፍል</th><th>ስልክ</th><th>የተዋሰበት ቀን</th><th>የሚመለስበት ቀን</th><th>የተመለሰበት ቀን</th><th>ሁኔታ</th></tr>';
    
    mysqli_data_seek($records, 0);
    while ($row = mysqli_fetch_assoc($records)) {
        $status = $row['status'];
        $status_text = ($status == 'borrowed' && strtotime($row['due_date']) < strtotime(date('Y-m-d'))) ? 'ጊዜ አልፎበታል' : ($status == 'returned' ? 'ተመልሷል' : 'ተውሷል');
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['book_code'] . '</td>';
        echo '<td>' . $row['book_name'] . '</td>';
        echo '<td>' . $row['writer_name'] . '</td>';
        echo '<td>' . $row['borrower_name'] . '</td>';
        echo '<td>' . $row['class'] . '</td>';
        echo '<td>' . $row['phone_number'] . '</td>';
        echo '<td>' . $row['borrow_date'] . '</td>';
        echo '<td>' . $row['due_date'] . '</td>';
        echo '<td>' . ($row['return_date'] ?: '—') . '</td>';
        echo '<td>' . $status_text . '</td>';
        echo '</tr>';
    }
    echo '</table></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo __('borrow_history'); ?> - <?php echo __('site_title'); ?></title>
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
  flex-wrap: wrap;
  gap: 10px;
}

.logo-area {
  display: flex;
  align-items: center;
  gap: 12px;
}

header img { height: 40px; width: auto; }

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

.lang-btn.active { background: #fff200; color: #a61d21; }
.lang-btn:hover:not(.active) { background: rgba(255,255,255,0.2); }

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

.logout-btn a:hover { background: #fff200; color: #a61d21; }

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
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 25px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.95);
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

.stat-info h4 { color: #666; font-size: 14px; margin-bottom: 5px; }
.stat-info p { color: #a61d21; font-size: 24px; font-weight: 700; }

.filter-section {
  background: white;
  padding: 20px;
  border-radius: 20px;
  margin-bottom: 25px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  border: 1px solid #eee;
}

.filter-title {
  font-size: 18px;
  font-weight: 700;
  color: #a61d21;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-form {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  flex: 1;
  min-width: 150px;
}

.filter-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #666;
  margin-bottom: 5px;
}

.filter-select, .filter-input {
  width: 100%;
  padding: 10px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 30px;
  font-size: 14px;
  transition: 0.3s;
}

.filter-select:focus, .filter-input:focus {
  border-color: #fff200;
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 242, 0, 0.1);
}

.filter-btn {
  padding: 10px 24px;
  background: #a61d21;
  color: white;
  border: none;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 2px solid #fff200;
  transition: 0.3s;
  white-space: nowrap;
}

.filter-btn:hover { background: #7e191b; transform: translateY(-2px); }

.filter-btn.download {
  background: #28a745;
}

.filter-btn.download:hover { background: #218838; }

.filter-btn.print {
  background: #f7941d;
}

.filter-btn.print:hover { background: #e07e1a; }

.filter-btn.reset {
  background: #666;
}

.filter-btn.reset:hover { background: #555; }

.history-section {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.section-header h3 {
  color: #a61d21;
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.records-count {
  background: #f7941d;
  color: white;
  padding: 6px 16px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
}

.table-responsive {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #f0f0f0;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1000px;
}

th {
  background: linear-gradient(135deg, #a61d21, #7e191b);
  color: white;
  padding: 12px 10px;
  font-size: 13px;
  font-weight: 600;
  text-align: left;
}

td {
  padding: 10px;
  border-bottom: 1px solid #f0f0f0;
  color: #333;
  font-size: 14px;
}

tr:hover td { background: #fff8e1; }

.status-badge {
  padding: 5px 12px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.status-borrowed { background: #fff20020; color: #856404; }
.status-returned { background: #28a74520; color: #28a745; }
.status-overdue { background: #dc354520; color: #dc3545; }

.overdue-text { color: #dc3545; font-weight: 700; }

.code-badge {
  background: #a61d2120;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
  color: #a61d21;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.empty-state span { font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.5; }

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

.back-btn:hover { background: #555; transform: translateY(-2px); }

@media (max-width: 768px) {
  header { flex-direction: column; gap: 12px; }
  .logo-area { flex-direction: column; text-align: center; }
  .lang-switcher { margin-right: 0; margin-bottom: 10px; }
  .filter-form { flex-direction: column; }
  .filter-group { width: 100%; }
  .filter-btn { width: 100%; justify-content: center; }
}

/* Print Styles */
@media print {
  body { background: white; }
  .logo-background, header, .stats-grid, .filter-section, .back-btn, .section-header .records-count { display: none; }
  .container { padding: 0; margin: 0; }
  .history-section { box-shadow: none; border: none; }
  table { border: 1px solid #000; }
  th { background: #ddd !important; color: #000; }
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
    <h2><?php echo __('borrow_history'); ?></h2>
  </div>
  <div style="display: flex; align-items: center;">
    <div class="lang-switcher">
      <a href="?lang=en<?php echo $month_filter ? '&month='.$month_filter : ''; ?><?php echo $year_filter ? '&year='.$year_filter : ''; ?>" class="lang-btn <?php echo $_SESSION['lang'] == 'en' ? 'active' : ''; ?>">EN</a>
      <a href="?lang=am<?php echo $month_filter ? '&month='.$month_filter : ''; ?><?php echo $year_filter ? '&year='.$year_filter : ''; ?>" class="lang-btn <?php echo $_SESSION['lang'] == 'am' ? 'active' : ''; ?>">አማ</a>
    </div>
    <div class="logout-btn">
      <a href="../auth_logout.php"><span>🚪</span> <?php echo __('logout'); ?></a>
    </div>
  </div>
</header>

<div class="container">
  
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">📖</div>
      <div class="stat-info">
        <h4><?php echo __('borrowed'); ?></h4>
        <p><?php echo $total_borrowed; ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⚠️</div>
      <div class="stat-info">
        <h4><?php echo __('overdue'); ?></h4>
        <p><?php echo $total_overdue; ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-info">
        <h4><?php echo __('returned'); ?></h4>
        <p><?php echo $total_returned; ?></p>
      </div>
    </div>
  </div>
  
  <div class="filter-section">
    <div class="filter-title">
      <span>🔍</span> የውሰት ታሪክ ማጣሪያ / Borrow History Filter
    </div>
    <form method="GET" action="borrow_history.php" class="filter-form">
      <div class="filter-group">
        <label>📅 ወር / Month (Ethiopian)</label>
        <select name="month" class="filter-select">
          <option value="">ሁሉም ወራት / All Months</option>
          <?php foreach ($ethiopian_months as $num => $name): ?>
            <option value="<?php echo $num; ?>" <?php echo $month_filter == $num ? 'selected' : ''; ?>>
              <?php echo $name; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-group">
        <label>📆 ዓመተ ምህረት / Year</label>
        <input type="number" name="year" class="filter-input" placeholder="2017" value="<?php echo $year_filter ?: ''; ?>" min="2000" max="2100">
      </div>
      <div class="filter-group">
        <label>📊 ሁኔታ / Status</label>
        <select name="status" class="filter-select">
          <option value="">ሁሉም / All</option>
          <option value="borrowed" <?php echo $status_filter == 'borrowed' ? 'selected' : ''; ?>>ተውሷል / Borrowed</option>
          <option value="returned" <?php echo $status_filter == 'returned' ? 'selected' : ''; ?>>ተመልሷል / Returned</option>
        </select>
      </div>
      <div class="filter-group">
        <label>🔍 ፍለጋ / Search</label>
        <input type="text" name="search" class="filter-input" placeholder="ስም፣ መጽሐፍ... / Name, Book..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <button type="submit" class="filter-btn">
        <span>🔍</span> አጣራ / Filter
      </button>
      <?php
        $query_params = $_GET;
        $query_params['download_excel'] = 1;
        $download_url = 'borrow_history.php?' . http_build_query($query_params);
      ?>
      <a href="<?php echo $download_url; ?>" class="filter-btn download">
        <span>📥</span> Excel
      </a>
      <button type="button" class="filter-btn print" onclick="window.print()">
        <span>🖨️</span> አትም / Print
      </button>
      <?php if($month_filter || $year_filter || $status_filter || $search): ?>
        <a href="borrow_history.php" class="filter-btn reset">
          <span>✖</span> አጽዳ / Clear
        </a>
      <?php endif; ?>
    </form>
  </div>
  
  <div class="history-section">
    <div class="section-header">
      <h3>
        <span>📋</span> የውሰት ታሪክ / Borrow History
      </h3>
      <span class="records-count"><?php echo mysqli_num_rows($records); ?> መዝገቦች / Records</span>
    </div>
    
    <?php if(mysqli_num_rows($records) > 0): ?>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>ተ.ቁ / ID</th>
              <th>📕 ኮድ</th>
              <th>📘 መጽሐፍ</th>
              <th>✍️ ደራሲ</th>
              <th>👤 ተዋሳይ</th>
              <th>🏫 ክፍል</th>
              <th>📱 ስልክ</th>
              <th>📅 የተዋሰበት</th>
              <th>⏰ የሚመለስበት</th>
              <th>✅ የተመለሰበት</th>
              <th>📊 ሁኔታ</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = mysqli_fetch_assoc($records)): 
              $status = $row['status'];
              $is_overdue = ($status == 'borrowed' && strtotime($row['due_date']) < strtotime(date('Y-m-d')));
              
              if ($is_overdue) {
                  $status_class = 'status-overdue';
                  $status_text = '⚠️ ጊዜ አልፎበታል';
              } elseif ($status == 'returned') {
                  $status_class = 'status-returned';
                  $status_text = '✅ ተመልሷል';
              } else {
                  $status_class = 'status-borrowed';
                  $status_text = '📖 ተውሷል';
              }
            ?>
              <tr>
                <td><strong>#<?php echo $row['id']; ?></strong></td>
                <td><span class="code-badge"><?php echo $row['book_code']; ?></span></td>
                <td><?php echo htmlspecialchars($row['book_name']); ?></td>
                <td><?php echo htmlspecialchars($row['writer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['borrower_name']); ?></td>
                <td><?php echo $row['class']; ?></td>
                <td><?php echo $row['phone_number']; ?></td>
                <td><?php echo $row['borrow_date']; ?></td>
                <td class="<?php echo $is_overdue ? 'overdue-text' : ''; ?>">
                  <?php echo $row['due_date']; ?>
                </td>
                <td><?php echo $row['return_date'] ?: '—'; ?></td>
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
        <p>ምንም መዝገብ አልተገኘም / No records found</p>
      </div>
    <?php endif; ?>
    
    <a href="dashboard.php" class="back-btn">
      <span>←</span> <?php echo __('back_to_dashboard'); ?>
    </a>
  </div>
  
</div>

</body>
</html>