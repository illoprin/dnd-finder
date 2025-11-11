<?
require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get request data
  $login = trim($_POST['login'] ?? '');
  $nickname = trim($_POST['nickname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $password_repeat = $_POST['password_repeat'] ?? '';

  // Errors array
  $errors = [];

  // Validate data
  if (empty($login) || strlen($login) < 5) {
    $errors[] = "Логин должен содержать минимум 5 символов";
  }

  if (empty($nickname) || strlen($nickname) < 5) {
    $errors[] = "Никнейм должен содержать минимум 5 символов";
  }

  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Введите корректный email";
  }

  if (empty($password) || strlen($password) < 8) {
    $errors[] = "Пароль должен содержать минимум 8 символов";
  }

  if ($password !== $password_repeat) {
    $errors[] = "Пароли не совпадают";
  }

  // Check forbidden chars
  $forbidden_chars = ['/', '<', '>'];
  foreach ($forbidden_chars as $char) {
    if (strpos($login, $char) !== false || strpos($nickname, $char) !== false || strpos($email, $char) !== false) {
      $errors[] = "Запрещенные символы: /, <, >";
      break;
    }
  }

  // Check data in database if has not errors
  if (empty($errors)) {
    try {
      // Check same login
      $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
      $stmt->execute([$login]);
      if ($stmt->fetch()) {
        $errors[] = "Пользователь с таким логином уже существует";
      }

      // Check same email
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $errors[] = "Пользователь с таким email уже существует";
      }

      // If has no duplicate errors, create user
      if (empty($errors)) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user entry
        $stmt = $pdo->prepare("INSERT INTO users (login, nickname, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->execute([$login, $nickname, $email, $password_hash]);

        // Get id of new user
        $user_id = $pdo->lastInsertId();

        // Show success page
        showSuccessPage($login);
        exit;
      }
    } catch (PDOException $e) {
      $errors[] = "Ошибка базы данных: " . $e->getMessage();
    }
  }

  // If has errors -> show errors page
  if (!empty($errors)) {
    showErrorPage($errors);
  }
} else {
  // Redirect to main page if method is not post
  header('Location: /');
  exit;
}

/**
 * Показывает страницу успешной регистрации
 */
function showSuccessPage($login)
{
  ?>
    <!DOCTYPE html>
    <html lang="ru">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <? require_once "../components/head.php" ?>
      <title>D&D Finder — Успешный вход</title>
    </head>

    <body class="justify-content-center align-items-center flex-column">
      <div class="container text-center">
        <p class="fw-bold fs-1 mb-3">
          Регистрация успешна, <? echo $login ?>👍
        </p>
        <a href="/pages/auth.php#login" class="btn btn-accent">Перейти на страницу входа</a>
      </div>
    </body>

    </html>
  <?
}

// WARN Code Duplication
function showErrorPage($errors)
{
  ?>
    <!DOCTYPE html>
    <html lang="ru">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <? require_once "../components/head.php" ?>
      <title>D&D Finder — Ошибка регистрации</title>
    </head>

    <body class="justify-content-center align-items-center flex-column">
      <div class="container text-center">
        <p class="fw-bold fs-1 mb-3">
          Есть ошибки ⚠️
        </p>
        <? foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert">
          <? echo $error; ?>
        </div>
        <? endforeach; ?>
        <a href="/pages/auth.php#register" class="btn btn-accent">Повторить регистрацию</a>
      </div>
    </body>

    </html>
  <?
}
