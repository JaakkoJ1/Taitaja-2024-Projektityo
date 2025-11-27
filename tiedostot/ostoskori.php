<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}
$userid = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT ostoskoriid FROM vkauppa_ostoskori WHERE asiakasid=? AND tila='kaytossa'");
$stmt->bind_param("i", $userid);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

$cartid = $cart['ostoskoriid'] ?? null;
$cartitems = [];


if ($cartid) {
    $stmt = $conn->prepare("
        SELECT kt.kori_tuotteetid, kt.tuoteid, kt.maara, kt.hinta, t.nimi, t.kuva, t.varastossa
        FROM vkauppa_kori_tuotteet kt
        JOIN vkauppa_tuotteet t ON kt.tuoteid = t.tuoteid
        WHERE kt.ostoskoriid=?
    ");
    $stmt->bind_param("i", $cartid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cartitems[] = $row;
    }
}
$totalprice = 0;
foreach ($cartitems as $item) {
    $totalprice += $item['maara'] * $item['hinta'];
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Ostoskori</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
<style>

#cart-table.table, 
#cart-table.table th, 
#cart-table.table td {
    border: none !important;
}

#cart-table.table tbody tr {
    border-bottom: 1px solid #ddd !important;
}

#cart-table.table thead tr {
    border-bottom: 2px solid #333 !important;
}
</style>
</head>
<body>
<div class="glass-card">
    <div class="content">
        <div class="header-row">
            <h1 class="tauri-regular text-color-1">
                <img class="img-logo" src="images/logo.png">OSTOSKORI
            </h1>
            <div>
                <a style="margin-right: 35px; text-decoration: none;" class="zalando-sans button-2" href="verkkokauppa.php">Takaisin</a>
            </div>
        </div>
        <div class="header-row-2">
            <div>
                <h1 class="tauri-regular text-color-1">Ostoskori</h1>
            </div>
        </div>
        <div class="header-row-2 form-2">
            <div class="col-md-8">
                <p class="empty-cart-message zalando-sans text-color-2" style="<?= empty($cartitems) ? '' : 'display:none;' ?>">
                    Ostoskorisi on tyhjä.
                </p>
                <?php if (!empty($cartitems)): ?>
                <table id="cart-table" class="table table-bordered zalando-sans">
                    <thead>
                        <tr>
                            <th>Tuote</th>
                            <th>Hinta</th>
                            <th>Määrä</th>
                            <th>Yhteensä</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                    <?php foreach ($cartitems as $item): ?>
                        <tr id="row-<?= $item['kori_tuotteetid'] ?>">
                            <td><?= htmlspecialchars($item['nimi']) ?></td>
                            <td><?= number_format($item['hinta'], 2) ?> €</td>
                            <td>
                                <button class="btn btn-default btn-sm minus" data-id="<?= $item['kori_tuotteetid'] ?>">-</button>
                                <span id="qty-<?= $item['kori_tuotteetid'] ?>"><?= $item['maara'] ?></span>
                                <button class="btn btn-default btn-sm plus"
                                        data-id="<?= $item['kori_tuotteetid'] ?>"
                                        data-stock="<?= $item['varastossa'] ?>">+</button>
                            </td>
                            <td>
                                <span id="total-<?= $item['kori_tuotteetid'] ?>">
                                    <?= number_format($item['maara'] * $item['hinta'], 2) ?>
                                </span> €
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <table id="cart-table" class="table table-bordered zalando-sans">
                    <tr>
                        <thead>
                            <th>
                                Tilauksen yhteenveto
                            </th>
                        </thead>
                    </tr>
                    <tr>
                        <th>Yhteensä</th>
                        <th><span id="grand-total"><?= number_format($totalprice, 2) ?></span> €</th>
                    </tr>
                </table>
                <button class="button-1 btn btn-lg btn-block zalando-sans"
                        id="checkout-btn"
                        <?= empty($cartitems) ? 'disabled' : '' ?>>
                    Kassalle
                </button>
            </div>
        </div>
    </div>
</div>
<div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
    <div class="carousel-inner">
        <div class="item active">
            <img src="images/bg-2.jpeg">
        </div>
        <div class="item">
            <img src="images/bg-1.jpeg">
        </div>
        <div class="item">
            <img src="images/bg-3.jpeg">
        </div>
        <div class="item">
            <img src="images/bg-4.jpeg">
        </div>
    </div>
</div>
<script>
function checkIfCartEmpty() {
    let rows = $('#cart-body tr').length;
    if (rows === 0) {
        $('.empty-cart-message').show();
        $('#checkout-btn').prop('disabled', true);
        $('#cart-table').remove();
    }
}

$(document).ready(function() {
    function updateTotals(cartitemid, newqty, price) {
        $('#qty-' + cartitemid).text(newqty);
        $('#total-' + cartitemid).text((newqty * price).toFixed(2));

        let grandtotal = 0;
        $('td span[id^="total-"]').each(function() {
            grandtotal += parseFloat($(this).text());
        });
        $('#grand-total').text(grandtotal.toFixed(2));
    }


        $('.plus').click(function() {
            let cartitemid = $(this).data('id');
            let stock = parseInt($(this).data('stock'));
            let qty = parseInt($('#qty-' + cartitemid).text());

            $.post('kori_paivitys.php', {action: 'add', cartitemid: cartitemid}, function(data) {
                if (data.success) {
                    qty = data.qty;
                    let price = parseFloat(data.price) || 0;
                    updateTotals(cartitemid, qty, price);
                    toggleButtons(cartitemid, qty, stock);
                } else {
                    alert(data.message);
                    toggleButtons(cartitemid, qty, stock);
                }
            }, 'json');
        });

        $('.minus').click(function() {
            let cartitemid = $(this).data('id');
            let stock = parseInt($('.plus[data-id="' + cartitemid + '"]').data('stock'));
            let qty = parseInt($('#qty-' + cartitemid).text());

            $.post('kori_paivitys.php', {action: 'remove', cartitemid: cartitemid}, function(data) {
                if (data.success) {
                    qty = data.qty;
                    let price = parseFloat(data.price) || 0;
                    updateTotals(cartitemid, qty, price);

                    if (qty === 0) {
                    $('#row-' + cartitemid).remove();
                    checkIfCartEmpty();
                }
                    toggleButtons(cartitemid, qty, stock);

                    checkIfCartEmpty();
                } else {
                    alert(data.message);
                }
            }, 'json');
        });
});


</script>

</body>
</html>