<?
session_start();
require_once "../config.php";

if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // Получаем данные из формы
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $type = $_POST['type'] ?? '';
  $image_url = null;

  // Валидация данных
  if (empty($title) || empty($description) || empty($type)) {
    $errors[] = "Все поля обязательны для заполнения!";
  } elseif (strlen($title) > 200) {
    $errors[] = "Название заявки слишком длинное (максимум 255 символов)";
  } else {

    // WARN Code duplication
    // Обработка загрузки изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['image'];


      // Проверка типа файла
      $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
      $file_type = mime_content_type($file['tmp_name']);

      if (!in_array($file_type, $allowed_types)) {
        $errors[] = "Недопустимый формат изображения. Разрешены: JPEG, JPG, PNG, WEBP, GIF";
      }
      // Проверка размера файла (3MB)
      elseif ($file['size'] > 3 * 1024 * 1024) {
        $errors[] = "Размер изображения не должен превышать 3MB";
      } else {
        // Создаем папку uploads если ее нет (с рекурсивным созданием)
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
          if (!mkdir($upload_dir, 0755, true)) {
            $errors[] = "Не удалось создать папку для загрузки файлов";
          }
        }

        // Проверяем, доступна ли папка для записи
        if (empty($error) && !is_writable($upload_dir)) {
          $errors[] = "Папка для загрузки недоступна для записи";
        }

        if (empty($errors)) {
          // Генерируем уникальное имя файла
          $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
          $filename = uniqid() . '_' . time() . '.' . $file_extension;
          $file_path = $upload_dir . $filename;

          // Пытаемся загрузить файл
          if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $image_url = "/uploads/" . $filename;
          } else {
            $errors[] = "Ошибка при загрузке изображения. Проверьте права доступа к папке uploads";
            // Дополнительная диагностика
            error_log("Upload error: cannot move file to " . $file_path);
            error_log("Upload dir permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
          }
        }
      }
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $errors[] = "Ошибка при загрузке файла: " . $_FILES['image']['error'];
    }

    // Если нет ошибок - сохраняем заявку в БД
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("INSERT INTO applications (user_id, type, title, description, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $type, $title, $description, $image_url]);

        header("Location: /pages/account.php#apps");
        exit;
      } catch (PDOException $e) {
        $errors[] = "Ошибка при сохранении заявки: " . $e->getMessage();

        // Если была загружена картинка, но произошла ошибка БД - удаляем ее
        if ($image_url && file_exists(".." . $image_url)) {
          unlink(".." . $image_url);
        }
      }
    }
  }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>D&D Finder — Новая заявка</title>

  <? require_once "../components/head.php" ?>

</head>

<body>
  <? require_once "../components/navbar.php" ?>

  <!-- Contents -->
  <div class="container my-3">
    <div class="mb-4">
      <a href="/pages/account.php#apps" class="btn btn-outline-light">← В личный кабинет</a>
    </div>

    <div class="card p-4 shadow-lg">
      <h3 class="mb-4 text-center">Новая заявка</h3>

      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="title" class="form-label">Название заявки</label>
          <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Описание заявки</label>
          <textarea class="form-control" id="description" rows="6" placeholder="Опиши суть своей заявки..." name="description" required></textarea>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Изображение заявки</label>
          <input type="file" class="form-control" id="image" accept="image/*" name="image" required>
          <img id="preview" class="img-preview" alt="Предпросмотр изображения">
        </div>


        <div class="mb-3">
          <label for="image" class="form-label" for="type">Тип заявки</label>
          <select class="form-select" name="type" id="type">
            <option value="master">Я мастер, ищу игроков</option>
            <option value="player" default>Я игрок, ищу мастера</option>
          </select>
        </div>


        <div class="text-center mt-3">
          <button type="submit" class="btn btn-accent px-4">Сохранить изменения</button>
        </div>


        <? foreach ($errors as $error): ?>
          <div class="alert alert-danger m-0 mt-3" role="alert">
            <? echo $error; ?>
          </div>
        <? endforeach; ?>
      </form>
    </div>
  </div>

  <? require_once "../components/footer.php" ?>

  <script>
    // Превью изображения при загрузке
    const input = document.getElementById('image');
    const preview = document.getElementById('preview');

    document.addEventListener("DOMContentLoaded", () => {
      if (!preview.attributes.src) {
        preview.classList.add("d-none");
      }
    });

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src = e.target.result;
          preview.classList.remove("d-none");
        };
        reader.readAsDataURL(file);
      }
    });
  </script>

</body>

</html>