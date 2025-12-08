<?php
// ===========================================
// COOKIE CONSENT – SERVER-SIDE CHECK
// ===========================================
// $consentGiven = true, jos käyttäjä on tehnyt valinnan (hyväksynyt tai hylännyt)
$consentGiven = isset($_COOKIE['cookie_consent']);

// $cookieAccepted = true, jos käyttäjä on hyväksynyt evästeet
$cookieAccepted = isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accepted';
?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Cookie Consent</title>
<style>
/* Overlay tausta */
.cookie-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

.cookie-overlay.hidden {
    display: none;
}

/* Modal keskellä */
.cookie-modal {
    background: #ffffff;
    padding: 25px;
    border-radius: 5px;
    max-width: 400px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    font-family: Arial, sans-serif;
}

.button-group {
    margin-top: 15px;
}

.cc-btn {
    margin: 5px;
    padding: 10px 18px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.cc-btn.accept {
    background-color: #4caf50;
    color: white;
    filter: brightness(95%);
}

.cc-btn.accept:hover {
    transition-duration: 0.4s;
    background-color: #39853aff;
}

.cc-btn.reject {
    background-color: #f44336;
    color: white;
    filter: brightness(95%);
}

.cc-btn.reject:hover {
    transition-duration: 0.4s;
    background-color: #c42e23ff;
}
</style>
</head>
<body>

<?php if (!$consentGiven): ?> 
<!-- MODAALI COOKIE BANNER -->
<div id="cookie-overlay" class="cookie-overlay">
    <div id="cookie-modal" class="cookie-modal">
        <img src="../images/logo.png" style="filter: brightness(0%); width: 50px;" alt="Yrityksen logo">
        <br>
        <h2 class="zalando-sans text-color-2">Tarvitsemme suostumuksesi</h2>
        <p class="zalando-sans text-color-2">Käytämme evästeitä parantaaksemme sivuston käyttökokemusta.</p>
        <div class="button-group" style="text-align: center;">
            <button id="reject-cookies" class="cc-btn reject zalando-sans">Hylkää kaikki</button>
            <button id="accept-cookies" class="cc-btn accept zalando-sans">Hyväksy kaikki</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// ===========================================
// Evästeisiin perustuvat skriptit
// ===========================================
if ($cookieAccepted) {
    // Hyväksytty → ladataan analytiikka, mainokset tms.
    echo '<script>console.log("Evästeet hyväksytty, ladataan skriptit...");</script>';
    // Esimerkki: Google Analytics
    // echo '<script src="analytics.js"></script>';
}
?>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const overlay = document.getElementById("cookie-overlay");

    if (overlay) {
        // Estetään scrollaus modalin aikana
        document.body.style.overflow = 'hidden';
    }

    function hideBanner() {
        overlay.classList.add("hidden");
        document.body.style.overflow = 'auto';
    }

    // HYVÄKSY
    const acceptBtn = document.getElementById("accept-cookies");
    if (acceptBtn) {
        acceptBtn.addEventListener("click", () => {
            document.cookie = "cookie_consent=accepted; path=/; max-age=" + 60*60*24*365;
            hideBanner();
            loadCookiesScripts();
        });
    }

    // HYLKÄÄ
    const rejectBtn = document.getElementById("reject-cookies");
    if (rejectBtn) {
        rejectBtn.addEventListener("click", () => {
            document.cookie = "cookie_consent=rejected; path=/; max-age=" + 60*60*24*365;
            hideBanner();
        });
    }

    // Funktio evästeisiin perustuville skripteille
    function loadCookiesScripts() {
        if (document.cookie.includes("cookie_consent=accepted")) {
            console.log("Evästeet hyväksytty, ladataan skriptit...");
            // Esimerkki: Google Analytics
            // let script = document.createElement('script');
            // script.src = "analytics.js";
            // document.head.appendChild(script);
        }
    }
});
</script>

</body>
</html>
