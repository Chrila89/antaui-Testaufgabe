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
<body bgcolor="#b18ddfff">
    <h1 style="text-align: center;">Login</h1>

    <form action="login.php" method="post">
        <table align="center" cellpadding="5">
            <tr>
                <td><label for="username">Benutzername:</label></td>
                <td><input type="text" id="username" name="username"></td>
            </tr>
            <tr>
                <td><label for="password">Passwort:</label></td>
                <td><input type="password" id="password" name="password"></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <button type="submit">Anmelden</button>
                </td>
            </tr>
            <?php if(!empty($message)): ?>
            <tr>
                <td colspan="2" align="center" style="color: red;">
                    <?php echo htmlspecialchars($message); ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </form>
</body>
</html>