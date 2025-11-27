<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kayttajanimi = trim($_POST["kayttajanimi"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT tyontekijaid, kayttajanimi, salasana, rooli FROM vkauppa_tyontekija WHERE kayttajanimi = ?");
    $stmt->bind_param("s", $kayttajanimi);
    $stmt->execute();
    $result = $stmt->get_result();
    $tyontekija = $result->fetch_assoc();

    if (!$tyontekija) {
        $message = "❌ Väärä käyttäjänimi tai salasana.";
    } elseif (!password_verify($password, $tyontekija["salasana"])) {
        $message = "❌ Väärä käyttäjänimi tai salasana.";
    } else {
    switch ($tyontekija["rooli"]) {
        case "Pakkaaja":
            $_SESSION["pakkaaja_id"] = $tyontekija["tyontekijaid"];
            session_regenerate_id(true);
            header("Location: pakkaaja.php");
            exit();
        case "Kuljettaja":
            $_SESSION["kuljettaja_id"] = $tyontekija["tyontekijaid"];
            session_regenerate_id(true);
            header("Location: kuljettaja.php");
            exit();
        case "Nouto_tyontekija":
            $_SESSION["nouto_id"] = $tyontekija["tyontekijaid"];
            session_regenerate_id(true);
            header("Location: nouto.php");
            exit();
        default:
            $message = "❌ Tuntematon rooli.";
    }
}
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taitaja 2024 Semifinaali - Kirjautuminen</title>

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
                        <img class="img-logo" src="images/logo.png">KIRJAUDU SISÄÄN
                    </h1>
                    <a style="margin-right: 35px; text-decoration: none;" class="zalando-sans button-2" href="index.html"><span class="glyphicon glyphicon-home"></span>Etusivu</a>
                </div>
                <div class="header-row-2">
                    <div>
                        <h1 class="tauri-regular text-color-1">Kirjautuminen</h1>
                        <br>
                        <p class="zalando-sans text-color-1">Kirjaudu sisään, niin pääset tilaamaan työskentelemään.</p>
                        <br>
                        <div style="padding-right:15px; padding-left:15px;">
                            <?php if(!empty($message)): ?>
                                <div class="alert alert-danger alert-dismissible" style="position: relative;">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <form class="form-1" method="POST" action="">
                            <p class="zalando-sans text-color-2">Syötä kayttajanimi</p>
                            <input type="text" name="kayttajanimi" required>
                            <br>
                            <br>
                            <p class="zalando-sans text-color-2">Syötä salasana</p>
                            <input type="password" name="password" required>
                            <br>
                            <br>
                            <button type="submit" class="zalando-sans button-1"><span class="glyphicon glyphicon-log-in"></span>Kirjaudu sisään</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
            <div class="carousel-inner">
                <div class="item active">
                    <img src="images/bg-3.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-1.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-4.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-2.jpeg">
                </div>
            </div>
        </div>
    </body>
</html>