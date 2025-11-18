<?
session_start();
require_once "../config.php";

if (!isset($_GET['id'])) {
  header("Location: /");
  exit;
}

// User id
$id = $_GET['id'] ?? '';

if (empty($id)) {
  header("Location: /");
  exit;
}

$id = (int)$id;
$is_owner = $id == $_SESSION['user_id'];

$error = "";
try {
  // Fetch user data
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$id]);
  $user = $stmt->fetch();

  // Fetch user's apps
  $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ?");
  $stmt->execute([$id]);
  $apps = $stmt->fetchAll();
} catch (PDOException $e) {
  $error = "Ошибка базы данных " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>D&D Finder — Профиль пользователя</title>

  <? require_once "../components/head.php" ?>
</head>

<body class="d-flex flex-column min-vh-100">

  <? require_once "../components/navbar.php" ?>

  <!-- Contents -->
  <div class="container my-5">
    
    <!-- Error Block -->
    <? if(!empty($error)): ?>
      <div class="alert alert-danger">
        <?= $error ?>
      </div>
    <? endif; ?>

    <!-- First part: profile -->
    <div class="row mb-5">
      <div class="col-md-6">
        <h2 class="fw-bold mb-3">
          <?= htmlspecialchars($user['nickname']) ?>
        </h2>

        <p><strong>Telegram:</strong>
          <a
            href="https://t.me/<?= $user['telegram_username'] ?>"
            target="_blank"
            class="profile-link">
            <i class="bi bi-telegram"></i>
            @<?= $user['telegram_username'] ?>
          </a>
        </p>

        <p><strong>Email:</strong> <?= $user['email'] ?></p>

        <p><strong>Описание профиля:</strong>
          <?= $user['description']
           ? htmlspecialchars($user['description']) : '<small class="fs-5 text-secondary">Без описания</small>' ?>
        </p>
      </div>
    </div>

    <!-- Second part: apps -->
    <div>
      <h3 class="fw-bold mb-4">Заявки</h3>

      <div class="row row-cols-1 row-cols-md-3 g-4">

        <? if (!empty($apps)): ?>

        <? foreach ($apps as $app): ?>
          <div class="col">
            <div class="card h-100 app">
              <img
                src="<?= $app['image_url'] ?>"
                class="card-img-top"
                alt="Заявка"
                style="height: 200px; object-fit: cover;"
              >
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($app['title']) ?></h5>
                <p class="card-text"><?= htmlspecialchars(mb_strimwidth($app['description'], 0, 150, "...")) ?></p>
                <a href="/pages/app.php?id=<?= $app['id'] ?>" class="btn btn-accent mt-auto">Детальнее</a>
              </div>
            </div>
          </div>
        <? endforeach; ?>

        <? else: ?>
          <small class="fs-5 text-secondary">У пользователя нет активных заявок</small>
        <? endif; ?>

      </div>
    </div>
  </div>

  <? require_once "../components/footer.php" ?>

</body>

</html>