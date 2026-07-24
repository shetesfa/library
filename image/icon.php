<?php
// Path to your PNG logo file
$logo_path = "icon.png"; // put your real PNG in the same folder

// Set correct header for PNG
header('Content-Type: image/png');

// Output the image
readfile($logo_path);
?>