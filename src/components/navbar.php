<?php
require_once __DIR__ . '/../config.php';

$nav_matches = [];
// fetch active matches count and list if user is authenticated
if (is_logged_in()) {
  try {
    $nav_user_id = $_SESSION['user_id'];
    // fetch only accepted matches to display as notifications
    $nav_stmt = $pdo->prepare("
            SELECT r.*, a.title AS app_title,
                   u_creator.nickname AS creator_nickname,
                   u_responder.nickname AS responder_nickname,
                   a.user_id AS creator_id
            FROM responses r
            INNER JOIN applications a ON r.app_id = a.id
            INNER JOIN users u_creator ON a.user_id = u_creator.id
            INNER JOIN users u_responder ON r.user_id = u_responder.id
            WHERE r.status = 'accepted' AND (a.user_id = ? OR r.user_id = ?)
            ORDER BY r.created_at DESC
            LIMIT 5
        ");
    $nav_stmt->execute([$nav_user_id, $nav_user_id]);
    $nav_matches = $nav_stmt->fetchAll();
  } catch (PDOException $e) {
    // fail silently to avoid breaking the layout if database is down
    error_log("navbar match loading failed: " . $e->getMessage());
  }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">

    <div class="d-flex align-items-center">
      <a
        class="navbar-brand fw-bold me-4"
        href="/"
      >D&D Finder</a>
      <ul class="navbar-nav d-flex flex-row gap-3">
        <li class="nav-item">
          <a
            class="nav-link"
            href="/pages/about.php"
          >О нас</a>
        </li>
        <?php if (is_logged_in()): ?>
        <li class="nav-item">
          <a
            class="nav-link text-warning fw-bold"
            href="/pages/tinder.php"
          >
            <i class="bi bi-fire me-1"></i>Tinder
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="d-flex align-items-center gap-3">
      <?php if (is_logged_in()): ?>

      <div class="dropdown">
        <button
          class="btn btn-outline-light position-relative"
          type="button"
          id="matchesDropdown"
          data-bs-toggle="dropdown"
          aria-expanded="false"
          title="Ваши матчи"
        >
          <i class="bi bi-dice-5-fill"></i>
          <?php if (!empty($nav_matches)): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= count($nav_matches) ?>
          </span>
          <?php endif; ?>
        </button>

        <ul
          class="dropdown-menu dropdown-menu-end shadow mt-2"
          aria-labelledby="matchesDropdown"
          style="width: 280px; max-height: 400px; overflow-y: auto;"
        >
          <li class="dropdown-header border-bottom text-white pb-2 fw-bold">Новые совпадения 🎲</li>

          <?php if (!empty($nav_matches)): ?>
          <?php foreach ($nav_matches as $nav_match): ?>
          <?php
                // extract correct partner nickname inside navigation scope
                $nav_is_creator = ($nav_match['creator_id'] == $nav_user_id);
                $nav_partner = $nav_is_creator ? $nav_match['responder_nickname'] : $nav_match['creator_nickname'];
                ?>
          <li>
            <a
              class="dropdown-item py-2 border-bottom border-light"
              href="/pages/account.php#matches"
            >
              <div class="text-truncate fw-bold text-success">@
                <?= htmlspecialchars($nav_partner) ?>
              </div>
              <small
                class="text-white-50 text-wrap d-block"
                style="font-size: 0.75rem;"
              >
                Кампания:
                <?= htmlspecialchars($nav_match['app_title']) ?>
              </small>
            </a>
          </li>
          <?php endforeach; ?>
          <li>
            <a
              class="dropdown-item text-center text-primary small fw-bold py-2"
              href="/pages/account.php#matches"
            >
              Смотреть все контакты
            </a>
          </li>
          <?php else: ?>
          <li class="text-center py-3 small text-secondary">У вас пока нет матчей.<br>Крутите анкеты!</li>
          <?php endif; ?>
        </ul>
      </div>

      <a
        class="btn btn-outline-light"
        title="Новая заявка"
        href="/pages/app_new.php"
      >
        <i class="bi bi-file-earmark-plus"></i>
      </a>

      <span class="text-white-50">
        Привет, <a
          href="/pages/account.php"
          class="text-white fw-bold text-decoration-none border-bottom border-white border-opacity-25"
          title="В личный кабинет"
        >
          <?= htmlspecialchars($_SESSION['user_nickname']) ?>
        </a>
      </span>

      <?php else: ?>
      <a
        href="/pages/auth.php#register"
        class="btn btn-accent me-2"
      >Регистрация</a>
      <a
        href="/pages/auth.php#login"
        class="btn btn-outline-light"
      >Вход</a>
      <?php endif; ?>
    </div>
  </div>
</nav>