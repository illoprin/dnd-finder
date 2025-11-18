<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <? require_once "../components/head.php" ?>
  <title>D&D Finder — <?= $title ?></title>
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