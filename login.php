<?php
session_start();

// Verbindung zur MAMP MySQL-Datenbank
$db_host = 'localhost';  // Standard-Host für MAMP
$db_user = 'root';       // Standard-MAMP-Benutzer
$db_pass = 'root';       // Standard-MAMP-Passwort
$db_name = 'user_management';  // Der Name Ihrer Datenbank

// Verbindung herstellen
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Überprüfen der Verbindung
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$error = ''; // Variable für Fehlermeldungen

// Wenn das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Eingaben bereinigen
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Benutzerdaten aus der Datenbank abfragen
    $query = "SELECT id, username, password FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Überprüfen, ob das Passwort korrekt ist
        if (password_verify($password, $user['password'])) {
            // Erfolgreiche Anmeldung
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Falsches Passwort!";
        }
    } else {
        $error = "Benutzername existiert nicht.";
    }
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 300px;
            margin: auto;
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error != ''): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <input type="text" id="username" name="username" placeholder="Benutzername" required>
            <input type="password" id="password" name="password" placeholder="Passwort" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
