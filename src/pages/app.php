<?
session_start();
require_once "../config.php";

if (!isset($_GET['id'])) {
  header("Location: /pages/search.php");
  exit;
}

$error = "";

$app_id = $_GET['id'] ?? '';

if (empty($app_id)) {
  header("Location: /pages/search.php");
  exit;
}

try {
  $stmt = $pdo->prepare(
    "SELECT
    a.*, u.nickname, u.telegram_username
  FROM applications a
  LEFT JOIN users u ON a.user_id = u.id
  WHERE a.id = ?"
  );
  $stmt->execute([$app_id]);
  $is_favorite = isLoggedIn() ? isApplicationFavorite($app_id, $_SESSION['user_id']) : false;
} catch (PDOException $e) {
  $error = "Ошибка базы данных " . $e->getMessage();
}
$app_entry = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Заявка</title>
  <? require_once "../components/head.php" ?>
</head>

<body>

  <? require_once "../components/navbar.php" ?>

  <!-- Contents -->
  <div class="container my-5">

    <!-- Error Block -->
    <? if (!empty($error)): ?>
      <div class="alert alert-danger">
        <?= $error ?>
      </div>
    <? endif; ?>

    <a href="search.php" class="btn btn-outline-light mb-4">&larr; К заявкам</a>

    <div class="row g-4">

      <!-- Left column -->
      <div class="col-md-6 text-center">
        <img src="<?= $app_entry['image_url'] ?>" class="img-fluid rounded shadow" alt="Изображение заявки">
      </div>

      <!-- Right column -->
      <div class="col-md-6">
        <div class="d-flex justify-content-between mb-3">
          <h2><?= $app_entry['title'] ?></h2>

          <? if (isLoggedIn()) : ?>
            <!-- Favorites button -->
            <? if ($_SESSION['user_id'] != $app_entry['user_id']): ?>
              <div>

                <? if (!$is_favorite): ?>
                  <a
                    class="btn btn-outline-light"
                    href="/actions/favorite_handler.php?id=<?= $app_id ?>&is_favorite=0&redirect=<?= $_SERVER['REQUEST_URI'] ?>"
                    title="Добавить в избранное">
                    <i class="bi bi-bookmark-plus"></i>
                  </a>
                <? else: ?>
                  <a
                    class="btn btn-outline-light"
                    href="/actions/favorite_handler.php?id=<?= $app_id ?>&is_favorite=1&redirect=<?= $_SERVER['REQUEST_URI'] ?>"
                    title="Удалить из избранного">
                    <i class="bi bi-bookmark-plus-fill"></i>
                  </a>
                <? endif; ?>

              </div>

            <? endif; ?>

            <!-- Edit button -->
            <? if ($_SESSION['user_id'] == $app_entry['user_id']): ?>
              <div>
                <a
                  class="btn btn-outline-light"
                  href="/pages/app_edit.php?id=<?= $app_entry['id'] ?>"
                  title="Редактировать">
                  <i class="bi bi-pencil-square"></i>
                </a>
              </div>
            <? endif; ?>

          <? endif; ?>

        </div>

        <div class="accent-block mb-3">
          <span>Автор:</span>
          <a href="account_public.php?id=<?= $app_entry['user_id'] ?>" class="profile-link mx-2">
            <?= $app_entry['nickname'] ?>
          </a>
        </div>

        <p class="mb-3">
          <?= $app_entry['description'] ?>
        </p>

        <div class="accent-block mt-3">
          <a href="https://t.me/<?= $app_entry['telegram_username'] ?>" class="profile-link">
            <i class="bi bi-telegram"></i>
            <?= $app_entry['telegram_username'] ?>
          </a>
        </div>
      </div>
    </div>

  </div>

  <? require_once "../components/footer.php" ?>

</body>

</html>