<?
session_start();
require_once "../config.php";

// check logged in
if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit();
}

// check id
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: /");
  exit;
}

$app_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
  // check this entry in db
  $stmt = $pdo->prepare("SELECT user_id, image_url FROM applications WHERE id = ?");
  $stmt->execute([$app_id]);
  $app_entry = $stmt->fetch();

  // check ownership
  if ($app_entry['user_id'] !== $user_id) {
    header("Location: /");
    exit;
  }
  
  // delete image
  if (file_exists(".." . $app_entry['image_url'])) {
    unlink(".." . $app_entry['image_url']);
  }
  
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