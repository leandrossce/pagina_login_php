<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => true,       // Envia o cookie apenas via HTTPS
    'httponly' => true,     // Torna o cookie inacessível via JavaScript
    'samesite' => 'Strict', // Protege contra ataques CSRF
]);

session_start();
$timeout_duration = 1800; // 30 minutos


if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: /login/login.html');
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();


if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.html');
    exit();
}
?>
