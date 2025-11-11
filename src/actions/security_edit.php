<?
// WARN fix this

session_start();
require_once "../config.php";

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
  header('Location: /pages/login.php');
  exit();
}

// Получаем данные из POST
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$login = trim($_POST['login'] ?? '');

// Валидация данных
$errors = [];

// Проверка текущего пароля
if (empty($current_password)) {
  $errors[] = 'Текущий пароль обязателен для изменения данных';
}

// Проверка логина
if (!empty($login)) {
  if (strlen($login) < 5) {
    $errors[] = 'Логин должен содержать не менее 5 символов';
  } elseif (preg_match('/[\/<>]/', $login)) {
    $errors[] = 'Логин содержит запрещенные символы: /, <, >';
  }
}

// Проверка нового пароля
if (!empty($new_password)) {
  if (strlen($new_password) < 8) {
    $errors[] = 'Новый пароль должен содержать не менее 8 символов';
  } elseif (preg_match('/[\/<>]/', $new_password)) {
    $errors[] = 'Новый пароль содержит запрещенные символы: /, <, >';
  }
}

// Если есть ошибки валидации - показываем их
if (!empty($errors)) {
  showErrorPage($errors);
  exit();
}

// Получаем данные текущего пользователя
$user_id = $_SESSION['user_id'];
try {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();

  if (!$user) {
    showErrorPage(['Пользователь не найден']);
    exit();
  }
} catch (PDOException $e) {
  showErrorPage(['Ошибка базы данных при получении данных пользователя']);
  exit();
}

// Проверяем текущий пароль
if (!password_verify($current_password, $user['password_hash'])) {
  showErrorPage(['Неверный текущий пароль']);
  exit();
}

// Проверяем уникальность логина (если он меняется)
if (!empty($login) && $login !== $user['login']) {
  try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ? AND id != ?");
    $stmt->execute([$login, $user_id]);
    if ($stmt->fetch()) {
      showErrorPage(['Этот логин уже используется другим пользователем']);
      exit();
    }
  } catch (PDOException $e) {
    showErrorPage(['Ошибка базы данных при проверке логина']);
    exit();
  }
}

// Подготавливаем данные для обновления
$update_fields = [];
$update_params = [];

// Логин
if (!empty($login) && $login !== $user['login']) {
  $update_fields[] = 'login = ?';
  $update_params[] = $login;
}

// Пароль
if (!empty($new_password)) {
  $update_fields[] = 'password_hash = ?';
  $update_params[] = password_hash($new_password, PASSWORD_DEFAULT);
}

// Если нечего обновлять
if (empty($update_fields)) {
  showErrorPage(['Нет данных для изменения']);
  exit();
}

// Добавляем ID пользователя в параметры
$update_params[] = $user_id;

// Обновляем данные в базе данных
try {
  $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
  $stmt = $pdo->prepare($sql);
  $success = $stmt->execute($update_params);

  if ($success) {
    // Обновляем данные в сессии, если изменился логин
    if (!empty($login) && $login !== $user['login']) {
      $_SESSION['user_login'] = $login;
    }

    // Перенаправляем обратно в личный кабинет
    header('Location: /pages/account.php#security');
    exit();
  } else {
    showErrorPage(['Ошибка при обновлении данных в базе данных']);
  }
} catch (PDOException $e) {
  showErrorPage(['Ошибка базы данных: ' . $e->getMessage()]);
}


// Функция для показа ошибок
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
    <title>D&D Finder — Ошибка изменения безопасности</title>
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
      <a href="/pages/account.php#security" class="btn btn-accent">В личный кабинет</a>
    </div>
  </body>

  </html>
<?
}
?>