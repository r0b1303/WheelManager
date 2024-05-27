<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Nicht eingeloggt, leite zur Login-Seite um
    header("Location: login.php");
    exit;
}

// Überprüfen, ob der angemeldete Benutzer der Admin ist
if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
  // Weiterleitung zum Admin-Panel
  header("Location: admin_panel.php");
  exit;
}

// Verbindung zur Datenbank herstellen
$db_host = 'localhost';  
$db_user = 'root';       
$db_pass = 'root';       
$db_name = 'user_management';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Überprüfen der Verbindung
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Eingabe verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reifentyp = $conn->real_escape_string($_POST['reifentyp']);
    $anzahl = intval($_POST['anzahl']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO reifenwechsel (user_id, reifentyp, anzahl) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $reifentyp, $anzahl);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<p>Daten erfolgreich eingetragen!</p>";
    } else {
        echo "<p>Fehler beim Eintragen der Daten.</p>";
    }
    $stmt->close();
}

// Reifensummen abrufen
$autoQuery = "SELECT SUM(anzahl) AS total_auto FROM reifenwechsel WHERE user_id = ? AND reifentyp = 'Auto'";
$motoQuery = "SELECT SUM(anzahl) AS total_moto FROM reifenwechsel WHERE user_id = ? AND reifentyp = 'Motorrad'";
$stmt = $conn->prepare($autoQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$autoSum = $result->fetch_assoc()['total_auto'] ?? 0;

$stmt = $conn->prepare($motoQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$motoSum = $result->fetch_assoc()['total_moto'] ?? 0;

$autoCost = $autoSum * 3.50; // Kosten für Autoreifen
$motoCost = $motoSum * 2.50; // Kosten für Motorradreifen

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reifenverwaltung</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        form {
            margin-top: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .logout {
            color: red;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .summary {
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="summary">
            <p>Autoreifen: <?php echo $autoSum; ?> Stück (<?php echo number_format($autoCost, 2, ',', '.'); ?> €)</p>
            <p>Motorradreifen: <?php echo $motoSum; ?> Stück (<?php echo number_format($motoCost, 2, ',', '.'); ?> €)</p>
        </div>
        <form action="index.php" method="post">
            <label for="reifentyp">Reifentyp:</label>
            <select id="reifentyp" name="reifentyp">
                <option value="Motorrad">Motorradreifen</option>
                <option value="Auto">Autoreifen</option>
            </select>
            <label for="anzahl">Anzahl:</label>
            <input type="number" id="anzahl" name="anzahl" min="1" required>
            <input type="submit" value="Einreichen">
        </form>
        <a href="logout.php" class="logout">Abmelden</a>
    </div>
</body>
</html>
