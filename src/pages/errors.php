<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — <?= $title ?></title>
  <? require_once "../components/head.php" ?>
</head>

<body class="justify-content-center align-items-center flex-column">
  <div class="container text-center">
    <p class="fw-bold fs-1 mb-3">
      Есть ошибки ⚠️
    </p>
    <? foreach ($errors as $error): ?>
      <div class="alert alert-danger" role="alert">
        <? echo $error; ?>
      </div>
    <? endforeach; ?>
    <a href="<?= $link_href ?>" class="btn btn-accent"><?= $link_title ?></a>
  </div>
</body>

</html>