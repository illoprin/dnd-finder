<?
$dbhost = 'mysql';
$dbname = 'dndfinder';
$dbuser = 'dndfinder_user';
$dbpassword = 'dndfinder_password';

function check_file($file, $allowed_types, $max_size) {
  $errors = [];

  // Check type
  $file_type = mime_content_type($file['tmp_name']);
  if (!in_array($file_type, $allowed_types)) {
    $errors[] = "Недопустимый формат изображения. Разрешены: " . implode(", ", $allowed_types);
  }

  // Check file size
  if ($file['size'] > $max_size) {
    $errors[] = "Размер изображения не должен превышать 3MB";
  }

  return $errors;
}

function upload_file($file, $dir) {
  // Generate unique file name
  $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
  $filename = uniqid() . '_' . time() . '.' . $file_extension;
  $file_path = $dir . $filename;
  // Try load file
  if (move_uploaded_file($file['tmp_name'], $file_path)) {
    // Return absolute path if success
    return "/uploads/" . $filename;
  }
  return false;
}

function create_or_check_directory($dir) {
  // Try to create directory
  if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
      return false;
    }
  }

  // Check permissions
  return is_writable($dir);
}

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

$app_types = [
  'master' => '👑 Мастер ищет игроков',
  'player' => '🎭 Игрок ищет мастера',
];
