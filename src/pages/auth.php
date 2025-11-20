<?
session_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <title>D&D Finder — Регистрация / Вход</title>
  <? require_once "../components/head.php" ?>
</head>

<body>

  <? require_once "../components/navbar.php" ?>

  <!-- Contents -->
  <div class="container-xl mt-auto" style="max-width: 500px;">

    <div class="card p-4">
      <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="register-tab" data-bs-toggle="tab"
            data-bs-target="#register" type="button" role="tab">Регистрация</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="login-tab" data-bs-toggle="tab" data-bs-target="#login"
            type="button" role="tab">Вход</button>
        </li>
      </ul>
      <div class="tab-content">

        <!-- Registration -->
        <div class="tab-pane fade show active" id="register" role="tabpanel">
          <form name="registration" action="/actions/registration.php" method="POST">
            <div class="mb-3">
              <label for="regLogin" class="form-label">Логин</label>
              <input type="text" class="form-control" name="login" required>
            </div>
            <div class="mb-3">
              <label for="regNickname" class="form-label">Никнейм</label>
              <input type="text" class="form-control"  name="nickname" required>
            </div>
            <div class="mb-3">
              <label for="regEmail" class="form-label">Email</label>
              <input type="email" class="form-control"  name="email" required>
            </div>
            <div class="mb-3">
              <label for="regPassword" class="form-label">Пароль</label>
              <input type="password" class="form-control"  name="password" required>
            </div>
            <div class="mb-3">
              <label for="regPasswordRepeat" class="form-label">Повторение пароля</label>
              <input type="password" class="form-control"  name="password_repeat" required>
            </div>
            <button type="submit" class="btn btn-accent w-100">Зарегистрироваться</button>
          </form>
        </div>

        <!-- Login -->
        <div class="tab-pane fade" id="login" role="tabpanel">
          <form name="login" action="/actions/login.php" method="POST">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email или логин</label>
              <input type="text" class="form-control"  name="login" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Пароль</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-accent w-100">Войти</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="/js/tabsManager.js"></script>
  <script src="/js/validation.js"></script>
  <script src="/js/auth.js"></script>

  <? require_once "../components/footer.php" ?>

</body>

</html>