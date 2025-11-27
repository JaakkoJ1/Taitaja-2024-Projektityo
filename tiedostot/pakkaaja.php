<?php
session_start();
require 'db.php';

if (!isset($_SESSION['pakkaaja_id'])) {
    header("Location: tyontekija_kirjautuminen.php");
    exit;
}

$pakkaajaId = $_SESSION['pakkaaja_id'];
$message = "";

if (isset($_POST['accept_order'], $_POST['tilausid'])) {
    $tilausid = intval($_POST['tilausid']);

    $stmt = $conn->prepare("
        UPDATE vkauppa_tilaus 
        SET tila='pakkauksessa', pakkaajaid=? 
        WHERE tilausid=? AND tila='tilattu'
    ");
    $stmt->bind_param("ii", $pakkaajaId, $tilausid);
    $stmt->execute();

    $_SESSION['message'] = ($stmt->affected_rows > 0) ? 
        "✅ Tilaus #$tilausid hyväksytty!" : "❌ Tilaus on jo hyväksytty.";

    header("Location: " . $_SERVER['PHP_SELF']);
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

$omat = $conn->prepare("
    SELECT t.tilausid, t.ostoskoriid, t.tila, t.tilattu_aika,
           a.etunimi, a.sukunimi
    FROM vkauppa_tilaus t
    JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    JOIN vkauppa_asiakas a ON o.asiakasid = a.asiakasid
    WHERE t.tila='pakkauksessa' AND t.pakkaajaid=?
    ORDER BY t.tilattu_aika ASC
");
$omat->bind_param("i", $pakkaajaId);
$omat->execute();
$result = $omat->get_result();

?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pakkaaja - Tilaukset</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<style>
.sidebar { position: fixed; left: 0; width: 220px; height: 100%; background: #f8f9fa; padding: 20px; border-right: 1px solid #ddd; }
.content { margin-left: 220px; padding: 20px; }
.sidebar a { display: block; padding: 10px 0; color: #333; text-decoration: none; }
.sidebar a.active { font-weight: bold; color: #007bff; }
.table img { max-width: 50px; }
</style>
</head>
<body>

<div class="sidebar">
    <h3>Pakkaaja</h3>
    <div class="list-group">
        <a href="#" class="nav-link active" id="tab_tilattuja">Tilattuja tilauksia</a>
        <a href="#" class="nav-link" id="tab_omat">Omat hyväksytyt tilaukset</a>
        <a href="kirjaudu_ulos_tyontekija.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a>
    </div>
</div>

<div class="content">
    <?php if($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

        <div id="tilattuja_tab">
            <h2>Tilattuja tilauksia</h2>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tilaus ID</th>
                        <th>Etunimi</th>
                        <th>Sukunimi</th>
                        <th>Tilattu</th>
                        <th>Toiminta</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $tilattuja->fetch_assoc()): ?>
                    <tr id="tilaus-row-<?= $row['tilausid'] ?>">
                        <td><?= $row['tilausid'] ?></td>
                        <td><?= htmlspecialchars($row['etunimi']) ?></td>
                        <td><?= htmlspecialchars($row['sukunimi']) ?></td>
                        <td><?= htmlspecialchars($row['tilattu_aika']) ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="tilausid" value="<?= $row['tilausid'] ?>">
                                <button type="submit" name="accept_order" class="btn btn-primary btn-sm">
                                    ✔ Hyväksy
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>


    <div id="omat_tab" style="display:none;">
        <h2>Omat hyväksytyt tilaukset</h2>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tilaus ID</th>
                    <th>Etunimi</th>
                    <th>Sukunimi</th>
                    <th>Tilattu</th>
                    <th>Lisätiedot</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['tilausid'] ?></td>
                    <td><?= htmlspecialchars($row['etunimi']) ?></td>
                    <td><?= htmlspecialchars($row['sukunimi']) ?></td>
                    <td><?= htmlspecialchars($row['tilattu_aika']) ?></td>
                    <td>
                        <button class="btn btn-info btn-sm order-details-btn"
                                data-tilausid="<?= $row['tilausid'] ?>">
                            Näytä tiedot
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>


    <div id="order_details" style="margin-top:20px;"></div>
    </div>

<script>
$(document).ready(function() {

    // --- Tab switching ---
    $('#tab_tilattuja').click(function(){
        $(this).addClass('active');
        $('#tab_omat').removeClass('active');
        $('#tilattuja_tab').show();
        $('#omat_tab').hide();
    });

    $('#tab_omat').click(function(){
        $(this).addClass('active');
        $('#tab_tilattuja').removeClass('active');
        $('#tilattuja_tab').hide();
        $('#omat_tab').show();
    });

    // --- Load order details ---
    $(document).on('click', '.order-details-btn', function(){
        var tilausid = $(this).data('tilausid');
        $.ajax({
            url: 'pakkaaja_details.php',
            method: 'GET',
            data: { tilausid: tilausid },
            success: function(html){
                $('#order_details').html(html);
            },
            error: function(){
                alert('Virhe ladattaessa tilauksen tietoja.');
            }
        });
    });

    // --- Mark order as done ---
    $(document).on('click', '.mark-done-btn', function() {
        var tilausid = $(this).data('tilausid');
        var row = $('#omat_tab').find('tr').filter(function() {
            return $(this).find('td:first').text() == tilausid;
        });

        $.ajax({
            url: 'pakkaaja_details.php',
            method: 'POST',
            data: { mark_done: 1, tilausid: tilausid },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.remove();              // Remove row immediately
                    $('#order_details').html(''); // Clear details panel

                    $('<div class="alert alert-success">Tilaus #' + tilausid + ' merkitty valmiiksi.</div>')
                        .appendTo('.content')
                        .delay(3000)
                        .fadeOut(500, function(){ $(this).remove(); });
                } else {
                    alert(response.error || 'Tapahtui virhe!');
                }
            },
            error: function() {
                alert('Virhe palvelimella!');
            }
        });
    });

    // --- Auto fade out any alerts after 10 seconds ---
    setTimeout(function() {
        $(".alert").fadeOut(1000);
    }, 10000);

});
</script>

</body>
</html>
