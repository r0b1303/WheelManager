<?php
session_start(); // Startet die Session, um Zugriff auf die Session-Variablen zu haben

// Löscht alle Session-Variablen
session_unset();

// Zerstört die Session
session_destroy();

// Leitet den Benutzer zur Login-Seite um
header("Location: login.php");
exit;
?>
