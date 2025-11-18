<?
session_start();
require_once "../config.php";

if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit();
}

$errors = [];

// Process form data
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // Get form data
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $type = $_POST['type'] ?? '';
  $image_url = null;

  // Validate title
  if (preg_match('/[\/<>]/', $title)) {
    $errors[] = "Заголовок содержит запрещённые символы: /, <, >";
  }

  // Validate description
  if (preg_match('/[\/<>]/', $description)) {
    $errors[] = "Описание содержит запрещённые символы: /, <, >";
  }

  // Validate type
  if (empty($type)) {
    $errors[] = "Тип заявки должен быть заполнен";
  }

  // Load image
  if (empty($errors)) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['image'];

      // Check file size and format
      $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
      array_map(function($elem) {
        $errors[] = $elem;
      }, check_file($file, $allowed_types, 3 * 1024 * 1024));
      
      // Create uploads dir
      if (empty($errors)) {
        $upload_dir = "../uploads/";
        if (!create_or_check_directory($upload_dir)) {
          $errors[] = "Не удаётся создать папку для загрузки или она недоступна для записи";
        }
      }

      // Upload file
      if (empty($errors)) {
        $image_url = upload_file($file, $upload_dir);
        if (!$image_url) {
          $errors[] = "Ошибка при загрузке изображения";
        }
      }
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $errors[] = "Ошибка при загрузке файла: " . $_FILES['image']['error'];
    }
  }

  // Add entry to db
  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("INSERT INTO applications (user_id, type, title, description, image_url) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([$_SESSION['user_id'], $type, $title, $description, $image_url]);

      header("Location: /pages/account.php#apps");
      exit;
    } catch (PDOException $e) {
      $errors[] = "Ошибка при сохранении заявки: " . $e->getMessage();

      // If image loaded and entry added with errors - delete image
      if ($image_url && file_exists(".." . $image_url)) {
        unlink(".." . $image_url);
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

      <? foreach ($errors as $error): ?>
        <div class="alert alert-danger m-0 mt-3" role="alert">
          <? echo $error; ?>
        </div>
      <? endforeach; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="title" class="form-label">Название заявки</label>
          <input
            type="text"
            class="form-control"
            id="title"
            name="title"
            minlength="5"
            required
          >
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Описание заявки</label>
          <textarea
            class="form-control"
            id="description"
            rows="6"
            placeholder="Опиши суть своей заявки..."
            minlength="10"
            name="description"
            required
          ></textarea>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Изображение заявки</label>
          <input
            type="file"
            class="form-control"
            id="image"
            accept="image/*"
            name="image"
            required
          >
          <img id="preview" class="img-preview" alt="Предпросмотр изображения">
        </div>

        <div class="mb-3">
          <label for="image" class="form-label" for="type">Тип заявки</label>
          <select class="form-select" name="type" id="type">
            <option value="master"><?= $app_types['master'] ?></option>
            <option value="player" default><?= $app_types['player'] ?></option>
          </select>
        </div>

        <div class="text-center mt-3">
          <button type="submit" class="btn btn-accent px-4">Сохранить изменения</button>
        </div>

      </form>
    </div>
  </div>

  <? require_once "../components/footer.php" ?>

  <script>
    // Loaded image preview
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