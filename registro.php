<?php
// Configuração do banco de dados
$host = 'localhost';
$db   = 'user_auth';
$user = 'root'; // substitua pelo seu usuário do banco de dados
$pass = ''; // substitua pela sua senha do banco de dados
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opções para PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     echo 'Falha na conexão: ' . $e->getMessage();
     exit();
}

// (Mesma configuração de banco de dados que antes)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verifica se as senhas coincidem
    if ($password !== $confirm_password) {
        echo 'As senhas não coincidem.';
        exit();
    }

    // Verifica se o nome de usuário ou e-mail já existem
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo 'O e-mail já existente.';
        exit();
    }

    // Hash da senha
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insere novo usuário
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    if ($stmt->execute([$username, $email, $password_hash])) {
        echo 'Registro bem-sucedido! Você pode agora <a href="index.html">fazer login</a>.';
    } else {
        echo 'Ocorreu um erro. Por favor, tente novamente.';
    }
}
?>
