<?
session_start();
require_once "../config.php";

// Check auth
if (!isLoggedIn()) {
  header('Location: /pages/login.php');
  exit();
}

// Получаем данные из POST
$nickname = trim($_POST['nickname'] ?? '');
$email = trim($_POST['email'] ?? '');
$telegram_username = trim($_POST['telegram_username'] ?? '');
$description = trim($_POST['description'] ?? '');

// Валидация данных
$errors = [];

// Проверка никнейма
if (empty($nickname)) {
  $errors[] = 'Никнейм не может быть пустым';
} elseif (strlen($nickname) < 5) {
  $errors[] = 'Никнейм должен содержать не менее 5 символов';
} elseif (preg_match('/[\/<>]/', $nickname)) {
  $errors[] = 'Никнейм содержит запрещенные символы: /, <, >';
}

// Проверка email
if (empty($email)) {
  $errors[] = 'Email не может быть пустым';
} elseif (strlen($email) < 5) {
  $errors[] = 'Email должен содержать не менее 5 символов';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = 'Некорректный формат email';
} elseif (preg_match('/[\/<>]/', $email)) {
  $errors[] = 'Email содержит запрещенные символы: /, <, >';
}

// Проверка telegram username
if (!empty($telegram_username) && preg_match('/[\/<>]/', $telegram_username)) {
  $errors[] = 'Telegram username содержит запрещенные символы: /, <, >';
}

// Проверка описания
if (!empty($description) && preg_match('/[\/<>]/', $description)) {
  $errors[] = 'Описание содержит запрещенные символы: /, <, >';
}

// Если есть ошибки - показываем их
if (!empty($errors)) {
  showErrorPage($errors);
  exit();
}

// Проверяем уникальность email (кроме текущего пользователя)
$user_id = $_SESSION['user_id'];
try {
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
  $stmt->execute([$email, $user_id]);
  if ($stmt->fetch()) {
    showErrorPage(['Этот email уже используется другим пользователем']);
    exit();
  }
} catch (PDOException $e) {
  showErrorPage(['Ошибка базы данных при проверке email']);
  exit();
}

// Обновляем данные пользователя в базе данных
try {
  // Подготавливаем запрос
  $sql = "UPDATE users SET nickname = ?, email = ?, telegram_username = ?, description = ? WHERE id = ?";
  $stmt = $pdo->prepare($sql);

  // Выполняем запрос
  $success = $stmt->execute([
    $nickname,
    $email,
    empty($telegram_username) ? null : $telegram_username,
    empty($description) ? null : $description,
    $user_id
  ]);

  if ($success) {
    $_SESSION['user_nickname'] = $nickname;

    // Перенаправляем обратно в личный кабинет
    header('Location: /pages/account.php#edit');
    exit();
  } else {
    showErrorPage(['Ошибка при обновлении данных в базе данных']);
  }
} catch (PDOException $e) {
  showErrorPage(['Ошибка базы данных: ' . $e->getMessage()]);
}


// Show errors page
function showErrorPage($errors) {
  $title = "Ошибка редактирования профиля";
  $link_href = "/pages/account.php";
  $link_title  = "В личный кабинет";
  require_once "../pages/errors.php";
}
