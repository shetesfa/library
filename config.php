<?php
// config.php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Only start if not already started
}

$host = "localhost";
$user = "root";
$pass = ""; // Your decoded password
$db   = "atsede_library";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Service temporarily unavailable");
}

mysqli_set_charset($conn, "utf8mb4");

// Login check function
function check_login($required_role = '') {
    if(!isset($_SESSION['username'])) {
        header("Location: ../auth_login.php");
        exit();
    }
    if($required_role != '' && $_SESSION['role'] != $required_role) {
        header("Location: ../auth_login.php");
        exit();
    }
}

// Format date function
function formatDate($date) {
    if(empty($date) || $date == '0000-00-00') {
        return '—';
    }
    return date('d M Y', strtotime($date));
}

// Convert Gregorian to Ethiopian date
function gregorianToEthiopian($gregorianDate) {
    $date = new DateTime($gregorianDate);
    $gregYear = (int)$date->format('Y');
    $gregMonth = (int)$date->format('n');
    $gregDay = (int)$date->format('j');
    
    $ethYear = $gregYear - 7;
    
    if ($gregMonth < 9 || ($gregMonth == 9 && $gregDay < 11)) {
        $ethYear--;
    }
    
    $ethNewYear = new DateTime("$gregYear-09-11");
    if ($gregYear % 4 == 3) {
        $ethNewYear = new DateTime("$gregYear-09-12");
    }
    
    $daysDiff = $date->diff($ethNewYear)->days;
    
    if ($date < $ethNewYear) {
        $ethNewYear = new DateTime(($gregYear-1) . "-09-11");
        if (($gregYear-1) % 4 == 3) {
            $ethNewYear = new DateTime(($gregYear-1) . "-09-12");
        }
        $daysDiff = $date->diff($ethNewYear)->days;
    }
    
    $ethMonth = floor($daysDiff / 30) + 1;
    $ethDay = ($daysDiff % 30) + 1;
    
    if ($ethMonth > 13) {
        $ethMonth = 13;
        $ethDay = min($ethDay, 6);
    }
    
    $amharicMonths = [
        1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ኅዳር', 4 => 'ታኅሣሥ',
        5 => 'ጥር', 6 => 'የካቲት', 7 => 'መጋቢት', 8 => 'ሚያዝያ',
        9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ', 13 => 'ጳጉሜ'
    ];
    
    $ethMonthName = $amharicMonths[$ethMonth] ?? '';
    
    return $ethDay . ' ' . $ethMonthName . ' ' . $ethYear;
}

function getCurrentEthiopianDate() {
    return gregorianToEthiopian(date('Y-m-d'));
}

function formatEthiopianDate($dateString) {
    if(empty($dateString) || $dateString == '0000-00-00') {
        return '—';
    }
    return gregorianToEthiopian($dateString);
}
?>