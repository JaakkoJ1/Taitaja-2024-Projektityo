<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $etunimi = trim($_POST["etunimi"]);
    $sukunimi = trim($_POST["sukunimi"]);
    $puhelinnumero = trim($_POST["puhelinnumero"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (strlen($password) < 6) {
    $message = " <div class='alert alert-danger alert-dismissible'>
                    <a href='#' class='close' data-dismiss='alert'>&times;</a>
                    ❌ Salasanan täytyy olla vähintään 6 merkkiä!
                </div>";
    } else {
        $checkStmt = $conn->prepare("SELECT asiakasid FROM vkauppa_asiakas WHERE sahkoposti = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "<div class='alert alert-danger alert-dismissible'>
                            <a href='#' class='close' data-dismiss='alert'>&times;</a>
                            ❌ Sähköposti on jo käytössä!
                        </div>";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO vkauppa_asiakas (etunimi, sukunimi, puhelinnumero, sahkoposti, salasana) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $etunimi, $sukunimi, $puhelinnumero, $email, $hash);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success alert-dismissible'>
                                <a href='#' class='close' data-dismiss='alert'>&times;</a>
                                ✅ Rekisteröityminen onnistui! Voit nyt kirjautua sisään.
                            </div>";
            } else {
                $message = "<div class='alert alert-danger alert-dismissible'>
                                <a href='#' class='close' data-dismiss='alert'>&times;</a>
                                ❌ Rekisteröityminen epäonnistui: " . $stmt->error . "
                            </div>";
            }
            $stmt->close();
        }

        $checkStmt->close();
    }

}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taitaja 2024 Semifinaali - Rekiströityminen</title>

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
                        <img class="img-logo" src="images/logo.png">Rekisteröidy
                    </h1>
                    <div>
                        <a style="margin-right: 35px; text-decoration: none;" class="zalando-sans button-2" href="index.html"><span class="glyphicon glyphicon-home"></span>Etusivu</a>
                        <a style="margin-right: 35px; text-decoration: none;" class="zalando-sans button-2" href="kirjautuminen.php"><span class="glyphicon glyphicon-log-in"></span>Kirjaudu sisään</a>
                    </div>
                </div>
                <div class="header-row-2">
                    <div>
                        <h1 class="tauri-regular text-color-1">Rekisteröityminen</h1>
                        <br>
                        <p class="zalando-sans text-color-1">Rekisteröidy, niin pääset kirjautumaan ja tilaamaan tuotteita.</p>
                        <br>
                        <div style="padding-right:15px; padding-left:15px;">
                            <?php if(!empty($message)) echo $message; ?>
                        </div>
                    </div>
                    <form class="form-1" method="POST" action="">
                        <p class="zalando-sans text-color-2">Syötä etunimi</p>
                        <input type="text" name="etunimi" required>
                        <br>
                        <br>
                        <p class="zalando-sans text-color-2">Syötä sukunimi</p>
                        <input type="text" name="sukunimi" required>
                        <br>
                        <br>
                        <p class="zalando-sans text-color-2">Syötä puhelinnumero</p>
                        <input type="text" name="puhelinnumero" required>
                        <br>
                        <br>
                        <p class="zalando-sans text-color-2">Syötä sähköposti</p>
                        <input type="email" name="email" required>
                        <br>
                        <br>
                        <p class="zalando-sans text-color-2">Syötä salasana</p>
                        <input type="password" name="password" required>
                        <br>
                        <br>
                        <button type="submit" class="zalando-sans button-1">Rekisteröidy</button>
                        <br>
                        <br>
                        <p class="zalando-sans text-color-2">Kirjaudu sisään <a class="link" href="kirjautuminen.php">täältä.</a></p>
                    </form>
                </div>
            </div>
        </div>
        <div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
            <div class="carousel-inner">
                <div class="item">
                    <img src="images/bg-4.jpeg">
                </div>
                <div class="item active">
                    <img src="images/bg-2.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-3.jpeg">
                </div>
                <div class="item">
                    <img src="images/bg-1.jpeg">
                </div>
            </div>
        </div>
    </body>
</html>