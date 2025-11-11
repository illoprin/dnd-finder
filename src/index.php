<?
session_start()
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>D&D Finder — Найди игроков и мастеров</title>

  <? require_once "components/head.php" ?>

</head>

<body>

  <? require_once "components/navbar.php" ?>

  <!-- Hero -->
  <main>
    <section class="text-center">
      <div class="container">
        <h1 class="display-4 fw-bold">Найди свою игровую партию в Dungeons & Dragons</h1>
        <p class="lead mt-3">Сервис для игроков и мастеров, который помогает быстро находить компанию для приключений.
        </p>
        <a href="/pages/search.php" class="btn btn-accent btn-lg mt-3">Начать поиск</a>
      </div>
    </section>

    <!-- О D&D -->
    <section class="py-5">
      <div class="container">
        <h2 class="section-title text-center mb-4">Что такое Dungeons & Dragons?</h2>
        <div class="row align-items-center">
          <div class="col-md-6">
            <p>Dungeons & Dragons — это легендарная настольная ролевая игра, в которой вы создаёте персонажей и
              отправляетесь в мир приключений.
              Мастер описывает мир и события, а игроки принимают решения и бросают кубики, чтобы узнать исход своих
              действий.</p>
          </div>
          <div class="col-md-6">
            <img src="/static/landing_about.jpg" class="img-fluid rounded shadow" alt="D&D">
          </div>
        </div>
      </div>
    </section>

    <!-- О сервисе -->
    <section class="py-5 bg-dark bg-opacity-25">
      <div class="container">
        <h2 class="section-title text-center mb-4">О нашем сервисе</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card h-100 text-center p-3">
              <img src="/static/dnd_map.jpeg" class="card-img-top rounded-circle mx-auto mt-3"
                style="width:250px;" alt="Игроки">
              <div class="card-body">
                <h5 class="card-title">Игрокам</h5>
                <p class="card-text">Находите мастеров и присоединяйтесь к интересным кампаниям.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 text-center p-3">
              <img src="/static/dnd_master.jpeg" class="card-img-top rounded-circle mx-auto mt-3"
                style="width:250px;" alt="Мастера">
              <div class="card-body">
                <h5 class="card-title">Мастерам</h5>
                <p class="card-text">Публикуйте заявки и приглашайте игроков в свои приключения.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 text-center p-3">
              <img src="/static/dnd_community.jpeg" class="card-img-top rounded-circle mx-auto mt-3"
                style="width:250px;" alt="Сообщество">
              <div class="card-body">
                <h5 class="card-title">Сообщество</h5>
                <p class="card-text">Объединяйтесь с людьми, которые разделяют ваш интерес к D&D.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <? require_once "components/footer.php" ?>


</body>

</html>