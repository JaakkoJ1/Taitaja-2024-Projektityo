<?php
session_start();
require '../db.php';

if (!isset($_SESSION['pakkaaja_id'])) {
    exit;
}

$tilattuja = $conn->query("
    SELECT t.tilausid, t.ostoskoriid, t.tila, t.tilattu_aika,
           a.etunimi, a.sukunimi
    FROM vkauppa_tilaus t
    JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    JOIN vkauppa_asiakas a ON o.asiakasid = a.asiakasid
    WHERE t.tila='tilattu'
    ORDER BY t.tilattu_aika ASC
");

$rows = [];
while($row = $tilattuja->fetch_assoc()){
    $rows[] = $row;
}

echo json_encode($rows);
