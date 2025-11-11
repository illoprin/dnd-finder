<?
session_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <? require_once "../components/head.php" ?>

  <title>D&D Finder — Регистрация / Вход</title>
</head>

<body>

  <? require_once "../components/navbar.php" ?>

  <!-- Contents -->
  <div class="container-xl mt-auto" style="max-width: 500px;">


    <div class="card p-4">
      <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="pills-register-tab" data-bs-toggle="pill"
            data-bs-target="#pills-register" type="button" role="tab">Регистрация</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login"
            type="button" role="tab">Вход</button>
        </li>
      </ul>
      <div class="tab-content" id="pills-tabContent">

        <!-- Alert -->
        <div id="validationAlert" class="alert alert-dismissible d-none" role="alert">
          <span id="alertMessage"></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Registration -->
        <div class="tab-pane fade show active" id="pills-register" role="tabpanel">
          <form name="registration" action="/actions/registration.php" method="POST">
            <div class="mb-3">
              <label for="regLogin" class="form-label">Логин</label>
              <input type="text" class="form-control" id="regLogin" name="login" required>
            </div>
            <div class="mb-3">
              <label for="regNickname" class="form-label">Никнейм</label>
              <input type="text" class="form-control" id="regNickname" name="nickname" required>
            </div>
            <div class="mb-3">
              <label for="regEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="regEmail" name="email" required>
            </div>
            <div class="mb-3">
              <label for="regPassword" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="regPassword" name="password" required>
            </div>
            <div class="mb-3">
              <label for="regPasswordRepeat" class="form-label">Повторение пароля</label>
              <input type="password" class="form-control" id="regPasswordRepeat" name="password_repeat" required>
            </div>
            <button type="submit" class="btn btn-accent w-100">Зарегистрироваться</button>
          </form>
        </div>

        <!-- Login -->
        <div class="tab-pane fade" id="pills-login" role="tabpanel">
          <form name="login" action="/actions/login.php" method="POST">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email или логин</label>
              <input type="text" class="form-control" id="loginEmail" name="login" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="loginPassword" name="password" required>
            </div>
            <button type="submit" class="btn btn-accent w-100">Войти</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="/js/auth.js"></script>

  <? require_once "../components/footer.php" ?>

</body>

</html>