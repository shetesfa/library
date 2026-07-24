<?php


// Default language
if(!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Language switch
if(isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// English translations
$lang_en = [
    // General
    'site_title' => 'Atsede Teguhan Sunday School Library',
    'welcome' => 'Welcome to Our Library System',
    'login' => 'Login',
    'logout' => 'Logout',
    'dashboard' => 'Dashboard',
    'back_to_home' => 'Back to Home',
    'search' => 'Search',
    'clear' => 'Clear',
    'actions' => 'Actions',
    'delete' => 'Delete',
    'confirm_delete' => 'Are you sure?',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'close' => 'Close',
    'loading' => 'Loading...',
    
    // Navigation
    'manage_users' => 'Manage Users',
    'manage_books' => 'Manage Books',
    'manage_categories' => 'Manage Categories',
    'transactions' => 'Transactions',
    'books' => 'Books',
    'categories' => 'Categories',
    
    // Books
    'book_title' => 'Book Title',
    'book_name' => 'Book Name',
    'writer' => 'Writer',
    'author' => 'Author',
    'published_year' => 'Year',
    'price' => 'Price',
    'quantity' => 'Quantity',
    'category' => 'Category',
    'available_books' => 'Available Books',
    'total_books' => 'Total Books',
    'total_copies' => 'Total Copies',
    'available_now' => 'Available Now',
    'borrowed' => 'Borrowed',
    'in_stock' => 'in stock',
    'out_of_stock' => 'Out of stock',
    'only_left' => 'Only left',
    'add_book' => 'Add Book',
    'edit_book' => 'Edit Book',
    'delete_book' => 'Delete Book',
    'borrow_book' => 'Borrow Book',
    'return_book' => 'Return Book',
    'borrow_history' => 'Borrow History',
    'view_books' => 'View Books',
    'search_books' => 'Search books, authors, categories...',
    'no_books_found' => 'No books found',
    'no_books_available' => 'No books available in the library',
    'try_adjusting_search' => 'Try adjusting your search or filter',
    
    // Borrow
    'borrower_name' => 'Borrower Name',
    'class' => 'Class',
    'phone_number' => 'Phone Number',
    'borrow_date' => 'Borrow Date',
    'due_date' => 'Due Date',
    'return_date' => 'Return Date',
    'status' => 'Status',
    'borrow_period' => 'Borrow Period',
    'borrow_period_message' => 'Books are borrowed for 7 days',
    'confirm_borrow' => 'Confirm Borrow',
    'book_borrowed_success' => 'Book borrowed successfully!',
    'already_borrowed' => 'This borrower already has a book borrowed!',
    'return_success' => 'Book returned successfully!',
    'overdue' => 'Overdue',
    'returned' => 'Returned',
    
    // Users
    'username' => 'Username',
    'password' => 'Password',
    'role' => 'Role',
    'admin' => 'Admin',
    'librarian' => 'Librarian',
    'add_user' => 'Add User',
    'remove_user' => 'Remove User',
    'reset_password' => 'Reset Password',
    'default_password' => 'Default password',
    'current_user' => 'You',
    'cannot_delete_self' => 'You cannot delete your own account',
    'user_added' => 'User added successfully',
    'user_removed' => 'User removed successfully',
    'password_reset' => 'Password reset to default 123',
    
    // Change Password
    'change_password' => 'Change Password',
    'force_password_change' => 'FORCED PASSWORD CHANGE',
    'force_password_message' => 'You\'re using default password. Please set a new password to continue.',
    'new_password' => 'New Password',
    'confirm_password' => 'Confirm Password',
    'update_password' => 'Update Password',
    'password_changed' => 'Password changed successfully!',
    'redirecting' => 'Redirecting to dashboard in',
    'seconds' => 'seconds',
    'password_requirements' => 'Password Requirements',
    'min_length' => 'At least 6 characters',
    'not_default' => 'Not equal to default (123)',
    'passwords_match' => 'Passwords match',
    'passwords_do_not_match' => 'Passwords do not match',
    'weak' => 'Weak password',
    'medium' => 'Medium password',
    'strong' => 'Strong password',
    'enter_password' => 'Enter password',
    
    // Categories
    'category_name' => 'Category Name',
    'add_category' => 'Add Category',
    'delete_category' => 'Delete Category',
    'category_added' => 'Category added successfully',
    'category_exists' => 'Category already exists',
    'category_removed' => 'Category removed successfully',
    'category_in_use' => 'Cannot delete - Category has books',
    'no_categories' => 'No categories yet',
    'add_first_category' => 'Add your first category',
    
    // Test Credentials
    'test_credentials' => 'TEST CREDENTIALS',
    'welcome_back' => 'Welcome Back',
    'sign_in' => 'Sign In',
    'invalid_login' => 'Invalid username or password',
    
    // Footer
    'all_rights_reserved' => 'All Rights Reserved',
    'bible_verse' => '"Train up a child in the way he should go" - Proverbs 22:6',
    
    // Transactions
    'total_transactions' => 'Total Transactions',
    'currently_borrowed' => 'Currently Borrowed',
    'borrow_history_title' => 'Borrow History',
    'no_transactions' => 'No transactions found',
    'no_borrowed_books' => 'No books currently borrowed',
    
    // Stats
    'total_titles' => 'Total Titles',
    'different_books' => 'Different books',
    'all_copies' => 'All books ever purchased',
    'ready_to_borrow' => 'Ready to borrow',
    'currently_out' => 'Currently out',
];

// Amharic translations
$lang_am = [
    // General
    'site_title' => 'አጸደ ትጉሃን ሰንበት ትምህርት ቤት ቤተመጽሀፍት',
    'welcome' => 'እንኳን ወደ ቤተመጻሕፍት ሥርዓታችን በደህና መጡ',
    'login' => 'ግባ',
    'logout' => 'ውጣ',
    'dashboard' => 'ዳሽቦርድ',
    'back_to_home' => 'ወደ dashboard ተመለስ',
    'search' => 'ፈልግ',
    'clear' => 'አጽዳ',
    'actions' => 'ድርጊቶች',
    'delete' => 'ሰርዝ',
    'confirm_delete' => 'እርግጠኛ ነህ?',
    'save' => 'አስቀምጥ',
    'cancel' => 'ሰርዝ',
    'close' => 'ዝጋ',
    'loading' => 'በመጫን ላይ...',
    
    // Navigation
    'manage_users' => 'ተጠቃሚዎችን አስተዳድር',
    'manage_books' => 'መጻሕፍትን አስተዳድር',
    'manage_categories' => 'catagory አስተዳድር',
    'transactions' => 'ግብይቶች',
    'books' => 'መጻሕፍት',
    'categories' => 'catagory',
    
    // Books
    'book_title' => 'የመጽሐፍ ርዕስ',
    'book_name' => 'የመጽሐፍ ስም',
    'writer' => 'ጸሐፊ',
    'author' => 'ደራሲ',
    'published_year' => 'ዓመት',
    'price' => 'ዋጋ',
    'quantity' => 'ብዛት',
    'category' => 'ምድብ',
    'available_books' => 'አሁን ያሉ መጻሕፍት',
    'total_books' => 'ጠቅላላ መጻሕፍት',
    'total_copies' => 'አጠቃላይ ቅጂዎች',
    'available_now' => 'አሁን ያሉን',
    'borrowed' => 'ተውሷል',
    'in_stock' => 'በቤተመጽሀፍት አለ',
    'out_of_stock' => 'በቤተመጽሀፍት የለም',
    'only_left' => 'የቀረው',
    'add_book' => 'መጽሐፍ ለመጨመር',
    'edit_book' => 'መጽሐፍ አርትዕ',
    'delete_book' => 'መጽሐፍ ሰርዝ',
    'borrow_book' => 'መጽሐፍ ተውስ',
    'return_book' => 'መጽሐፍ መልስ',
    'borrow_history' => 'የውሰት ታሪክ',
    'view_books' => 'መጻሕፍትን ተመልከት',
    'search_books' => 'መጻሕፍትን፣ ደራሲያን፣ ምድቦችን ፈልግ...',
    'no_books_found' => 'ምንም መጽሐፍ አልተገኘም',
    'no_books_available' => 'በቤተመጻሕፍት ውስጥ ምንም መጽሐፍ የለም',
    'try_adjusting_search' => 'ፍለጋህን አስተካክል',
    
    // Borrow
    'borrower_name' => 'የተዋሰው ስም',
    'class' => 'ክፍል',
    'phone_number' => 'ስልክ ቁጥር',
    'borrow_date' => 'የተዋሰበት ቀን',
    'due_date' => 'የሚመለስበት ቀን',
    'return_date' => 'የተመለሰበት ቀን',
    'status' => 'ሁኔታ',
    'borrow_period' => 'የውሰት ጊዜ',
    'borrow_period_message' => 'መጻሕፍት ለ 7 ቀናት ይውሳሉ',
    'confirm_borrow' => 'ውሰት አረጋግጥ',
    'book_borrowed_success' => 'መጽሐፍ በተሳካ ሁኔታ ተውሷል!',
    'already_borrowed' => 'ይህ ተዋሳይ አስቀድሞ መጽሐፍ ተውሷል!',
    'return_success' => 'መጽሐፍ በተሳካ ሁኔታ ተመልሷል!',
    'overdue' => 'ጊዜ አልፎበታል',
    'returned' => 'ተመልሷል',
    
    // Users
    'username' => 'የተጠቃሚ ስም',
    'password' => 'የይለፍ ቃል',
    'role' => 'ሚና',
    'admin' => 'አስተዳዳሪ',
    'librarian' => 'ቤተመጻሕፍት ባለሙያ',
    'add_user' => 'ተጠቃሚ ጨምር',
    'remove_user' => 'ተጠቃሚ አስወግድ',
    'reset_password' => 'የይለፍ ቃል ዳግም አስጀምር',
    'default_password' => 'ነባሪ የይለፍ ቃል',
    'current_user' => 'አንተ',
    'cannot_delete_self' => 'የራስህን አካውንት መሰረዝ አትችልም',
    'user_added' => 'ተጠቃሚ በተሳካ ሁኔታ ተጨምሯል',
    'user_removed' => 'ተጠቃሚ በተሳካ ሁኔታ ተወግዷል',
    'password_reset' => 'የይለፍ ቃል ወደ 123 ተመልሷል',
    
    // Change Password
    'change_password' => 'የይለፍ ቃል ቀይር',
    'force_password_change' => 'የይለፍ ቃል መቀየር ግዴታ ነው',
    'force_password_message' => 'የድሮውን የይለፍ ቃል እየተጠቀምክ ነው። ለመቀጠል እባክህ አዲስ የይለፍ ቃል አስገባ።',
    'new_password' => 'አዲስ የይለፍ ቃል',
    'confirm_password' => 'የይለፍ ቃል አረጋግጥ',
    'update_password' => 'የይለፍ ቃል ቀይር',
    'password_changed' => 'የይለፍ ቃል በተሳካ ሁኔታ ተቀይሯል!',
    'redirecting' => 'ወደ ዳወደዋናው ገጽ በ',
    'seconds' => 'ሰከንዶች ውስጥ ይዛወራል',
    'password_requirements' => 'የይለፍ ቃል መስፈርቶች',
    'min_length' => 'ቢያንስ 6 ዲጂት',
    'not_default' => 'ከድሮው (123) ጋር እኩል መሆን የለበትም',
    'passwords_match' => 'የይለፍ ቃላት ተመሳሳይ ናቸው',
    'passwords_do_not_match' => 'የይለፍ ቃላት አይመሳሰሉም',
    'weak' => 'ደካማ የይለፍ ቃል',
    'medium' => 'መካከለኛ የይለፍ ቃል',
    'strong' => 'ጠንካራ የይለፍ ቃል',
    'enter_password' => 'የይለፍ ቃል አስገባ',
    
    // Categories
    'category_name' => 'የምድብ ስም',
    'add_category' => 'ምድብ ጨምር',
    'delete_category' => 'ምድብ ሰርዝ',
    'category_added' => 'ምድብ በተሳካ ሁኔታ ተጨምሯል',
    'category_exists' => 'ምድብ አስቀድሞ አለ',
    'category_removed' => 'ምድብ በተሳካ ሁኔታ ተወግዷል',
    'category_in_use' => 'መሰረዝ አይቻልም - ምድቡ መጻሕፍት አሉት',
    'no_categories' => 'ምንም ምድቦች የሉም',
    'add_first_category' => 'የመጀመሪያ ምድብህን ጨምር',
    
    // Test Credentials
    'test_credentials' => 'የሙከራ መለያዎች',
    'welcome_back' => 'እንኳን በደህና መጣህ',
    'sign_in' => 'ግባ',
    'invalid_login' => 'የተጠቃሚ ስም ወይም የይለፍ ቃል ትክክል አይደለም',
    
    // Footer
    'all_rights_reserved' => 'መብቱ በህግ የተጠበቀ ነው',
    
    
    // Transactions
    'total_transactions' => 'አጠቃላይ ግብይቶች',
    'currently_borrowed' => 'አሁን የተዋሱ',
    'borrow_history_title' => 'የውሰት ታሪክ',
    'no_transactions' => 'ምንም ግብይት አልተገኘም',
    'no_borrowed_books' => 'ምንም የተዋሱ መጻሕፍት የሉም',
    
    // Stats
    'total_titles' => 'አጠቃላይ ርዕሶች',
    'different_books' => 'የተለያዩ መጻሕፍት',
    'all_copies' => 'ጠቅላላ ያሉን መጻህፍቶች',
    'ready_to_borrow' => 'ለመዋስ ዝግጁ',
    'currently_out' => 'አሁን ውሰድ',
    // Add these to your $lang_am array in lang.php

// Admin Dashboard
'admin_dashboard' => 'የአስተዳዳሪ ዳሽቦርድ',
'books_inventory' => 'የመጻሕፍት ክምችት',
'books_found' => 'የተገኙ መጻሕፍት',
'search_by' => 'በመጽሐፍ ስም፣ ደራሲ ወይም ምድብ ፈልግ',
'all_categories' => 'ሁሉም ምድቦች',
'clear' => 'አጽዳ',
'id' => 'መለያ',
'qty' => 'ብዛት',
'action' => 'ድርጊት',
'delete' => 'ሰርዝ',
'confirm_delete' => 'እርግጠኛ ነህ?',
'no_books' => 'ምንም መጽሐፍ የለም',
'no_books_search' => 'ለተፈለገው ነገር ምንም መጽሐፍ አልተገኘም',
'total' => 'ጠቅላላ',

// Librarian Dashboard
'librarian_dashboard' => 'የቤተመጻሕፍት ባለሙያ ዳሽቦርድ',
'available_books' => 'ዝግጁ መጻሕፍት',
'borrow' => 'ውሰድ',
'return' => 'መልስ',
'currently_borrowed' => 'አሁን የተዋሱ',
'borrow_history' => 'የውሰት ታሪክ',
'book_title' => 'የመጽሐፍ ርዕስ',
'borrower' => 'ተዋሳይ',
'phone' => 'ስልክ',
'borrow_date' => 'የተዋሰበት ቀን',
'due_date' => 'የሚመለስበት ቀን',
'return_date' => 'የተመለሰበት ቀን',
'status' => 'ሁኔታ',
'overdue' => 'ጊዜ አልፎበታል',
'returned' => 'ተመልሷል',
'no_borrowed_books' => 'ምንም የተዋሱ መጻሕፍት የሉም',
'no_history' => 'ምንም የውሰት ታሪክ የለም',

// User Management
'add_user' => 'ተጠቃሚ ጨምር',
'remove_user' => 'ተጠቃሚ አስወግድ',
'reset_password' => 'የይለፍ ቃል ዳግም አስጀምር',
'username' => 'የተጠቃሚ ስም',
'role' => 'ሚና',
'admin' => 'አስተዳዳሪ',
'librarian' => 'ቤተመጻሕፍት ባለሙያ',
'default_password' => 'የድሮ የይለፍ ቃል',
'current_user' => 'አንተ',
'cannot_delete_self' => 'የራስህን አካውንት መሰረዝ አትችልም',
'user_added' => 'ተጠቃሚ በተሳካ ሁኔታ ተጨምሯል',
'user_removed' => 'ተጠቃሚ በተሳካ ሁኔታ ተወግዷል',
'password_reset' => 'የይለፍ ቃል ወደ 123 ተመልሷል',
'username_exists' => 'የተጠቃሚ ስም አስቀድሞ አለ',

// Category Management
'category_name' => 'የምድብ ስም',
'add_category' => 'ምድብ ጨምር',
'delete_category' => 'ምድብ ሰርዝ',
'category_added' => 'ምድብ በተሳካ ሁኔታ ተጨምሯል',
'category_exists' => 'ምድብ አስቀድሞ አለ',
'category_removed' => 'ምድብ በተሳካ ሁኔታ ተወግዷል',
'category_in_use' => 'መሰረዝ አይቻልም - ምድቡ መጻሕፍት አሉት',
'no_categories' => 'ምንም ምድቦች የሉም',
'existing_categories' => 'ያሉ ምድቦች',
'total_categories' => 'ጠቅላላ ምድቦች',

// Book Management
'add_book' => 'መጽሐፍ ጨምር',
'edit_book' => 'መጽሐፍ አርትዕ',
'book_name' => 'የመጽሐፍ ስም',
'writer' => 'ጸሐፊ',
'author' => 'ደራሲ',
'published_year' => 'የታተመበት ዓመት',
'price' => 'ዋጋ',
'quantity' => 'ብዛት',
'category' => 'ምድብ',
'select_category' => 'ምድብ ምረጥ',
'in_stock' => 'ክምችት አለ',
'out_of_stock' => 'ክምችት የለም',
'only_left' => 'የቀረው',
'book_added' => 'መጽሐፍ በተሳካ ሁኔታ ተጨምሯል',
'book_deleted' => 'መጽሐፍ በተሳካ ሁኔታ ተሰርዟል',

// Transactions
'transactions' => 'ግብይቶች',
'total_transactions' => 'ጠቅላላ ግብይቶች',
'borrowed_books' => 'የተዋሱ መጻሕፍት',
'returned_books' => 'የተመለሱ መጻሕፍት',
'borrower_name' => 'የተዋሰው ስም',
'class' => 'ክፍል',
'phone_number' => 'ስልክ ቁጥር',
'book' => 'መጽሐፍ',
'return_book' => 'መጽሐፍ መልስ',
'confirm_return' => 'መጽሐፍ መመለስ?',
'book_returned' => 'መጽሐፍ በተሳካ ሁኔታ ተመልሷል',



// Login Page
'welcome_back' => 'እንኳን በደህና ተመለስክ',
'sign_in' => 'ግባ',
'sign_in_message' => 'ወደ ቤተመጻሕፍት ሥርዓት ለመግባት',
'invalid_login' => 'የተጠቃሚ ስም ወይም የይለፍ ቃል ትክክል አይደለም',
'test_credentials' => 'የሙከራ መለያዎች',

// Index Page
'welcome_to_library' => 'እንኳን ወደ ቤተመጻሕፍት ሥርዓታችን በደህና መጡ',
'manage_books_borrowing' => 'መጻሕፍትን፣ ውሰት እና መልስ በቀላሉ ያስተዳድሩ',
'access_library' => 'ወደ ቤተመጻሕፍት ሥርዓት ይግቡ',
'book_management' => 'የመጽሐፍ አስተዳደር',
'user_management' => 'የተጠቃሚ አስተዳደር',
'borrow_return' => 'ውሰት እና መልስ',
'faith_knowledge' => 'በእምነት እና በእውቀት አእምሮን ማብቃት',

// Stats
'total_titles' => 'ጠቅላላ ርዕሶች',
'total_copies' => 'ጠቅላላ ቅጂዎች',
'available_now' => 'አሁን ዝግጁ',
'borrowed' => 'ተውሷል',
'different_books' => 'የተለያዩ መጻሕፍት',
'all_copies_ever' => 'ጠቅላላ',
'ready_to_borrow' => 'ለመውሰድ ዝግጁ',
'currently_out' => 'አሁን የተዋሱ',

// Buttons
'search' => 'ፈልግ',
'clear' => 'አጽዳ',
'delete' => 'ሰርዝ',
'save' => 'አስቀምጥ',
'cancel' => 'ሰርዝ',
'close' => 'ዝጋ',
'back' => 'ተመለስ',
'confirm' => 'አረጋግጥ',
'add' => 'ጨምር',
'edit' => 'አርትዕ',
'view' => 'ተመልከት',



// Messages
'loading' => 'በመጫን ላይ...',
'please_wait' => 'እባክህ ቆይ...',
'success' => 'ተሳክቷል',
'error' => 'ስህተት',
'warning' => 'ማስጠንቀቂያ',
'info' => 'መረጃ',

// Time
'today' => 'ዛሬ',
'yesterday' => 'ትላንት',
'tomorrow' => 'ነገ',
'days' => 'ቀናት',
];

function __($key) {
    global $lang_en, $lang_am;
    $lang = $_SESSION['lang'] ?? 'en';
    
    if($lang == 'am') {
        return $lang_am[$key] ?? $key;
    }
    return $lang_en[$key] ?? $key;
}
?>