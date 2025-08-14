<?php
namespace Test\Model;

class Log
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    private function readCsv()
    {
        if (!file_exists($this->file)) {
            return [];
        }
        $rows = array_map('str_getcsv', file($this->file));
        if (empty($rows) || count($rows) < 2) {
            return [];
        }
        $header = array_map('trim', $rows[0]);
        $data = [];
        foreach (array_slice($rows, 1) as $row) {
            if (count($header) === count($row)) {
                $data[] = array_combine($header, $row);
            }
        }
        return $data;
    }

    private function writeCsv($data)
    {
        $fp = fopen($this->file, 'w');
        fputcsv($fp, ['username', 'date', 'action']);
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }

    public function addLog($username, $action)
    {
        $logs = $this->readCsv();
        $logs[] = [
            'username' => $username,
            'date' => date('Y-m-d H:i:s'),
            'action' => $action
        ];
        $this->writeCsv($logs);
    }

    public function getLogs()
    {
        return $this->readCsv();
    }

    public function getLogsByUser($username, $limit = 5)
    {
        $logs = $this->readCsv();
        $userLogs = array_filter($logs, fn($row) => $row['username'] === $username);
        usort($userLogs, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
        return array_slice($userLogs, 0, $limit);
    }
}
