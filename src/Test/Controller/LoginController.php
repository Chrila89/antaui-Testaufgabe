<?php
namespace Test\Controller;

use Test\Model\User;
use Test\Model\Log;

class LoginController {
    private User $userModel;
    private Log $logModel;

    public function __construct() {
        $this->userModel = new User('data/user.csv');
        $this->logModel = new Log('data/log.csv');
    }

    public function login(string $username, string $password): bool {
        $user = $this->userModel->getUserByUsername($username);

        if (!$user) {
            $this->logModel->addLog($username, "Login fehlgeschlagen: Benutzer existiert nicht");
            return false;
        }

        if ($user['blocked'] == 1) {
            $this->logModel->addLog($username, "Login fehlgeschlagen: Benutzer gesperrt");
            return false;
        }

        if ($user['password'] === $password) {
            $this->userModel->resetFailedAttempts($username);
            $this->userModel->updateLastLogin($username);
            $this->logModel->addLog($username, "Login erfolgreich");
            return true;
        } else {
            $this->userModel->incrementFailedAttempts($username);
            $this->logModel->addLog($username, "Login fehlgeschlagen: falsches Passwort");

            if (($user['failed'] + 1) >= 3) {
                $this->userModel->blockUser($username);
                $this->logModel->addLog($username, "Benutzer wurde gesperrt wegen zu vieler Fehlversuche beim Login");
            }
            return false;
        }
    }
}
