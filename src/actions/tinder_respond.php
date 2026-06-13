<?php
session_start();

require_once "../config.php";

// redirect if user is unauthorized
if (!is_logged_in()) {
  header("Location: /pages/auth.php#login");
  exit();
}

// process only post requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $app_id = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
  $message = isset($_POST['message']) ? trim($_POST['message']) : '';
  $errors = [];

  if ($app_id > 0 && !empty($message)) {
    try {
      // double check if response already exists to prevent duplicates
      $check_query = "SELECT id FROM responses WHERE app_id = :app_id AND user_id = :user_id";
      $check_stmt = $pdo->prepare($check_query);
      $check_stmt->execute(['app_id' => $app_id, 'user_id' => $user_id]);

      if (!$check_stmt->fetch()) {
        // insert new response into database with default pending status
        $insert_query = "INSERT INTO responses (app_id, user_id, status, message) 
                                 VALUES (:app_id, :user_id, 'pending', :message)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([
          'app_id' => $app_id,
          'user_id' => $user_id,
          'message' => $message
        ]);
      }

      // redirect back to tinder to show next application
      header("Location: /pages/tinder.php?skip=" . $app_id);
      exit();
    } catch (PDOException $e) {
      // handle database query failure
      $title = "Не удалось отправить заявку";
      $link_href = "/pages/tinder.php";
      $link_title = "На страницу поиска";
      $errors[] = "Ошибка: " . $e->getMessage();
      require_once "../pages/errors.php";
      exit();
    }
  }
}

// redirect back to tinder if data is invalid or request is not post
header("Location: /pages/tinder.php");
exit();
