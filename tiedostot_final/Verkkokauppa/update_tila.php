<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

$tilausid = (int)($_POST['tilausid'] ?? 0);

$stmt = $conn->prepare("SELECT tila, cancel_deadline FROM vkauppa_tilaus WHERE tilausid=?");
$stmt->bind_param("i", $tilausid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$statusText = [
    'tilattu' => 'Tilattu',
    'pakkauksessa' => 'Pakkauksessa',
    'odottaa_kuljetusta' => 'Odottaa kuljetusta',
    'odottaa_noutamista' => 'Odottaa noutamista',
    'odottaa_keraamista' => 'Odottaa kuljettajan keräämistä',
    'kuljetuksessa' => 'Kuljetuksessa',
    'suoritettu' => 'Suoritettu',
    'peruttu' => 'Peruttu'
];

$statusColor = [
    'tilattu' => 'bg-warning',
    'pakkauksessa' => 'bg-info',
    'odottaa_kuljetusta' => 'bg-secondary',
    'odottaa_noutamista' => 'bg-success',
    'odottaa_keraamista' => 'bg-secondary',
    'kuljetuksessa' => 'bg-primary',
    'suoritettu' => 'bg-success',
    'peruttu' => 'bg-danger'
];

if ($row) {
    $tilausTila = $row['tila'];
    $response = [
        'success' => true,
        'tila' => $tilausTila,
        'displayStatus' => $statusText[$tilausTila] ?? 'Tuntematon',
        'badgeClass' => $statusColor[$tilausTila] ?? 'bg-dark',
        'cancel_deadline' => $row['cancel_deadline'] ?? null
    ];
} else {
    $response = ['success' => false];
}

echo json_encode($response);
