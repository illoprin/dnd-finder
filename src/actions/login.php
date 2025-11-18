<?
session_start();
require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get request data
  $login = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';

  // Errors array
  $errors = [];

  // Validate data
  if (empty($login)) {
    $errors[] = "Введите логин или email";
  }

  if (empty($password)) {
    $errors[] = "Введите пароль";
  }

  // Check user login data in DB
  if (empty($errors)) {
    try {
      // Find user by email or login
      $stmt = $pdo->prepare("SELECT id, login, nickname, email, password_hash FROM users WHERE login = ? OR email = ?");
      $stmt->execute([$login, $login]);
      $user = $stmt->fetch();

      if ($user) {
        // Check password
        if (password_verify($password, $user['password_hash'])) {
          // Create session
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_login'] = $user['login'];
          $_SESSION['user_nickname'] = $user['nickname'];

          // Redirect to accout page
          header('Location: /pages/account.php');
          exit;
        } else {
          $errors[] = "Неверный пароль";
        }
      } else {
        $errors[] = "Пользователь с таким логином/email не найден";
      }
    } catch (PDOException $e) {
      $errors[] = "Ошибка базы данных: " . $e->getMessage();
    }
  }

  // If there are errors - show these
  if (!empty($errors)) {
    showErrorPage($errors);
  }
} else {
  // If request method is not post - redirect to main page
  header('Location: /');
  exit;
}

function showErrorPage($errors) {
  $title = "Ошибка входа";
  $link_href = "/pages/auth.php#login";
  $link_title = "Повторить вход";
  require_once "../pages/errors.php";
}
?>