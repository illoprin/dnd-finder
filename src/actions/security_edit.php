<?
session_start();
require_once "../config.php";

// Check auth
if (!isLoggedIn()) {
  header('Location: /pages/login.php');
  exit();
}

// Get POST data
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$login = trim($_POST['login'] ?? '');

// Validation
$errors = [];

// Current password is necessary to update auth data
if (empty($current_password)) {
  $errors[] = 'Текущий пароль обязателен для изменения данных';
}

// Check login
if (strlen($login) < 5) {
  $errors[] = 'Логин должен содержать не менее 5 символов';
} elseif (preg_match('/[\/<>]/', $login)) {
  $errors[] = 'Логин содержит запрещенные символы: /, <, >';
}

// Check new password
if (!empty($new_password)) {
  if (strlen($new_password) < 8) {
    $errors[] = 'Новый пароль должен содержать не менее 8 символов';
  } elseif (preg_match('/[\/<>]/', $new_password)) {
    $errors[] = 'Новый пароль содержит запрещенные символы: /, <, >';
  }
}

// Show validation errors
if (!empty($errors)) {
  showErrorPage($errors);
  exit();
}

// Check user's password
try {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();
} catch (PDOException $e) {
  showErrorPage(['Ошибка базы данных ' . $e->getMessage()]);
  exit();
}

if (!password_verify($current_password, $user['password_hash'])) {
  showErrorPage(['Неверный текущий пароль']);
  exit();
}

$update_params = [];
$update_values = [];

// Check login if changes
if ($user['login'] !== $login) {
  try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ? AND id != ?");
    $stmt->execute([$login, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
      showErrorPage(['Пользователь с таким логином уже существует']);
      exit();
    }

    // Allow updates
    $update_params[] = 'login = ?';
    $update_values[] = $login;

  } catch (PDOException $e) {
    showErrorPage(['Ошибка базы данных ' . $e->getMessage()]);
    exit();  
  }
}

// Hash new password
if (!empty($new_password)) {
  $update_params[] = 'password_hash = ?';
  $update_values[] = password_hash($new_password, PASSWORD_DEFAULT);
}

// Check updates
if (empty($update_params)) {
  showErrorPage(["Нет данных для обновления"]);
  exit();
}

$update_values[] = $_SESSION['user_id'];

// Update data
try {
  $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $update_params) . " WHERE id = ?");
  $stmt->execute($update_values);

  // Redirect to account page
  header("Location: /pages/account.php#security");
  exit();
} catch (PDOException $e) {
  showErrorPage(['Ошибка базы данных ' . $e->getMessage()]);
  exit();    
}

// Show errors function
function showErrorPage($errors) {
  $title = "Ошибка редактирования параметров безопасности";
  $link_title = "В личный кабинет";
  $link_href = "/pages/account.php#security";
  require_once "../pages/errors.php";
}