<?php
session_start();

session_unset();
session_destroy();

session_start();

header("Location: tyontekija_kirjautuminen.php");
exit;