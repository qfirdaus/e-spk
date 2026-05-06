<?php
// controllers/PenglibatanController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/Penglibatan.php';

class PenglibatanController
{
    private Penglibatan $model;
    private string $errorMessage = '';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $pdo = Database::pdoAdditional('dbx_mysql_istaddb', 'production');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new Penglibatan($pdo);
    }

    public function getAllPenglibatan(): array
    {
        try {
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

            return $this->model->getAllActive($matrik);

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function testConnection()
    {
        return $this->model->testConnection();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }    
}
