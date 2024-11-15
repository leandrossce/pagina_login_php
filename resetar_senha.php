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

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifica se o token existe e não expirou
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires >= ?');
    $stmt->execute([$token, date('U')]);

    $reset = $stmt->fetch();

    if ($reset) {
        // Exibe o formulário para redefinir a senha
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Redefinir Senha</title>
            <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
            <h2>Redefinir Senha</h2>
            <form action="resetar_senha.php" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                Nova Senha: <input type="password" name="password" required><br>
                Confirmar Nova Senha: <input type="password" name="confirm_password" required><br>
                <input type="submit" value="Redefinir Senha">
            </form>
        </body>
        </html>
        <?php
    } else {
        echo 'Token inválido ou expirado.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Processa a redefinição de senha
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo 'As senhas não coincidem.';
        exit();
    }

    // Verifica o token novamente
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires >= ?');
    $stmt->execute([$token, date('U')]);

    $reset = $stmt->fetch();

    if ($reset) {
        // Atualiza a senha do usuário
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        if ($stmt->execute([$password_hash, $reset['email']])) {
            // Remove o token de reset
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
            $stmt->execute([$reset['email']]);

            echo 'Senha redefinida com sucesso! Você pode agora <a href="index.html">fazer login</a>.';
        } else {
            echo 'Ocorreu um erro ao redefinir a senha.';
        }
    } else {
        echo 'Token inválido ou expirado.';
    }
} else {
    echo 'Método inválido.';
}
?>
