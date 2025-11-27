<?php
session_start();
require 'db.php';

if (!isset($_SESSION['pakkaaja_id'])) {
    echo json_encode(['success' => false, 'error' => 'Et ole kirjautunut.']);
    exit;
}

$pakkaajaId = $_SESSION['pakkaaja_id'];

// --- MARK ORDER AS DONE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done'], $_POST['tilausid'])) {
    $tilausid_post = intval($_POST['tilausid']);

    $stmt_check = $conn->prepare("SELECT tilaus_tyyli FROM vkauppa_tilaus WHERE tilausid=?");
    $stmt_check->bind_param("i", $tilausid_post);
    $stmt_check->execute();
    $order_check = $stmt_check->get_result()->fetch_assoc();

    if (!$order_check) {
        echo json_encode(['success' => false, 'error' => 'Tilausta ei löytynyt.']);
        exit;
    }

    $new_state = ($order_check['tilaus_tyyli'] === "nouto") ? "odottaa_noutamista" : "odottaa_keraamista";

    $update = $conn->prepare("UPDATE vkauppa_tilaus SET tila=? WHERE tilausid=?");
    $update->bind_param("si", $new_state, $tilausid_post);
    $update->execute();

    echo json_encode(['success' => true, 'tilausid' => $tilausid_post]);
    exit;
}

// --- GET ORDER DETAILS ---
if (!isset($_GET['tilausid'])) {
    echo "<div class='alert alert-danger'>Virhe: tilausid puuttuu.</div>";
    exit;
}

$tilausid = intval($_GET['tilausid']);

/* GET ORDER + CUSTOMER INFO */
$stmt = $conn->prepare("
    SELECT t.tilausid, t.tilattu_aika, t.tila, t.ostoskoriid, t.tilaus_tyyli,
           a.etunimi, a.sukunimi, a.sahkoposti, a.puhelinnumero
    FROM vkauppa_tilaus t
    JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    JOIN vkauppa_asiakas a ON o.asiakasid = a.asiakasid
    WHERE t.tilausid = ?
");
$stmt->bind_param("i", $tilausid);
$stmt->execute();
$order = $stmt->get_result();

if ($order->num_rows === 0) {
    echo "<div class='alert alert-danger'>Tilausta ei löytynyt.</div>";
    exit;
}

$orderData = $order->fetch_assoc();

/* GET ORDERED PRODUCTS */
$stmt2 = $conn->prepare("
    SELECT p.nimi, p.hinta, p.kuva, op.maara
    FROM vkauppa_kori_tuotteet op
    JOIN vkauppa_tuotteet p ON op.tuoteid = p.tuoteid
    WHERE op.ostoskoriid = ?
");
$stmt2->bind_param("i", $orderData['ostoskoriid']);
$stmt2->execute();
$products = $stmt2->get_result();

$total = 0;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Tilaus #<?= $orderData['tilausid'] ?></h3>
    </div>

    <div class="panel-body">
        <h4>Asiakkaan tiedot</h4>
        <p><b>Nimi:</b> <?= htmlspecialchars($orderData['etunimi'] . " " . $orderData['sukunimi']) ?></p>
        <p><b>Sähköposti:</b> <?= htmlspecialchars($orderData['sahkoposti']) ?></p>
        <p><b>Puhelin:</b> <?= htmlspecialchars($orderData['puhelinnumero']) ?></p>

        <hr>

        <h4>Tilauksen tiedot</h4>
        <p><b>Tila:</b> <?= htmlspecialchars($orderData['tila']) ?></p>
        <p><b>Tilattu:</b> <?= htmlspecialchars($orderData['tilattu_aika']) ?></p>
        <p><b>Ostoskori ID:</b> <?= $orderData['ostoskoriid'] ?></p>
        <p><b>Tilaustyyli:</b> <?= htmlspecialchars($orderData['tilaus_tyyli']) ?></p>

        <hr>

        <h4>Tuotteet</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tuote</th>
                    <th>Kuva</th>
                    <th>Määrä</th>
                    <th>Hinta</th>
                    <th>Yhteensä</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $products->fetch_assoc()):
                    $sum = $p['hinta'] * $p['maara'];
                    $total += $sum;
                ?>
                <tr>
                    <td><?= htmlspecialchars($p['nimi']) ?></td>
                    <td><img src="<?= htmlspecialchars($p['kuva']) ?>" alt="<?= htmlspecialchars($p['nimi']) ?>" style="max-width: 50px;"></td>
                    <td><?= $p['maara'] ?></td>
                    <td><?= number_format($p['hinta'], 2) ?> €</td>
                    <td><?= number_format($sum, 2) ?> €</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h4><b>Kokonaishinta:</b> <?= number_format($total, 2) ?> €</h4>

        <button type="button" class="btn btn-success mark-done-btn" data-tilausid="<?= $orderData['tilausid'] ?>">
            ✔ Merkitse valmiiksi
        </button>
    </div>
</div>
