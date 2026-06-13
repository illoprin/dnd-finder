<?php
session_start();
require_once "../config.php";

// check if user is authorized
if (!is_logged_in()) {
  header("Location: /pages/auth.php#login");
  exit();
}

$user_id = $_SESSION['user_id'];
$response_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$success = false;

// validate allowed status transitions
if ($response_id > 0 && in_array($status, ['accepted', 'declined'])) {
  try {
    // secure query
    // verify that current user is actually the creator of the application being responded to
    $query = "SELECT r.id 
                  FROM responses r
                  INNER JOIN applications a ON r.app_id = a.id
                  WHERE r.id = :response_id AND a.user_id = :user_id";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
      'response_id' => $response_id,
      'user_id' => $user_id
    ]);

    if ($stmt->fetch()) {
      // update response status inside database
      $update_query = "UPDATE responses SET status = :status WHERE id = :response_id";
      $update_stmt = $pdo->prepare($update_query);
      $update_stmt->execute([
        'status' => $status,
        'response_id' => $response_id
      ]);
      $success = true;
    }
  } catch (PDOException $e) {
    // handle database query failure
    $title = "Не удалось выполнить запрос";
    $link_href = "/pages/account.php#responses";
    $link_title = "В личный кабинет";
    $errors[] = "Ошибка: " . $e->getMessage();
    require_once "../pages/errors.php";
    exit();
  }
}

// redirect back to account responses page
if ($success)
  header("Location: /pages/account.php#matches");
else
  header("Location: /pages/account.php#responses");

exit();
