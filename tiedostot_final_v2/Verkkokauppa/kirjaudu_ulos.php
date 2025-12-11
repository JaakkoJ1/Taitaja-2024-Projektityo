<?php
session_start();

session_unset();
session_destroy();

session_start();
$_SESSION['message'] = "Olet kirjautunut ulos onnistuneesti.";

header("Location: verkkokauppa.php");
exit;