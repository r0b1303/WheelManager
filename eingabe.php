<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Verbindung zur Datenbank herstellen
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'user_management';
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Eingabe verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reifentyp = $_POST['reifentyp'];
    $anzahl = $_POST['anzahl'];
    $user_id = $_SESSION['user_id']; // User-ID aus der Session holen

    $stmt = $conn->prepare("INSERT INTO reifenwechsel (user_id, reifentyp, anzahl) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $reifentyp, $anzahl);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Daten erfolgreich eingetragen!";
    } else {
        $message = "Fehler beim Eintragen der Daten.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reifeneingabe</title>
</head>
<body>
    <h1>Reifeneingabe</h1>
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="eingabe.php" method="post">
        <label for="reifentyp">Reifentyp:</label>
        <select id="reifentyp" name="reifentyp">
            <option value="Motorrad">Motorradreifen</option>
            <option value="Auto">Autoreifen</option>
        </select>
        <br>
        <label for="anzahl">Anzahl:</label>
        <input type="number" id="anzahl" name="anzahl" min="1" required>
        <br>
        <input type="submit" value="Einreichen">
    </form>
</body>
</html>
