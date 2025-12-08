<?php
session_start();
require '../db.php';

if (!isset($_SESSION['nouto_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$pakkaajaId = $_SESSION['nouto_id'];
$order = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouto_koodi'])) {
    $nouto_koodi = trim($_POST['nouto_koodi']);
    if ($nouto_koodi !== '') {
        $stmt = $conn->prepare("
            SELECT t.tilausid, t.tila, t.tilaus_hinta, t.tilattu_aika,
                o.asiakasid, u.etunimi, u.sukunimi,
                kt.tuoteid, kt.maara, kt.hinta AS tuote_hinta,
                p.nimi AS tuotenimi
            FROM vkauppa_nouto n
            INNER JOIN vkauppa_tilaus t ON t.tilausid = n.tilausid
            INNER JOIN vkauppa_ostoskori o ON o.ostoskoriid = t.ostoskoriid
            INNER JOIN vkauppa_kori_tuotteet kt ON kt.ostoskoriid = o.ostoskoriid
            INNER JOIN vkauppa_tuotteet p ON p.tuoteid = kt.tuoteid
            INNER JOIN vkauppa_asiakas u ON u.asiakasid = o.asiakasid
            WHERE n.nouto_koodi = ? AND t.tila != 'suoritettu'
        ");
        $stmt->bind_param("s", $nouto_koodi);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (!empty($rows)) {
            $order = [
                'tilausid' => $rows[0]['tilausid'],
                'tila' => $rows[0]['tila'],
                'tilaus_hinta' => $rows[0]['tilaus_hinta'],
                'tilattu_aika' => $rows[0]['tilattu_aika'],
                'customer_name' => $rows[0]['etunimi'].' '.$rows[0]['sukunimi'],
                'items' => []
            ];
            foreach ($rows as $row) {
                $order['items'][] = [
                    'tuotenimi' => $row['tuotenimi'],
                    'maara' => $row['maara'],
                    'hinta' => $row['tuote_hinta']
                ];
            }
        } else {
            $error = "Koodilla ei löyytynyt tilauksia.";
        }
    } else {
        $error = "Syötä noutokoodi.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done_id'])) {
    $tilausid = intval($_POST['mark_done_id']);
    $update = $conn->prepare("UPDATE vkauppa_tilaus SET tila='suoritettu' WHERE tilausid=?");
    $update->bind_param("i", $tilausid);
    $success = $update->execute();
    echo json_encode(['success' => $success]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Noutokoodi Tarkistus</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
<style>
.sidebar { position: fixed; left: 0; width: 220px; height: 100%; background: #f8f9fa; padding: 20px; border-right: 1px solid #ddd; box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.3); }
.sidebar h3 { margin-top: 0; }
.sidebar a { display: block; padding: 10px 0; color: #333; text-decoration: none; }
.sidebar a:hover { color: #7ebdffff; transition-duration: 0.3s; filter: brightness(75%); }
.sidebar a.active { font-weight: bold; color: #007bff; filter: brightness(75%); }
.content { flex-grow: 1; padding: 20px; }
.tab-content {
    margin-left: 20px;
}

.form-2 {
    width: 400px;
}

.nouto-test {
      margin-left: 250px;
      margin-top: 50px;
      margin-right: 50px;
    }

@media (max-width: 768px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        border-right: none;
        box-shadow: none;
    }
    .content {
        margin-left: 0;
    }

    .tab-content {
    margin-left: 0;
}

.form-2 {
    width: 100%;
}

    .nouto-test {
      margin-left: 50px;
      margin-top: 50px;
      margin-right: 50px;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <h3 class="tauri-regular text-color-2"><img class="img-logo-2" src="../images/logo.png" style="width: 50px;" alt="Yrityksen logo">NOUTO TARKISTUS</h3>
    <a href="#" class="nav-link active zalando-sans text-color-2" id="tab_nouto">Noutokoodi tarkistus</a>
    <a class="zalando-sans text-color-2" href="kirjaudu_ulos.php"><span class="glyphicon glyphicon-log-out"></span> Kirjaudu ulos</a>
</div>
<div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
    <div class="carousel-inner">
        <div class="item active">
            <img src="../images/bg-1.jpeg" alt="Tausta kuva 1">
        </div>
        <div class="item">
            <img src="../images/bg-2.jpeg" alt="Tausta kuva 2">
        </div>
        <div class="item">
            <img src="../images/bg-3.jpeg" alt="Tausta kuva 3">
        </div>
        <div class="item">
            <img src="../images/bg-4.jpeg" alt="Tausta kuva 4">
        </div>
    </div>
</div>
<div class="nouto-test">
<div class="tab-content">
    <div id="nouto_tab">
        <h2 class="tauri-regular text-color-2">NOUTOKOODI TARKISTUS</h2>
        <br>
        <div class="form-2">
        <form method="POST" class="mb-4">
            <div class="mb-3 zalando-sans text-color-2">
                <label for="nouto_koodi" class="form-label">Syötä noutokoodi:</label>
                <input type="text" class="form-control" id="nouto_koodi" name="nouto_koodi" required>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Hae tilaus</button>
        </form>

        <?php if ($error): ?>
            <br>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <br>
            <div class="panel panel-default" id="orderCard">
                <div class="panel-heading">
                    <strong>Tilaus ID:</strong> <?= $order['tilausid'] ?> | 
                    <strong>Asiakas:</strong> <?= htmlspecialchars($order['customer_name']) ?>
                </div>
                <div class="panel-body">
                    <p><strong>Tilattu:</strong> <?= $order['tilattu_aika'] ?></p>
                    <p><strong>Hinta:</strong> €<?= number_format($order['tilaus_hinta'],2) ?></p>
                    <ul class="list-group mb-3">
                        <?php foreach ($order['items'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?= htmlspecialchars($item['tuotenimi']) ?> x<?= $item['maara'] ?>
                                <span>€<?= number_format($item['hinta'],2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="btn btn-success mark-done-btn" style="filter: brightness(95%);" data-tilausid="<?= $order['tilausid'] ?>">Merkitse suoritettu</button>
                </div>
            </div>
        <?php endif; ?>
                        </div>
    </div>
</div>
</div>

<script>
$(document).ready(function(){

    $(document).on('click', '.mark-done-btn', function(){
        var tilausid = $(this).data('tilausid');
        $.post('', { mark_done_id: tilausid }, function(response){
            if(response.success){
                $('#orderCard').remove();
                $('<div class="alert alert-success">Tilaus #' + tilausid + ' merkitty suoritetuksi.</div>')
                    .appendTo('.content')
                    .delay(3000).fadeOut(500, function(){ $(this).remove(); });
            } else {
                alert('Virhe tilan päivittämisessä.');
            }
        }, 'json');
    });

    setTimeout(function() { $(".alert").fadeOut(1000); }, 10000);
});
</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
