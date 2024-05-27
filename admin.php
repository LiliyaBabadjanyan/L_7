<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php

$admin_username = 'admin';
$admin_password = 'pass';

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $admin_username ||
    $_SERVER['PHP_AUTH_PW'] !== $admin_password) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
    exit();
}

try {
    $stmt = $db->query("SELECT * FROM application");
    $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Ошибка при извлечении данных пользователей: " . $e->getMessage();
    exit();
}

echo '<table border="1">';
echo '<tr><th>Имя</th><th>Телефон</th><th>Email</th><th>Год рождения</th><th>Пол</th><th>Биография</th><th>Языки программирования</th><th>Действия</th></tr>';
foreach ($usersData as $userData) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($userData['names']) . '</td>';
    echo '<td>' . htmlspecialchars($userData['phones']) . '</td>';
    echo '<td>' . htmlspecialchars($userData['email']) . '</td>';
    echo '<td>' . htmlspecialchars($userData['dates']) . '</td>';
    echo '<td>' . htmlspecialchars($userData['gender']) . '</td>';
    echo '<td>' . htmlspecialchars($userData['biography']) . '</td>';

    try {
        $stmt = $db->prepare("SELECT id_lang FROM application_languages WHERE id = ?");
        $stmt->execute([$userData['id']]);
        $userLanguages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        echo "Ошибка при извлечении данных о языках программирования: " . $e->getMessage();
        exit();
    }

    echo '<td>' . implode(', ', array_map('htmlspecialchars', $userLanguages)) . '</td>';

    echo '<td><a href="edit_user.php?id=' . $userData['id'] . '">Редактировать</a> | <form action="delete_user.php" method="post" style="display:inline;"><input type="hidden" name="id" value="' . $userData['id'] . '"><input type="submit" value="Удалить"></form></td>';
    echo '</tr>';
}
echo '</table>';

try {
    $stmt = $db->query("SELECT id_lang, COUNT(*) AS count FROM application_languages GROUP BY id_lang");
    $languagesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<h2>Статистика по языкам программирования</h2>';
    echo '<ul>';
    foreach ($languagesStats as $languageStat) {
        echo '<li>' . htmlspecialchars($languageStat['id_lang']) . ': ' . $languageStat['count'] . ' пользователей</li>';
    }
    echo '</ul>';
} catch (PDOException $e) {
    echo "Ошибка при извлечении статистики по языкам программирования: " . $e->getMessage();
    exit();
}

?>
</body>
</html>
