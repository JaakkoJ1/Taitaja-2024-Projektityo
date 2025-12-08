<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Et ole kirjautunut.']);
    exit;
}

$asiakasid = $_SESSION['user_id'];
$tilausid = intval($_POST['tilausid'] ?? 0);

if (!$tilausid) {
    echo json_encode(['success' => false, 'error' => 'Virheellinen tilaus.']);
    exit;
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        SELECT o.ostoskoriid
        FROM vkauppa_tilaus t
        INNER JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
        WHERE t.tilausid = ? AND o.asiakasid = ? AND t.tila NOT IN ('suoritettu','peruttu')
        LIMIT 1
    ");
    $stmt->bind_param("ii", $tilausid, $asiakasid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Tilausta ei voi perua.');
    }

    $ostoskoriid = $result->fetch_assoc()['ostoskoriid'];

    $stmt = $conn->prepare("SELECT tuoteid, maara FROM vkauppa_kori_tuotteet WHERE ostoskoriid = ?");
    $stmt->bind_param("i", $ostoskoriid);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmtUpdate = $conn->prepare("UPDATE vkauppa_tuotteet SET varastossa = varastossa + ? WHERE tuoteid = ?");
    foreach ($items as $item) {
        $stmtUpdate->bind_param("ii", $item['maara'], $item['tuoteid']);
        $stmtUpdate->execute();
    }

    $stmt = $conn->prepare("UPDATE vkauppa_tilaus SET tila='peruttu' WHERE tilausid=?");
    $stmt->bind_param("i", $tilausid);
    $stmt->execute();

    $conn->commit();

    echo json_encode(['success' => true, 'tilausid' => $tilausid]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
