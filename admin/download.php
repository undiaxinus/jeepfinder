<?php
$file = '../app/JeepFinder.apk';

if (file_exists($file)) {
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="JeepFinder.apk"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    die('Error: APK file not found');
}
?>
