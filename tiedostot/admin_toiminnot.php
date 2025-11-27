<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ADD
    if (isset($_POST['add_type'])) {
        $type = $_POST['add_type'];

        if ($type === 'worker') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            if ($username && $password && $role) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO vkauppa_tyontekija (kayttajanimi, salasana, rooli) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashed_password, $role);
                $stmt->execute();
            }
            $redirect_tab = 'workers';
        }
        elseif ($type === 'product') {
            $name = $_POST['nimi'] ?? '';
            $price = $_POST['hinta'] ?? 0;
            $description = $_POST['kuvaus'] ?? '';
            $image = $_POST['kuva'] ?? '';
            $stock = $_POST['varastossa'] ?? 0;
            $category = $_POST['luokka'] ?? '';
            $active = $_POST['aktiivinen'] ?? 1;

            if ($name && $price && $stock) {
                $stmt = $conn->prepare("INSERT INTO vkauppa_tuotteet (nimi, hinta, kuvaus, kuva, varastossa, luokka, aktiivinen) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssisi", $name, $price, $description, $image, $stock, $category, $active);
                $stmt->execute();
            }
            $redirect_tab = 'products';
        }
    }

    // EDIT
    elseif (isset($_POST['edit_type'], $_POST['edit_id'])) {
        $type = $_POST['edit_type'];
        $id = intval($_POST['edit_id']);

        if ($type === 'worker') {
            $username = $_POST['username'];
            $role = $_POST['role'];
            $password = trim($_POST['password']);

            if (!empty($password)) {
                // Password was changed → hash it
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE vkauppa_tyontekija SET kayttajanimi=?, salasana=?, rooli=? WHERE tyontekijaid=?");
                $stmt->bind_param("sssi", $username, $hashedPassword, $role, $id);
            } else {
                // Password not changed → leave it as is
                $stmt = $conn->prepare("UPDATE vkauppa_tyontekija SET kayttajanimi=?, rooli=? WHERE tyontekijaid=?");
                $stmt->bind_param("ssi", $username, $role, $id);
            }

            $stmt->execute();
        }
        $redirect_tab = $_POST['redirect_tab'] ?? 'users';
    }

    header("Location: admin_paneeli.php?tab=" . urlencode($redirect_tab));
    exit;
}
?>
