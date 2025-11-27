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
    <link rel="stylesheet" href="css/styles.css">

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
    <div class="glass-card">
        <div class="content">
            <div class="header-row-1">
                <form class="form-3" method="POST" action="">
                    <h1 class="tauri-regular text-color-2">
                        <img class="img-logo-2" src="images/logo.png">MAKSUTIEDOT
                    </h1>
                    <br>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px;" type="text" placeholder="Kortinhaltijan nimi" required>
                    <br>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px;" id="card" type="text" placeholder="0000-0000-0000-0000" maxlength="19" inputmode="numeric" required>
                    <br>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 110px;" id="exp" type="text" placeholder="KK/VV" maxlength="5" required>
                    <input class="zalando-sans text-color-2" style="width: 110px;" id="cvc" type="text" inputmode="numeric" maxlength="3" placeholder="CVC" required>
                    <br>
                    <br>
                    <select class="zalando-sans text-color-2" style="width: 110px; padding: 5px; color: gray; border: solid black 2px; border-radius: 5px;" id="toimitustapa" name="toimitustapa">
                        <option value="nouto">Nouto</option>
                        <option value="kuljetus">Kuljetus</option>
                    </select>
                    <br>
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px; display:none;" id="osoite" type="text" placeholder="Kotiosoite">
                    <br>
                    <input class="zalando-sans text-color-2" style="width: 225px; display:none;" id="kaupunki" type="text" placeholder="Kaupunki">
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
                <img src="images/bg-4.jpeg">
            </div>
            <div class="item">
                <img src="images/bg-1.jpeg">
            </div>
            <div class="item">
                <img src="images/bg-2.jpeg">
            </div>
            <div class="item">
                <img src="images/bg-3.jpeg">
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

        document.getElementById('toimitustapa').addEventListener('change', function () {
            const osoite = document.getElementById('osoite');
            const kaupunki = document.getElementById('kaupunki');

            if (this.value === 'kuljetus') {
                osoite.style.display = 'block';
                kaupunki.style.display = 'block';
                osoite.required = true;
                kaupunki.required = true;
            } else {
                osoite.style.display = 'none';
                kaupunki.style.display = 'none';
                osoite.required = false;
                kaupunki.required = false;
            }
        });
    </script>
</body>
</html>