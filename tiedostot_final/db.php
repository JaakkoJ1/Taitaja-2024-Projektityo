<?php
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "verkkokauppa";

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        die("Yhteys epÃ¤onnistui: " . $conn->connect_error);
    }
?>