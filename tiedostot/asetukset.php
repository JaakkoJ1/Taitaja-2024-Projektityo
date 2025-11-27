<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])){
    header("Location: kirjautuminen.php");
    exit;
}

$userid = $_SESSION['user_id'];
$message = "";

$nimi_stmt = $conn->prepare("SELECT etunimi FROM vkauppa_asiakas WHERE asiakasid=?");
$nimi_stmt->bind_param("i", $userid);
$nimi_stmt->execute();

$nimi_result = $nimi_stmt->get_result();
$row = $nimi_result->fetch_assoc();

$nimi = $row['etunimi'] ?? "Käyttäjä";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_password') {
        $newpass = $_POST['new_password'];

        if (strlen($newpass) < 6) {
            $message = " <div class='alert alert-danger alert-dismissible'>
                <a href='#' class='close' data-dismiss='alert'>&times;</a>
                ❌ Salasanan täytyy olla vähintään 6 merkkiä!
            </div>";
        } else {
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE vkauppa_asiakas SET salasana=? WHERE asiakasid=?");
            $stmt->bind_param("si", $hash, $userid);
            $stmt->execute();

            $message = "<div class='alert alert-success alert-dismissible'>
                            <a href='#' class='close' data-dismiss='alert'>&times;</a>
                            ✅ Salasana vaihdettu onnistuneesti!
                        </div>";
        }
    }

    if ($action === "delete_user") {
        $confirmpass = $_POST['confirm_password'];

        $stmt = $conn->prepare("select salasana FROM vkauppa_asiakas WHERE asiakasid=?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['salasana'];

        if (!password_verify($confirmpass, $hash)) {
            $message = " <div class='alert alert-danger alert-dismissible'>
                <a href='#' class='close' data-dismiss='alert'>&times;</a>
                ❌ Väärä salasana. Käyttäjää ei poistettu!
            </div>";
        } else {
            $stmt = $conn->prepare("DELETE FROM vkauppa_asiakas WHERE asiakasid=?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();

        session_destroy();
        header("Location: verkkokauppa.php");
        exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taitaja 2024 Semifinaali - Asetukset</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
</head>
    <body>
        <div class="glass-card">
            <div class="content">
                <div class="header-row">
                    <h1 class="tauri-regular text-color-1">
                        <img class="img-logo" src="images/logo.png">ASETUKSET
                    </h1>
                    <a style="margin-right: 35px; text-decoration: none;" class="zalando-sans button-2" href="verkkokauppa.php">Takaisin</a>
                </div>
                <div class="header-row-2">
                    <div>
                        <h1 class="tauri-regular text-color-1">Hei <?= htmlspecialchars($nimi) ?></h1>
                        <br>
                        <?php if (!empty($message)): ?>
                            <?= $message ?>
                        <?php endif; ?>
                        <form class="form-2" method="POST" action="">
                            <p class="zalando-sans text-color-2">Syötä uusi salasana</p>
                            <input type="password" name="new_password" required>
                            <input type="hidden" name="action" value="update_password">
                            <button type="submit" class="zalando-sans button-1">Vaihda</button>
                        </form> <br><br>

                        <form class="form-2" method="POST" action="">
                            <p class="zalando-sans text-color-2">Poista käyttäjä</p>
                            <input type="password" name="confirm_password" required placeholder="Vahvista salasanalla">
                            <input type="hidden" name="action" value="delete_user">
                            <button type="submit" class="zalando-sans button-1">Poista</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
            <div class="carousel-inner">
                <div class="item active">
                    <img src="images/bg-2.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-3.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-4.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-1.jpeg">
                </div>
            </div>
        </div>
    </body>
</html>