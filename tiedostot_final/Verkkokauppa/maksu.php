<?php
session_start();
require "../db.php";

$asiakasid = $_SESSION['user_id'] ?? null;

if (!$asiakasid) {
    header("Location: kirjautuminen.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cart = $conn->prepare("
        SELECT ostoskoriid 
        FROM vkauppa_ostoskori 
        WHERE asiakasid = ? AND tila = 'kaytossa'
        LIMIT 1
    ");
    $cart->bind_param("i", $asiakasid);
    $cart->execute();
    $result = $cart->get_result();

    if ($result->num_rows === 0) {
        header("Location: ostoskori.php?empty=1");
        exit;
    }

    $row = $result->fetch_assoc();
    $ostoskoriid = $row['ostoskoriid'];


    $items = $conn->prepare("
        SELECT maara, hinta 
        FROM vkauppa_kori_tuotteet 
        WHERE ostoskoriid = ?
    ");
    $items->bind_param("i", $ostoskoriid);
    $items->execute();
    $resultItems = $items->get_result();

    $total_price = 0;
    while ($row = $resultItems->fetch_assoc()) {
        $total_price += $row['maara'] * $row['hinta'];
    }

    if ($total_price <= 0) {
        die("Virhe: Kokonaishintaa ei vastaanotettu.");
    }

    $delivery_method = $_POST['toimitustapa'] ?? "nouto";


    $insert = $conn->prepare("
        INSERT INTO vkauppa_tilaus (ostoskoriid, tilaus_hinta, tila, tilaus_tyyli)
        VALUES (?, ?, 'tilattu', ?)
    ");
    $insert->bind_param("ids", $ostoskoriid, $total_price, $delivery_method);
    $insert->execute();

    $tilausid = $insert->insert_id;


    $items = $conn->prepare("
        SELECT tuoteid, maara 
        FROM vkauppa_kori_tuotteet 
        WHERE ostoskoriid = ?
    ");
    $items->bind_param("i", $ostoskoriid);
    $items->execute();
    $resultItems = $items->get_result();

    while ($row = $resultItems->fetch_assoc()) {
        $updateStock = $conn->prepare("
            UPDATE vkauppa_tuotteet 
            SET varastossa = varastossa - ? 
            WHERE tuoteid = ?
        ");
        $updateStock->bind_param("ii", $row['maara'], $row['tuoteid']);
        $updateStock->execute();
    }


    if ($delivery_method === "nouto") {

        function generateUniquePickupCode($conn, $length = 6) {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

            while (true) {
                $code = substr(str_shuffle($chars), 0, $length);

                $check = $conn->prepare("SELECT noutoid FROM vkauppa_nouto WHERE nouto_koodi = ?");
                $check->bind_param("s", $code);
                $check->execute();
                $exists = $check->get_result();

                if ($exists->num_rows === 0) {
                    return $code;
                }
            }
        }

        $pickupCode = generateUniquePickupCode($conn);

        $stmt = $conn->prepare("
            INSERT INTO vkauppa_nouto (tilausid, nouto_koodi)
            VALUES (?, ?)
        ");
        $stmt->bind_param("is", $tilausid, $pickupCode);
        $stmt->execute();
    }

    if ($delivery_method === "kuljetus") {
        $full_address = $_POST['osoite'] . ', ' . $_POST['kaupunki'] . ', ' . $_POST['maa'];

        $stmt = $conn->prepare("
            INSERT INTO vkauppa_kuljetus (tilausid, katuosoite, tarkennus)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $tilausid, $full_address, $_POST['tarkennus']);
        $stmt->execute();
    }

    $closeCart = $conn->prepare("UPDATE vkauppa_ostoskori SET tila = 'maksettu' WHERE ostoskoriid = ?");
    $closeCart->bind_param("i", $ostoskoriid);
    $closeCart->execute();

    header("Location: kiitos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taitaja 2024 Semifinaali - Maksu</title>

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
</head>
<style>
    .glass-card .content {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        margin: 25px;
    }
</style>
<body>
    <?php include '../cookie_consent.php'; ?>
    <div class="glass-card">
        <div class="content">
            <div class="header-row-1">
                <form class="form-3" method="POST" action="">
                    <h1 class="tauri-regular text-color-2">
                        <img class="img-logo-2" src="../images/logo.png" alt="Yrityksen logo">MAKSUTIEDOT
                    </h1>
                    <br>
                    <br>
                    <label for="kortinhaltijan_nimi" class="zalando-sans text-color-2">Kortinhaltijan nimi</label>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px;" type="text" id="kortinhaltijan_nimi" name="cardholder" required>
                    <br>
                    <br>
                    <label for="card" class="zalando-sans text-color-2">Kortin numero</label>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px;" id="card" type="text" name="card" placeholder="0000-0000-0000-0000" maxlength="19" inputmode="numeric" required>
                    <br>
                    <br>
                    <label for="exp" class="zalando-sans text-color-2">Voimassaoloaika</label>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 110px;" id="exp" type="text" name="exp" placeholder="KK/VV" maxlength="5" required>
                    <br>
                    <br>
                    <label for="cvc" class="zalando-sans text-color-2">CVC-koodi</label>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 110px;" id="cvc" type="text" name="cvc" inputmode="numeric" maxlength="3" placeholder="CVC" required>
                    <br>
                    <br>
                    <select class="zalando-sans text-color-2" style="width: 110px; padding: 5px; color: black; border: solid black 2px; border-radius: 5px;" id="toimitustapa" name="toimitustapa">
                        <option value="nouto">Nouto</option>
                        <option value="kuljetus">Kuljetus</option>
                    </select>
                    <br>
                    <br>
                    <label id="label_osoite" for="osoite" class="zalando-sans text-color-2">Katu ja numero</label>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px; display:none;" 
                        id="osoite" type="text" name="osoite" list="kadut" required>
                        <datalist id="kadut">
                            <option value="Aapeliaukio">
                            <option value="Aarteenetsijänkuja">
                            <option value="Aholahdentie">
                            <option value="Hatsalankatu">
                            <option value="Kauppakatu">
                            <option value="Puijonkatu">
                            <option value="Maaherrankatu">
                            <option value="Savonkatu">
                            <option value="Minna Canthin katu">
                            <option value="Niskakatu">
                            <option value="Tulliportinkatu">
                        </datalist>
                    <br>
                    <label id="label_tarkennus" for="tarkennus" class="zalando-sans text-color-2">Asunto / Lisätieto (valinnainen)</label>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px; display:none;" 
                        id="tarkennus" type="text" name="tarkennus">
                    <br>
                    <select class="zalando-sans text-color-2" style="width: 225px; display:none; padding:5px; color: black; border: solid black 2px; border-radius: 5px;" 
                            id="kaupunki" name="kaupunki" disabled>
                        <option value="">Valitse kaupunki</option>
                        <option value="kuopio">Kuopio</option>
                    </select>
                    <br>

                    <select class="zalando-sans text-color-2" style="width: 225px; display:none; padding:5px; color: black; border: solid black 2px; border-radius: 5px;" 
                            id="maa" name="maa" disabled>
                        <option value="">Valitse maa</option>
                        <option value="suomi">Suomi</option>
                    </select>
                    <br>
                    <br>
                    <button class="zalando-sans button-1" type="submit" style="width: 225px;">Vahvista maksu</button>
                </form>
            </div>
        </div>
    </div>
    <div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
        <div class="carousel-inner">
            <div class="item active">
                <img src="../images/bg-4.jpeg" alt="Tausta kuva 1">
            </div>
            <div class="item">
                <img src="../images/bg-1.jpeg" alt="Tausta kuva 2">
            </div>
            <div class="item">
                <img src="../images/bg-2.jpeg" alt="Tausta kuva 3">
            </div>
            <div class="item">
                <img src="../images/bg-3.jpeg" alt="Tausta kuva 4">
            </div>
        </div>
    </div>
    <script>
        document.getElementById("exp").addEventListener("input", function (e) {
            let v = e.target.value.replace(/\D/g,'');
            if (v.length >= 3) {
                e.target.value = v.slice(0,2) + '/' + v.slice(2,4);
            } else {
                e.target.value = v;
            }
        });

        document.getElementById("card").addEventListener("input", function (e) {
            let value = e.target.value.replace(/\D/g, "");
            value = value.substring(0, 16);
            let formatted = value.match(/.{1,4}/g);
            if (formatted) {
                e.target.value = formatted.join("-");
            } else {
                e.target.value = "";
            }
        });

        document.getElementById('cvc').addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        const osoite = document.getElementById('osoite');
        const tarkennus = document.getElementById('tarkennus');
        const kaupunki = document.getElementById('kaupunki');
        const maa = document.getElementById('maa');

        const label_osoite = document.getElementById('label_osoite');
        const label_tarkennus = document.getElementById('label_tarkennus');

        function toggleDeliveryFields() {
            const show = document.getElementById('toimitustapa').value === 'kuljetus';

            osoite.style.display = show ? 'block' : 'none';
            tarkennus.style.display = show ? 'block' : 'none';
            kaupunki.style.display = show ? 'block' : 'none';
            maa.style.display = show ? 'block' : 'none';

            label_osoite.style.display = show ? 'block' : 'none';
            label_tarkennus.style.display = show ? 'block' : 'none';

            osoite.required = show;
            kaupunki.required = show;
            maa.required = show;

            osoite.disabled = !show;
            tarkennus.disabled = !show;
            kaupunki.disabled = !show;
            maa.disabled = !show;
        }

        // run on page load and on change
        toggleDeliveryFields();
        document.getElementById('toimitustapa').addEventListener('change', toggleDeliveryFields);

    </script>
</body>
</html>