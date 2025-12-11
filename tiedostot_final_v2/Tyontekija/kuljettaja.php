<?php
session_start();
require '../db.php';

if (!isset($_SESSION['kuljettaja_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$kuljettajaId = intval($_SESSION['kuljettaja_id']);
$message = "";

/* ---------------------------
   Geocode address function
---------------------------- */
function geocodeAddress($address) {
    $apiKey = "AIzaSyAL9EOTkgnrWOjotOyfVNATGUI7xouhpW4";
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if(isset($data['status']) && $data['status'] === 'OK') {
        return [
            'lat' => $data['results'][0]['geometry']['location']['lat'],
            'lng' => $data['results'][0]['geometry']['location']['lng']
        ];
    }
    return false;
}

/* ---------------------------
   Accept order
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_order'], $_POST['tilausid'])) {
    $tilausid = intval($_POST['tilausid']);

    $stmtAddr = $conn->prepare("SELECT katuosoite, tarkennus FROM vkauppa_kuljetus WHERE tilausid=?");
    $stmtAddr->bind_param("i", $tilausid);
    $stmtAddr->execute();
    $addrResult = $stmtAddr->get_result()->fetch_assoc();

    if($addrResult) {
        $fullAddress = trim($addrResult['katuosoite'] . ' ' . $addrResult['tarkennus']);
        $coords = geocodeAddress($fullAddress);

        if ($coords) {
            $stmtCheck = $conn->prepare("SELECT tilausid FROM vkauppa_kuljetus WHERE tilausid=?");
            $stmtCheck->bind_param("i", $tilausid);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();

            if ($resCheck->num_rows > 0) {
                $stmtUpdate = $conn->prepare("
                    UPDATE vkauppa_kuljetus
                    SET kuljettajaid=?, lat=?, lng=?
                    WHERE tilausid=?
                ");
                $stmtUpdate->bind_param("iddi", $kuljettajaId, $coords['lat'], $coords['lng'], $tilausid);
                $stmtUpdate->execute();
            } else {
                $stmtInsert = $conn->prepare("
                    INSERT INTO vkauppa_kuljetus (tilausid, kuljettajaid, lat, lng)
                    VALUES (?, ?, ?, ?)
                ");
                $stmtInsert->bind_param("iidd", $tilausid, $kuljettajaId, $coords['lat'], $coords['lng']);
                $stmtInsert->execute();
            }
        }
    }

    $stmtOrder = $conn->prepare("
        UPDATE vkauppa_tilaus
        SET tila='kuljetuksessa'
        WHERE tilausid=? AND tila='odottaa_keraamista'
    ");
    $stmtOrder->bind_param("i", $tilausid);
    $stmtOrder->execute();

    $_SESSION['message'] = "🚚 Otit tilauksen #$tilausid kuljetukseen!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ---------------------------
   Fetch pending orders
---------------------------- */
if(isset($_GET['fetch_pending'])) {
    $res = $conn->query("
        SELECT t.tilausid, t.tilattu_aika, a.etunimi, a.sukunimi
        FROM vkauppa_tilaus t
        JOIN vkauppa_ostoskori o ON t.ostoskoriid=o.ostoskoriid
        JOIN vkauppa_asiakas a ON o.asiakasid=a.asiakasid
        WHERE t.tila='odottaa_keraamista'
        ORDER BY t.tilattu_aika ASC
    ");

    $orders = [];
    while($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($orders);
    exit;
}

/* ---------------------------
   Fetch accepted orders
---------------------------- */
$omat = $conn->prepare("
    SELECT t.tilausid, t.tilattu_aika, a.etunimi, a.sukunimi, k.katuosoite, k.tarkennus, a.puhelinnumero, k.lat, k.lng
    FROM vkauppa_tilaus t
    JOIN vkauppa_ostoskori o ON t.ostoskoriid=o.ostoskoriid
    JOIN vkauppa_asiakas a ON o.asiakasid=a.asiakasid
    JOIN vkauppa_kuljetus k ON k.tilausid=t.tilausid
    WHERE t.tila='kuljetuksessa' AND k.kuljettajaid=?
    ORDER BY t.tilattu_aika ASC
");
$omat->bind_param("i", $kuljettajaId);
$omat->execute();
$omat_orders = $omat->get_result();

/* ---------------------------
   Mark order as done
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done'], $_POST['tilausid'])) {
    $tilausid = intval($_POST['tilausid']);

    $stmtDone = $conn->prepare("
        UPDATE vkauppa_tilaus
        SET tila='suoritettu'
        WHERE tilausid=? AND tila='kuljetuksessa'
    ");
    $stmtDone->bind_param("i", $tilausid);
    $stmtDone->execute();

    $message = $stmtDone->affected_rows > 0
        ? "✔ Tilaus #$tilausid merkitty toimitetuksi!"
        : "⚠ Tilausta #$tilausid ei voitu päivittää.";

    echo json_encode([
        'success' => $stmtDone->affected_rows > 0,
        'message' => $message
    ]);
    exit;
}


/* ---------------------------
   Fetch orders for map (AJAX)
---------------------------- */
if (isset($_GET['map_orders'])) {
    $stmtMap = $conn->prepare("
        SELECT t.tilausid, k.katuosoite, k.tarkennus, k.lat, k.lng, a.etunimi, a.sukunimi
        FROM vkauppa_tilaus t
        JOIN vkauppa_kuljetus k ON t.tilausid=k.tilausid
        JOIN vkauppa_ostoskori o ON t.ostoskoriid=o.ostoskoriid
        JOIN vkauppa_asiakas a ON o.asiakasid=a.asiakasid
        WHERE t.tila='kuljetuksessa'
    ");
    $stmtMap->execute();
    $res = $stmtMap->get_result();
    $orders = [];
    while ($row = $res->fetch_assoc()) {
        if(is_numeric($row['lat']) && is_numeric($row['lng']) && $row['lat'] != 0 && $row['lng'] != 0){
            $orders[] = $row;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($orders);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kuljettaja - Toimitukset</title>

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
.content { margin-left: 220px; padding: 20px; }
.sidebar a { display: block; padding: 10px 0; color: #333; text-decoration: none; }
.sidebar a:hover { color: #7ebdffff; transition-duration: 0.3s; filter: brightness(75%); }
.sidebar a.active { font-weight: bold; color: #007bff; filter: brightness(75%); }
.tab-content {
    margin-left: 20px;
}

.kuljettaja-test {
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

    .kuljettaja-test {
      margin-left: 50px;
      margin-top: 50px;
      margin-right: 50px;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <h3 class="tauri-regular text-color-2"><img class="img-logo-2" src="../images/logo.png" style="width: 50px;" alt="Yrityksen logo">KULJETTAJA</h3>
    <div class="list-group">
        <a href="#" class="nav-link active zalando-sans text-color-2" id="tab_haettavat">Haettavat tilaukset</a>
        <a href="#" class="nav-link zalando-sans text-color-2" id="tab_omat">Omat kuljetukset</a>
        <a href="#" class="nav-link zalando-sans text-color-2" id="tab_kartta">Kartta</a>
        <a class="zalando-sans text-color-2" href="kirjaudu_ulos.php"><span class="glyphicon glyphicon-log-out"></span> Kirjaudu ulos</a>
    </div>
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
<div class="kuljettaja-test">
<div class="tab-content">

<!-- Pending Orders -->
<div id="haettavat_tab">
    <h2 class="tauri-regular text-color-2">HAETTAVAT TILAUKSET</h2>
<br>
<div class="form-2">
      <div class="table-responsive">
    <table class="table table-bordered table-striped zalando-sans text-color-2">
        <thead>
            <tr>
                <th>Tilaus ID</th>
                <th>Asiakas</th>
                <th>Tilattu</th>
                <th>Toiminta</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
</div>
<br>
</div>
<!-- Accepted Orders -->
<div id="omat_tab" style="display:none;">
    <h2 class="tauri-regular text-color-2">OMAT KULJETUKSET</h2>
    <br>
<div class="form-2">
      <div class="table-responsive">
    <table class="table table-bordered table-striped zalando-sans text-color-2">
        <thead>
            <tr>
                <th>Tilaus ID</th>
                <th>Asiakas</th>
                <th>Osoite</th>
                <th>Tarkennus</th>
                <th>Puhelinnumero</th>
                <th>Tilattu</th>
                <th>Toiminta</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $omat_orders->fetch_assoc()): ?>
            <tr id="row-<?= $row['tilausid'] ?>">
                <td><?= $row['tilausid'] ?></td>
                <td><?= htmlspecialchars($row['etunimi']." ".$row['sukunimi']) ?></td>
                <td><?= htmlspecialchars($row['katuosoite']) ?></td>
                <td><?= htmlspecialchars($row['tarkennus']) ?></td>
                <td><a href="tel:<?= htmlspecialchars($row['puhelinnumero']) ?>"><?= htmlspecialchars($row['puhelinnumero']) ?></a></td>
                <td><?= $row['tilattu_aika'] ?></td>
                <td><button class="btn btn-success btn-sm mark-delivered-btn" data-id="<?= $row['tilausid'] ?>">✔ Merkitse toimitetuksi</button></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>
<br>
</div>

<?php if(isset($_SESSION['message'])): ?>
    <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>

<!-- Map -->
<div id="kartta_tab" style="display:none;">
    <h2 class="tauri-regular text-color-2">KULJETUKSET KARTALLA</h2>
        <br>
    <div id="map" style="width:100%; height:600px;"></div>
</div>

</div>


<script>
let map, driverMarker = null, orderMarkers = [], directionsService, directionsRenderer;
let selectedOrderMarker = null;
let driverPos = null;

// ---------------------------
// Initialize Google Map
// ---------------------------
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 62.894, lng: 27.678 },
        zoom: 14
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({ map: map });

    loadOrderMarkers();

    trackDriverLocation();
}

// ---------------------------
// Track driver continuously
// ---------------------------
function trackDriverLocation() {
    if (!navigator.geolocation) {
        alert("Selaimesi ei tue geolokaatiota.");
        return;
    }

    navigator.geolocation.watchPosition(position => {
        driverPos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };

        if (!driverMarker) {
            driverMarker = new google.maps.Marker({
                position: driverPos,
                map: map,
                title: "Sinun sijaintisi",
                icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
            });
        } else {
            driverMarker.setPosition(driverPos);
        }

        map.setCenter(driverPos);
    }, err => {
        console.error("GPS error:", err);
    }, { enableHighAccuracy: true, maximumAge: 1000, timeout: 5000 });
}

// ---------------------------
// Load and display order markers
// ---------------------------
function loadOrderMarkers() {
    $.getJSON("?map_orders=1", function(orders) {
        // Clear old markers
        orderMarkers.forEach(m => m.setMap(null));
        orderMarkers = [];

        orders.forEach(order => {
            const lat = parseFloat(order.lat);
            const lng = parseFloat(order.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                title: `#${order.tilausid} ${order.etunimi} ${order.sukunimi}`
            });

            marker.addListener("click", () => {
                selectedOrderMarker = marker;
                alert(`Valittu tilaus #${order.tilausid}. Klikkaa "Aloita reitti" aloittaaksesi navigoinnin.`);
            });

            orderMarkers.push(marker);
        });
    });
}

// ---------------------------
// Start navigation to selected order
// ---------------------------
function startRoute() {
    if (!selectedOrderMarker) {
        alert("Valitse ensin tilaus klikkaamalla pinniä.");
        return;
    }
    if (!driverPos) {
        alert("Odota, että GPS on paikalla.");
        return;
    }

    directionsService.route({
        origin: driverPos,
        destination: selectedOrderMarker.getPosition(),
        travelMode: 'DRIVING'
    }, (result, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
        } else {
            console.error("Directions failed:", status);
            alert("Reittiohjeita ei voitu ladata: " + status);
        }
    });
}

// ---------------------------
// AJAX: refresh pending orders
// ---------------------------
function refreshPendingOrders() {
    $.getJSON("?fetch_pending=1", function(data) {
        if (!data) return;

        const tbody = $("#haettavat_tab tbody");
        tbody.empty();

        data.forEach(row => {
            const tr = $(`
                <tr>
                    <td>${row.tilausid}</td>
                    <td>${row.etunimi} ${row.sukunimi}</td>
                    <td>${row.tilattu_aika}</td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="tilausid" value="${row.tilausid}">
                            <button name="accept_order" class="btn btn-success btn-sm">🚚 Ota kuljetettavaksi</button>
                        </form>
                    </td>
                </tr>
            `);
            tbody.append(tr);
        });
    });
}

// ---------------------------
// Document ready
// ---------------------------
$(document).ready(function() {
    const startBtn = $('<button id="startRouteBtn" class="btn btn-primary" style="margin-top:10px;">Aloita reitti</button>');
    startBtn.click(startRoute);

    function showTab(tabId) {
        $(".nav-link").removeClass("active");
        $("#tab_" + tabId).addClass("active");
        $("#haettavat_tab, #omat_tab, #kartta_tab").hide();
        $("#" + tabId + "_tab").show();

        if (tabId === "kartta") {
            if (!map) initMap();
            if ($("#startRouteBtn").length === 0) {
                $("#kartta_tab").append(startBtn);
            }
        } else {
            $("#startRouteBtn").remove();
        }
    }

    $("#tab_haettavat").click(() => showTab("haettavat"));
    $("#tab_omat").click(() => showTab("omat"));
    $("#tab_kartta").click(() => showTab("kartta"));

    showTab("haettavat");

    setInterval(refreshPendingOrders, 5000);
    refreshPendingOrders();

    $(document).on('click', '.mark-delivered-btn', function() {
        const tilausid = $(this).data('id');
        const btn = $(this);

        $.ajax({
            url: '',
            method: 'POST',
            data: { mark_done: 1, tilausid: tilausid },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#row-' + tilausid).fadeOut();

                    const alertBox = $('<div class="alert alert-info" style="margin-top:10px;"></div>').text(response.message);
                    $('#omat_tab').prepend(alertBox);
                    setTimeout(() => alertBox.fadeOut(), 3000);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Tapahtui virhe, yritä uudelleen.');
            }
        });
    });
});

// ---------------------------
// Load Google Maps API dynamically
// ---------------------------
(function(){
    const script = document.createElement("script");
    script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyAL9EOTkgnrWOjotOyfVNATGUI7xouhpW4&callback=initMap";
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
})();
</script>

</body>
</html>
