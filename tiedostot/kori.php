<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Check if logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Kirjaudu sisään ensin.']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['productId'] ?? 0);

// ------------------------------------------------------------------
// 1. FIND OR CREATE ACTIVE CART
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT ostoskoriid FROM vkauppa_ostoskori WHERE asiakasid = ? AND tila = 'kaytossa'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    // create new cart
    $stmt = $conn->prepare("INSERT INTO vkauppa_ostoskori (asiakasid, tila) VALUES (?, 'kaytossa')");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartId = $conn->insert_id;
} else {
    $cartId = $cart['ostoskoriid'];
}

// ------------------------------------------------------------------
// 2. GET PRODUCT INFO (stock + price)
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT varastossa, hinta FROM vkauppa_tuotteet WHERE tuoteid = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Tuotetta ei löydy.']);
    exit;
}

$stock = (int)$product['varastossa'];
$price = $product['hinta'];

// ------------------------------------------------------------------
// 3. CHECK IF PRODUCT ALREADY IN CART
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT kori_tuotteetid, maara FROM vkauppa_kori_tuotteet WHERE ostoskoriid = ? AND tuoteid = ?");
$stmt->bind_param("ii", $cartId, $productId);
$stmt->execute();
$cartItem = $stmt->get_result()->fetch_assoc();

// ------------------------------------------------------------------
// 4. ACTION: ADD
// ------------------------------------------------------------------
if ($action === 'add') {

    $currentQty = $cartItem['maara'] ?? 0;

    if ($currentQty >= $stock) {
        echo json_encode([
            "success" => false,
            "message" => "Varastoraja saavutettu! Enempää ei voi lisätä."
        ]);
        exit;
    }


    if ($cartItem) {
        // Increase amount
        $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara = maara + 1 WHERE kori_tuotteetid = ?");
        $stmt->bind_param("i", $cartItem['kori_tuotteetid']);
        $stmt->execute();

    } else {
        // Create cart item
        $stmt = $conn->prepare("INSERT INTO vkauppa_kori_tuotteet (ostoskoriid, tuoteid, maara, hinta) VALUES (?, ?, 1, ?)");
        $stmt->bind_param("iid", $cartId, $productId, $price);
        $stmt->execute();
    }

    $stmt = $conn->prepare("UPDATE vkauppa_ostoskori SET viimeksi_paivitetty = NOW() WHERE ostoskoriid = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// ------------------------------------------------------------------
// 5. ACTION: REMOVE
// ------------------------------------------------------------------
if ($action === 'remove') {

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Ei tuotteita poistettavaksi.']);
        exit;
    }

    if ($cartItem['maara'] <= 1) {
        // remove row
        $stmt = $conn->prepare("DELETE FROM vkauppa_kori_tuotteet WHERE kori_tuotteetid = ?");
        $stmt->bind_param("i", $cartItem['kori_tuotteetid']);
        $stmt->execute();
    } else {
        // decrease amount
        $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara = maara - 1 WHERE kori_tuotteetid = ?");
        $stmt->bind_param("i", $cartItem['kori_tuotteetid']);
        $stmt->execute();
    }

    $stmt = $conn->prepare("UPDATE vkauppa_ostoskori SET viimeksi_paivitetty = NOW() WHERE ostoskoriid = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// ------------------------------------------------------------------
// UNKNOWN ACTION
// ------------------------------------------------------------------
echo json_encode(['success' => false, 'message' => 'Tuntematon toiminto.']);
?>