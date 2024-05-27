<?php
session_start();

// Überprüfen, ob der Benutzer als Admin eingeloggt ist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    // Nicht eingeloggt oder kein Admin, leite zur Login-Seite um
    header("Location: login.php");
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

// Erweiterte Datenabfrage mit SQL JOIN, um Benutzernamen zu erhalten
$query = "SELECT users.username, reifenwechsel.reifentyp, reifenwechsel.anzahl FROM reifenwechsel JOIN users ON reifenwechsel.user_id = users.id";
$result = $conn->query($query);

if ($result === false) {
    die("Fehler bei der Abfrage: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reifenverwaltung</title>
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
            max-width: 800px;
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
        h1 {
            color: #333;
            text-align: center;
        }
        .logout {
            color: red;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin - Reifenverwaltung</h1>
        <table>
            <thead>
                <tr>
                    <th>Benutzername</th>
                    <th>Reifentyp</th>
                    <th>Anzahl</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['reifentyp']); ?></td>
                    <td><?php echo htmlspecialchars($row['anzahl']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="logout.php" class="logout">Abmelden</a>
    </div>
</body>
</html>
<?php
$conn->close();
?>
