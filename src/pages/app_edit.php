<?
session_start();
require_once "../config.php";

if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit();
}

// Check id param
$id = $_GET['id'] ?? '';

if (empty($id)) {
  header("Location: /");
  exit;
}

$id = (int)$id;
$error = "";

// Check ownership
try {
  $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
  $stmt->execute([$id]);
  $app = $stmt->fetch();
} catch (PDOException $e) {
  $error = "Ошибка базы данных " . $e->getMessage();
}

// App not found
if (!$app) {
  header("Location: /");
  exit;
}

// Has no edit permissions
if ($app['user_id'] != $_SESSION['user_id']) {
  header("Location: /");
  exit;
}

$errors = [];

// Process form data
if ($_SERVER['REQUEST_METHOD'] === "POST") {

  // Get form data
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $type = $_POST['type'];
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

    // Process file
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['image'];

      // Check file size and format
      $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
      array_map(function ($elem) {
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
        // Delete old one
        if (file_exists(".." . $app['image_url'])) {
          unlink(".." . $app['image_url']);
        }
      }
    }

    // Update app entry
    if (empty($errors)) {
      try {

        // Check updates
        $update_values = [];
        $update_params = [];

        // Check title
        if ($title !== $app['title']) {
          $update_params[] = "title = ?";
          $update_values[] = $title;
        }
        // Check description
        if ($description !== $app['description']) {
          $update_params[] = "description = ?";
          $update_values[] = $description;
        }
        // Check type
        if ($type !== $app['type']) {
          $update_params[] = "type = ?";
          $update_values[] = $type;
        }
        // Check image
        if ($image_url) {
          $update_params[] = "image_url = ?";
          $update_values[] = $image_url;
        }
        $update_values[] = $id;

        // Check nothing to update case
        if (!empty($update_params)) {
          $stmt = $pdo->prepare(
            "UPDATE applications SET " . implode(", ", $update_params) . " WHERE id = ?"
          );
          $stmt->execute($update_values);
        }

        header("Location: /pages/app_edit.php?id=$id");
        exit;
      } catch (PDOException $e) {
        $errors[] = "Ошибка при сохранении заявки: " . $e->getMessage();
        // If image loaded and entry updated with errors - delete image
        if ($image_url && file_exists(".." . $image_url)) {
          unlink(".." . $image_url);
        }
      }
    }
  }
}

$error ? array_push($errors, $error) : "";

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Редактирование заявки</title>
  <? require_once "../components/head.php" ?>
</head>

<body>
  <? require_once "../components/navbar.php" ?>

  <!-- Contents -->
  <div class="container my-3">

    <div class="mb-4">
      <a
        href="/pages/app.php?id=<?= $id ?>"
        class="btn btn-outline-light">
        На страницу заявки
      </a>
    </div>

    <div class="card p-4 shadow-lg">
      <h3 class="mb-4 text-center">Редактирование заявки</h3>

      <!-- Error Block -->
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
            value="<?= $app['title'] ?>"
            require>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Описание заявки</label>
          <textarea
            class="form-control"
            id="description"
            rows="6"
            placeholder="Опиши суть своей заявки..."
            name="description"
            minlength="10"
            require><?= $app['description'] ?></textarea>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Изображение заявки</label>
          <input
            type="file"
            class="form-control"
            id="image"
            accept="image/*"
            name="image"
          >
          <img
            id="preview"
            src="<?= $app['image_url'] ?>"
            class="img-preview"
            alt="Предпросмотр изображения">
        </div>

        <div class="mb-3">
          <label for="type" class="form-label" for="type">Тип заявки - <strong><?= $app_types[$app['type']] ?></strong></label>
          <select class="form-select" name="type" id="type">
            <option value="master"><?= $app_types['master'] ?></option>
            <option value="player"><?= $app_types['player'] ?></option>
          </select>
        </div>

        <div class="text-center mt-4">
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