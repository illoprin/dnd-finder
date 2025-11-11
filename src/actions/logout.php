<?php
session_start();

// Destroy session data
$_SESSION = array();

// Destroy session
session_destroy();

// Redirect to index.php page
header('Location: /');
exit;
?>