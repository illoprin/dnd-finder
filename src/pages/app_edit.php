<?
session_start();
require_once "../config.php";

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // TODO process form data
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>D&D Finder — Редактирование заявки</title>

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
      <h3 class="mb-4 text-center">Редактирование заявки</h3>

      <form>
        <div class="mb-3">
          <label for="title" class="form-label">Название заявки</label>
          <input type="text" class="form-control" id="title" name="title">
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Описание заявки</label>
          <textarea class="form-control" id="description" rows="6" placeholder="Опиши суть своей заявки..." name="description"></textarea>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Изображение заявки</label>
          <input type="file" class="form-control" id="image" accept="image/*">
          <img id="preview" class="img-preview" alt="Предпросмотр изображения">
        </div>

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-accent px-4">Сохранить изменения</button>
        </div>
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