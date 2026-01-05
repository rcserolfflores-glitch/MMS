<?php
// Backwards-compatible login endpoint. Some redirects point to `login.php`.
// This file simply includes the existing `login_process.php` implementation.
require_once __DIR__ . '/login_process.php';
?>
