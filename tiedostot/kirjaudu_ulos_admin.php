<?php
session_start();

session_unset();
session_destroy();

session_start();

header("Location: admin_kirjautuminen.php");
exit;