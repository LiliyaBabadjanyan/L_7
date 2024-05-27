<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вход в систему</title>
</head>

<body>
<?php
/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин и id пользователя.
 * После авторизации пользователь перенаправляется на главную страницу
 * для изменения ранее введенных данных.
 **/

header('Content-Type: text/html; charset=UTF-8');

// Начинаем сессию.
session_start();

// Если есть логин в сессии, то пользователь уже авторизован.
if (!empty($_SESSION['login'])) {
  // Проверяем запрос на выход из системы
  if (!empty($_POST['logout'])) {
    session_destroy();
    header('Location: ./login.php');
    exit();
  } else {
    // Пользователь уже авторизован, перенаправляем его на главную страницу
    header('Location: ./');
    exit();
  }
}

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиенте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Генерация CSRF-токена при GET-запросе
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  // Сообщения об ошибках
  if (!empty($_GET['nologin'])) {
    echo "<div>Пользователя с таким логином не существует</div>";
  }
  if (!empty($_GET['wrongpass'])) {
    echo "<div>Неверный пароль!</div>";
  }
?>

  <form action="" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <input name="login" placeholder="Введи логин"/>
    <input name="pass" type="password" placeholder="Введи пароль"/>
    <input type="submit" id="login" value="Войти" />
  </form>

<?php
} else {
  // Проверка CSRF-токена при POST-запросе
  if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Неверный CSRF-токен.');
  }

  // Проверка логина и пароля в базе данных
  $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
  $stmt = $db->prepare("SELECT id, pass FROM login_pass WHERE login = ?");
  $stmt->execute([$_POST['login']]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    header('Location: ?nologin=1');
    exit();
  }

  if ($row["pass"] != md5($_POST['pass'])) {
    header('Location: ?wrongpass=1');
    exit();
  }

  // Авторизация пользователя
  $_SESSION['login'] = htmlspecialchars($_POST['login']);
  $_SESSION['uid'] = $row["id"];

  // Перенаправление на главную страницу
  header('Location: ./');
}
?>

</body>
</html>
