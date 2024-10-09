<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }


    if (isset($_POST['nome'])) {
        require("../db.php");
    
        // Verifique a conexão
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }
        //caso o usuario tenha adicionado cpf e endereco
        if ($_POST['cpf'] != "" && $_POST['endereco'] != "") {
            $sql = "INSERT INTO clientes(nome, cpf, status, endereco, telefone) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('ssiss', $_POST['nome'], $_POST['cpf'], $status, $_POST['endereco'], $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
            }
        //caso o usuario tenha adicionado somente cpf
        } else if($_POST['cpf'] != "" && $_POST['endereco'] == "") {
            $sql = "INSERT INTO clientes(nome, cpf, status, telefone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('ssis', $_POST['nome'], $_POST['cpf'], $status, $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
            }
        //caso o usuario tenha adicionado somente endereco
        } else if($_POST['cpf'] == "" && $_POST['endereco'] != "") {
            $sql = "INSERT INTO clientes(nome, endereco, status, telefone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('ssis', $_POST['nome'], $_POST['endereco'], $status, $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
            }
        //caso não tenha inserido nenhum deles
        } else{
            $sql = "INSERT INTO clientes(nome, status, telefone) VALUES ( ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('sis', $_POST['nome'], $status, $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
            }
        }

        header("Location: ./cad_clientes.php");
        exit();
    }
    


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Cliente</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao dashboard -->
    <a href="../view/clientes.php">Voltar</a>
    
    <h2>Cadastrar Cliente</h2>

    <form action="./cad_clientes.php" method="POST">
        Nome:*
        <input type="text" name="nome" maxlength="200" placeholder='nome' required><br><br>
        CPF:
        <input type="text" id='cpf' name="cpf" minlength="14" maxlength="15" placeholder='CPF'><br><br>
        Endereço:
        <input type="text" name="endereco" maxlength="400" placeholder='Endereço'><br><br>
        Telefone:*
        <input type="text" id='tel' name="telefone" minlength="15" maxlength="20" placeholder='Telefone' required><br><br>

        <button type="submit">Cadastrar</button>
    </form>
    <?php
         if(isset($_SESSION['log'])){
            echo "<b>" . $_SESSION['log'] . "</b><br><br>";
            unset($_SESSION['log']);
        }
    ?>
    <!-- Mascara para CPF e telefone -->
    <script>
        function capitalizeWords(nome) {
            return nome.replace(/\b\w/g, function(letra) {
                return letra.toUpperCase();
            });
        }


        // Função para aplicar máscara no CPF
        function mascaraCPF(cpf) {
            cpf = cpf.replace(/\D/g, ""); // Remove tudo o que não é dígito
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2"); // Coloca ponto após os 3 primeiros dígitos
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2"); // Coloca ponto após os 6 primeiros dígitos
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2"); // Coloca hífen entre o terceiro bloco e os dois últimos dígitos
            return cpf;
        }

        // Função para aplicar máscara no telefone
        function mascaraTelefone(telefone) {
            telefone = telefone.replace(/\D/g, ""); // Remove tudo o que não é dígito
            telefone = telefone.replace(/(\d{2})(\d)/, "($1) $2"); // Coloca parênteses em torno dos 2 primeiros dígitos
            telefone = telefone.replace(/(\d{5})(\d)/, "$1-$2"); // Coloca hífen após os 5 primeiros dígitos
            return telefone;
        }

        // Função para adicionar as máscaras automaticamente nos campos
        window.onload = function() {
            // Campo CPF
            var cpfInput = document.getElementById('cpf');
            cpfInput.addEventListener('input', function() {
                this.value = mascaraCPF(this.value);
            });

            // Campo Telefone
            var telefoneInput = document.getElementById('tel');
            telefoneInput.addEventListener('input', function() {
                this.value = mascaraTelefone(this.value);
            });

            // Campo Nome
            var nomeInput = document.getElementsByName('nome')[0];
            nomeInput.addEventListener('input', function() {
                this.value = capitalizeWords(this.value);
            });
        };
    </script>
</body>
</html>