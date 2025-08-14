<?php
session_start();

require '../../src/autoload.php';

use Test\Model\User;
use Test\Model\Log;

$userModel = new User('../../data/user.csv');
$logModel = new Log('../../data/log.csv');

$message = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $userModel->getUserByUsername($username);

    if ($user) {
        if ($user['blocked'] == 1) {
            $message = "Benutzer ist gesperrt!";
        } elseif ($user['password'] === $password) {
            $userModel->resetFailedAttempts($username);
            $userModel->updateLastLogin($username);
            $logModel->addLog($username, "Erfolgreich eingeloggt");

            $_SESSION['username'] = $username;

            header("Location: dashboard.php");
            exit;

        } else {
            $userModel->incrementFailedAttempts($username);
            $logModel->addLog($username, "Falsches Passwort");

            if ($user['failed'] + 1 >= 3) {
                $userModel->blockUser($username);
                $logModel->addLog($username, "Benutzer wurde gesperrt wegen zu vieler Fehlversuche beim Login");
                $message = "Benutzer wurde gesperrt!";
            } else {
                $message = "Falsches Passwort!";
            }
        }
    } else {
        $message = "Benutzer nicht gefunden!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="post">
        <label>Benutzername:</label><br>
        <input type="text" name="username"><br><br>

        <label>Passwort:</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit" style="background-color: #0077cc; color: white; border: none; padding: 8px 14px; border-radius: 4px;">
            Anmelden
        </button>
    </form>
</body>
</html>