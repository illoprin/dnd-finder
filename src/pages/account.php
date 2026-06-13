<?
session_start();
require_once "../config.php";

if (!is_logged_in()) {
  header("Location: /pages/auth.php#login");
  exit();
}

$errors = [];
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

  // get incoming responses
  // to the user's applications (where status is pending)
  $stmt = $pdo->prepare("
    SELECT r.*, a.title AS app_title, u.nickname AS sender_nickname
    FROM responses r
    INNER JOIN applications a ON r.app_id = a.id
    INNER JOIN users u ON r.user_id = u.id
    WHERE a.user_id = ? AND r.status = 'pending'
    ORDER BY r.created_at DESC
  ");
  $stmt->execute([$user_id]);
  $incoming_responses = $stmt->fetchAll();

  // get successful matches where user responded
  // and creator accepted, OR user accepted someone's response
  $stmt = $pdo->prepare("
    SELECT r.*, a.title AS app_title,
           u_creator.nickname AS creator_nickname, u_creator.telegram_username AS creator_tg,
           u_responder.nickname AS responder_nickname, u_responder.telegram_username AS responder_tg,
           a.user_id AS creator_id
    FROM responses r
    INNER JOIN applications a ON r.app_id = a.id
    INNER JOIN users u_creator ON a.user_id = u_creator.id
    INNER JOIN users u_responder ON r.user_id = u_responder.id
    WHERE r.status = 'accepted' AND (a.user_id = ? OR r.user_id = ?)
    ORDER BY r.created_at DESC
  ");
  $stmt->execute([$user_id, $user_id]);
  $matches = $stmt->fetchAll();
} catch (PDOException $e) {
  $errors[] = "Ошибка базы данных " . $e->getMessage();
}

// clean old responses
$res = clean_old_declined_responses();
array_push($errors, $res['errors']);

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
        <button class="nav-link active" id="matches-tab" data-bs-toggle="tab" data-bs-target="#matches" type="button" role="tab">
          Мои матчи
        </button>
      </li>

      <li class="nav-item">
        <button class="nav-link position-relative" id="responses-tab" data-bs-toggle="tab" data-bs-target="#responses" type="button" role="tab">
          Входящие отклики
          <?php if (!empty($incoming_responses)): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= count($incoming_responses) ?>
            </span>
          <?php endif; ?>
        </button>
      </li>

      <li class="nav-item">
        <button class="nav-link" id="apps-tab" data-bs-toggle="tab" data-bs-target="#apps" type="button" role="tab">
          Мои заявки
        </button>
      </li>

      <li class="nav-item">
        <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
          Редактировать профиль
        </button>
      </li>

      <li class="nav-item">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
          Безопасность
        </button>
      </li>

    </ul>

    <div class="tab-content mt-3">

      <!-- Edit profile data -->
      <div class="tab-pane fade" id="edit" role="tabpanel">
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
                    <a href="/actions/app_delete.php?id=<?= $app['id'] ?>" class="btn btn-outline-danger">
                      <i class="bi bi-trash-fill"></i>
                    </a>
                  </div>
                </div>
              </div>
            <? endforeach; ?>

          </div>
        <? else: ?>
          <div class="text-center my-5">
            <h1 class="fw-normal">У вас нет активных заявок, <a class="profile-link" href="/pages/app_new.php">создайте</a></h1>
          </div>
        <? endif; ?>
      </div>

      <!-- Matches -->

      <div class="tab-pane fade show active" id="matches" role="tabpanel">
        <?php if (!empty($matches)): ?>
          <div class="row row-cols-1 row-cols-md-2 g-4 mt-4">
            <?php foreach ($matches as $match): ?>
              <?php
              // identify who is the other person in this match
              $is_creator = ($match['creator_id'] == $user_id);
              $partner_name = $is_creator ? $match['responder_nickname'] : $match['creator_nickname'];
              $partner_tg = $is_creator ? $match['responder_tg'] : $match['creator_tg'];
              ?>
              <div class="col">
                <div class="card h-100 border-success border-opacity-25 shadow-sm">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3">
                      <i class="bi bi-dice-5-fill me-2"></i>Успешный матч!
                    </h5>
                    <p class="card-text mb-2">
                      По заявке:
                      <a
                        href="/pages/app.php?id=<?= $match['app_id'] ?>"
                        class="profile-link">
                        <?= htmlspecialchars($match['app_title']) ?>
                      </a>
                    </p>
                    <p class="card-text mb-3">
                      Вы играете с:
                      <a
                        href="/pages/account_public.php?id=<?= $match['creator_id'] ?>"
                        class="profile-link">
                        <?= htmlspecialchars($partner_name) ?>
                      </a>
                    </p>

                    <a href="https://t.me/<?= urlencode($partner_tg) ?>" target="_blank" class="btn btn-success w-100">
                      <i class="bi bi-telegram me-2"></i>Написать в Telegram
                    </a>

                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center my-5">
            <h3 class="">Пока нет активных матчей. Время кинуть кубики в <a class="profile-link" href="/pages/tinder.php">Tinder</a>!</h3>
          </div>
        <?php endif; ?>
      </div>

      <!-- Responses -->
      <div class="tab-pane fade" id="responses" role="tabpanel">
        <?php if (!empty($incoming_responses)): ?>
          <div class="row row-cols-1 g-3 mt-4">
            <?php foreach ($incoming_responses as $resp): ?>
              <div class="col">
                <div class="card shadow-sm">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                      Отклик от
                      <strong>
                        <?= htmlspecialchars($resp['sender_nickname']) ?>
                      </strong>
                    </span>
                    <span>
                      На заявку:
                      <a
                        href="/pages/app.php?id=<?= $resp['app_id'] ?>"
                        class="profile-link">
                        <?= htmlspecialchars($resp['app_title']) ?>
                      </a>
                      </small>
                  </div>
                  <div class="card-body">
                    <p class="card-text p-3 rounded" style="white-space: pre-wrap;"><?= htmlspecialchars($resp['message']) ?></p>
                  </div>
                  <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="/actions/response_status.php?id=<?= $resp['id'] ?>&status=declined" class="btn btn-outline-danger">Отклонить</a>
                    <a href="/actions/response_status.php?id=<?= $resp['id'] ?>&status=accepted" class="btn btn-success">Принять 🎲</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center my-5">
            <h3 class="fw-normal">Новых откликов на ваши заявки нет.</h3>
          </div>
        <?php endif; ?>
      </div>


    </div>
  </div>

  <? require_once "../components/footer.php" ?>

  <script src="/js/tabsManager.js"></script>
  <script src="/js/validation.js"></script>
  <script src="/js/account.js"></script>
</body>

</html>