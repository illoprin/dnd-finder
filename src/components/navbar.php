<? require_once __DIR__ . '/../config.php'; ?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">D&D Finder</a>
    <div>
      <? if (isLoggedIn()): ?>

        <a class="btn btn-outline-light" title="Новая заявка" href="/pages/app_new.php">
          <i class="bi bi-file-earmark-plus"></i>
        </a>

        <span>
          Привет, <a href="/pages/account.php" class="profile-link"><? echo $_SESSION['user_nickname'] ?></a>
        </span>

      <? else: ?>
        <a href="/pages/auth.php#register" class="btn btn-accent me-2">Регистрация</a>
        <a href="/pages/auth.php#login" class="btn btn-outline-light">Вход</a>
      <? endif; ?>
    </div>
  </div>
</nav>