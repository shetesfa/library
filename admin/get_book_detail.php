<?php
include("../config.php");

if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    exit('Unauthorized');
}

$id = intval($_GET['id']);
$book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT b.*, c.name as category_name 
    FROM books b 
    JOIN categories c ON b.category_id = c.id 
    WHERE b.id = $id"));

if(!$book) {
    echo '<p style="text-align: center; color: #999;">Book not found</p>';
    exit;
}

$ethiopianDate = !empty($book['registration_date']) ? $book['registration_date'] : getCurrentEthiopianDate();
$firstLetter = strtoupper(substr($book['book_name'], 0, 1));
?>

<!-- REMOVE these lines:
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
...
<style>
... all the CSS ...
</style>
</head>
<body>
-->

<!-- KEEP ONLY the content HTML without extra wrapper -->
<style>
/* Move all CSS styles here but they will still work */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Main wrapper */
.book-detail-wrapper {
    perspective: 1500px;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    max-width: 650px;
}

/* 3D Scene Container */
.book-3d-scene {
    width: 100%;
    height: 450px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin-bottom: 35px;
    background: transparent;
    border-radius: 30px;
}

/* 3D Book Container */
.book-3d-container {
    position: relative;
    width: 250px;
    height: 340px;
    transform-style: preserve-3d;
    animation: bookFloat 6s ease-in-out infinite;
}

@keyframes bookFloat {
    0%, 100% { 
        transform: rotateY(-10deg) rotateX(4deg) translateY(0px); 
    }
    20% { 
        transform: rotateY(3deg) rotateX(-2deg) translateY(-12px); 
    }
    40% { 
        transform: rotateY(-7deg) rotateX(3deg) translateY(-6px); 
    }
    60% { 
        transform: rotateY(9deg) rotateX(-3deg) translateY(-15px); 
    }
    80% { 
        transform: rotateY(-4deg) rotateX(1deg) translateY(-8px); 
    }
}

/* Closed Book Cover - Front Face */
.book-cover {
    position: absolute;
    width: 215px;
    height: 295px;
    left: 18px;
    top: 22px;
    background: linear-gradient(150deg, #c62828 0%, #8e1a1a 40%, #5c0e0e 100%);
    border-radius: 12px 30px 30px 12px;
    box-shadow: 
        12px 15px 35px rgba(0,0,0,0.6),
        inset 4px 0 0 rgba(255,242,0,0.3),
        inset -4px 0 0 rgba(0,0,0,0.5);
    transform: rotateY(-14deg) translateZ(5px);
    transform-style: preserve-3d;
    overflow: hidden;
    border: 3px solid #f0c040;
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Glossy overlay on cover */
.book-cover::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, 
        rgba(255,242,0,0.15) 0%, 
        rgba(247,148,29,0.08) 25%, 
        transparent 55%);
    z-index: 1;
    pointer-events: none;
}

/* Decorative border on cover */
.book-cover::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    bottom: 20px;
    border: 2px solid rgba(255,242,0,0.4);
    border-radius: 6px 20px 20px 6px;
    z-index: 1;
    pointer-events: none;
}

/* Logo Circle on Cover */
.book-cover-logo {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    z-index: 3;
    border: 3px solid #f0c040;
    box-shadow: 
        0 0 40px rgba(255,215,0,0.6),
        0 0 80px rgba(255,215,0,0.3);
    overflow: hidden;
    background: #ffffff;
    animation: logoGlow 3s ease-in-out infinite;
    margin-bottom: 15px;
}

@keyframes logoGlow {
    0%, 100% { box-shadow: 0 0 40px rgba(255,215,0,0.6), 0 0 80px rgba(255,215,0,0.3); }
    50% { box-shadow: 0 0 60px rgba(255,215,0,0.9), 0 0 100px rgba(255,215,0,0.5); }
}

.book-cover-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
}

.logo-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    background: #f8f0e0;
}

/* Book Title on Cover */
.book-cover-title {
    position: relative;
    z-index: 3;
    color: #ffea80;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    text-shadow: 0 2px 6px rgba(0,0,0,0.8);
    letter-spacing: 1px;
    padding: 5px 15px;
    background: rgba(0,0,0,0.4);
    border-radius: 20px;
    backdrop-filter: blur(2px);
    max-width: 90%;
    word-break: break-word;
}

/* Book Code on Cover */
.book-cover-code {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 3;
    color: #ffea80;
    font-size: 10px;
    font-weight: 600;
    background: rgba(0,0,0,0.5);
    padding: 3px 10px;
    border-radius: 12px;
    letter-spacing: 0.5px;
}

/* Spine - Left side of book */
.book-spine {
    position: absolute;
    width: 32px;
    height: 295px;
    left: 0;
    top: 22px;
    background: linear-gradient(180deg, #6b1212 0%, #9b1c1c 35%, #7a1515 70%, #4a0a0a 100%);
    border-radius: 30px 6px 6px 30px;
    box-shadow: 
        -5px 0 20px rgba(0,0,0,0.6),
        inset -2px 0 0 rgba(255,242,0,0.25);
    transform: rotateY(-30deg) translateZ(-2px);
    z-index: 1;
    border-left: 2px solid #e0a800;
}

/* Decorative bands on spine */
.book-spine::before {
    content: '';
    position: absolute;
    top: 25%;
    left: 6px;
    right: 6px;
    height: 6px;
    background: #f0c040;
    border-radius: 3px;
    box-shadow: 0 0 10px rgba(255,215,0,0.5);
}

.book-spine::after {
    content: '';
    position: absolute;
    bottom: 25%;
    left: 6px;
    right: 6px;
    height: 6px;
    background: #f0c040;
    border-radius: 3px;
    box-shadow: 0 0 10px rgba(255,215,0,0.5);
}

/* Pages Edge - Visible when book is closed */
.book-pages-edge {
    position: absolute;
    width: 190px;
    height: 278px;
    left: 35px;
    top: 30px;
    background: linear-gradient(180deg, 
        #f8f3e8 0%, #ebe0cc 10%, #f8f3e8 22%,
        #ebe0cc 35%, #f8f3e8 48%, #ebe0cc 60%,
        #f8f3e8 72%, #ebe0cc 85%, #f8f3e8 100%);
    border-radius: 4px 16px 16px 4px;
    box-shadow: 
        inset 0 0 20px rgba(0,0,0,0.2),
        2px 0 5px rgba(0,0,0,0.3);
    transform: rotateY(-4deg) translateZ(-3px);
    z-index: 0;
}

/* Subtle page lines on the edge */
.book-pages-edge::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        180deg,
        transparent,
        transparent 2px,
        rgba(0,0,0,0.04) 2px,
        rgba(0,0,0,0.04) 4px
    );
    border-radius: 4px 16px 16px 4px;
}

/* Floating Particles */
.particles {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: 10;
}

.particle {
    position: absolute;
    width: 6px;
    height: 6px;
    background: #ffd700;
    border-radius: 50%;
    box-shadow: 
        0 0 12px #ffb700,
        0 0 24px rgba(255,200,0,0.5);
    animation: particleRise 4s ease-in-out infinite;
    opacity: 0;
}

.particle:nth-child(1) { left: 8%; top: 25%; animation-delay: 0s; }
.particle:nth-child(2) { left: 88%; top: 32%; animation-delay: 0.7s; }
.particle:nth-child(3) { left: 18%; top: 72%; animation-delay: 1.4s; }
.particle:nth-child(4) { left: 78%; top: 65%; animation-delay: 2.1s; }
.particle:nth-child(5) { left: 52%; top: 15%; animation-delay: 2.8s; }
.particle:nth-child(6) { left: 35%; top: 50%; animation-delay: 3.5s; }

@keyframes particleRise {
    0%, 100% { 
        opacity: 0; 
        transform: translateY(0) scale(0.3); 
    }
    35% { 
        opacity: 1; 
        transform: translateY(-28px) scale(1.2); 
    }
    65% { 
        opacity: 0.4; 
        transform: translateY(-38px) scale(0.7); 
    }
}

/* Book ID Badge */
.book-id-badge {
    text-align: center;
    margin-bottom: 25px;
}

.book-id-badge span {
    display: inline-block;
    background: linear-gradient(135deg, #b71c1c, #7f0000);
    color: #ffffff;
    padding: 12px 35px;
    border-radius: 35px;
    font-size: 24px;
    font-weight: 700;
    border: 2px solid #f0c040;
    box-shadow: 
        0 8px 25px rgba(183,28,28,0.5),
        0 0 30px rgba(255,215,0,0.2);
    animation: pulseBadge 2.5s ease-in-out infinite;
    letter-spacing: 1px;
}

@keyframes pulseBadge {
    0%, 100% { 
        box-shadow: 0 8px 25px rgba(183,28,28,0.5), 0 0 30px rgba(255,215,0,0.2); 
    }
    50% { 
        box-shadow: 0 8px 40px rgba(183,28,28,0.8), 0 0 50px rgba(255,215,0,0.5); 
    }
}

/* All Book Information Grid */
.book-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    width: 100%;
    margin-top: 5px;
}

.info-item {
    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,240,230,0.9));
    padding: 16px 18px;
    border-radius: 16px;
    border-left: 5px solid #f7941d;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.info-item:hover {
    background: #fff8e1;
    transform: translateX(5px);
    border-left-color: #ffd700;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.info-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 6px;
    letter-spacing: 0.8px;
}

.info-value {
    font-size: 16px;
    color: #333;
    font-weight: 600;
    word-break: break-word;
}

.info-value.price {
    color: #28a745;
    font-size: 19px;
    font-weight: 700;
}

.info-value.category {
    color: #f7941d;
    font-weight: 700;
}

.info-item.registration-date {
    grid-column: 1 / -1;
    background: linear-gradient(135deg, rgba(232,245,233,0.95), rgba(200,230,201,0.9));
    border-left: 5px solid #28a745;
}

.info-item.registration-date .info-label {
    color: #2e7d32;
}

.info-item.registration-date .info-value {
    color: #1b5e20;
    font-size: 18px;
}

/* Full Book Name Title */
.book-name-title {
    text-align: center;
    margin-bottom: 20px;
}

.book-name-title .name {
    font-size: 26px;
    font-weight: 800;
    color: #333;
    text-shadow: none;
    line-height: 1.4;
    word-break: break-word;
}

/* Responsive Design */
@media (max-width: 550px) {
    .book-3d-scene {
        height: 380px;
    }
    
    .book-3d-container {
        transform: scale(0.8);
    }
    
    .book-info-grid {
        grid-template-columns: 1fr;
    }
    
    .book-id-badge span {
        font-size: 20px;
        padding: 10px 25px;
    }
    
    .book-name-title .name {
        font-size: 22px;
    }
}
</style>

<div class="book-detail-wrapper">
    
    <!-- Book ID Badge -->
    <div class="book-id-badge">
        <span>🏷️ <?php echo htmlspecialchars($book['book_code']); ?></span>
    </div>
    
    <!-- 3D Book Scene -->
    <div class="book-3d-scene">
        <div class="book-3d-container">
            
            <!-- Spine -->
            <div class="book-spine"></div>
            
            <!-- Pages Edge (Closed) -->
            <div class="book-pages-edge"></div>
            
            <!-- Front Cover with Logo and Title -->
            <div class="book-cover">
                <!-- Book Code on Cover -->
                <div class="book-cover-code"><?php echo htmlspecialchars($book['book_code']); ?></div>
                
                <!-- Logo Circle -->
                <div class="book-cover-logo">
                    <?php if(file_exists("../image/icon.png")): ?>
                        <img src="../image/icon.png" alt="Book Logo">
                    <?php elseif(file_exists("../image/icon.php")): ?>
                        <img src="../image/icon.php" alt="Book Logo">
                    <?php else: ?>
                        <div class="logo-fallback">📚</div>
                    <?php endif; ?>
                </div>
                
                <!-- Book Title on Cover -->
                <div class="book-cover-title">
                    <?php 
                    $shortTitle = mb_strlen($book['book_name']) > 25 ? mb_substr($book['book_name'], 0, 22) . '...' : $book['book_name'];
                    echo htmlspecialchars($shortTitle);
                    ?>
                </div>
            </div>
            
            <!-- Floating Particles -->
            <div class="particles">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
            </div>
            
        </div>
    </div>
    
    <!-- Full Book Name Title -->
    <div class="book-name-title">
        <div class="name">📕 <?php echo htmlspecialchars($book['book_name']); ?></div>
    </div>
    
    <!-- All Book Information Grid -->
    <div class="book-info-grid">
        <div class="info-item">
            <div class="info-label">📕 የመጽሐፍ ስም / Book Name</div>
            <div class="info-value"><?php echo htmlspecialchars($book['book_name']); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">✍️ ደራሲ / Writer</div>
            <div class="info-value"><?php echo htmlspecialchars($book['writer_name'] ?: '—'); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">📑 ምድብ / Category</div>
            <div class="info-value category"><?php echo htmlspecialchars($book['category_name']); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">📅 ዓመተ ምህረት / Year</div>
            <div class="info-value"><?php echo htmlspecialchars($book['published_year'] ?: '—'); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">💰 ዋጋ / Price</div>
            <div class="info-value price">ብር <?php echo number_format($book['price'], 2); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">📊 ብዛት / Quantity</div>
            <div class="info-value"><?php echo $book['quantity']; ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">👤 የመፅሀፉ አስገቢ ስም / Registered By</div>
            <div class="info-value"><?php echo htmlspecialchars($book['registered_by'] ?: '—'); ?></div>
        </div>
        
        <div class="info-item registration-date">
            <div class="info-label">📆 የተመዘገበበት ቀን / Registration Date (Ethiopian Calendar)</div>
            <div class="info-value"><?php echo $ethiopianDate; ?></div>
        </div>
    </div>
    
</div>

<!-- REMOVE these lines:
</body>
</html>
-->