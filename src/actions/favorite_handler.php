<?
session_start();
require_once "../config.php";

if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit;
}

$id = $_GET['id'] ?? '';
$hash = $_GET['hash'] ?? '';
$is_favorite = $_GET['is_favorite'] ?? '';

if (empty($id)) {
  header("Location: /");
  exit;
}

$user_id = $_SESSION['user_id'];

if ($is_favorite === "1") {
  // Remove from favorites
  $stmt = $pdo->prepare("DELETE FROM liked_apps WHERE user_id = ? AND application_id = ?");
  $stmt->execute([$user_id, (int)$id]);
} else {
  // Add to favorites
  $stmt = $pdo->prepare("INSERT IGNORE INTO liked_apps (user_id, application_id) VALUES (?, ?)");
  $stmt->execute([$user_id, (int)$id]);
}

header("Location: " . $_SERVER['HTTP_REFERER'] . "#$hash");
exit;