<?
session_start();
require_once "../config.php";

if (!isLoggedIn()) {
  header("Location: /pages/auth.php#login");
  exit();
}

$error = "";
$user_id = $_SESSION['user_id'];

try {
  // Get user data from database
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user_entry = $stmt->fetch();

  // Get user's app entries from database
  $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $user_apps = $stmt->fetchAll();

  // Get liked apps from database
  $stmt = $pdo->prepare("
    SELECT a.*, u.nickname as author_nickname
    FROM applications a 
    INNER JOIN liked_apps la ON a.id = la.application_id 
    INNER JOIN users u ON a.user_id = u.id 
    WHERE la.user_id = ?
    ORDER BY la.id DESC
  ");
  $stmt->execute([$user_id]);
  $favorites = $stmt->fetchAll();
} catch (PDOException $e) {
  $error = "Ошибка базы данных " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Личный кабинет</title>

  <? require_once "../components/head.php" ?>
</head>

<body>

  <? require_once "../components/navbar.php" ?>

  <!-- Content -->
  <div class="container my-5">


    <!-- Error Block -->
    <? if (!empty($error)): ?>
      <div class="alert alert-danger">
        <?= $error ?>
      </div>
    <? endif; ?>

    <!-- Upper block: Account Data -->
    <div class="mb-4 p-3 rounded card">
      <div class="row g-3">
        <div class="col-md-6">
          <h4 class="mb-1 fw-bold">
            <?= $user_entry['nickname'] ?>
          </h4>
          <p class="mb-0">
            Email:
            <span class="fw-bold"><?= $user_entry['email'] ?></span>
          </p>
          <p class="mb-0">
            Зарегистрирован:
            <span class="fw-bold"><?= $user_entry['created_at'] ?></span>
          </p>
        </div>
        <div class="col-md-6 text-end">
          <a href="/actions/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-left"></i></a>
          <a href="/pages/delete_account.php" class="btn btn-outline-danger"><i class="bi bi-trash-fill"></i></a>

        </div>
      </div>

    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
          Редактировать профиль
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
          Безопасность
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="apps-tab" data-bs-toggle="tab" data-bs-target="#apps" type="button" role="tab">
          Мои заявки
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="favorites-tab" data-bs-toggle="tab" data-bs-target="#favorites" type="button" role="tab">
          Избранные заявки
        </button>
      </li>
    </ul>

    <div class="tab-content mt-3">

      <!-- Edit profile data -->
      <div class="tab-pane fade show active" id="edit" role="tabpanel">
        <form class="mt-4" name="edit" action="/actions/profile_edit.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Никнейм</label>
            <input
              type="text"
              class="form-control"
              value="<?= $user_entry['nickname'] ?>"
              name="nickname">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              value="<?= $user_entry['email'] ?>"
              name="email">
          </div>
          <div class="mb-3">
            <label class="form-label">Telegram</label>
            <div class="input-group mb-3">
              <span class="input-group-text" id="telegram-username">@</span>
              <input
                type="text"
                class="form-control"
                value="<?= $user_entry['telegram_username'] ?>"
                aria-describedby="telegram-username"
                name="telegram_username">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Описание профиля</label>
            <textarea
              class="form-control"
              name="description"
              name="description"><?= $user_entry['description'] ?></textarea>
          </div>
          <div class="alert alert-warning d-none" id="editFormAlert">
            Форма изменена, сохраните изменения
          </div>
          <button type="submit" class="btn btn-accent">Сохранить</button>
        </form>
      </div>
      <!-- Edit login and password -->
      <div class="tab-pane fade" id="security" role="tabpanel">
        <form name="security" action="/actions/security_edit.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Логин</label>
            <input
              type="text"
              class="form-control"
              value="<?= $user_entry['login'] ?>"
              name="login" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="securityCurrentPassword">Текущий пароль</label>
            <input
              type="password"
              class="form-control"
              name="current_password"
              id="securityCurrentPassword"
              placeholder="Введите текущий пароль">
          </div>
          <div class="mb-3">
            <label class="form-label" for="securityNewPassword">Новый пароль</label>
            <input
              type="password"
              class="form-control"
              id="securityNewPassword"
              name="new_password"
              placeholder="Введите новый пароль">
          </div>
          <div class="alert alert-warning d-none" id="securityFormAlert">
            Форма изменена, сохраните изменения
          </div>
          <button type="submit" class="btn btn-accent">Сохранить</button>
        </form>
      </div>
      <!-- My apps -->
      <div class="tab-pane fade" id="apps" role="tabpanel">
        <? if (!empty($user_apps)): ?>
          <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">

            <? foreach ($user_apps as $app): ?>
              <div class="col">
                <div class="card h-100">
                  <img
                    src="<?= $app['image_url'] ?>"
                    class="card-img-top"
                    alt="Заявка"
                    style="height: 200px; object-fit: cover;">
                  <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($app['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars(mb_strimwidth($app['description'], 0, 150, "...")) ?></p>
                  </div>
                  <div class="card-footer d-flex justify-content-between p-3">
                    <a href="/pages/app_edit.php?id=<?= $app['id'] ?>" class="btn btn-accent">Редактировать</a>
                    <a href="/actions/app_delete.php?id=<?= $app['id'] ?>" class="btn btn-outline-danger">Удалить</a>
                  </div>
                </div>
              </div>
            <? endforeach; ?>

          </div>
        <? else: ?>
          <h1 class="fw-normal">У вас нет активных заявок, <a class="profile-link" href="/pages/app_new.php">создайте</a></h1>
        <? endif; ?>
      </div>

      <!-- Favorites -->
      <div class="tab-pane fade" id="favorites" role="tabpanel">
        <? if (!empty($favorites)): ?>
          <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">
            <? foreach ($favorites as $app): ?>
              <!-- Favorite app card -->
              <div class="col">
                <div class="card h-100">
                  <img
                    src="<?= $app['image_url'] ?>"
                    class="card-img-top"
                    alt="Заявка"
                    style="height: 200px; object-fit: cover;">
                  <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($app['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars(mb_strimwidth($app['description'], 0, 150, "...")) ?></p>
                  </div>
                  <div class="card-footer d-flex justify-content-between p-3">
                    <a href="/pages/app.php?id=<?= $app['id'] ?>" class="btn btn-accent">Перейти к заявке</a>
                    <a href="/actions/favorite_handler.php?id=<?= $app['id'] ?>&is_favorite=1&hash=favorites" class="btn btn-outline-danger">Удалить из избранных</a>
                  </div>
                </div>
              </div>
            <? endforeach; ?>
          </div>
        <? else: ?>
          <h1 class="fw-normal">Нет избранных заявок</h1>
        <? endif; ?>
      </div>

    </div>
  </div>

  <? require_once "../components/footer.php" ?>

  <script src="/js/tabsManager.js"></script>
  <script src="/js/validation.js"></script>
  <script src="/js/account.js"></script>
</body>

</html>