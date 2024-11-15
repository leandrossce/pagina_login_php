<?php
session_start();



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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepara a consulta SQL
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Senha correta
        session_regenerate_id(true); // Protege contra fixação de sessão
        $_SESSION['user_id'] = $user['id']; // Armazena o ID do usuário na sessão
        $_SESSION['username'] = $user['email'];
        // Redireciona para a página protegida
        header('Location: /login/paginas/page1.php');
        exit();
    } else {
        echo 'Usuário ou senha inválidos.';
    }
}
?>
