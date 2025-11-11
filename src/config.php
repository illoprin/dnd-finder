<?
$dbhost = 'mysql';
$dbname = 'dndfinder';
$dbuser = 'dndfinder_user';
$dbpassword = 'dndfinder_password';

try {
  $options = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ];
  $pdo = new PDO(
    "mysql:host=$dbhost;dbname=$dbname",
    $dbuser,
    $dbpassword,
    $options
  );
} catch (PDOException $e) {
  die('Ошибка подключения: ' . $e->getMessage());
}

function isLoggedIn() {
  return isset($_SESSION["user_id"]);
}
