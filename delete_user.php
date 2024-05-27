<?php

// Проверка наличия идентификатора пользователя в запросе
if (!isset($_POST['id'])) {
    // Если идентификатор пользователя не передан, перенаправляем обратно на страницу админа
    header("Location: admin.php");
    exit();
}

// Получаем идентификатор пользователя из запроса
$user_id = $_POST['id'];

// Подключение к базе данных
try {
    $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Если не удалось подключиться к базе данных, выводим сообщение об ошибке
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
    exit();
}

// Удаляем связанные записи из таблицы application_languages
try {
    // Подготавливаем запрос на удаление
    $stmt = $db->prepare("DELETE FROM application_languages WHERE id_app = ?");
    // Выполняем запрос с передачей идентификатора пользователя в качестве параметра
    $stmt->execute([$user_id]);
} catch (PDOException $e) {
    // Если произошла ошибка при удалении связанных записей, выводим сообщение об ошибке
    echo "Ошибка удаления связанных записей: " . $e->getMessage();
    exit();
}

// Удаляем пользователя из таблицы application
try {
    // Подготавливаем запрос на удаление
    $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
    // Выполняем запрос с передачей идентификатора пользователя в качестве параметра
    $stmt->execute([$user_id]);
    
    // После удаления перенаправляем обратно на страницу админа
    header("Location: admin.php");
    exit();
} catch (PDOException $e) {
    // Если произошла ошибка при удалении пользователя, выводим сообщение об ошибке
    echo "Ошибка удаления пользователя: " . $e->getMessage();
    exit();
}

?>
