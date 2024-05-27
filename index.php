<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    header("Location: admin_panel.php");
    exit;
}

$db_host = 'localhost';  
$db_user = 'root';       
$db_pass = 'root';       
$db_name = 'user_management';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$displayMessage = false;  // Flag to control message display
$success = false;         // Flag to determine message type

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reifentyp = $conn->real_escape_string($_POST['reifentyp']);
    $anzahl = intval($_POST['anzahl']);
    $reifen_beschreibung = isset($_POST['reifen_beschreibung']) ? $conn->real_escape_string($_POST['reifen_beschreibung']) : '';
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO reifenwechsel (user_id, reifentyp, anzahl, reifen_beschreibung) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $reifentyp, $anzahl, $reifen_beschreibung);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $displayMessage = true;
        $success = true;
    } else {
        $displayMessage = true;
        $success = false;
    }
    $stmt->close();
}

$historyQuery = "SELECT reifentyp, anzahl, reifen_beschreibung, Datum FROM reifenwechsel WHERE user_id = ? ORDER BY Datum DESC";
$stmt = $conn->prepare($historyQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$historyResult = $stmt->get_result();

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
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: auto;
        }
        .container, .history {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            margin-bottom: 20px;
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        form, .history-data {
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        #message-container {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            z-index: 1000;
        }
        .history-data {
        overflow-x: auto; /* Ermöglicht horizontales Scrollen innerhalb der history-data */
        }
        table {
            width: 100%;
            min-width: 600px; /* Stellt sicher, dass die Tabelle nicht zu stark zusammengedrückt wird */
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function showMessage(success) {
            var message = success ? "Daten erfolgreich eingetragen!" : "Fehler beim Eintragen der Daten.";
            var color = success ? "green" : "red";
            var msgContainer = document.getElementById('message-container');
            msgContainer.textContent = message;
            msgContainer.style.color = color;
            msgContainer.style.display = 'block';
            setTimeout(function() {
                msgContainer.style.display = 'none';
            }, 4000); // Nachricht nach 4 Sekunden ausblenden
        }
    </script>
</head>
<body>
    <?php if($displayMessage): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showMessage(<?php echo $success ? 'true' : 'false'; ?>);
        });
    </script>
    <?php endif; ?>
    <div id="message-container"></div>
    <div class="container">
        <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <form action="index.php" method="post">
            <label for="reifentyp">Reifentyp:</label>
            <select id="reifentyp" name="reifentyp">
                <option value="Motorrad">Motorradreifen</option>
                <option value="Auto">Autoreifen</option>
            </select>
            <label for="anzahl">Anzahl:</label>
            <input type="number" id="anzahl" name="anzahl" min="1" required>
            <label for="reifen_beschreibung">Beschreibung (optional):</label>
            <input type="text" id="reifen_beschreibung" name="reifen_beschreibung">
            <input type="submit" value="Einreichen">
        </form>

        <a href="logout.php" class="logout">Abmelden</a>
    </div>
    <div class="history">
        <h2>Reifenverlauf</h2>
        <div class="history-data">
            <table>
                <thead>
                    <tr>
                        <th>Reifentyp</th>
                        <th>Anzahl</th>
                        <th>Beschreibung</th>
                        <th>Datum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $historyResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['reifentyp']); ?></td>
                        <td><?php echo htmlspecialchars($row['anzahl']); ?></td>
                        <td><?php echo htmlspecialchars($row['reifen_beschreibung'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars((new DateTime($row['Datum']))->format('H:i \U\h\r \a\m d.m.Y')); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
