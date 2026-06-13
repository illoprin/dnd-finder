<?
session_start();
require_once "config.php"

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Найди игроков и мастеров</title>
  <? require_once "components/head.php" ?>
</head>

<body>

  <? require_once "components/navbar.php" ?>

  <!-- Hero -->
  <main class="container-lg my-auto text-center">

    <h1 class="display-4 fw-bold">Найди свою игровую партию в Dungeons & Dragons</h1>
    <p class="lead mt-3">Сервис для игроков и мастеров, который помогает быстро находить компанию для приключений.
    </p>

    <div class="d-flex align-items-center justify-content-center flex-column gap-1">
      <? if (is_logged_in()): ?>
        <a href="/pages/tinder.php" class="btn btn-accent btn-lg mt-3">
          Начать поиск
        </a>
        <a href="/pages/search.php" class="text-secondary">
          Legacy
        </a>
      <? else: ?>
        <a href="/pages/search.php" class="btn btn-accent btn-lg mt-3">
          Просмотреть заявки
        </a>
      <? endif; ?>
    </div>

  </main>

  <? require_once "components/footer.php" ?>

</body>

</html>