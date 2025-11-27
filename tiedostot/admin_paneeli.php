<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_kirjautuminen.php");
    exit;
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
// Fetch users
$users = [];
$stmt = $conn->prepare("SELECT asiakasid, etunimi, sukunimi, puhelinnumero, sahkoposti FROM vkauppa_asiakas");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $users[] = $row;

// Fetch workers
$workers = [];
$stmt = $conn->prepare("SELECT tyontekijaid, kayttajanimi, salasana, rooli FROM vkauppa_tyontekija");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $workers[] = $row;

// Fetch products
$products = [];
$stmt = $conn->prepare("SELECT tuoteid, nimi, hinta, kuvaus, kuva, varastossa, luokka, aktiivinen FROM vkauppa_tuotteet");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $products[] = $row;

if (isset($_POST['delete_type']) && isset($_POST['delete_id'])) {
    $type = $_POST['delete_type'];
    $id = intval($_POST['delete_id']);

    if ($type === "user") {

        // 1. Delete cart items
        $stmt = $conn->prepare("
            DELETE kt FROM vkauppa_kori_tuotteet kt
            JOIN vkauppa_ostoskori o ON kt.ostoskoriid = o.ostoskoriid
            WHERE o.asiakasid = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // 2. Delete carts
        $stmt = $conn->prepare("DELETE FROM vkauppa_ostoskori WHERE asiakasid = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // 3. Delete user
        $stmt = $conn->prepare("DELETE FROM vkauppa_asiakas WHERE asiakasid = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    elseif ($type === "worker") {
        $stmt = $conn->prepare("DELETE FROM vkauppa_tyontekija WHERE tyontekijaid = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    elseif ($type === "product") {
      $stmt = $conn->prepare("UPDATE vkauppa_tuotteet SET aktiivinen = 0 WHERE tuoteid = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
    }

    $redirect_tab = isset($_POST['redirect_tab']) ? $_POST['redirect_tab'] : 'users';
    header("Location: admin_paneeli.php?tab=" . urlencode($redirect_tab));
    exit;
}


?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Paneeli</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<style>
.sidebar { position: fixed; left: 0; width: 200px; height: 100%; background: #f8f9fa; padding: 20px; border-right: 1px solid #ddd; }
.content { margin-left: 220px; padding: 20px; }
.sidebar a { display: block; padding: 10px 0; color: #333; text-decoration: none; }
.sidebar a.active { font-weight: bold; color: #007bff; }
.table img { max-width: 50px; }
</style>
</head>
<body>
<div class="sidebar sidebar-inverse">
  <h3>Admin Paneeli</h3>
  <a href="#" class="nav-link" data-tab="users">Käyttäjät</a>
  <a href="#" class="nav-link" data-tab="workers">Työntekijät</a>
  <a href="#" class="nav-link" data-tab="products">Tuotteet</a>
  <a href="kirjaudu_ulos_admin.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a>
</div>

<div class="content">
  <!-- Users Tab -->
  <div id="users" class="tab-content">
    <h2 >Käyttäjät</h2>
    <table class="table table-bordered table-striped">
      <thead>
        <tr><th>ID</th><th>Etunimi</th><th>Sukunimi</th><th>Puhelinnumero</th><th>Sähköposti</th><th>Toiminta</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['asiakasid'] ?></td>
          <td><?= htmlspecialchars($u['etunimi']) ?></td>
          <td><?= htmlspecialchars($u['sukunimi']) ?></td>
          <td><?= htmlspecialchars($u['puhelinnumero']) ?></td>
          <td><?= htmlspecialchars($u['sahkoposti']) ?></td>
          <td>
          <form method="POST" onsubmit="return confirm('Poista tämä käyttäjä?');">
            <input type="hidden" name="delete_type" value="user">
            <input type="hidden" name="delete_id" value="<?= $u['asiakasid'] ?>">
            <input type="hidden" name="redirect_tab" value="users">
            <button class="btn btn-danger btn-sm">Poista</button>
          </form>
        </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Workers Tab -->
  <div id="workers" class="tab-content" style="display:none;">
    <h2>Työntekijät
      <button style="margin-left: 20px;" class="btn btn-success mb-3" data-toggle="modal" data-target="#addWorkerModal">
        ➕ Lisää työntekijä
      </button>
    </h2>
    <table class="table table-bordered table-striped">
      <thead>
        <tr><th>ID</th><th>Käyttäjänimi</th><th>Rooli</th><th>Toiminta</th></tr>
      </thead>
      <tbody>
        <?php foreach ($workers as $w): ?>
        <tr>
          <td><?= $w['tyontekijaid'] ?></td>
          <td><?= htmlspecialchars($w['kayttajanimi']) ?></td>
          <td><?= htmlspecialchars($w['salasana']) ?></td>
          <td><?= htmlspecialchars($w['rooli']) ?></td>
          <td>
          <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            <form method="POST" onsubmit="return confirm('Poista tämä työntekijä?');">
              <input type="hidden" name="delete_type" value="worker">
              <input type="hidden" name="delete_id" value="<?= $w['tyontekijaid'] ?>">
              <input type="hidden" name="redirect_tab" value="workers">
              <button class="btn btn-danger btn-sm">Poista</button>
            </form>
            <button class="btn btn-primary btn-sm" 
                    data-toggle="modal" 
                    data-target="#editWorkerModal" 
                    data-id="<?= $w['tyontekijaid'] ?>" 
                    data-username="<?= htmlspecialchars($w['kayttajanimi']) ?>"
                    data-password="<?= htmlspecialchars($w['salasana']) ?>"
                    data-role="<?= htmlspecialchars($w['rooli']) ?>">
              Muokkaa
            </button>
          </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- Add Worker Modal -->
    <div class="modal fade" id="addWorkerModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="admin_toiminnot.php">
            <input type="hidden" name="edit_id" id="addWorker">

            <div class="modal-header">
              <h4 class="modal-title">Lisää työntekijä</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
              <input type="hidden" name="add_type" value="worker">

              <div class="form-group">
                <label>Käyttäjänimi</label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="form-group">
                <label>Salasana</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <?php
                  $enumQuery = $conn->query("SHOW COLUMNS FROM vkauppa_tyontekija LIKE 'rooli'");
                  $enumRow = $enumQuery->fetch_assoc();

                  $enumValues = [];
                  if (preg_match("/^enum\('(.*)'\)$/", $enumRow['Type'], $matches)) {
                      $enumValues = explode("','", $matches[1]);
                }
              ?>
                <div class="form-group">
                    <label>Rooli</label>
                    <select name="role" class="form-control">
                      <option value=""></option>
                        <?php foreach ($enumValues as $val): ?>
                            <option value="<?= htmlspecialchars($val) ?>">
                                <?= htmlspecialchars($val) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary">Tallenna</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Sulje</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Edit Worker Modal -->
    <div class="modal fade" id="editWorkerModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="admin_toiminnot.php">
            <input type="hidden" name="redirect_tab" id="editWorkerRedirectTab" value="workers">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Muokkaa työntekijää</h4>
            </div>
            <div class="modal-body">
              <input type="hidden" name="edit_type" value="worker">
              <input type="hidden" name="edit_id" id="editWorkerId">
              <div class="form-group">
                <label>Käyttäjänimi</label>
                <input type="text" class="form-control" name="username" id="editWorkerUsername">
              </div>
              <div class="form-group">
                <label>Salasana</label>
                <input type="text" class="form-control" name="password" placeholder="Jätä tyhjäksi jos ei muutosta">
              </div>
                <?php
                  $enumQuery = $conn->query("SHOW COLUMNS FROM vkauppa_tyontekija LIKE 'rooli'");
                  $enumRow = $enumQuery->fetch_assoc();

                  $enumValues = [];
                  if (preg_match("/^enum\('(.*)'\)$/", $enumRow['Type'], $matches)) {
                      $enumValues = explode("','", $matches[1]);
                }
                ?>
                <div class="form-group">
                    <label>Rooli</label>
                    <select name="role" class="form-control" id="editWorkerRole">
                        <?php foreach ($enumValues as $val): ?>
                            <option value="<?= htmlspecialchars($val) ?>">
                                <?= htmlspecialchars($val) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Tallenna</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Sulje</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Products Tab -->
  <div id="products" class="tab-content" style="display:none;">
    <h2>Tuotteet
      <button style="margin-left: 20px;" class="btn btn-success mb-3" data-toggle="modal" data-target="#addProductModal">
        ➕ Lisää tuote
      </button>
    </h2>
    <table class="table table-bordered table-striped">
      <thead>
        <tr><th>ID</th><th>Nimi</th><th>Hinta</th><th>Stock</th><th>Luokka</th><th>Tila</th><th>Kuvaus</th><th>Kuva</th><th>Toiminta</th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><?= $p['tuoteid'] ?></td>
          <td><?= htmlspecialchars($p['nimi']) ?></td>
          <td>€<?= $p['hinta'] ?></td>
          <td><?= $p['varastossa'] ?></td>
          <td><?= htmlspecialchars($p['luokka']) ?></td>
          <td><?= htmlspecialchars($p['aktiivinen']) == 1 ? 'Aktiivinen' : 'Ei aktiivinen' ?></td>
          <td><?= htmlspecialchars($p['kuvaus']) ?></td>
          <td><img src="<?= htmlspecialchars($p['kuva']) ?>" alt="<?= htmlspecialchars($p['nimi']) ?>"></td>
          <td>
          <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            <form method="POST" onsubmit="return confirm('Poista tämä tuote?');">
              <input type="hidden" name="delete_type" value="product">
              <input type="hidden" name="delete_id" value="<?= $p['tuoteid'] ?>">
              <input type="hidden" name="redirect_tab" value="products">
              <button class="btn btn-danger btn-sm">Poista</button>
            </form>
            <button class="btn btn-primary btn-sm" 
                data-toggle="modal" 
                data-target="#editProductModal" 
                data-id="<?= $p['tuoteid'] ?>"
                data-name="<?= htmlspecialchars($p['nimi']) ?>"
                data-price="<?= $p['hinta'] ?>"
                data-stock="<?= $p['varastossa'] ?>"
                data-category="<?= htmlspecialchars($p['luokka']) ?>"
                data-active="<?= $p['aktiivinen'] ?>"
                data-description="<?= htmlspecialchars($p['kuvaus']) ?>"
                data-image="<?= htmlspecialchars($p['kuva']) ?>">
              Muokkaa
            </button>
          </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- Add Product Modal -->
      <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="admin_toiminnot.php">
              <input type="hidden" name="edit_id" id="addProduct">

              <div class="modal-header">
                <h4 class="modal-title">Lisää tuote</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>

              <div class="modal-body">
                <input type="hidden" name="add_type" value="product">

                <div class="form-group">
                  <label>Tuotenimi</label>
                  <input type="text" name="nimi" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Hinta (€)</label>
                  <input type="number" step="0.01" name="hinta" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Kuvaus</label>
                  <textarea name="kuvaus" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                  <label>Kuva URL</label>
                  <textarea name="kuva" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                  <label>Varastossa</label>
                  <input type="number" name="varastossa" class="form-control" required>
                </div>

                <?php
                $enumQuery = $conn->query("SHOW COLUMNS FROM vkauppa_tuotteet LIKE 'luokka'");
                $enumRow = $enumQuery->fetch_assoc();

                $enumValues = [];
                if (preg_match("/^enum\('(.*)'\)$/", $enumRow['Type'], $matches)) {
                    $enumValues = explode("','", $matches[1]);
                }
                ?>
                <div class="form-group">
                    <label>Luokka</label>
                    <select name="luokka" class="form-control">
                        <option value="">-- Valitse luokka --</option>
                        <?php foreach ($enumValues as $val): ?>
                            <option value="<?= htmlspecialchars($val) ?>">
                                <?= htmlspecialchars($val) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                  <label>Tila</label>
                  <select name="aktiivinen" class="form-control">
                    <option value="1">Aktiivinen</option>
                    <option value="0">Ei aktiivinen</option>
                  </select>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn btn-primary">Tallenna</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Sulje</button>
              </div>

            </form>
          </div>
        </div>
      </div>
      <!-- Edit Product Modal -->
      <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="admin_toiminnot.php">
              <input type="hidden" name="redirect_tab" id="editProductRedirectTab" value="products">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Muokkaa tuotetta</h4>
              </div>
              <div class="modal-body">
                <input type="hidden" name="edit_type" value="product">
                <input type="hidden" name="edit_id" id="editProductId">
                <div class="form-group">
                  <label>Nimi</label>
                  <input type="text" class="form-control" name="name" id="editProductName">
                </div>
                <div class="form-group">
                  <label>Hinta (€)</label>
                  <input type="number" step="0.01" class="form-control" name="price" id="editProductPrice">
                </div>
                <div class="form-group">
                  <label>Varastossa</label>
                  <input type="number" class="form-control" name="stock" id="editProductStock">
                </div>
                <div class="form-group">

                <?php
                $enumQuery = $conn->query("SHOW COLUMNS FROM vkauppa_tuotteet LIKE 'luokka'");
                $enumRow = $enumQuery->fetch_assoc();

                $enumValues = [];
                if (preg_match("/^enum\('(.*)'\)$/", $enumRow['Type'], $matches)) {
                    $enumValues = explode("','", $matches[1]);
                }
                ?>
                <div class="form-group">
                    <label>Luokka</label>
                    <select name="category" class="form-control" id="editProductCategory">
                        <?php foreach ($enumValues as $val): ?>
                            <option value="<?= htmlspecialchars($val) ?>">
                                <?= htmlspecialchars($val) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                  <label>Aktiivinen</label>
                  <select class="form-control" name="active" id="editProductActive">
                    <option value="1">Aktiivinen</option>
                    <option value="0">Ei aktiivinen</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Kuvaus</label>
                  <textarea class="form-control" name="description" id="editProductDescription" required></textarea>
                </div>
                <div class="form-group">
                  <label>Kuva URL</label>
                  <textarea class="form-control" name="image" id="editProductImage" required></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Tallenna</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Sulje</button>
              </div>
            </form>
          </div>
        </div>
      </div>
</div>
<script>
$(document).ready(function(){
    var activeTab = '<?= $active_tab ?>';

    $('.tab-content').hide();
    $('#' + activeTab).show();

    $('.sidebar a').removeClass('active');
    $('.sidebar a[data-tab="' + activeTab + '"]').addClass('active');

    $('.sidebar a').click(function(e){
        var tab = $(this).data('tab');
        if(tab){
            e.preventDefault(); 
            $('.sidebar a').removeClass('active');
            $(this).addClass('active');

            $('.tab-content').hide();
            $('#' + tab).show();
        }
    });
});

$('#editProductModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  $('#editProductId').val(button.data('id'));
  $('#editProductName').val(button.data('name'));
  $('#editProductPrice').val(button.data('price'));
  $('#editProductStock').val(button.data('stock'));
  $('#editProductCategory').val(button.data('category'));
  $('#editProductActive').val(button.data('active'));
  $('#editProductDescription').val(button.data('description'));
  $('#editProductImage').val(button.data('image'));

  // This ensures the redirect_tab is correct
  $('#editProductRedirectTab').val('products');
});

$('#editWorkerModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  $('#editWorkerId').val(button.data('id'));
  $('#editWorkerUsername').val(button.data('username'));
  $('#editWorkerPassword').val(button.data('password'));
  $('#editWorkerRole').val(button.data('role'));

  $('#editWorkerRedirectTab').val('workers');
});
</script>
</body>
</html>
