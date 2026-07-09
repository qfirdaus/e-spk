<?php

require_once __DIR__ . '/../../../controllers/KetuaJabatanController.php';

if (isset($_GET["term"])) {
    $term = $_GET["term"];

    $controller = new KetuaJabatanController();
    $response = $controller->searchStaf($term); 

    include __DIR__ . '/search-result-table.php';
}