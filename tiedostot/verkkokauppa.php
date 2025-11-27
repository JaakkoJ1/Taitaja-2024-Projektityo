<?php
session_start();
require 'db.php';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$timeout_hours = 24;
$stmt = $conn->prepare("
    UPDATE vkauppa_ostoskori
    SET tila = 'luovutettu'
    WHERE tila = 'kaytossa'
      AND viimeksi_paivitetty < NOW() - INTERVAL $timeout_hours HOUR
");
$stmt->execute();

$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 24;
$allowedPerPage = [6, 12, 24, 33];

if (!in_array($perPage, $allowedPerPage)) {
    $perPage = 24;
}   
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT tuoteid, nimi, hinta, kuvaus, kuva, luokka, varastossa FROM vkauppa_tuotteet WHERE 1=1";

$conditions = [];
$params = [];
$types = "";

if ($search !== '') {
    $conditions[] = "nimi LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($category !== '') {
    $conditions[] = "luokka = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

$count_sql = str_replace("SELECT tuoteid, nimi, hinta, kuvaus, kuva, luokka", "SELECT COUNT(*) AS total", $sql);
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
$stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalProducts = $result->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

$start = ($page - 1) * $perPage;
$sql .= " ORDER BY nimi ASC LIMIT $start, $perPage";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$categories = [];
$catResult = $conn->query("SELECT DISTINCT luokka FROM vkauppa_tuotteet ORDER BY luokka ASC");
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row['luokka'];
}

$cartQuantities = [];

if ($userId) {
    $sqlCart = "SELECT tuoteid, maara FROM vkauppa_kori_tuotteet 
                INNER JOIN vkauppa_ostoskori USING (ostoskoriid)
                WHERE asiakasid = ? AND tila = 'kaytossa'";
    $stmtCart = $conn->prepare($sqlCart);
    $stmtCart->bind_param("i", $userId);
    $stmtCart->execute();
    $cartResult = $stmtCart->get_result();

    while ($row = $cartResult->fetch_assoc()) {
        $cartQuantities[$row['tuoteid']] = (int)$row['maara'];
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Taitaja 2024 Semifinaali - Tuotteet</title>

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

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
<style>
    body {
        background-color: white;
    }

    body {
        overflow-y: scroll;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    body::-webkit-scrollbar {
        display: none;
    }

    .product-img-wrapper {
        width: 100%;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f8f8;
        overflow: hidden;
    }

    .product-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .panel-body {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        height: 400px;
    }
</style>
</head>
<body>
<div class="test">
    <div class="header-row">
        <h1 class="tauri-regular text-color-1">
            <img class="img-logo" src="images/logo.png">TUOTTEET
        </h1>
        <div>
            <a style="margin-right: 20px; text-decoration: none;" class="zalando-sans button-2" href="index.html"><span class="glyphicon glyphicon-home"></span>Etusivu</a>
            <a style="margin-right: 20px; text-decoration: none;" class="zalando-sans button-2" href="asetukset.php">Asetukset</a>
            <a style="margin-right: 20px; text-decoration: none;" class="zalando-sans button-2" href="ostoskori.php"><span class="glyphicon glyphicon-shopping-cart"></span>Ostoskori</a>
            <?php if ($userId): ?>
                <a href="kirjaudu_ulos.php">
                    <button type="button" class="zalando-sans button-2" style="margin-right: 20px;"><span class="glyphicon glyphicon-log-out"></span>Kirjaudu ulos</button>
                </a>
            <?php else: ?>
                <a href="kirjautuminen.php">
                    <button type="button" class="zalando-sans button-2" style="margin-right: 20px;"><span class="glyphicon glyphicon-log-in"></span>Kirjaudu sisään</button>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-row-2">
        <div>
            <h1 class="tauri-regular text-color-1">Tervetuloa<br>verkkokauppaan</h1>
            <br>
            <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<div class="container" style="padding-top: 20px;">
    <form method="GET" class="form-inline">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="&#xf002; Hae tuotetta" class="form-control zalando-sans" style="font-family: FontAwesome, Arial; font-style: normal; margin-right: 5px;">
        <select name="category" class="form-control zalando-sans" style="margin-right: 5px;">
            <option value="">Kaikki luokat</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $cat === $category ? 'selected' : '' ?>><?= ucfirst(str_replace('-', ' ', $cat)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="perPage" class="form-control zalando-sans" style="margin-right: 15px;">
            <?php 
            foreach ([6, 12, 24, 33] as $opt) {
                $sel = ($opt == $perPage) ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt per sivu</option>";
            }
            ?>
        </select>
        <button type="submit" class="zalando-sans button-1" style="margin-right: 5px;">Suodata</button>
        <button type="submit" class="zalando-sans button-1" onclick="resetFilters()" style="margin-right: 5px;">Nollaa</button>
    </form>

    <div class="row" style="padding-top: 20px;">
        <?php foreach ($products as $product): ?>
            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><p class="zalando-sans"><strong><?= htmlspecialchars($product['nimi']) ?></strong></p></div>
                    <div class="panel-body">
                        <div class="product-img-wrapper">
                            <?php if (!empty($product['kuva'])): ?>
                                <img src="<?= htmlspecialchars($product['kuva']) ?>" class="product-img" alt="<?= htmlspecialchars($product['nimi']) ?>">
                            <?php else: ?>
                                <span>Ei kuvaa</span>
                            <?php endif; ?>
                        </div>
                        <br>
                        <p class="zalando-sans"><?= htmlspecialchars($product['kuvaus']) ?></p>
                        <p class="zalando-sans"><strong>Hinta:</strong> <?= number_format($product['hinta'], 2) ?> €</p>
                        <p class="zalando-sans"><strong>Luokka:</strong> <?= htmlspecialchars($product['luokka']) ?></p>
                    </div>
                    <div class="cart-controls text-center">
                        <button class="btn btn-default btn-sm minus" data-id="<?= $product['tuoteid'] ?>">-</button>
                        <?php
                            $qty = $cartQuantities[$product['tuoteid']] ?? 0;
                        ?>
                        <span class="quantity" id="qty-<?= $product['tuoteid'] ?>"><?= $qty ?></span>

                        <button 
                            class="btn btn-default btn-sm plus" 
                            data-id="<?= $product['tuoteid'] ?>" 
                            data-stock="<?= $product['varastossa'] ?>"
                            <?= ($qty >= $product['varastossa']) ? 'disabled' : '' ?>
                        >+</button>
                        <br><br>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($products)) echo "<p>Ei tuotteita saatavilla.</p>"; ?>
    </div>

    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="<?= $i == $page ? 'active' : '' ?>">
                        <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&perPage=<?= $perPage ?>&page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<div id="signin-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <h4 class="zalando-sans">Kirjaudu sisään</h4>
        <p class="zalando-sans">Lisätäksesi tuotteita ostoskoriin sinun täytyy kirjautua sisään.</p>
        <a href="kirjautuminen.php" class="zalando-sans link">Kirjaudu sisään</a> |
        <a href="rekisteroityminen.php" class="zalando-sans link">Luo tili</a>
        <button onclick="closeModal()" class="zalando-sans">Sulje</button>
    </div>
</div>

</body>
<script>    
let userId = <?= $userId ? $userId : 'null' ?>;

function resetFilters() {
    event.preventDefault();
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    url.searchParams.delete('category');
    url.searchParams.delete('perPage');

    window.location.href = url.toString();
}

function showModal() {
    document.getElementById('signin-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('signin-modal').style.display = 'none';
}

// + - buttons
document.querySelectorAll('.plus').forEach(button => {
    button.addEventListener('click', function() {
        if (!userId) { 
            showModal(); 
            return; 
        }

        let productId = this.dataset.id;
        let stock = parseInt(this.dataset.stock);
        let qtySpan = document.getElementById('qty-' + productId);
        let qty = parseInt(qtySpan.innerText);

        fetch('kori.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=add&productId=' + productId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                qtySpan.innerText = qty + 1;

            if (qty + 1 >= stock) {
                button.disabled = true;
                alert("Varastoraja saavutettu!");
            }

            } else {
                alert(data.message);
            }
        });
    });
});

document.querySelectorAll('.minus').forEach(button => {
    button.addEventListener('click', function() {
        if (!userId) return;

        let productId = this.dataset.id;
        let qtySpan = document.getElementById('qty-' + productId);
        let qty = parseInt(qtySpan.innerText);
        if (qty <= 0) return;

        fetch('kori.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=remove&productId=' + productId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                qtySpan.innerText = qty - 1;
                let plusBtn = document.querySelector('.plus[data-id="'+productId+'"]');
                if (plusBtn.disabled) plusBtn.disabled = false;
            } else {
                alert(data.message);
            }
        });
    });
});
</script>
</html>