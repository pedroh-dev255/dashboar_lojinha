<?php
    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();

    //Se logado, direciona para o dashboard
    if(isset($_SESSION['login'])){
        header("Location: ./");
        exit();
    }

    function logAttempt($email, $conn) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // Preparar a consulta com placeholders
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, user_agent) VALUES (?, ?, ?)");
        
        // Verificar se a preparação da consulta foi bem-sucedida
        if ($stmt === false) {
            die('Erro na preparação da consulta: ' . $mysqli->error);
        }
        
        // Associar parâmetros e executar a consulta
        $stmt->bind_param("sss", $email, $ipAddress, $userAgent);
        $stmt->execute();
        
        // Fechar a declaração
        $stmt->close();
    }

    if(isset($_POST['login']) && isset($_POST['pass'])){
        // Carrega conexão com banco de dados
        require("./db.php");

        // Prepara a consulta SQL para evitar SQL Injection
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_POST['login']);
        $stmt->execute();
        $result = $stmt->get_result();

        logAttempt($_POST['login'], $conn);

        // Verifica se o usuário foi encontrado
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifica a senha usando password_verify (senha é hash no banco de dados)
            if (password_verify($_POST['pass'], $user['senha'])) {
                // Salva os dados do usuário na sessão
                $_SESSION['login'] = $user['id'];
                //nivel de acesso
                $_SESSION['nivel'] = $user['nivel'];
                
                // Redireciona para o dashboard
                header("Location: ./");
                exit();
            } else {
                $_SESSION['log'] = "Senha incorreta";
                    $_SESSION['log1'] = "error"; // success , warning, error
            }
        } else {
            $_SESSION['log'] = "Usuário não encontrado";
            $_SESSION['log1'] = "error"; // success , warning, error
        }
        
    }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="./style/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/geral.css">
    <link rel="stylesheet" href="./style/login.css">
    <title>Login</title>
    <link rel="stylesheet" href="./style/popup.css">
    <script src="./js/all.js"></script>
</head>
<body>
    <!-- POPUP -->
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>

        <div class="page">
            
            <form action="./login.php" method="post" class="formLogin">
                <h1>LOGIN</h1>
                <label for="login">Email</label>
                <input type="email" name="login" required>
                <label for="pass">Senha</label>
                <input type="password" name="pass" required>
                <button type="submit" class="btn">Entrar</button>
                <?php
                    if(isset($_SESSION['log'])){
                        echo "<script >showPopin('".$_SESSION['log']."', '".$_SESSION['log1']."');</script>";
                        unset($_SESSION['log'], $_SESSION['log1']);
                    }
                ?>
            </form>
        </div>
    
</body>
</html>
