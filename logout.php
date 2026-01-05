<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();
// Clear client-side localStorage entries related to profile, then redirect to homepage
// Output a tiny HTML page that clears localStorage via JS (cannot clear localStorage from server)
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Logging out…</title>
</head>
<body>
	<script>
		try {
			// Clear persisted profile info to avoid showing the previous user's avatar
			// when a different user logs in on the same browser.
			localStorage.removeItem('user_avatar');
			localStorage.removeItem('user_fullname');
			// remove any per-user id marker if present
			localStorage.removeItem('user_id');
		} catch (e) { /* ignore */ }
		// then navigate back to homepage
		window.location.replace('homepage.php');
	</script>
</body>
</html>
<?php
exit();
?> 