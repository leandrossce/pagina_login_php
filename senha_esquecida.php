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

    // Verifica se o e-mail existe no banco de dados
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user) {
        // Gera um token único
        $token = bin2hex(random_bytes(50));

        // Define o tempo de expiração (por exemplo, 1 hora a partir de agora)
        $expires = date('U') + 3600;

        // Remove qualquer token existente para este usuário
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$email]);

        // Insere o novo token na tabela de resets
        $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)');
        $stmt->execute([$email, $token, $expires]);

        // Envia o e-mail com o link de redefinição
        //$reset_link = 'http://seusite.com/resetar_senha.php?token=' . $token;
        $reset_link = 'localhost/login/resetar_senha.php?token=' . $token;        

        $to = $email;
        $subject = 'Recuperação de Senha';
        $message = 'Clique no link para redefinir sua senha: ' . $reset_link;
        $headers = 'From: no-reply@' . "\r\n" .
                   'Reply-To: no-reply@' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        if (mail($to, $subject, $message, $headers)) {
            echo 'Um e-mail com instruções foi enviado para ' . htmlspecialchars($email) . '.';
        } else {
            echo 'Falha ao enviar e-mail. Por favor, tente novamente.';
        }
    } else {
        echo 'E-mail não encontrado.';
    }
}
?>
