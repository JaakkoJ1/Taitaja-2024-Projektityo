<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'message'=>'Kirjaudu sisään']);
    exit;
}

$action = $_POST['action'] ?? '';
$cartitemid = (int)($_POST['cartitemid'] ?? 0);

$stmt = $conn->prepare("
    SELECT kori_tuotteetid, tuoteid, maara, hinta, ostoskoriid
    FROM vkauppa_kori_tuotteet
    WHERE kori_tuotteetid=?
");
$stmt->bind_param("i", $cartitemid);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success'=>false, 'message'=>'Tuotetta ei löytynyt']);
    exit;
}

$price = (float)$item['hinta'];
$qty = (int)$item['maara'];
$cartId = $item['ostoskoriid'];

if ($action === 'add') {
    $stmt = $conn->prepare("SELECT varastossa FROM vkauppa_tuotteet WHERE tuoteid=?");
    $stmt->bind_param("i", $item['tuoteid']);
    $stmt->execute();
    $stock = (int)$stmt->get_result()->fetch_assoc()['varastossa'];

    if ($qty >= $stock) {
        echo json_encode([
            'success' => false,
            'message' => "Varastossa ei ole enää riittävästi tuotteita (vain $stock jäljellä).",
            'stock' => $stock
        ]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara=maara+1 WHERE kori_tuotteetid=?");
    $stmt->bind_param("i", $cartitemid);
    $stmt->execute();
    $qty++;
} elseif ($action === 'remove') {
    if ($qty <= 1) {
        $stmt = $conn->prepare("DELETE FROM vkauppa_kori_tuotteet WHERE kori_tuotteetid=?");
        $stmt->bind_param("i", $cartitemid);
        $stmt->execute();
        $qty = 0;
    } else {
        $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara=maara-1 WHERE kori_tuotteetid=?");
        $stmt->bind_param("i", $cartitemid);
        $stmt->execute();
        $qty--;
    }
}

$stmt = $conn->prepare("UPDATE vkauppa_ostoskori SET viimeksi_paivitetty = NOW() WHERE ostoskoriid = ?");
$stmt->bind_param("i", $cartId);
$stmt->execute();

echo json_encode(['success'=>true, 'price'=>$price, 'qty'=>$qty]);
exit;
?>
