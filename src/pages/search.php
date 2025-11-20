<?
session_start();
require_once "../config.php";

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6; // Количество заявок на странице
$offset = ($page - 1) * $per_page;

// Получаем общее количество заявок
$total_stmt = $pdo->query("SELECT COUNT(*) FROM applications");
$total_apps = $total_stmt->fetchColumn();
$total_pages = ceil($total_apps / $per_page);

// Получаем заявки для текущей страницы
$stmt = $pdo->prepare("
    SELECT *
    FROM applications
    ORDER BY crated_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Поиск заявок</title>
  <? require_once "../components/head.php" ?>
</head>

<body>
  <? require_once "../components/navbar.php" ?>

  <!-- Контент -->
  <div class="container my-5">

    <!-- Пагинация -->
    <? if ($total_pages > 1): ?>
      <nav aria-label="Навигация по страницам">
        <ul class="pagination justify-content-center mb-4">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>" <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Назад</a>
          </li>

          <? for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <? endfor; ?>

          <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>" <?= $page >= $total_pages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Вперёд</a>
          </li>
        </ul>
      </nav>
    <? endif; ?>

    <!-- Карточки заявок -->
    <div class="row g-4">
      <? if (empty($applications)): ?>
        <div class="col-12 text-center">
          <p class="text-muted">Заявок пока нет</p>
        </div>
      <? else: ?>
        <? foreach ($applications as $app): ?>
          <div class="col-md-4">
            <div class="card h-100 app">
              <img src="<?= $app['image_url'] ? htmlspecialchars($app['image_url']) : 'https://placehold.co/600x400?text=Нет+изображения' ?>"
                class="card-img-top" alt="<?= htmlspecialchars($app['title']) ?>"
                style="height: 200px; object-fit: cover;" />
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold"><?= htmlspecialchars($app['title']) ?></h5>
                <p class="card-text flex-grow-1"><?= htmlspecialchars(mb_strimwidth($app['description'], 0, 150, "...")) ?></p>
                <span class="d-block profile-link">
                  <?= $app_types[$app['type']] ?>
                </span>
                <a href="/pages/app.php?id=<?= $app['id'] ?>" class="btn btn-accent mt-2">Детальнее</a>
              </div>
            </div>
          </div>
        <? endforeach; ?>
      <? endif; ?>
    </div>

  </div>

  <? require_once "../components/footer.php" ?>

</body>

</html>