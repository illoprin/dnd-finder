<?

// ------------------------------------------------------------
//             Functions
// ------------------------------------------------------------

function check_file($file, $allowed_types, $max_size)
{
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

function upload_file($file, $dir)
{
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

function create_or_check_directory($dir)
{
  // Try to create directory
  if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
      return false;
    }
  }

  // Check permissions
  return is_writable($dir);
}


function is_logged_in()
{
  return isset($_SESSION["user_id"]);
}

/**
 * removes responses with status 'declined' that are older than 30 days
 * @return array(count: int, errors: string[])
 */
function clean_old_declined_responses(): array
{
  global $pdo;
  $ret = [
    'count' => 0,
    'errors' => array(),
  ];
  try {
    // sql query using internal database interval subtraction to find rows older than 30 days
    $query = "DELETE FROM responses 
                WHERE status = 'declined' 
                  AND created_at < NOW() - INTERVAL 30 DAY";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // total number of affected rows
    $ret['count'] = $stmt->rowCount();
  } catch (PDOException $e) {
    // error tracking for maintenance logs
    $ret['errors'][] = ("failed to clean database responses: " . $e->getMessage());
  }
  return $ret;
}

// ------------------------------------------------------------
//             Initial
// ------------------------------------------------------------

// constats

$dbhost = 'mysql';
$dbname = 'dndfinder';
$dbuser = 'dndfinder_user';
$dbpassword = 'dndfinder_password';

$app_types = [
  'master' => '👑 Мастер ищет игроков',
  'player' => '🎭 Игрок ищет мастера',
];

// connect to sql

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

// create tables

try {

  // users
  $pdo->exec("
    create table if not exists users (
      id int(4) primary key auto_increment,
      login varchar(255) not null unique,
      nickname varchar(64) not null,
      email varchar(255) not null unique,
      password_hash varchar(255) not null,
      description text,
      telegram_username varchar(255),
      created_at timestamp default current_timestamp
    )
  ");

  // applications
  $pdo->exec("
    create table if not exists applications (
      id int primary key auto_increment,
      user_id int not null,
      type enum('player', 'master') not null,
      title varchar(255) not null,
      description text not null,
      image_url varchar(255),
      crated_at timestamp default current_timestamp,
      foreign key (user_id) references users(id) on delete cascade
    )
  ");

  // responses
  $pdo->exec("
    create table responses (
      id int auto_increment primary key,
      ticket_id int not null,
      user_id int not null,
      status enum('pending', 'accepted', 'declined') default 'pending',
      message text,
      created_at timestamp default current_timestamp
    )
  ");

} catch (PDOException $e) {
  die("Ошибка создания таблиц " . $e->getMessage());
}