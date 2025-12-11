<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$asiakasid = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        t.tilausid,
        t.ostoskoriid,
        t.tila AS order_status,
        t.tilaus_tyyli,
        t.tilaus_hinta,
        t.tilattu_aika,
        t.cancel_deadline,
        n.nouto_koodi,
        k.katuosoite,
        k.tarkennus,
        kt.tuoteid,
        kt.maara,
        kt.hinta AS tuote_hinta,
        p.nimi AS tuotenimi
    FROM vkauppa_tilaus t
    INNER JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    INNER JOIN vkauppa_kori_tuotteet kt ON kt.ostoskoriid = o.ostoskoriid
    INNER JOIN vkauppa_tuotteet p ON p.tuoteid = kt.tuoteid
    LEFT JOIN vkauppa_nouto n ON n.tilausid = t.tilausid
    LEFT JOIN vkauppa_kuljetus k ON k.tilausid = t.tilausid
    WHERE o.asiakasid = ? AND t.tila NOT IN ('suoritettu','peruttu')
    ORDER BY t.tilattu_aika DESC, t.tilausid DESC
");
$stmt->bind_param("i", $asiakasid);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $tid = $row['tilausid'];
    if (!isset($orders[$tid])) {
        $orders[$tid] = [
            'tilausid' => $tid,
            'tilaus_hinta' => $row['tilaus_hinta'],
            'tila' => $row['order_status'],
            'tilaus_tyyli' => $row['tilaus_tyyli'],
            'tilattu_aika' => $row['tilattu_aika'],
            'cancel_deadline' => $row['cancel_deadline'] ?? null,
            'nouto_koodi' => $row['nouto_koodi'] ?? null,
            'katuosoite' => $row['katuosoite'] ?? null,
            'tarkennus' => $row['tarkennus'] ?? null,
            'items' => [],
        ];
    }
    $orders[$tid]['items'][] = [
        'tuotenimi' => $row['tuotenimi'],
        'maara' => $row['maara'],
        'hinta' => $row['tuote_hinta']
    ];
}

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
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omat tilaukset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    <style>
        .order-card { margin-bottom: 20px; }
        .status-badge { font-size: 1.25rem; padding: 0.6em 1em; border-radius: 0.5rem; filter: brightness(95%); }
    </style>
</head>
<body class="bg-light">
<?php include '../cookie_consent.php'; ?>
<div class="glass-card">
<div class="content">
<div class="container my-5">
    <div class="header-row">
    <h1 class="mb-4 tauri-regular text-color-1">
        <img class="img-logo" src="../images/logo.png" alt="Yrityksen logo">OMAT TILAUKSET
    </h1>
    <div>
        <a style="margin-right: 25px; text-decoration: none;" class="zalando-sans button-2" href="verkkokauppa.php">Etusivu</a>
    </div>
    </div>
    <br>
    <?php if (empty($orders)): ?>
        <div class="alert alert-info zalando-sans text-color-2">Sinulla ei ole aktiivisia tilauksia.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order):
                $displayStatus = $statusText[$order['tila']] ?? 'Tuntematon';
                $badgeClass = $statusColor[$order['tila']] ?? 'bg-dark';
            ?>
            <div class="col-12 order-card" 
                data-tilausid="<?= $order['tilausid'] ?>" 
                data-cancel-deadline="<?= $order['cancel_deadline'] ?? '' ?>">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge status-badge <?= $badgeClass ?>" data-tilausid="<?= $order['tilausid'] ?>">
                            <?= htmlspecialchars($displayStatus) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if($order['tila'] === 'odottaa_noutamista'): ?>
                            <p class="zalando-sans text-color-2">
                                <strong>Automaattinen peruutus:</strong> 
                                <span class="countdown" data-deadline="<?= $order['cancel_deadline'] ?>"></span>
                            </p>
                        <?php endif; ?>
                        <p class="zalando-sans text-color-2"><strong>Tilaustapa:</strong> <?= htmlspecialchars($order['tilaus_tyyli']) ?></p>
                        <p class="zalando-sans text-color-2"><strong>Viimeisin aktiivisuus:</strong> <?= $order['tilattu_aika'] ?></p>
                        <ul class="list-group mb-3 zalando-sans text-color-2">
                            <?php foreach ($order['items'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($item['tuotenimi']) ?> x<?= $item['maara'] ?>
                                    <span><?= number_format($item['hinta'], 2) ?> €</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if ($order['tilaus_tyyli'] === 'nouto'): ?>
                            <p class="zalando-sans text-color-2"><strong>Noutokoodi:</strong> <?= htmlspecialchars($order['nouto_koodi'] ?? '-') ?></p>
                        <?php elseif ($order['tilaus_tyyli'] === 'kuljetus'): ?>
                            <p class="zalando-sans text-color-2"><strong>Osoite:</strong> <?= htmlspecialchars($order['katuosoite'] ?? '') ?> <?= htmlspecialchars($order['tarkennus'] ?? '') ?></p>
                        <?php endif; ?>
                        <button class="btn btn-danger cancel-order mt-2" data-tilausid="<?= $order['tilausid'] ?>">Peru tilaus</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
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

<script>
function updateOrderStatus() {
    $('.order-card').each(function() {
        var card = $(this);
        var tilausid = card.data('tilausid');

        $.ajax({
            url: 'update_tila.php',
            method: 'POST',
            data: { tilausid: tilausid },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var badge = $('.status-badge[data-tilausid="'+tilausid+'"]');

                    badge.removeClass(function(i, className) {
                        return (className.match(/(^|\s)bg-\S+/g) || []).join(' ');
                    });

                    badge.addClass(response.badgeClass);
                    badge.text(response.displayStatus);

                    if(response.tila === 'suoritettu' || response.tila === 'peruttu') {
                        card.remove();
                        return;
                    }

                    if(response.tila === 'odottaa_noutamista') {
                        if(card.find('.countdown').length === 0 && response.cancel_deadline) {
                            var countdownHtml = `<p class="zalando-sans text-color-2"><strong>Automaattinen peruutus:</strong>
                                <span class="countdown" data-deadline="${response.cancel_deadline}"></span></p>`;
                            card.find('.card-body').prepend(countdownHtml);
                        }
                    }
                    updateCountdowns();
                }
            }
        });
    });
}

$(document).on('click', '.cancel-order', function() {
    if(!confirm("Haluatko varmasti perua tämän tilauksen?")) return;
    var button = $(this);
    var tilausid = button.data('tilausid');

        $.post('peru_tilaus.php', { tilausid: tilausid }, function(response) {
            console.log(response); // already verified
            if(response.success) {
                var card = $('.order-card').filter(`[data-tilausid='${tilausid}']`);
                card.find('.status-badge').removeClass().addClass('badge status-badge bg-danger').text('Peruttu');
                card.find('.cancel-order').remove();
                // optionally hide the card
                // card.fadeOut();
            } else {
                alert("Virhe: " + response.error);
            }
        }, 'json');
});

$(document).ready(function() {
    setInterval(updateOrderStatus, 5000);
});

function updateCountdowns() {
    $('.countdown').each(function() {
        var span = $(this);

        var raw = span.attr('data-deadline');
        if (!raw) {
            span.text("0s");
            return;
        }

        raw = raw.trim();

        if (!/^\d+$/.test(raw)) { 
            span.text("0s");
            return;
        }

        var deadline = parseInt(raw) * 1000; // convert to ms
        var now = Date.now();
        var distance = deadline - now;

        if (distance <= 0) {
            var card = span.closest('.order-card');
            if (!card.data('cancelled')) {
                card.data('cancelled', true);
                cancelOrder(card.data('tilausid'), card, span);
            }
        } else {
            var totalSeconds = Math.floor(distance / 1000);
            var hours = Math.floor(totalSeconds / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;

            span.text(
                (hours > 0 ? hours + "h " : "") +
                (minutes > 0 ? minutes + "m " : "") +
                seconds + "s"
            );
        }
    });
}


function cancelOrder(tilausid, card, span) {
    console.log("Cancelling order ID:", tilausid);
    $.post('peru_tilaus.php', { tilausid: tilausid }, function(resp) {
        if(resp.success) {
            card.find('.status-badge')
                .removeClass()
                .addClass('badge status-badge bg-danger')
                .text('Peruttu');
            card.find('.cancel-order').remove();
            span.text('0s');
        } else {
            alert("Virhe peruutuksessa: " + resp.error);
        }
    }, 'json');
}


setInterval(updateCountdowns, 1000);
updateCountdowns();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
