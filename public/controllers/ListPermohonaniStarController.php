<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/ListPermohonaniStar.php';

class ListPermohonaniStarController
{
    private ListPermohonaniStar $model;
    private PDO $pdoEhepa;

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
        $lang = $_SESSION['lang'] ?? 'ms';
        $this->pdoEhepa = Database::pdoMysql();
        $this->pdoEhepa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new ListPermohonaniStar($this->pdoEhepa);   
    }

    public function getAllPingatGraduan(): array
    {
        $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

        return $this->model->getListMohonPingatGraduan($matrik);    
    }

}