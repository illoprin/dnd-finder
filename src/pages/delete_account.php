<?php
session_start();
// Включаем файл с подключением к БД
require_once '../config.php';

// Устанавливаем кодировку
header('Content-Type: text/html; charset=utf-8');

// Проверяем, авторизован ли пользователь
if (!is_logged_in()) {
  header('Location: /pages/auth.php#login');
  exit;
}

// Обрабатываем форму удаления аккаунта
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password = $_POST['password'] ?? '';
  $confirm_text = trim($_POST['confirm_text'] ?? '');
  $errors = [];

  // Валидация данных
  if (empty($password)) {
    $errors[] = "Введите ваш пароль для подтверждения";
  }

  if ($confirm_text !== 'удалить мой аккаунт') {
    $errors[] = "Пожалуйста, точно введите фразу 'удалить мой аккаунт' для подтверждения";
  }

  if (empty($errors)) {
    try {
      // Получаем хеш пароля пользователя из БД
      $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
      $stmt->execute([$_SESSION['user_id']]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password_hash'])) {
        // Пароль верный - удаляем аккаунт
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        // Проверяем, был ли аккаунт удален
        if ($stmt->rowCount() > 0) {
          // Уничтожаем сессию
          session_destroy();

          // Показываем страницу подтверждения удаления
          show_success_page();
          exit;
        } else {
          $errors[] = "Ошибка при удалении аккаунта";
        }
      } else {
        $errors[] = "Неверный пароль";
      }
    } catch (PDOException $e) {
      $errors[] = "Ошибка базы данных: " . $e->getMessage();
    }
  }

  // Если есть ошибки, показываем форму с ошибками
  if (!empty($errors)) {
    show_delete_form($errors);
  }
} else {
  // Показываем форму подтверждения удаления
  show_delete_form();
}

/**
 * Показывает форму для удаления аккаунта
 */
function show_delete_form($errors = [])
{
?>
  <!DOCTYPE html>
  <html lang="ru">

  <head>
    <title>Удаление аккаунта</title>
    <? require_once "../components/head.php" ?>
    <style>
      .warning-icon {
        font-size: 3rem;
        color: #e74c3c;
        margin-bottom: 1rem;
        text-align: center;
      }

      .requirements {
        background: #fff3cd;
        color: #2d2321;

        border: 1px solid #ffeaa7;
        border-radius: 5px;
        padding: 1rem;
        margin-bottom: 1rem;
      }
    </style>
  </head>

  <body class="justify-content-center">
    <div class="container">
      <div class="card">
        <div class="card-body">
          <div class="warning-icon">⚠️</div>
          <h1 class="text-center mb-4">Удаление аккаунта</h1>

          <div class="alert alert-warning">
            <strong>Внимание!</strong> Это действие необратимо. Все ваши данные будут безвозвратно удалены.
          </div>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="POST">
            <div class="requirements">
              <h6>Для удаления аккаунта необходимо:</h6>
              <ul class="mb-0">
                <li>Ввести ваш текущий пароль</li>
                <li>Ввести точную фразу: <code>удалить мой аккаунт</code></li>
              </ul>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-4">
              <label for="confirm_text" class="form-label">
                Введите фразу: <code>удалить мой аккаунт</code>
              </label>
              <input type="text" class="form-control" id="confirm_text" name="confirm_text" required>
              <div class="form-text">Это необходимо для подтверждения серьезности ваших намерений</div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Вы уверены, что хотите удалить аккаунт? Это действие нельзя отменить!')">
                УДАЛИТЬ АККАУНТ
              </button>
              <a href="/pages/account.php" class="btn btn-accent-outline">Отмена - вернуться в аккаунт</a>
              <a href="/" class="btn btn-accent-outline">На главную</a>
            </div>
          </form>
        </div>

      </div>
    </div>
  </body>

  </html>
<?php
}

/**
 * Показывает страницу успешного удаления аккаунта
 */
function show_success_page()
{
?>
  <!DOCTYPE html>
  <html lang="ru">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аккаунт удален</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .success-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        padding: 2rem;
        text-align: center;
        max-width: 500px;
        width: 100%;
      }

      .success-icon {
        font-size: 4rem;
        color: #95a5a6;
        margin-bottom: 1rem;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <div class="success-card">
        <div class="success-icon">🗑️</div>
        <h1 class="text-secondary mb-4">Аккаунт удален</h1>
        <p class="mb-3">Ваш аккаунт и все связанные с ним данные были успешно удалены.</p>
        <p class="mb-4">Мы сожалеем, что вы решили уйти. Надеемся увидеть вас снова!</p>
        <div class="d-grid gap-2">
          <a href="/" class="btn btn-primary">На главную</a>
          <a href="/pages/auth.php#register" class="btn btn-outline-primary">Создать новый аккаунт</a>
        </div>
      </div>
    </div>
  </body>

  </html>
<?php
}
?>