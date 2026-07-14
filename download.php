<?php
// download.php - File download handler
// Place this in: facebookscraper/download.php

if (!isset($_GET['file'])) {
    die('No file specified');
}

$filename = basename($_GET['file']);
$filepath = './downloads/' . $filename;

// Security check - only allow .mp4 and .mkv files
$allowed_extensions = array('mp4', 'mkv', 'webm', 'avi');
$file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    die('Invalid file type');
}

// Check if file exists
if (!file_exists($filepath)) {
    die('File not found');
}

// Send file to browser
header('Content-Type: video/' . $file_extension);
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($filepath);
exit;
?>
