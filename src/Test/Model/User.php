<?php
namespace Test\Model;

class User {
    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function getUserByUsername($username) {
        $rows = $this->readCSV();
        foreach ($rows as $row) {
            if ($row['username'] === $username) {
                return $row;
            }
        }
        return false;
    }

    public function incrementFailedAttempts($username) {
        $rows = $this->readCSV();
        foreach ($rows as &$row) {
            if ($row['username'] === $username) {
                $row['failed'] = intval($row['failed']) + 1;
            }
        }
        $this->writeCSV($rows);
    }

    public function resetFailedAttempts($username) {
        $rows = $this->readCSV();
        foreach ($rows as &$row) {
            if ($row['username'] === $username) {
                $row['failed'] = 0;
            }
        }
        $this->writeCSV($rows);
    }

    public function blockUser($username) {
        $rows = $this->readCSV();
        foreach ($rows as &$row) {
            if ($row['username'] === $username) {
                $row['blocked'] = 1;
            }
        }
        $this->writeCSV($rows);
    }

    public function updateLastLogin($username) {
        $rows = $this->readCSV();
        foreach ($rows as &$row) {
            if ($row['username'] === $username) {
                $row['lastlogin'] = date("Y-m-d H:i:s");
            }
        }
        $this->writeCSV($rows);
    }

    private function readCSV() {
        $rows = [];
        if (($handle = fopen($this->file, 'r')) !== false) {
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = array_combine($header, $data);
            }
            fclose($handle);
        }
        return $rows;
    }

    private function writeCSV($rows) {
        if (($handle = fopen($this->file, 'w')) !== false) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }
    }
}
