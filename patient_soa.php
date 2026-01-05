<?php
// patient_soa.php
// Lightweight redirect to the patient portal's SOA panel to avoid 404s when linked from sidebar.
// Keeps everything in the single-page portal while providing a stable URL for direct links.

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Use absolute path to ensure it works from any current URL
$target = '/drea/patient_portal.php?panel=soa';

// Prefer a Location header redirect
header('Location: ' . $target, true, 302);
// Fallback for browsers that don't follow header redirects for some reason
echo '<!doctype html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target) . '">';
echo '<title>Redirecting...</title></head><body>'; 
echo 'If you are not redirected automatically, <a href="' . htmlspecialchars($target) . '">click here</a>.';
echo '</body></html>';
exit;
