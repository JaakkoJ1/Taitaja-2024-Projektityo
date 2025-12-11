<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$asiakasid = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT t.tilausid, t.ostoskoriid, t.tilaus_hinta, t.tilaus_tyyli, t.tilattu_aika,
           n.nouto_koodi, k.katuosoite, k.tarkennus
    FROM vkauppa_tilaus t
    INNER JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    LEFT JOIN vkauppa_nouto n ON n.tilausid = t.tilausid
    LEFT JOIN vkauppa_kuljetus k ON k.tilausid = t.tilausid
    WHERE o.asiakasid = ?
    ORDER BY t.tilattu_aika DESC
    LIMIT 1
");
$stmt->bind_param("i", $asiakasid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Tilauksen tietoja ei löytynyt.";
    exit;
}

$stmtItems = $conn->prepare("
    SELECT kt.tuoteid, kt.maara, kt.hinta, p.nimi
    FROM vkauppa_kori_tuotteet kt
    INNER JOIN vkauppa_tuotteet p ON kt.tuoteid = p.tuoteid
    WHERE kt.ostoskoriid = ?
");
$stmtItems->bind_param("i", $order['ostoskoriid']);
$stmtItems->execute();
$items = $stmtItems->get_result();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiitos tilauksestasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
<?php include '../cookie_consent.php'; ?>
<div class="glass-card">
    <div class="content">
        <div class="container my-5">
        <div class="header-row">
            <h1 class="tauri-regular text-color-1">
                        <img class="img-logo" src="../images/logo.png" alt="Yrityksen logo">Kiitos tilauksestasi!
            </h1>
        </div>
        <br>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="zalando-sans text-color-2"><strong>Tilaustapa:</strong> <?= htmlspecialchars($order['tilaus_tyyli']) ?></p>
                        <p class="zalando-sans text-color-2"><strong>Luotu:</strong> <?= $order['tilattu_aika'] ?></p>

                        <?php if ($order['tilaus_tyyli'] === 'nouto'): ?>
                            <p class="zalando-sans text-color-2"><strong>Noutokoodi:</strong> <?= htmlspecialchars($order['nouto_koodi'] ?? '-') ?></p>
                            <p class="zalando-sans text-color-2"><strong>Tilaus perutaan 48 tunnin kuluttua jos sitä ei noudeta.</strong></p>
                        <?php elseif ($order['tilaus_tyyli'] === 'kuljetus'): ?>
                            <p class="zalando-sans text-color-2"><strong>Osoite:</strong> <?= htmlspecialchars($order['katuosoite'] ?? '') ?> <?= htmlspecialchars($order['tarkennus'] ?? '') ?></p>
                        <?php endif; ?>
                        <br>
                        <h4>Tilatut tuotteet:</h4>
                        <ul class="list-group mb-3 zalando-sans text-color-2">
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <?= htmlspecialchars($item['nimi']) ?> x<?= $item['maara'] ?>
                                    <span><?= number_format($item['hinta'], 2) ?> €</span>
                                </li>
                            <?php endwhile; ?>
                        </ul>

                        <h5 class="text-end">Yhteensä: <?= number_format($order['tilaus_hinta'], 2) ?> €</h5>
                        <br>
                        <a style="text-decoration: none; margin-right: 10px;" class="zalando-sans button-1" href="verkkokauppa.php">Takaisin etusivulle</a>
                        <a style="text-decoration: none;" class="zalando-sans button-1" href="omat_tilaukset.php">Näytä tilaukseni</a>
                    </div>
                </div>
            </div>
    </div>
</div>
<div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
            <div class="carousel-inner">
                <div class="item active">
                    <img src="../images/bg-4.jpeg" alt="Tausta kuva 1">
                </div>
                <div class="item">
                    <img src="../images/bg-2.jpeg" alt="Tausta kuva 2">
                </div>
                <div class="item">
                    <img src="../images/bg-1.jpeg" alt="Tausta kuva 3">
                </div>
                <div class="item">
                    <img src="../images/bg-3.jpeg" alt="Tausta kuva 4">
                </div>
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>