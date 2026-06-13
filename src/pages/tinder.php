<?php
session_start();

require_once "../config.php";

// check if user is logged in
if (!is_logged_in()) {
  header("Location: /pages/auth.php#login");
  exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$app = null;
$tinder_allowed = false;

try {
  // get excluded application id if user clicked skip
  $skip_id = isset($_GET['skip']) ? (int)$_GET['skip'] : 0;

  // fetch one random application that doesn't belong to the user and hasn't been responded to yet
  $query = "SELECT a.*, u.nickname, u.telegram_username 
              FROM applications a
              JOIN users u ON a.user_id = u.id
              WHERE a.user_id != :user_id 
                AND a.id != :skip_id
                AND a.id NOT IN (
                  SELECT app_id
                  FROM responses
                  WHERE user_id = :user_id
                  AND status IN ('accepted', 'pending')
                )
              ORDER BY RAND() 
              LIMIT 1";

  $stmt = $pdo->prepare($query);
  $stmt->execute([
    'user_id' => $user_id,
    'skip_id' => $skip_id
  ]);

  $app = $stmt->fetch(PDO::FETCH_ASSOC);

  // fetch user and check telegram username
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user_entry = $stmt->fetch();
  $tinder_allowed = !empty($user_entry['telegram_username']);
} catch (PDOException $e) {
  $error = "database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Поиск заявок</title>
  <?php require_once "../components/head.php" ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
  <?php require_once "../components/navbar.php" ?>

  <main class="container-lg my-5">

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($app && $tinder_allowed): ?>
      <div class="card mx-auto shadow" style="max-width: 40rem;">

        <img
          src="<?= htmlspecialchars($app['image_url']) ?>"
          class="card-img-top"
          alt="campaign cover"
          style="height: 350px; object-fit: cover;">

        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge <?= $app['type'] === 'master' ? 'bg-danger' : 'bg-primary' ?>">
              <?= $app['type'] === 'master' ? 'Мастер' : 'Игрок' ?>
            </span>
            <small class="text-secondary">Автор: @<?= htmlspecialchars($app['nickname']) ?></small>
          </div>

          <h4 class="card-title fw-bold"><?= htmlspecialchars($app['title']) ?></h4>
          <p class="card-text" style="white-space: pre-wrap;"><?= htmlspecialchars($app['description']) ?></p>
        </div>

        <div class="card-footer d-flex justify-content-around p-3 border-top-0">
          <a href="tinder.php?skip=<?= $app['id'] ?>" class="btn btn-outline-danger btn-lg rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;" title="Пропустить">
            <i class="bi bi-x-lg fs-4"></i>
          </a>

          <button class="btn btn-success btn-lg rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;" data-bs-toggle="modal" data-bs-target="#respondModal" title="Бросить кубик на подписку!">
            <i class="bi bi-dice-5-fill fs-4"></i>
          </button>
        </div>
      </div>

      <div class="modal fade" id="respondModal" tabindex="-1" aria-labelledby="respondModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="respondModalLabel">Откликнуться на заявку</h5>
              <button type="button" class="btn-close" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../actions/tinder_respond.php" method="POST">
              <div class="modal-body">
                <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                <div class="mb-3">
                  <label for="message" class="form-label">Напишите короткое сообщение мастеру или игроку:</label>
                  <textarea class="form-control" id="message" name="message" rows="4" placeholder="Привет! Хочу поиграть в твоем кампейне..." required></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-success">Отправить отклик 🎲</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <? elseif (!$tinder_allowed): ?>
      <div class="text-center my-5">
        <div class="fs-1 text-muted mb-3">💢</div>
        <h3>Всего один шаг до мэтча!</h3>
        <p class="text-secondary"><a class="profile-link" href="/pages/account.php#edit">Укажите</a> свой Telegram Username, чтобы игроки смогли с вами связаться</p>
        <a href="/pages/account.php#edit" class="btn btn-primary mt-2">Редактировать профиль</a>
      </div>
    <? else: ?>
      <div class="text-center my-5">
        <div class="fs-1 text-muted mb-3">🎲</div>
        <h3>Вы просмотрели все доступные заявки!</h3>
        <p class="text-secondary">Загляните позже или создайте свою заявку в личном кабинете.</p>
        <a href="/pages/account.php" class="btn btn-primary mt-2">В личный кабинет</a>
      </div>
    <? endif; ?>



  </main>

  <? require_once "../components/footer.php" ?>
</body>

</html>