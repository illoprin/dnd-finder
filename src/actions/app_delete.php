<?
session_start();
require_once "../config.php";

// Check logged in
if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit();
}

// Check id
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: /");
  exit;
}

$app_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
  // Check this entry in db
  $stmt = $pdo->prepare("SELECT user_id, image_url FROM applications WHERE id = ?");
  $stmt->execute([$app_id]);
  $app_entry = $stmt->fetch();

  // Check ownership
  if ($app_entry['user_id'] !== $user_id) {
    header("Location: /");
    exit;
  }
  
  // Delete image
  if (file_exists(".." . $app_entry['image_url'])) {
    unlink(".." . $app_entry['image_url']);
  }

  // Delete entry
  $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
  $stmt->execute([$app_id]);
} catch (PDOException $e) {
  redirect();
}

redirect();

function redirect() {
  header("Location: /pages/account.php#apps");
  exit();
}