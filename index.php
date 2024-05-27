<?php

header('Content-Type: text/html; charset=UTF-8');

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Неверный CSRF-токен.');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        setcookie('login', '', time() - 3600);
        setcookie('pass', '', time() - 3600);

        $messages[] = 'Спасибо, результаты сохранены.';
        if (!empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
        }
    }

    $errors = array();
    $errors['names'] = !empty($_COOKIE['names_error']);
    $errors['phone'] = !empty($_COOKIE['phone_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['dates'] = !empty($_COOKIE['data_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['agree'] = !empty($_COOKIE['agree_error']);

    if ($errors['names']) {
        setcookie('names_error', '', time() - 3600);
        $messages[] = '<div>Заполните имя</div>';
    }
    if ($errors['phone']) {
        setcookie('phone_error', '', time() - 3600);
        $messages[] = '<div>Некорректный телефон</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', time() - 3600);
        $messages[] = '<div>Некорректный email</div>';
    }
    if ($errors['dates']) {
        setcookie('data_error', '', time() - 3600);
        $messages[] = '<div>Выберите дату рождения</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', time() - 3600);
        $messages[] = '<div>Укажите пол</div>';
    }
    if ($errors['agree']) {
        setcookie('agree_error', '', time() - 3600);
        $messages[] = '<div>Необходимо согласие</div>';
    }

    $values = array();
    $values['names'] = isset($_COOKIE['names_value']) ? htmlspecialchars($_COOKIE['names_value']) : '';
    $values['phone'] = isset($_COOKIE['phone_value']) ? htmlspecialchars($_COOKIE['phone_value']) : '';
    $values['email'] = isset($_COOKIE['email_value']) ? htmlspecialchars($_COOKIE['email_value']) : '';
    $values['dates'] = isset($_COOKIE['data_value']) ? htmlspecialchars($_COOKIE['data_value']) : '';
    $values['gender'] = isset($_COOKIE['gender_value']) ? htmlspecialchars($_COOKIE['gender_value']) : '';
    $values['biography'] = isset($_COOKIE['biography_value']) ? htmlspecialchars($_COOKIE['biography_value']) : '';
    $values['agree'] = isset($_COOKIE['agree_value']) ? htmlspecialchars($_COOKIE['agree_value']) : '';
    $values['language'] = !empty($_COOKIE['language_value']) ? json_decode($_COOKIE['language_value'], true) : array();

    if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
        $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
        
        $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $values['names'] = htmlspecialchars($row['names']);
        $values['phone'] = htmlspecialchars($row['phones']);
        $values['email'] = htmlspecialchars($row['email']);
        $values['dates'] = htmlspecialchars($row['dates']);
        $values['gender'] = htmlspecialchars($row['gender']);
        $values['biography'] = htmlspecialchars($row['biography']);
        $values['agree'] = true;

        $stmt = $db->prepare("SELECT * FROM application_languages WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $values['language'] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values['language'][] = htmlspecialchars($row['name_of_language']);
        }
    }

    $file = 'form.php';
    if (file_exists($file)) {
        include($file);
    } else {
        echo "Файл не найден.";
    }
} else {
    $errors = FALSE;
    if (empty($_POST['names'])) {
        setcookie('names_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('names_value', $_POST['names'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('email_value', $_POST['email'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (empty($_POST['dates'])) {
        setcookie('data_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('data_value', $_POST['dates'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (empty($_POST['gender'])) {
        setcookie('gender_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('gender_value', $_POST['gender'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (empty($_POST['agree'])) {
        setcookie('agree_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('agree_value', $_POST['agree'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (!empty($_POST['biography'])) {
        setcookie('biography_value', $_POST['biography'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (!empty($_POST['language'])) {
        setcookie('language_value', json_encode($_POST['language']), time() + 12 * 30 * 24 * 60 * 60);
    }

    if ($errors) {
        header('Location: index.php');
        exit();
    } else {
        setcookie('names_error', '', time() - 3600);
        setcookie('phone_error', '', time() - 3600);
        setcookie('email_error', '', time() - 3600);
        setcookie('data_error', '', time() - 3600);
        setcookie('gender_error', '', time() - 3600);
        setcookie('agree_error', '', time() - 3600);
    }

    if (!empty($_COOKIE[session_name()]) &&
        !empty($_SESSION['login'])) {
        $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
        
        $stmt = $db->prepare("UPDATE application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ? WHERE id = ?");
        $stmt->execute([$_POST['names'], $_POST['phone'], $_POST['email'], $_POST['dates'], $_POST['gender'], $_POST['biography'], $_SESSION['uid']]);

        $stmt = $db->prepare("DELETE FROM application_languages WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);

        foreach ($_POST['language'] as $item) {
            $stmt = $db->prepare("INSERT INTO application_languages SET id = ?, name_of_language = ?");
            $stmt->execute([$_SESSION['uid'], htmlspecialchars($item)]);
        }
    } else {
        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $max = rand(8, 16);
        $size = strlen($chars) - 1;
        $pass = '';
        while ($max--) {
            $pass .= $chars[rand(0, $size)];
        }
        $login = $chars[rand(0, 25)] . strval(time());
        setcookie('login', $login);
        setcookie('pass', $pass);

        $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));

        $stmt = $db->prepare("INSERT INTO application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ?");
        $stmt->execute([$_POST['names'], $_POST['phone'], $_POST['email'], $_POST['dates'], $_POST['gender'], $_POST['biography']]);

        $res = $db->query("SELECT max(id) FROM application");
        $row = $res->fetch();
        $count = (int) $row[0];

        foreach ($_POST['language'] as $item) {
            $stmt = $db->prepare("INSERT INTO application_languages SET id = ?, name_of_language = ?");
            $stmt->execute([$count, htmlspecialchars($item)]);
        }

        $stmt = $db->prepare("INSERT INTO login_pass SET id = ?, login = ?, pass = ?");
        $stmt->execute([$count, $login, md5($pass)]);
    }

    setcookie('save', '1');
    header('Location: ./');
}
?>

