<?php
// Подключение к базе данных
try {
    $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
} catch (PDOException $e) {
    echo "ERROR connecting to db" . $e->getMessage();
    exit();
}

// Начало сессии для защиты CSRF
session_start();

// Проверка, был ли передан параметр id через GET-запрос
if (!isset($_GET['id'])) {
    echo "User ID ERROR!";
    exit();
}

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Получение данных о пользователе по его ID
$stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
$stmt->execute([$_GET['id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Если пользователь с указанным ID не найден
if (!$userData) {
    echo "User not found ERROR!";
    exit();
}

// Если форма была отправлена для обновления
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Проверка CSRF-токена
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF-token');
    }

    // Обработка данных формы и обновление данных пользователя в базе данных
    $stmt = $db->prepare("UPDATE application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ? WHERE id = ?");
    $stmt->execute([
        htmlspecialchars($_POST['names']),
        htmlspecialchars($_POST['phones']),
        htmlspecialchars($_POST['email']),
        htmlspecialchars($_POST['dates']),
        htmlspecialchars($_POST['gender']),
        htmlspecialchars($_POST['biography']),
        $_GET['id']
    ]);

    header("Location: admin.php");
    exit();
}

// Если форма была отправлена для удаления
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Проверка CSRF-токена
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF-token');
    }

    // Получаем идентификатор пользователя, которого нужно удалить
    $userId = $_GET['id'];

    // Удаление связанных записей из таблицы application_languages
    $stmt = $db->prepare("DELETE FROM application_languages WHERE id_app = ?");
    $stmt->execute([$userId]);

    // Затем удаляем пользователя из таблицы application
    $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
    $stmt->execute([$userId]);

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Редактирование пользователя</h1>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label for="names">Имя:</label><br>
        <input type="text" id="names" name="names" value="<?php echo htmlspecialchars($userData['names']); ?>"><br>
        <label for="phones">Телефон:</label><br>
        <input type="tel" id="phones" name="phones" value="<?php echo htmlspecialchars($userData['phones']); ?>"><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>"><br>
        <label for="dates">Дата рождения:</label><br>
        <input type="date" id="dates" name="dates" value="<?php echo htmlspecialchars($userData['dates']); ?>"><br>
        <label for="gender">Пол:</label><br>
        <select id="gender" name="gender">
            <option value="M" <?php if ($userData['gender'] == 'M') echo 'selected'; ?>>Мужской</option>
            <option value="F" <?php if ($userData['gender'] == 'F') echo 'selected'; ?>>Женский</option>
        </select><br>
        <label for="biography">Биография:</label><br>
        <textarea id="biography" name="biography"><?php echo htmlspecialchars($userData['biography']); ?></textarea><br>
        <input type="submit" name="update" value="Сохранить изменения">
        <input type="submit" name="delete" value="Удалить пользователя" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
    </form>
</body>
</html>
